<?php

namespace App\Services\Docs;

use App\Models\DocSource;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Versions françaises officielles des pages : MDN (communauté), fr.react.dev et manuel PHP.
 * Renvoie null quand la page n'a pas (encore) été traduite.
 */
class FrenchDocs
{
    public function supports(DocSource $source): bool
    {
        return filled(config("devroad_docs.sources.{$source->key}.fr.provider"));
    }

    /** @return array{html: string, text: string, title: ?string, headings: array, url: string}|null */
    public function fetch(DocSource $source, string $path): ?array
    {
        $config = config("devroad_docs.sources.{$source->key}.fr");

        return match ($config['provider'] ?? null) {
            'mdn' => $this->mdn($config['prefix'], $path),
            'react' => $this->react($path),
            'php' => $this->php($path),
            default => null,
        };
    }

    /** Adresse de la page française d'origine (pour le lien « Voir l'original »). */
    public function originalUrl(DocSource $source, string $path): ?string
    {
        $config = config("devroad_docs.sources.{$source->key}.fr");

        return match ($config['provider'] ?? null) {
            'mdn' => rtrim(config('devroad_docs.french.mdn_site_url'), '/') . '/' . $this->mdnSlug($config['prefix'], $path),
            'react' => rtrim(config('devroad_docs.french.react_site_url'), '/') . '/' . $this->reactPath($path),
            'php' => rtrim(config('devroad_docs.french.php_url'), '/') . '/' . $path . '.php',
            default => null,
        };
    }

    /* ───────────────────────── MDN ───────────────────────── */

    private function mdn(string $prefix, string $path): ?array
    {
        $slug = $this->mdnSlug($prefix, $path);
        $markdown = $this->get(rtrim(config('devroad_docs.french.mdn_raw_url'), '/') . '/' . strtolower($slug) . '/index.md');
        if ($markdown === null) {
            return null;
        }

        [$front, $body] = $this->frontMatter($markdown);
        $html = Str::markdown($this->mdnMacros($this->mdnDefinitions($body)), ['html_input' => 'allow', 'allow_unsafe_links' => false]);
        $clean = DocHtml::clean('<h1>' . e(str_replace('`', '', $front['title'] ?? Str::afterLast($slug, '/'))) . '</h1>' . $html, fn (string $href) => $this->mdnLink($href));

        return $clean + ['url' => rtrim(config('devroad_docs.french.mdn_site_url'), '/') . '/' . $slug];
    }

    /** Chemin DevDocs → slug MDN canonique (les anciennes adresses sont redirigées). */
    public function mdnSlug(string $prefix, string $path): string
    {
        $slug = trim($prefix . '/' . trim($path, '/'), '/');
        $redirects = $this->mdnRedirects();
        $seen = 0;
        while (isset($redirects[strtolower($slug)]) && $seen++ < 5) {
            $slug = $redirects[strtolower($slug)];
        }

        return $slug;
    }

    /** @return array<string, string> ancien slug (minuscules) → nouveau slug */
    private function mdnRedirects(): array
    {
        $cached = Cache::get('docs:mdn-fr-redirects');
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $body = $this->get(rtrim(config('devroad_docs.french.mdn_raw_url'), '/') . '/_redirects.txt') ?? '';
        } catch (\Throwable $exception) {
            // Liste injoignable : on continue sans (les anciennes adresses MDN échoueront simplement).
            report($exception);

            return [];
        }

        $map = [];
        foreach (preg_split('/\R/', $body) as $line) {
            if (preg_match('#^/fr/docs/(\S+)\s+/fr/docs/(\S+)$#', trim($line), $m)) {
                $map[strtolower(rawurldecode($m[1]))] = rawurldecode($m[2]);
            }
        }
        Cache::put('docs:mdn-fr-redirects', $map, now()->addDays(7));

        return $map;
    }

    /** « - terme\n  - : définition » (listes de définitions MDN) → listes lisibles. */
    private function mdnDefinitions(string $markdown): string
    {
        return preg_replace('/^(\s*)- : /m', '$1- ', $markdown);
    }

    /** Macros KumaScript : liens de référence conservés, le reste (bacs à sable, tableaux de compatibilité…) retiré. */
    private function mdnMacros(string $markdown): string
    {
        $labels = [
            'optional_inline' => '*(facultatif)*', 'deprecated_inline' => '*(obsolète)*', 'experimental_inline' => '*(expérimental)*',
            'non-standard_inline' => '*(non standard)*', 'readonlyinline' => '*(lecture seule)*', 'readonly_inline' => '*(lecture seule)*',
        ];

        return preg_replace_callback('/\{\{\s*([\w\-]+)\s*(?:\((.*?)\))?\s*\}\}/s', function ($m) use ($labels) {
            $name = strtolower($m[1]);
            $args = array_map(fn ($arg) => trim((string) $arg, " \t\"'"), str_getcsv($m[2] ?? '', ',', '"', '\\'));
            $target = $args[0] ?? '';
            $label = ($args[1] ?? '') !== '' ? $args[1] : null;

            if (isset($labels[$name])) {
                return $labels[$name];
            }

            $slug = match ($name) {
                'jsxref' => 'Web/JavaScript/Reference/' . (preg_match('#^(Statements|Operators|Functions|Errors|Lexical_grammar|Classes|Global_Objects|Regular_expressions|Iteration_protocols|Template_literals|Strict_mode|Trailing_commas|Deprecated_and_obsolete_features|Data_structures)/#i', $target)
                    ? $target
                    : 'Global_Objects/' . str_replace(['.prototype.', '.', '()'], ['/', '/', ''], $target)),
                'domxref' => 'Web/API/' . str_replace(['.prototype.', '.', '()'], ['/', '/', ''], $target),
                'cssxref' => 'Web/CSS/' . $target,
                'htmlelement' => 'Web/HTML/Element/' . $target,
                'htmlattrxref' => 'Web/HTML/Global_attributes/' . $target,
                'httpheader' => 'Web/HTTP/Headers/' . $target,
                'httpstatus' => 'Web/HTTP/Status/' . $target,
                'glossary' => 'Glossary/' . $target,
                default => null,
            };

            if ($slug === null || $target === '') {
                return '';
            }

            $text = $label ?? match ($name) {
                'htmlelement' => '<' . $target . '>',
                'glossary' => $target,
                default => str_contains($target, '/') ? Str::afterLast($target, '/') : $target,
            };
            $code = $name !== 'glossary';
            $display = $code ? '`' . str_replace('`', '', $text) . '`' : $text;

            return '[' . $display . '](/fr/docs/' . $slug . ')';
        }, $markdown);
    }

    /** Lien MDN → page DevRoad quand la doc est dans DevRoad, sinon MDN en français. */
    private function mdnLink(string $href): ?string
    {
        if (str_starts_with($href, 'https://developer.mozilla.org/fr/docs/')) {
            $href = substr($href, strlen('https://developer.mozilla.org'));
        }
        if (! preg_match('#^/(?:fr|en-US)/docs/([^\#?]+)(\#[\w\-.%]+)?#i', $href, $m)) {
            return null;
        }

        $slug = $this->mdnCanonical(rawurldecode($m[1]));
        // Ancres MDN (« #méthodes_itératives ») → identifiants de titres DevRoad (« #methodes-iteratives »).
        $fragment = isset($m[2]) ? '#' . DocHtml::slug(str_replace('_', ' ', rawurldecode(substr($m[2], 1)))) : '';
        foreach (config('devroad_docs.sources') as $key => $source) {
            $prefix = $source['fr']['prefix'] ?? null;
            if (($source['fr']['provider'] ?? null) === 'mdn' && $prefix && stripos($slug, $prefix . '/') === 0) {
                return '/docs/' . $key . '/' . strtolower(substr($slug, strlen($prefix) + 1)) . $fragment;
            }
        }

        return rtrim(config('devroad_docs.french.mdn_site_url'), '/') . '/' . $slug . $fragment;
    }

    private function mdnCanonical(string $slug): string
    {
        $redirects = $this->mdnRedirects();
        $seen = 0;
        while (isset($redirects[strtolower($slug)]) && $seen++ < 5) {
            $slug = $redirects[strtolower($slug)];
        }

        return $slug;
    }

    /* ───────────────────────── React ───────────────────────── */

    private function react(string $path): ?array
    {
        $real = $this->reactPath($path);
        $markdown = $this->get(rtrim(config('devroad_docs.french.react_raw_url'), '/') . '/content/' . $real . '.md');
        if ($markdown === null) {
            return null;
        }

        [$front, $body] = $this->frontMatter($markdown);
        $body = $this->mdx($body);
        $html = Str::markdown($body, ['html_input' => 'allow', 'allow_unsafe_links' => false]);
        $clean = DocHtml::clean('<h1>' . e($front['title'] ?? Str::afterLast($real, '/')) . '</h1>' . $html, function (string $href) {
            if (preg_match('#^(?:https://(?:fr\.)?react\.dev)?/((?:reference|learn)/[\w\-/]+)(\#[\w\-]+)?$#', $href, $m)) {
                return '/docs/react/' . strtolower($m[1]) . ($m[2] ?? '');
            }

            return null;
        });

        return $clean + ['url' => rtrim(config('devroad_docs.french.react_site_url'), '/') . '/' . $real];
    }

    /** Chemin DevDocs (minuscules) → chemin réel de fr.react.dev (« reference/react/useState »). */
    private function reactPath(string $path): string
    {
        $map = Cache::get('docs:react-fr-paths');
        $map = is_array($map) ? $map : rescue(fn () => Cache::remember('docs:react-fr-paths', now()->addDays(7), function () {
            $paths = [];
            foreach (['sidebarReference.json', 'sidebarLearn.json'] as $file) {
                $json = json_decode($this->get(rtrim(config('devroad_docs.french.react_raw_url'), '/') . '/' . $file) ?? '[]', true) ?: [];
                array_walk_recursive($json, function ($value, $key) use (&$paths) {
                    if ($key === 'path' && is_string($value) && str_starts_with($value, '/')) {
                        $paths[strtolower(trim($value, '/'))] = trim($value, '/');
                    }
                });
            }

            return $paths;
        }), [], report: true);

        $path = trim(preg_replace('/\.html$/', '', $path), '/');

        return $map[strtolower($path)] ?? $path;
    }

    /** Composants MDX de react.dev → HTML simple (encadrés conservés, bacs à sable retirés). */
    private function mdx(string $markdown): string
    {
        $boxes = ['Pitfall' => 'Piège', 'Note' => 'Remarque', 'DeepDive' => 'En détail', 'Wip' => 'En cours de rédaction', 'Canary' => 'Fonctionnalité expérimentale', 'Hint' => 'Indice', 'Solution' => 'Solution'];
        foreach ($boxes as $tag => $label) {
            $markdown = preg_replace('/<' . $tag . '(\s[^>]*)?>/', "\n<blockquote>\n\n**$label**\n", $markdown);
            $markdown = str_replace('</' . $tag . '>', "\n</blockquote>\n", $markdown);
        }
        // Composants auto-fermants (<Diagram … />, <InlineToc />) : retirés.
        $markdown = preg_replace('/<[A-Z][\w.]*\b[^>]*\/>/s', '', $markdown);
        // Autres composants (<Intro>, <Sandpack>, <Recipes>…) : on garde leur contenu.
        $markdown = preg_replace('/<\/?[A-Z][\w.]*\b[^>]*>/s', "\n", $markdown);
        // Titres avec ancre explicite « ## Titre {/*ancre*/} » : l'ancre devient l'identifiant du titre.
        return preg_replace('/^(#{1,6})\s+(.+?)\s*\{\/\*([\w\-]+)\*\/\}\s*$/m', "<span id=\"$3\"></span>\n\n$1 $2", $markdown);
    }

    /* ───────────────────────── PHP ───────────────────────── */

    private function php(string $path): ?array
    {
        $path = preg_replace('/\.(html|php)$/', '', trim($path, '/'));
        $html = $this->get(rtrim(config('devroad_docs.french.php_url'), '/') . '/' . $path . '.php');
        if ($html === null) {
            return null;
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        $content = $xpath->query('//*[@id="layout-content"]')->item(0);
        if (! $content) {
            return null;
        }
        foreach ($xpath->query('.//*[contains(@class,"manualnavbar") or contains(@class,"change-language") or contains(@class,"edit-bug") or @id="usernotes" or contains(@class,"contribute")]', $content) as $node) {
            $node->parentNode?->removeChild($node);
        }
        $inner = '';
        foreach ($content->childNodes as $child) {
            $inner .= $document->saveHTML($child);
        }

        $clean = DocHtml::clean($inner, function (string $href) {
            if (preg_match('#^(?:https://www\.php\.net/manual/(?:fr|en)/)?([\w.\-]+)\.php(\#[\w\-.]+)?$#', $href, $m)) {
                return '/docs/php/' . strtolower($m[1]) . ($m[2] ?? '');
            }

            return null;
        });

        return $clean + ['url' => rtrim(config('devroad_docs.french.php_url'), '/') . '/' . $path . '.php'];
    }

    /* ───────────────────────── Utilitaires ───────────────────────── */

    /** @return array{0: array<string, string>, 1: string} */
    private function frontMatter(string $markdown): array
    {
        if (! preg_match('/^---\R(.*?)\R---\R/s', $markdown, $m)) {
            return [[], $markdown];
        }
        $front = [];
        foreach (preg_split('/\R/', $m[1]) as $line) {
            if (preg_match('/^([\w\-]+):\s*(.+)$/', $line, $kv)) {
                $front[$kv[1]] = trim($kv[2], " \"'");
            }
        }

        return [$front, substr($markdown, strlen($m[0]))];
    }

    /** Corps de la réponse, ou null si la page n'existe pas (404). Les autres erreurs remontent. */
    private function get(string $url): ?string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'DevRoad/1.0 (lecteur de documentation; +' . config('app.url') . ')'])
                ->timeout(20)->retry(2, 400, fn ($exception) => ! ($exception instanceof RequestException && $exception->response?->status() === 404), throw: false)
                ->get($url);
        } catch (RequestException $exception) {
            $response = $exception->response;
        }

        if ($response->status() === 404) {
            return null;
        }

        return $response->throw()->body();
    }
}
