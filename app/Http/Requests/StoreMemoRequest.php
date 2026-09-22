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
        $title = trim((string) $this->input('title', ''));
        $content = trim((string) $this->input('content', ''));

        if ($title === '' && $content !== '') {
            $candidate = preg_replace('/^#+\s*/', '', strtok($content, "\n"));
            $candidate = trim((string) $candidate);
            $this->merge(['title' => mb_substr($candidate !== '' ? $candidate : 'Nouvelle fiche', 0, 255)]);
        }
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
