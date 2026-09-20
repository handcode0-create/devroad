<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_preferences_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile/preferences', [
                'learning_goal' => 'Construire des projets',
                'daily_goal_minutes' => 60,
                'weekly_goal_sessions' => 5,
                'preferred_technology' => 'laravel',
                'email_notifications' => false,
                'learning_reminders' => true,
                'light_mode' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'preferences-updated')
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Construire des projets', $user->learning_goal);
        $this->assertSame(60, $user->daily_goal_minutes);
        $this->assertSame(5, $user->weekly_goal_sessions);
        $this->assertSame('laravel', $user->preferred_technology);
        $this->assertFalse($user->email_notifications);
        $this->assertTrue($user->learning_reminders);
        $this->assertTrue($user->light_mode);
    }

    public function test_invalid_profile_preferences_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile/preferences', [
                'learning_goal' => 'x',
                'daily_goal_minutes' => 25,
                'weekly_goal_sessions' => 9,
                'preferred_technology' => 'unknown-stack',
                'email_notifications' => 'no',
                'learning_reminders' => 'yes',
            ])
            ->assertSessionHasErrors([
                'daily_goal_minutes',
                'weekly_goal_sessions',
                'preferred_technology',
                'email_notifications',
                'learning_reminders',
            ])
            ->assertRedirect('/profile');
    }

    public function test_light_mode_can_be_toggled_without_submitting_other_preferences(): void
    {
        $user = User::factory()->create([
            'light_mode' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/theme', [
                'light_mode' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'theme-updated')
            ->assertRedirect('/profile');

        $this->assertTrue($user->refresh()->light_mode);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
