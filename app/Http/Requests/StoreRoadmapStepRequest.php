<?php

namespace App\Http\Requests;

use App\Models\RoadmapStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoadmapStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'objective' => ['nullable', 'string', 'max:5000'],
            'content' => ['nullable', 'string', 'max:100000'],
            'code_example' => ['nullable', 'string', 'max:50000'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(RoadmapStep::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => "Le titre de l'étape est obligatoire.",
            'title.min' => 'Le titre doit contenir au moins :min caractères.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'objective.max' => "L'objectif ne peut pas dépasser :max caractères.",
            'content.max' => 'Le contenu du cours est trop long.',
            'code_example.max' => "L'exemple de code est trop long.",
            'estimated_minutes.integer' => 'La durée estimée doit être un nombre entier.',
            'estimated_minutes.min' => 'La durée estimée doit être supérieure à 0.',
            'estimated_minutes.max' => 'La durée estimée ne peut pas dépasser :max minutes.',
            'status.in' => 'Le statut choisi est invalide.',
        ];
    }
}
