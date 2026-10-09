<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_les_en_tetes_de_securite_sont_envoyes(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $this->assertStringContainsString('camera=()', $response->headers->get('Permissions-Policy'));
    }

    public function test_hsts_uniquement_en_https(): void
    {
        $this->get('/login')->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
    }

    public function test_csp_stricte_en_production(): void
    {
        $this->app['env'] = 'production';

        $csp = $this->get('/login')->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    public function test_les_formulaires_d_authentification_sont_limites(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/forgot-password', ['email' => 'x@example.com']);
        }

        $this->post('/forgot-password', ['email' => 'x@example.com'])->assertStatus(429);
    }
}
