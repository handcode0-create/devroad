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
                Rule::in(['php', 'javascript', 'laravel']),
            ],
            'code' => [
                'nullable',
                'string',
                'max:20000',
            ],
            'command' => [
                'nullable',
                'string',
                'max:300',
            ],
            'file_path' => [
                'nullable',
                'string',
                'max:180',
                'regex:/^(?!.*\.\.)(?:[A-Za-z0-9_-]+\/)*[A-Za-z0-9_.-]+$/',
            ],
        ];
    }
}
