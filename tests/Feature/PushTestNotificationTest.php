<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushTestNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function subscribe(User $user): void
    {
        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', 'https://push.example/abc'),
            'endpoint' => 'https://push.example/abc',
            'public_key' => 'key',
            'auth_token' => 'auth',
            'content_encoding' => 'aesgcm',
        ]);
    }

    private function fakeNotifier(bool $configured, int $delivered): void
    {
        $this->app->instance(PushNotifier::class, new class($configured, $delivered) extends PushNotifier
        {
            public function __construct(private bool $isConfigured, private int $delivered) {}

            public function configured(): bool
            {
                return $this->isConfigured;
            }

            public function send($user, string $title, string $body, string $url): int
            {
                return $this->delivered;
            }
        });
    }

    public function test_guest_cannot_send_a_test_notification(): void
    {
        $this->postJson('/push-subscription/test')->assertUnauthorized();
    }

    public function test_server_without_vapid_keys_answers_503(): void
    {
        $user = User::factory()->create();
        $this->subscribe($user);
        $this->fakeNotifier(configured: false, delivered: 0);

        $this->actingAs($user)->postJson('/push-subscription/test')->assertStatus(503);
    }

    public function test_user_without_device_gets_a_clear_error(): void
    {
        $user = User::factory()->create();
        $this->fakeNotifier(configured: true, delivered: 1);

        $this->actingAs($user)->postJson('/push-subscription/test')->assertStatus(422);
    }

    public function test_test_notification_is_sent_to_the_user_devices(): void
    {
        $user = User::factory()->create();
        $this->subscribe($user);
        $this->fakeNotifier(configured: true, delivered: 1);

        $this->actingAs($user)->postJson('/push-subscription/test')
            ->assertOk()
            ->assertJson(['ok' => true, 'delivered' => 1]);
    }

    public function test_failed_delivery_answers_502(): void
    {
        $user = User::factory()->create();
        $this->subscribe($user);
        $this->fakeNotifier(configured: true, delivered: 0);

        $this->actingAs($user)->postJson('/push-subscription/test')->assertStatus(502);
    }
}
