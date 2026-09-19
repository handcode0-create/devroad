<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => [
                'required',
                'string',
                Rule::in(['php', 'javascript']),
            ],
            'code' => [
                'required',
                'string',
                'max:20000',
            ],
        ];
    }
}
