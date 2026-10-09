<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ReencryptAiKeysTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_cle_est_chiffree_en_base_et_jamais_en_clair(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['ai_provider' => 'gemini', 'ai_api_key' => 'AQ.secret-key-123456'])->save();

        $raw = DB::table('users')->where('id', $user->id)->value('ai_api_key');

        $this->assertStringNotContainsString('secret-key', $raw);
        $this->assertSame('AQ.secret-key-123456', $user->fresh()->ai_api_key);
        $this->assertArrayNotHasKey('ai_api_key', $user->fresh()->toArray());
    }

    public function test_la_commande_rechiffre_avec_la_cle_actuelle(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['ai_provider' => 'gemini', 'ai_api_key' => 'AQ.secret-key-123456'])->save();
        $before = DB::table('users')->where('id', $user->id)->value('ai_api_key');

        $this->artisan('devroad:reencrypt-ai-keys')->assertSuccessful();

        $after = DB::table('users')->where('id', $user->id)->value('ai_api_key');
        $this->assertNotSame($before, $after);
        $this->assertSame('AQ.secret-key-123456', $user->fresh()->ai_api_key);
        $this->assertSame('AQ.secret-key-123456', Crypt::decryptString($after));
    }
}
