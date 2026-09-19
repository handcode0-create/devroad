<?php

namespace App\Http\Requests;

use App\Models\Roadmap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoadmapRequest extends FormRequest
{
    // L'autorisation est gérée par la Policy dans le Controller
    // (et par le middleware auth sur les routes).
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'technology' => ['required', 'string', 'max:50', Rule::in(array_keys(config('devroad.technologies', [])))],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(Roadmap::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'technology.required' => 'La technologie est obligatoire.',
            'technology.in' => 'La technologie choisie est invalide.',
            'title.min' => 'Le titre doit contenir au moins :min caractères.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'description.max' => 'La description ne peut pas dépasser :max caractères.',
            'status.in' => 'Le statut choisi est invalide.',
        ];
    }
}