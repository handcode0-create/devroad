<?php

namespace App\Http\Requests;

use App\Models\RoadmapStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoadmapStepRequest extends FormRequest
{
    // L'autorisation (propriétaire de la roadmap) est vérifiée
    // par la Policy dans le Controller.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
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
            'status.in' => 'Le statut choisi est invalide.',
        ];
    }
}