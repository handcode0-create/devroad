<?php

namespace App\Services\Docs;

use App\Models\DocEntry;
use App\Models\DocPage;
use App\Models\DocSource;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Bibliothèque de documentation : synchronisation des index, lecture des pages
 * (téléchargées à la demande puis mises en cache) et recherche.
 */
class DocsLibrary
{
    private ?array $manifest = null;

    /* ───────────────────────── Synchronisation ───────────────────────── */

    /** @return array{key: string, entries: int} */
    public function sync(string $key): array
    {
        $config = config("devroad_docs.sources.$key") ?? throw new RuntimeException("Source inconnue : $key");

        return match ($config['provider']) {
            'devdocs' => $this->syncDevDocs($key, $config),
            'laravel' => $this->syncLaravel($key, $config),
            default => throw new RuntimeException("Fournisseur inconnu : {$config['provider']}"),
        };
    }

    public function isStale(string $key): bool
    {
        $source = DocSource::where('key', $key)->first();

        return ! $source?->synced_at || $source->synced_at->lt(now()->subDays((int) config('devroad_docs.stale_after_days', 7)));
    }

    private function syncDevDocs(string $key, array $config): array
    {
        $doc = $this->findInManifest($config['slug']);
        $base = rtrim(config('devroad_docs.devdocs.documents_url'), '/');
        $index = $this->http()->get("$base/{$doc['slug']}/index.json", ['v' => $doc['mtime'] ?? null])->throw()->json();
        $entries = collect($index['entries'] ?? [])->filter(fn ($entry) => filled($entry['name'] ?? null) && filled($entry['path'] ?? null));

        if ($entries->isEmpty()) {
            throw new RuntimeException("Index vide pour {$doc['slug']}.");
        }

        $version = trim(($doc['version'] ?? '') . ' ' . ($doc['release'] ?? '')) ?: null;

        return DB::transaction(function () use ($key, $config, $doc, $entries, $version) {
            $source = DocSource::firstOrNew(['key' => $key]);
            $changed = $source->remote_slug !== $doc['slug'] || $source->version !== $version;
            $source->fill([
                'provider' => 'devdocs',
                'remote_slug' => $doc['slug'],
                'name' => $config['name'] ?? $doc['name'],
                'version' => $version,
                'attribution' => $this->cleanAttribution($doc['attribution'] ?? null),
                'home_url' => $doc['links']['home'] ?? null,
            ])->save();

            // Nouvelle version : les pages en cache sont périmées.
            if ($changed) {
                $source->pages()->delete();
            }

            $this->replaceEntries($source, $entries->values()->map(function ($entry, $position) {
                [$path, $fragment] = array_pad(explode('#', $entry['path'], 2), 2, null);

                return ['name' => $entry['name'], 'type' => $entry['type'] ?? null, 'path' => $path, 'fragment' => $fragment, 'position' => $position];
            }));

            return ['key' => $key, 'entries' => $source->entries_count];
        });
    }

    private function syncLaravel(string $key, array $config): array
    {
        $branch = config('devroad_docs.laravel.branch');
        $raw = rtrim(config('devroad_docs.laravel.raw_url'), '/') . '/' . $branch;
        $toc = $this->http()->get("$raw/documentation.md")->throw()->body();

        // « - ## Prologue » puis « - [Routing](/docs/{{version}}/routing) »
        $pages = [];
        $group = null;
        foreach (preg_split('/\R/', $toc) as $line) {
            if (preg_match('/^\s*-\s*##\s*(.+)$/', $line, $m)) {
                $group = trim($m[1]);
            } elseif (preg_match('#\[([^\]]+)\]\(/docs/\{\{version\}\}/([a-z0-9\-]+)\)#i', $line, $m)) {
                $pages[$m[2]] = ['title' => trim($m[1]), 'group' => $group];
            }
        }
        if ($pages === []) {
            throw new RuntimeException('Sommaire Laravel introuvable.');
        }

        $bodies = [];
        foreach (array_chunk(array_keys($pages), 12) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn ($slug) => $pool->as($slug)->timeout(20)->withHeaders($this->headers())->get("$raw/$slug.md"),
                $chunk,
            ));
            foreach ($chunk as $slug) {
                $response = $responses[$slug] ?? null;
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $bodies[$slug] = $response->body();
                }
            }
        }

        return DB::transaction(function () use ($key, $config, $branch, $pages, $bodies) {
            $source = DocSource::firstOrNew(['key' => $key]);
            $source->fill([
                'provider' => 'laravel',
                'remote_slug' => $branch,
                'name' => $config['name'] ?? 'Laravel',
                'version' => $branch,
                'attribution' => '© Taylor Otwell — documentation sous licence MIT. Laravel est une marque de Taylor Otwell.',
                'home_url' => rtrim(config('devroad_docs.laravel.site_url'), '/') . '/' . $branch,
            ])->save();

            $entries = collect();
            foreach ($pages as $slug => $page) {
                if (! isset($bodies[$slug])) {
                    $entries->push(['name' => $page['title'], 'type' => $page['group'], 'path' => $slug, 'fragment' => null]);
                    continue;
                }
                $rendered = $this->renderLaravel($slug, $bodies[$slug]);
                // Titre complet de la page (« Eloquent: Relationships ») plutôt que l'intitulé court du sommaire.
                $entries->push(['name' => $rendered['title'] ?? $page['title'], 'type' => $page['group'], 'path' => $slug, 'fragment' => null]);
                DocPage::updateOrCreate(
                    ['doc_source_id' => $source->id, 'path' => $slug],
                    ['title' => $rendered['title'] ?? $page['title'], 'html' => $rendered['html'], 'text' => $rendered['text']],
                );
                foreach ($rendered['headings'] as $heading) {
                    if ($heading['level'] <= 3) {
                        $entries->push(['name' => $heading['text'], 'type' => $page['title'], 'path' => $slug, 'fragment' => $heading['id']]);
                    }
                }
            }

            $this->replaceEntries($source, $entries->values()->map(fn ($entry, $position) => $entry + ['position' => $position]));

            return ['key' => $key, 'entries' => $source->entries_count];
        });
    }

    private function replaceEntries(DocSource $source, Collection $entries): void
    {
        $source->entries()->delete();
        $now = now();
        foreach ($entries->chunk(500) as $chunk) {
            DocEntry::insert($chunk->map(fn ($entry) => [
                'doc_source_id' => $source->id,
                'name' => Str::limit($entry['name'], 250, ''),
                'search_name' => Str::limit(DocEntry::normalize($entry['name']), 250, ''),
                'type' => isset($entry['type']) ? Str::limit($entry['type'], 250, '') : null,
                'path' => Str::limit($entry['path'], 500, ''),
                'fragment' => isset($entry['fragment']) ? Str::limit($entry['fragment'], 250, '') : null,
                'position' => $entry['position'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        }
        $source->forceFill(['entries_count' => $entries->count(), 'synced_at' => now()])->save();
    }

    private function findInManifest(string $slug): array
    {
        $this->manifest ??= $this->http()->get(config('devroad_docs.devdocs.manifest_url'))->throw()->json() ?? [];

        $docs = collect($this->manifest);
        $doc = $docs->firstWhere('slug', $slug)
            ?? $docs->filter(fn ($doc) => str_starts_with($doc['slug'] ?? '', $slug . '~'))->sortByDesc('mtime')->first();

        return $doc ?? throw new RuntimeException("Documentation « $slug » absente de DevDocs.");
    }

    private function cleanAttribution(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        return trim(DocHtml::clean($html, fn () => null)['html']);
    }

    /* ───────────────────────── Lecture des pages ───────────────────────── */

    public function page(DocSource $source, string $path): DocPage
    {
        $path = trim($path, '/');
        $cached = $source->pages()->where('path', $path)->first();
        if ($cached) {
            return $cached;
        }

        if ($source->provider === 'laravel') {
            $raw = rtrim(config('devroad_docs.laravel.raw_url'), '/') . '/' . $source->remote_slug;
            $markdown = $this->http()->get("$raw/$path.md")->throw()->body();
            $rendered = $this->renderLaravel($path, $markdown);
        } else {
            $base = rtrim(config('devroad_docs.devdocs.documents_url'), '/');
            $html = $this->http()->get("$base/{$source->remote_slug}/$path.html")->throw()->body();
            $rendered = DocHtml::clean($html, fn (string $href) => $this->internalDevDocsLink($source, $path, $href));
        }

        $entryName = $source->entries()->where('path', $path)->whereNull('fragment')->value('name')
            ?? $source->entries()->where('path', $path)->value('name');

        return DocPage::updateOrCreate(
            ['doc_source_id' => $source->id, 'path' => $path],
            ['title' => $rendered['title'] ?? $entryName ?? Str::headline(basename($path)), 'html' => $rendered['html'], 'text' => $rendered['text']],
        );
    }

    /** Titres (h2/h3) d'une page déjà en cache, pour le sommaire. */
    public function headings(DocPage $page): array
    {
        preg_match_all('#<h([23])[^>]*\sid="([^"]+)"[^>]*>(.*?)</h\1>#si', $page->html, $matches, PREG_SET_ORDER);

        return collect($matches)->map(fn ($m) => [
            'level' => (int) $m[1],
            'id' => html_entity_decode($m[2]),
            'text' => trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES | ENT_HTML5)),
        ])->filter(fn ($h) => $h['text'] !== '')->values()->take(60)->all();
    }

    private function internalDevDocsLink(DocSource $source, string $currentPath, string $href): ?string
    {
        if (preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $href)) {
            return null;
        }

        return '/docs/' . $source->key . '/' . DocHtml::resolvePath($currentPath, $href);
    }

    /** @return array{html: string, text: string, title: ?string, headings: array} */
    private function renderLaravel(string $slug, string $markdown): array
    {
        $branch = config('devroad_docs.laravel.branch');
        $markdown = str_replace('{{version}}', $branch, $markdown);
        // Sommaire en tête de page (« - [Introduction](#introduction) ») : DevRoad affiche le sien.
        $markdown = preg_replace('/^(#\s+[^\n]+\n+)(?:[ \t]*[-*]\s+\[[^\]]+\]\(#[^)]+\)[ \t]*\n)+/m', '$1', $markdown, 1);
        // <a name="x"></a> devant un titre → ancre conservée.
        $markdown = preg_replace('/<a name="([\w\-]+)"><\/a>/', '<span id="$1"></span>', $markdown);
        // Callouts GitHub « > [!NOTE] » → libellé lisible.
        $markdown = preg_replace_callback('/^>\s*\[!(NOTE|WARNING|TIP|IMPORTANT|CAUTION)\]\s*$/mi', fn ($m) => '> **' . ['NOTE' => 'Note', 'WARNING' => 'Attention', 'TIP' => 'Astuce', 'IMPORTANT' => 'Important', 'CAUTION' => 'Prudence'][strtoupper($m[1])] . '**', $markdown);

        $html = Str::markdown($markdown, ['html_input' => 'allow', 'allow_unsafe_links' => false]);

        return DocHtml::clean($html, function (string $href) use ($branch) {
            if (preg_match('#^(?:https?://laravel\.com)?/docs/(?:' . preg_quote($branch, '#') . '|\d+\.x|master)/([a-z0-9\-]+)(\#[\w\-]+)?$#i', $href, $m)) {
                return '/docs/laravel/' . $m[1] . ($m[2] ?? '');
            }

            return null;
        });
    }

    /* ───────────────────────── Recherche ───────────────────────── */

    /**
     * Recherche dans les noms indexés (fonctions, éléments, sections…) puis dans
     * le texte des pages déjà en cache.
     *
     * @param  array<int, string>  $sourceKeys
     */
    public function search(string $query, array $sourceKeys = [], int $limit = 30): array
    {
        $q = DocEntry::normalize($query);
        if (mb_strlen($q) < 2) {
            return ['entries' => [], 'pages' => []];
        }

        $sources = DocSource::query()->when($sourceKeys, fn ($query) => $query->whereIn('key', $sourceKeys))->get()->keyBy('id');
        if ($sources->isEmpty()) {
            return ['entries' => [], 'pages' => []];
        }

        $like = '%' . addcslashes($q, '%_\\') . '%';
        // « hasmany » trouve « Has Many », « foreach » trouve « for each »…
        $compact = str_replace(' ', '', $q);
        $entries = DocEntry::query()
            ->whereIn('doc_source_id', $sources->keys())
            ->where(fn ($builder) => $builder
                ->where('search_name', 'like', $like)
                ->orWhereRaw("replace(search_name, ' ', '') like ?", ['%' . addcslashes($compact, '%_\\') . '%']))
            ->orderByRaw('case when search_name = ? then 0 when search_name like ? then 1 else 2 end', [$q, addcslashes($q, '%_\\') . '%'])
            ->orderByRaw('length(search_name)')
            ->orderBy('position')
            ->limit($limit)
            ->get();

        // Plusieurs mots : on complète avec les entrées qui contiennent chacun des mots.
        $words = $this->keywords($query);
        if ($entries->count() < $limit && count($words) > 1) {
            $more = DocEntry::query()
                ->whereIn('doc_source_id', $sources->keys())
                ->whereNotIn('id', $entries->pluck('id'))
                ->where(function ($builder) use ($words) {
                    foreach ($words as $word) {
                        $builder->where('search_name', 'like', '%' . addcslashes($word, '%_\\') . '%');
                    }
                })
                ->orderByRaw('length(search_name)')
                ->limit($limit - $entries->count())
                ->get();
            $entries = $entries->concat($more);
        }

        $pages = DocPage::query()
            ->whereIn('doc_source_id', $sources->keys())
            ->whereRaw('lower(text) like ?', [$like])
            ->limit(8)
            ->get(['id', 'doc_source_id', 'path', 'title', 'text']);

        return [
            'entries' => $entries->map(fn (DocEntry $entry) => $this->presentEntry($entry, $sources[$entry->doc_source_id]))->values()->all(),
            'pages' => $pages->map(fn (DocPage $page) => [
                'source' => $sources[$page->doc_source_id]->key,
                'source_name' => $sources[$page->doc_source_id]->name,
                'title' => $page->title,
                'url' => '/docs/' . $sources[$page->doc_source_id]->key . '/' . $page->path,
                'snippet' => $this->snippet($page->text, $q),
            ])->values()->all(),
        ];
    }

    public function presentEntry(DocEntry $entry, DocSource $source): array
    {
        return [
            'id' => $entry->id,
            'source' => $source->key,
            'source_name' => $source->name,
            'name' => $entry->name,
            'type' => $entry->type,
            'url' => '/docs/' . $source->key . '/' . $entry->path . ($entry->fragment ? '#' . $entry->fragment : ''),
        ];
    }

    /** Mots significatifs d'une question (sans mots vides français et anglais). */
    public function keywords(string $text): array
    {
        static $stop = null;
        $stop ??= array_flip(explode(' ', 'a au aux avec ce ces comment dans de des du elle en est et faire fait il je la le les leur mais me mon ne on ou par pas pour qu que quel quelle qui sa se ses son sur ta te tes ton tu un une utiliser utilise vos votre vous y quoi est-ce cest faut peut peux puis-je dois quand pourquoi entre the and for with how what why when use using in of to is are an it this that do does can'));

        return collect(preg_split('/[^a-z0-9_.$\-]+/', DocEntry::normalize($text)))
            ->map(fn ($word) => trim($word, '.-'))
            ->filter(fn ($word) => mb_strlen($word) >= 2 && ! isset($stop[$word]))
            ->unique()->take(8)->values()->all();
    }

    public function snippet(string $text, string $needle, int $radius = 110): string
    {
        $haystack = DocEntry::normalize($text);
        $pos = mb_strpos($haystack, $needle);
        if ($pos === false) {
            return Str::limit(preg_replace('/\s+/u', ' ', $text), $radius * 2);
        }
        $start = max(0, $pos - $radius);
        $chunk = mb_substr($text, $start, mb_strlen($needle) + $radius * 2);

        return ($start > 0 ? '…' : '') . trim(preg_replace('/\s+/u', ' ', $chunk)) . '…';
    }

    /* ───────────────────────── Utilitaires ───────────────────────── */

    private function http()
    {
        return Http::withHeaders($this->headers())->timeout(25)->retry(2, 400, throw: false);
    }

    private function headers(): array
    {
        return ['User-Agent' => 'DevRoad/1.0 (documentation reader; +' . config('app.url') . ')', 'Accept-Encoding' => 'gzip'];
    }
}
