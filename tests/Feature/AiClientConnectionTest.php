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

    public function test_gemini_retombe_sur_un_modele_disponible_quand_le_modele_est_introuvable(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'gemini-2.5-flash:generateContent')) {
                return Http::response(['error' => ['code' => 404, 'message' => 'models/gemini-2.5-flash is not found', 'status' => 'NOT_FOUND']], 404);
            }

            if ($request->method() === 'GET') {
                return Http::response(['models' => [
                    ['name' => 'models/gemini-3.8-flash', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/gemini-embedding-001', 'supportedGenerationMethods' => ['embedContent']],
                ]]);
            }

            return Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Salut']]]]]]);
        });

        $text = app(AiClient::class)->complete('gemini', 'cle-test', 'gemini-2.5-flash', 'sys', 'prompt');

        $this->assertSame('Salut', $text);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-3.8-flash:generateContent'));
    }

    public function test_gemini_essaie_les_modeles_actuels_quand_la_liste_des_modeles_est_inaccessible(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response(['error' => ['message' => 'denied']], 403);
            }

            if (str_contains($request->url(), 'gemini-2.5-flash:generateContent')) {
                return Http::response(['error' => ['code' => 404, 'message' => 'models/gemini-2.5-flash is not found', 'status' => 'NOT_FOUND']], 404);
            }

            return Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Salut']]]]]]);
        });

        $client = app(AiClient::class);
        $text = $client->complete('gemini', 'cle-sans-liste', 'gemini-2.5-flash', 'sys', 'prompt');

        $this->assertSame('Salut', $text);
        $this->assertSame('gemini-3.5-flash-lite', $client->lastModel);
    }
}
