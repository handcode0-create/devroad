<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $content = $this->sanitizeContent((string) $this->input('content', ''));
        $title = trim((string) $this->input('title', ''));
        $this->merge(['content' => $content]);
        $content = trim($content);

        if ($title === '' && $content !== '') {
            $candidate = preg_replace('/^#+\s*/', '', strtok($content, "\n"));
            $candidate = trim((string) $candidate);
            $this->merge(['title' => mb_substr($candidate !== '' ? $candidate : 'Nouvelle fiche', 0, 255)]);
        }
    }

    private function sanitizeContent(string $content): string
    {
        if (! str_contains($content, '<')) {
            return $content;
        }

        $allowed = ['p', 'div', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'hr', 'span'];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="devroad-content">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('devroad-content');
        if (! $root) return strip_tags($content);

        $nodes = [];
        foreach ($root->getElementsByTagName('*') as $node) $nodes[] = $node;
        foreach ($nodes as $node) {
            if (! in_array(strtolower($node->tagName), $allowed, true)) {
                $fragment = $dom->createDocumentFragment();
                while ($node->firstChild) $fragment->appendChild($node->firstChild);
                $node->parentNode?->replaceChild($fragment, $node);
                continue;
            }
            $attributes = [];
            foreach ($node->attributes as $attribute) $attributes[] = [$attribute->name, $attribute->value];
            while ($node->attributes->length > 0) $node->removeAttributeNode($node->attributes->item(0));
            foreach ($attributes as [$name, $value]) {
                $name = strtolower($name);
                if ($node->tagName === 'img' && in_array($name, ['src', 'alt'], true)) {
                    if ($name === 'src' && ! preg_match('#^/memos/attachments/[0-9]+$#', $value)) continue;
                    $node->setAttribute($name, $value);
                } elseif ($node->tagName === 'a' && $name === 'href' && preg_match('#^/memos/attachments/[0-9]+(?:/download)?$#', $value)) {
                    $node->setAttribute('href', $value);
                } elseif ($node->tagName === 'figure' && $name === 'data-attachment-id' && ctype_digit($value)) {
                    $node->setAttribute($name, $value);
                }
            }
        }

        $html = '';
        foreach ($root->childNodes as $child) $html .= $dom->saveHTML($child);
        return $html;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'max:50000'],
            'formatting' => ['sometimes', 'nullable', 'array'],
            'formatting.fontFamily' => ['sometimes', 'string', 'max:40'],
            'formatting.fontSize' => ['sometimes', 'integer', 'min:12', 'max:32'],
            'formatting.textTransform' => ['sometimes', 'in:none,uppercase,lowercase,capitalize'],
            'formatting.textAlign' => ['sometimes', 'in:left,center,right,justify'],
            'formatting.fontWeight' => ['sometimes', 'in:400,500,600,700'],
            'is_favorite' => ['sometimes', 'boolean'],
            'folder_id' => ['sometimes', 'nullable', 'integer'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:8'],
            'cover_attachment_id' => ['sometimes', 'nullable', 'integer'],
            'is_full_width' => ['sometimes', 'boolean'],
            'attachments' => ['sometimes', 'nullable', 'array', 'max:8'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt,md,json,csv,zip'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:10'],
            'tags.*' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.min' => 'Le titre doit contenir au moins :min caractères.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.max' => 'Le contenu ne peut pas dépasser :max caractères.',
            'formatting.fontFamily.max' => 'Cette police n’est pas valide.',
            'formatting.fontSize.integer' => 'La taille doit être un nombre entier.',
            'tags.max' => 'Un mémo ne peut pas avoir plus de :max tags.',
            'tags.*.max' => 'Un tag ne peut pas dépasser :max caractères.',
        ];
    }
}
