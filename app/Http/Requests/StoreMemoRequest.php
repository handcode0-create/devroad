<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemoRequest extends FormRequest
{
    // Autorisation gérée par MemoPolicy dans le Controller.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'max:50000'],
            'is_favorite' => ['sometimes', 'boolean'],

            // Tags envoyés sous forme de NOMS : ['Laravel', 'Routing']
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
            'tags.max' => 'Un mémo ne peut pas avoir plus de :max tags.',
            'tags.*.max' => 'Un tag ne peut pas dépasser :max caractères.',
        ];
    }
}