<?php

namespace App\Services\Docs;

use App\Models\DocPage;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiException;
use DOMDocument;

/**
 * Traduction en français, par l'IA de l'utilisateur, d'une page qui n'existe qu'en anglais.
 * La page est découpée en morceaux traduits un par un (progression visible) ; le résultat
 * est mis en cache et profite ensuite à tous les lecteurs.
 */
class DocsTranslator
{
    private const CHUNK_CHARS = 6000;

    private const MAX_CHUNKS = 40;

    public function __construct(private AiClient $ai)
    {
    }

    /** @return array<int, string> */
    public function chunks(DocPage $english): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="dr-root">' . $english->html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $blocks = [];
        $collect = function ($node) use (&$collect, &$blocks, $document) {
            foreach ($node->childNodes as $child) {
                $html = $document->saveHTML($child);
                // Un grand conteneur est découpé à l'intérieur ; le reste (listes, tableaux, code) reste entier.
                if (strlen($html) > self::CHUNK_CHARS && in_array(strtolower($child->nodeName), ['div', 'section', 'article'], true)) {
                    $collect($child);
                } elseif (trim(strip_tags($html)) !== '' || str_contains($html, '<img')) {
                    $blocks[] = $html;
                }
            }
        };
        $collect($document->getElementById('dr-root'));

        $chunks = [];
        $current = '<h1>' . e($english->title) . '</h1>';
        foreach ($blocks as $block) {
            if ($current !== '' && strlen($current) + strlen($block) > self::CHUNK_CHARS) {
                $chunks[] = $current;
                $current = '';
            }
            $current .= $block;
        }
        if ($current !== '') {
            $chunks[] = $current;
        }

        return array_slice($chunks, 0, self::MAX_CHUNKS);
    }

    /**
     * @return array{chunk: int, total: int, html: string, done: bool, title: ?string}
     *
     * @throws AiException
     */
    public function translateChunk(User $user, DocPage $english, int $index): array
    {
        $chunks = $this->chunks($english);
        $total = count($chunks);
        if (! isset($chunks[$index])) {
            throw new AiException('Morceau de page introuvable.');
        }

        $cached = $english->translations()->where('chunk', $index)->value('html');
        if ($cached === null) {
            if (! $user->hasAiAssistant()) {
                throw new AiException('Ajoute ta clé d’IA dans Paramètres → Assistant IA pour traduire cette page.');
            }

            $system = <<<'TXT'
            Tu es traducteur technique pour DevRoad, une application d'apprentissage du développement.
            Traduis en français le fragment HTML de documentation fourni, en tutoyant le lecteur.
            Règles strictes :
            - Garde exactement les mêmes balises et attributs HTML (href, id…), dans le même ordre.
            - Ne traduis jamais le contenu des balises <code> et <pre>, ni les noms de fonctions, classes, méthodes, propriétés, fichiers ou commandes.
            - Garde en anglais les termes techniques d'usage courant chez les développeurs francophones (middleware, callback, framework…).
            - Renvoie UNIQUEMENT le HTML traduit, sans bloc ``` ni commentaire.
            TXT;

            $translated = $this->ai->complete($user->ai_provider, $user->ai_api_key, $user->ai_model, $system, $chunks[$index], 4000);
            $translated = preg_replace('/^```(?:html)?\s*|\s*```$/', '', trim($translated));
            $result = DocHtml::clean($translated, fn (string $href) => str_starts_with($href, '/docs/') ? $href : null);
            // Le titre traduit (premier morceau) est conservé pour l'assemblage final.
            $cached = ($result['title'] ? '<h1>' . e($result['title']) . '</h1>' : '') . $result['html'];
            if (trim(strip_tags($cached)) === '') {
                throw new AiException('La traduction de ce passage a échoué. Réessaie.');
            }
            $english->translations()->updateOrCreate(['chunk' => $index], ['html' => $cached]);
        }

        $done = $english->translations()->count() >= $total;
        $title = null;
        if ($done) {
            $title = $this->assemble($english, $total);
        }

        return ['chunk' => $index, 'total' => $total, 'html' => $cached, 'done' => $done, 'title' => $title];
    }

    /** Tous les morceaux sont traduits : on enregistre la page française (traduction automatique). */
    private function assemble(DocPage $english, int $total): string
    {
        $html = $english->translations()->where('chunk', '<', $total)->orderBy('chunk')->pluck('html')->implode('');
        $clean = DocHtml::clean($html, fn (string $href) => str_starts_with($href, '/docs/') ? $href : null);
        $title = $clean['title'] ?: $english->title;

        DocPage::updateOrCreate(
            ['doc_source_id' => $english->doc_source_id, 'path' => $english->path, 'locale' => 'fr'],
            ['title' => $title, 'html' => $clean['html'], 'text' => $clean['text'], 'missing' => false, 'machine_translated' => true],
        );

        return $title;
    }
}
