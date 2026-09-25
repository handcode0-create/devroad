<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserLearningProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_with_completed_learning_profile_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        UserLearningProfile::create([
            'user_id' => $user->id,
            'academic_level' => 'licence',
            'level' => 'intermediate',
            'level_source' => 'assessment',
            'experience_years' => 2,
            'technologies' => ['php'],
            'goals' => ['backend'],
            'assessment_scores' => [],
            'assessment_answers' => [],
            'completed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('app.splash', absolute: false));
    }

    public function test_users_without_learning_profile_are_sent_to_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding.level', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
