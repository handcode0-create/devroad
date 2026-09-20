<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learning_goal' => ['nullable', 'string', 'max:120'],
            'daily_goal_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60, 90])],
            'weekly_goal_sessions' => ['required', 'integer', 'min:1', 'max:7'],
            'preferred_technology' => [
                'nullable',
                'string',
                Rule::in(array_keys(config('devroad.technologies', []))),
            ],
            'email_notifications' => ['boolean'],
            'learning_reminders' => ['boolean'],
            'light_mode' => ['boolean'],
        ];
    }
}
