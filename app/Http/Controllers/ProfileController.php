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
            'preferences' => [
                'learning_goal' => $user->learning_goal,
                'daily_goal_minutes' => $user->daily_goal_minutes,
                'weekly_goal_sessions' => $user->weekly_goal_sessions,
                'preferred_technology' => $user->preferred_technology,
                'email_notifications' => $user->email_notifications,
                'learning_reminders' => $user->learning_reminders,
                'light_mode' => $user->light_mode,
            ],
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
        $validated = $request->validate([
            'light_mode' => ['required', 'boolean'],
        ]);

        $request->user()->update($validated);

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
