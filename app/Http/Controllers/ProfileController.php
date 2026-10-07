<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateProfilePreferencesRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'learningProfile' => $user->learningProfile ? [
                'level' => $user->learningProfile->level,
                'level_source' => $user->learningProfile->level_source,
                'assessment_scores' => $user->learningProfile->assessment_scores,
                'completed_at' => $user->learningProfile->completed_at?->toIso8601String(),
            ] : null,
            'preferences' => [
                'learning_goal' => $user->learning_goal,
                'daily_goal_minutes' => $user->daily_goal_minutes,
                'weekly_goal_sessions' => $user->weekly_goal_sessions,
                'preferred_technology' => $user->preferred_technology,
                'email_notifications' => $user->email_notifications,
                'learning_reminders' => $user->learning_reminders,
                'reminder_time' => $user->reminder_time ?? '18:00',
                'reminder_days' => $user->reminder_days ?? [1, 2, 3, 4, 5, 6, 7],
                'light_mode' => $user->light_mode,
            ],
            // Assistant IA : jamais la clé elle-même, seulement ses 4 derniers caractères.
            'aiSettings' => [
                'provider' => $user->ai_provider,
                'model' => $user->ai_model,
                'hint' => $user->ai_key_hint,
                'enabled' => $user->hasAiAssistant(),
                'providers' => collect(\App\Services\Ai\AiClient::PROVIDERS)->map(fn ($provider, $key) => [
                    'key' => $key,
                    'name' => $provider['name'],
                    'default_model' => $provider['default_model'],
                    'models' => $provider['models'],
                    'keys_url' => $provider['keys_url'],
                ])->values(),
            ],
            'vapidPublicKey' => config('webpush.public_key'),
            'technologies' => collect(config('devroad.technologies', []))
                ->map(fn ($label, $value) => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Update DevRoad learning preferences and goals.
     */
    public function updatePreferences(
        UpdateProfilePreferencesRequest $request
    ): RedirectResponse {
        $request->user()->update($request->validated());

        return Redirect::route('profile.edit')
            ->with('status', 'preferences-updated');
    }

    /**
     * Update only the user's interface theme.
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        // « theme » (nouveau) ou « light_mode » (ancien interrupteur du profil).
        $validated = $request->validate([
            'theme' => ['required_without:light_mode', 'string', \Illuminate\Validation\Rule::in(\App\Models\User::THEMES)],
            'light_mode' => ['required_without:theme', 'boolean'],
        ]);

        $theme = $validated['theme'] ?? ($request->boolean('light_mode') ? 'clair' : 'nuit');

        $request->user()->update([
            'theme' => $theme,
            'light_mode' => in_array($theme, \App\Models\User::LIGHT_THEMES, true),
        ]);

        // Choix depuis le panneau « Apparence » : on reste sur la page courante.
        if ($request->has('theme')) {
            return back()->with('status', 'theme-updated');
        }

        return Redirect::route('profile.edit')
            ->with('status', 'theme-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
