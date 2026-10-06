<?php

namespace App\Services\Docs;

use App\Models\DocEntry;
use App\Models\DocPage;
use App\Models\DocSource;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * « Demander à l'IA » : on cherche d'abord dans la documentation, puis l'IA de
 * l'utilisateur rédige une réponse UNIQUEMENT à partir de ces extraits, avec les sources.
 */
class DocsAssistant
{
    private const MAX_SOURCES = 4;

    private const EXCERPT_CHARS = 3500;

    public function __construct(private DocsLibrary $library, private AiClient $ai)
    {
    }

    /**
     * @param  array<int, string>  $sourceKeys
     * @return array{answer: string, html: string, sources: array<int, array{n: int, title: string, source: string, url: string}>}
     *
     * @throws AiException
     */
    public function ask(User $user, string $question, array $sourceKeys = []): array
    {
        if (! $user->hasAiAssistant()) {
            throw new AiException('Ajoute d’abord ta clé d’IA dans Paramètres → Assistant IA.');
        }

        $excerpts = $this->retrieve($question, $sourceKeys);
        if ($excerpts->isEmpty()) {
            throw new AiException('Aucune page de documentation ne correspond à ta question. Reformule-la avec le nom d’une fonction, d’une balise ou d’une notion (ex. « Eloquent hasMany », « Array map »).');
        }

        $level = ['beginner' => 'débutant', 'intermediate' => 'intermédiaire', 'professional' => 'professionnel'][$user->learningProfile?->level] ?? 'intermédiaire';

        $system = <<<TXT
        Tu es l'assistant de documentation de DevRoad, une application d'apprentissage du développement.
        Réponds en français, de façon claire et concrète, pour un développeur de niveau {$level}.
        Appuie-toi UNIQUEMENT sur les extraits de documentation fournis. Cite tes sources avec leur numéro entre crochets, par exemple [1] ou [2].
        Si les extraits ne permettent pas de répondre, dis-le franchement et indique quoi chercher à la place ; n'invente rien.
        Format : Markdown court (titres ###, listes, blocs de code avec le langage). Pas d'introduction ni de conclusion inutiles.
        TXT;

        $context = $excerpts->map(fn ($excerpt) => "[{$excerpt['n']}] {$excerpt['title']} — {$excerpt['source']}\n{$excerpt['text']}")->implode("\n\n---\n\n");
        $prompt = "Extraits de documentation :\n\n{$context}\n\n---\n\nQuestion : {$question}";

        $answer = $this->ai->complete($user->ai_provider, $user->ai_api_key, $user->ai_model, $system, $prompt);

        return [
            'answer' => $answer,
            'html' => $this->renderAnswer($answer, $excerpts),
            'sources' => $excerpts->map(fn ($excerpt) => collect($excerpt)->only(['n', 'title', 'source', 'url'])->all())->values()->all(),
        ];
    }

    /** Pages les plus pertinentes, avec l'extrait qui contient le plus de mots de la question. */
    private function retrieve(string $question, array $sourceKeys): Collection
    {
        $words = $this->library->keywords($question);
        if ($words === []) {
            return collect();
        }

        $sources = DocSource::query()->when($sourceKeys, fn ($query) => $query->whereIn('key', $sourceKeys))->get()->keyBy('id');
        if ($sources->isEmpty()) {
            return collect();
        }

        // 1. Entrées d'index dont le nom contient un mot de la question, classées par nombre de mots trouvés.
        $candidates = DocEntry::query()
            ->whereIn('doc_source_id', $sources->keys())
            ->where(function ($builder) use ($words) {
                foreach ($words as $word) {
                    $builder->orWhere('search_name', 'like', '%' . addcslashes($word, '%_\\') . '%');
                }
            })
            ->limit(400)
            ->get()
            ->map(function (DocEntry $entry) use ($words) {
                $hits = collect($words)->filter(fn ($word) => str_contains($entry->search_name, $word))->count();

                return ['entry' => $entry, 'score' => $hits * 10 - min(mb_strlen($entry->search_name), 60) / 10];
            })
            ->sortByDesc('score');

        // 2. Pages déjà en cache dont le texte contient les mots.
        $pageHits = DocPage::query()
            ->whereIn('doc_source_id', $sources->keys())
            ->where(function ($builder) use ($words) {
                foreach ($words as $word) {
                    $builder->orWhereRaw('lower(text) like ?', ['%' . addcslashes($word, '%_\\') . '%']);
                }
            })
            ->limit(60)
            ->get(['id', 'doc_source_id', 'path', 'title', 'text'])
            ->map(fn (DocPage $page) => ['page' => $page, 'score' => collect($words)->sum(fn ($word) => min(substr_count(DocEntry::normalize($page->text), $word), 8))])
            ->sortByDesc('score');

        $picked = collect();
        foreach ($candidates as $candidate) {
            $key = $candidate['entry']->doc_source_id . ':' . $candidate['entry']->path;
            if (! $picked->has($key)) {
                $picked->put($key, ['source_id' => $candidate['entry']->doc_source_id, 'path' => $candidate['entry']->path, 'fragment' => $candidate['entry']->fragment]);
            }
            if ($picked->count() >= self::MAX_SOURCES - 1) {
                break;
            }
        }
        foreach ($pageHits as $hit) {
            if ($picked->count() >= self::MAX_SOURCES) {
                break;
            }
            $picked->put($hit['page']->doc_source_id . ':' . $hit['page']->path, ['source_id' => $hit['page']->doc_source_id, 'path' => $hit['page']->path, 'fragment' => null]);
        }

        $n = 0;

        return $picked->map(function ($item) use ($sources, $words, &$n) {
            $source = $sources[$item['source_id']];
            try {
                $page = $this->library->page($source, $item['path']);
            } catch (Throwable) {
                return null; // page injoignable : on continue avec les autres.
            }

            return [
                'n' => ++$n,
                'title' => $page->title ?: $item['path'],
                'source' => $source->name,
                'url' => '/docs/' . $source->key . '/' . $page->path . ($item['fragment'] ? '#' . $item['fragment'] : ''),
                'text' => $this->bestWindow($page->text, $words),
            ];
        })->filter()->values();
    }

    /** Fenêtre de texte qui contient le plus de mots de la question. */
    private function bestWindow(string $text, array $words): string
    {
        $text = trim(preg_replace("/[ \t]+/u", ' ', $text));
        if (mb_strlen($text) <= self::EXCERPT_CHARS) {
            return $text;
        }

        $normalized = DocEntry::normalize($text);
        $best = 0;
        $bestScore = -1;
        $step = 700;
        for ($start = 0; $start < mb_strlen($normalized); $start += $step) {
            $window = mb_substr($normalized, $start, self::EXCERPT_CHARS);
            $score = collect($words)->sum(fn ($word) => min(substr_count($window, $word), 6));
            if ($score > $bestScore) {
                [$best, $bestScore] = [$start, $score];
            }
        }

        return ($best > 0 ? '…' : '') . mb_substr($text, $best, self::EXCERPT_CHARS) . '…';
    }

    /** Markdown de l'IA → HTML nettoyé, avec les renvois [n] transformés en liens vers les sources. */
    private function renderAnswer(string $answer, Collection $excerpts): string
    {
        $html = Str::markdown($answer, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
        $html = DocHtml::clean($html, fn (string $href) => str_starts_with($href, '/docs/') ? $href : null)['html'];

        $urls = $excerpts->pluck('url', 'n');

        // On ne touche ni au code (« $tableau[1] ») ni aux liens existants.
        $parts = preg_split('#(<pre\b.*?</pre>|<code\b.*?</code>|<a\b.*?</a>)#si', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)->map(fn ($part, $index) => $index % 2 === 1 ? $part : preg_replace_callback('/\[(\d)\]/', function ($m) use ($urls) {
            $url = $urls[(int) $m[1]] ?? null;

            return $url ? '<a href="' . e($url) . '" data-source="' . $m[1] . '">[' . $m[1] . ']</a>' : $m[0];
        }, $part))->implode('');
    }
}
