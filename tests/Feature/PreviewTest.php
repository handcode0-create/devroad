<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_apercu_est_servi_avec_sa_propre_csp_et_sandbox(): void
    {
        $user = User::factory()->create();

        $url = $this->actingAs($user)->postJson('/preview', ['html' => '<script>document.title="ok"</script>'])
            ->assertOk()->json('url');

        $response = $this->actingAs($user)->get($url)->assertOk();
        $response->assertSee('document.title="ok"', false);
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringStartsWith('sandbox allow-scripts;', $csp);
        $this->assertStringContainsString("connect-src 'none'", $csp);
    }

    public function test_l_apercu_d_un_autre_utilisateur_est_introuvable(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $url = $this->actingAs($owner)->postJson('/preview', ['html' => '<p>secret</p>'])->json('url');

        $this->actingAs($other)->get($url)->assertNotFound();
    }

    public function test_un_visiteur_ne_peut_pas_creer_d_apercu(): void
    {
        $this->postJson('/preview', ['html' => '<p>x</p>'])->assertUnauthorized();
    }
}
