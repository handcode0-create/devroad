<?php

namespace Tests\Feature;

use Tests\TestCase;

class RailwayProductionTest extends TestCase
{
    public function test_login_trusts_railway_forwarded_https_headers(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '203.0.113.10',
        ])->get('/login');

        $response->assertOk();

        $this->assertTrue(request()->isSecure());
        $this->assertStringStartsWith('https://', url('/'));
        $this->assertStringStartsWith('https://', asset('build/assets/app.js'));
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/up')->assertOk();
    }
}
