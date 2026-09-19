<?php

namespace App\Http\Requests;

use App\Models\RoadmapStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStepStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(RoadmapStep::STATUSES)],
        ];
    }
}