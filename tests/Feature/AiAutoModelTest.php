<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAutoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_modele_vide_est_choisi_automatiquement_selon_la_cle(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response(['models' => [
                    ['name' => 'models/gemini-3.8-flash', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/gemini-3.1-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/text-embedding-004', 'supportedGenerationMethods' => ['embedContent']],
                ]]);
            }

            return Http::response(['candidates' => [['content' => ['parts' => [['text' => 'OK']]]]]]);
        });

        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile/ai', ['provider' => 'gemini', 'model' => '', 'api_key' => 'AQ.cle-de-test-1234567890'])
            ->assertSessionHasNoErrors();

        $this->assertSame('gemini-3.8-flash', $user->fresh()->ai_model);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-3.8-flash:generateContent'));
    }

    public function test_le_modele_par_defaut_est_garde_s_il_est_propose_par_la_cle(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response(['models' => [
                    ['name' => 'models/gemini-3.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
                    ['name' => 'models/gemini-3.8-flash', 'supportedGenerationMethods' => ['generateContent']],
                ]]);
            }

            return Http::response(['candidates' => [['content' => ['parts' => [['text' => 'OK']]]]]]);
        });

        $user = User::factory()->create();
        $this->actingAs($user)->put('/profile/ai', ['provider' => 'gemini', 'api_key' => 'AQ.autre-cle-1234567890']);

        $this->assertSame('gemini-3.5-flash-lite', $user->fresh()->ai_model);
    }
}
