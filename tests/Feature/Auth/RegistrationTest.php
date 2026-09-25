<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserLearningProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_are_sent_to_learning_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding.create', absolute: false));
    }

    public function test_onboarding_creates_a_personalized_learning_profile(): void
    {
        $user = User::factory()->create();

        $answers = [];
        foreach (config('devroad_onboarding.categories') as $category) {
            foreach ($category['questions'] as $question) {
                $answers[$question['id']] = $question['options'][0]['id'];
            }
        }

        $response = $this->actingAs($user)->postJson(route('onboarding.store'), [
            'academic_level' => 'engineering',
            'experience_years' => 3,
            'technologies' => ['php', 'laravel', 'javascript'],
            'goals' => ['backend', 'architecture'],
            'answers' => $answers,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $profile = UserLearningProfile::where('user_id', $user->id)->first();

        $this->assertNotNull($profile);
        $this->assertSame('engineering', $profile->academic_level);
        $this->assertSame('professional', $profile->level);
        $this->assertSame(['php', 'laravel', 'javascript'], $profile->technologies);
        $this->assertNotNull($profile->completed_at);
    }

    public function test_incomplete_onboarding_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'academic_level' => 'licence',
            'experience_years' => 0,
            'technologies' => ['php'],
            'goals' => ['backend'],
            'answers' => [],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('user_learning_profiles', 0);
    }
}
