<?php

namespace Tests\Feature;

use App\Services\Ai\AiClient;
use App\Services\Ai\AiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiClientConnectionTest extends TestCase
{
    private function messageFor(string $message): string
    {
        Http::fake(fn () => throw new ConnectionException($message));

        try {
            app(AiClient::class)->complete('gemini', 'cle', null, 'sys', 'prompt');
        } catch (AiException $e) {
            return $e->getMessage();
        }

        self::fail('Une AiException était attendue.');
    }

    public function test_une_erreur_de_certificat_est_expliquee(): void
    {
        $this->assertStringContainsString('cacert.pem', $this->messageFor('cURL error 60: SSL certificate problem: unable to get local issuer certificate'));
    }

    public function test_une_erreur_dns_est_expliquee(): void
    {
        $this->assertStringContainsString('DNS', $this->messageFor('cURL error 6: Could not resolve host: generativelanguage.googleapis.com'));
    }

    public function test_un_delai_depasse_est_explique(): void
    {
        $this->assertStringContainsString('trop de temps', $this->messageFor('cURL error 28: Operation timed out'));
    }
}
