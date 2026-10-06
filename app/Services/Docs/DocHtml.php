<?php

namespace App\Services\Docs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Nettoie le HTML d'une documentation tierce avant de l'afficher dans DevRoad :
 * liste blanche de balises et d'attributs (aucun script, style ni gestionnaire
 * d'événement), liens internes réécrits vers /docs/…, liens externes en nouvel onglet.
 */
class DocHtml
{
    private const ALLOWED = [
        'p', 'div', 'section', 'article', 'aside', 'header', 'span', 'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'mark', 'small', 'sub', 'sup', 'abbr', 'q', 'cite',
        'code', 'pre', 'kbd', 'samp', 'var',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'blockquote', 'figure', 'figcaption', 'details', 'summary',
        'a', 'img',
    ];

    /** Balises supprimées avec leur contenu. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'svg', 'math', 'noscript', 'template', 'link', 'meta'];

    /**
     * @param  callable(string $href): ?string  $resolveInternal  Renvoie l'URL DevRoad d'un lien interne, ou null s'il est externe.
     * @return array{html: string, text: string, title: ?string, headings: array<int, array{id: string, text: string, level: int}>}
     */
    public static function clean(string $html, callable $resolveInternal): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="dr-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('dr-root');
        if (! $root) {
            return ['html' => '', 'text' => '', 'title' => null, 'headings' => []];
        }

        self::walk($root, $resolveInternal);

        $title = null;
        $headings = [];
        $usedIds = [];
        foreach ((new DOMXPath($document))->query('.//h1|.//h2|.//h3', $root) as $heading) {
            /** @var DOMElement $heading */
            $text = trim(preg_replace('/\s+/u', ' ', $heading->textContent));
            if ($text === '') {
                continue;
            }
            $level = (int) substr($heading->nodeName, 1);
            if ($level === 1 && $title === null) {
                // Le titre est affiché dans l'en-tête de la page : on ne le répète pas dans le contenu.
                $title = $text;
                $heading->parentNode?->removeChild($heading);
                continue;
            }
            // Ancre posée juste avant le titre (« <a name> » des docs Laravel) : le titre la reprend,
            // pour garder un seul identifiant et que les liens #ancre tombent pile sur le titre.
            $previous = $heading->previousSibling;
            while ($previous && $previous->nodeType === XML_TEXT_NODE && trim($previous->textContent) === '') {
                $previous = $previous->previousSibling;
            }
            $anchor = null;
            if ($previous instanceof DOMElement && $previous->nodeName === 'span' && $previous->getAttribute('id') !== '' && trim($previous->textContent) === '') {
                $anchor = $previous;
            } elseif ($previous instanceof DOMElement && $previous->nodeName === 'p' && trim($previous->textContent) === ''
                && $previous->childNodes->length >= 1 && $previous->getElementsByTagName('span')->length === 1
                && $previous->getElementsByTagName('span')->item(0)->getAttribute('id') !== '') {
                $anchor = $previous;
            }
            $anchorId = $anchor ? ($anchor->nodeName === 'span' ? $anchor->getAttribute('id') : $anchor->getElementsByTagName('span')->item(0)->getAttribute('id')) : null;
            if ($anchor) {
                $anchor->parentNode->removeChild($anchor);
            }

            $id = $anchorId ?: ($heading->getAttribute('id') ?: self::slug($text));
            while (isset($usedIds[$id])) {
                $id .= '-' . count($usedIds);
            }
            $usedIds[$id] = true;
            $heading->setAttribute('id', $id);
            if ($level <= 3) {
                $headings[] = ['id' => $id, 'text' => $text, 'level' => $level];
            }
        }

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $document->saveHTML($child);
        }

        $text = trim(preg_replace('/[ \t]+/u', ' ', preg_replace('/\n{3,}/', "\n\n", html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '</h2>', '</h3>', '</pre>', '<br>'], ["</p>\n", "</li>\n", "</h2>\n", "</h3>\n", "</pre>\n", "\n"], $out)), ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

        return ['html' => $out, 'text' => $text, 'title' => $title, 'headings' => array_slice($headings, 0, 60)];
    }

    private static function walk(DOMNode $node, callable $resolveInternal): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
                continue;
            }
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child, $resolveInternal);

            if (! in_array($tag, self::ALLOWED, true)) {
                // Balise inconnue : on garde son contenu, sans l'enveloppe.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::filterAttributes($child, $tag, $resolveInternal);
        }
    }

    private static function filterAttributes(DOMElement $element, string $tag, callable $resolveInternal): void
    {
        $keep = [];
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if ($name === 'id' && preg_match('/^[\w\-:.]{1,120}$/u', $value)) {
                $keep['id'] = $value;
            } elseif ($tag === 'a' && $name === 'href') {
                $keep += self::link($value, $resolveInternal);
            } elseif ($tag === 'img' && $name === 'src' && preg_match('#^https://#i', $value)) {
                $keep['src'] = $value;
                $keep['loading'] = 'lazy';
            } elseif ($tag === 'img' && $name === 'alt') {
                $keep['alt'] = $value;
            } elseif (in_array($tag, ['td', 'th'], true) && in_array($name, ['colspan', 'rowspan'], true) && ctype_digit($value)) {
                $keep[$name] = $value;
            } elseif ($tag === 'pre' && $name === 'data-language' && preg_match('/^[\w+-]{1,20}$/', $value)) {
                $keep['data-language'] = $value;
            }
        }

        while ($element->attributes->length > 0) {
            $element->removeAttribute($element->attributes->item(0)->name);
        }
        foreach ($keep as $name => $value) {
            $element->setAttribute($name, $value);
        }
    }

    /** @return array<string, string> */
    private static function link(string $href, callable $resolveInternal): array
    {
        if ($href === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
            return [];
        }
        if (str_starts_with($href, '#')) {
            return ['href' => $href];
        }
        $internal = $resolveInternal($href);
        if ($internal !== null) {
            return ['href' => $internal];
        }
        if (preg_match('#^https?://#i', $href)) {
            return ['href' => $href, 'target' => '_blank', 'rel' => 'noopener noreferrer'];
        }

        return [];
    }

    public static function slug(string $text): string
    {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(\Illuminate\Support\Str::ascii($text))), '-');

        return $slug !== '' ? substr($slug, 0, 80) : 'section';
    }

    /** Résout un lien relatif comme le ferait un navigateur, à partir du chemin de la page courante. */
    public static function resolvePath(string $currentPath, string $href): string
    {
        [$href, $fragment] = array_pad(explode('#', $href, 2), 2, null);
        $href = preg_replace('/\?.*$/', '', $href);
        if ($href === '') {
            $path = $currentPath;
        } elseif (str_starts_with($href, '/')) {
            $path = ltrim($href, '/');
        } else {
            $base = explode('/', $currentPath);
            array_pop($base);
            foreach (explode('/', $href) as $segment) {
                if ($segment === '..') {
                    array_pop($base);
                } elseif ($segment !== '.' && $segment !== '') {
                    $base[] = $segment;
                }
            }
            $path = implode('/', $base);
        }
        $path = preg_replace('/\.html$/', '', $path);

        return $path . ($fragment !== null && $fragment !== '' ? '#' . $fragment : '');
    }
}
