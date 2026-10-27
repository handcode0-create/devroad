<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PwaInstallTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_installer_est_publique_et_fournit_le_lien_du_qr_code(): void
    {
        $this->get('/installer')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Install')
                ->where('installUrl', route('install', ['ref' => 'qr'])));
    }

    public function test_un_utilisateur_connecte_peut_aussi_ouvrir_la_page_installer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/installer')->assertOk();
    }

    public function test_la_page_installer_figure_dans_le_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('install'), false);
    }

    public function test_le_manifest_est_installable(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertIsArray($manifest, 'Le manifest doit être un JSON valide.');
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertStringStartsWith('/', $manifest['start_url']);

        $sizes = [];
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
            [$width, $height] = getimagesize(public_path(ltrim($icon['src'], '/')));
            $this->assertSame($icon['sizes'], "{$width}x{$height}", "Taille déclarée incorrecte pour {$icon['src']}");
            $sizes[$icon['purpose']][] = $icon['sizes'];
        }

        $this->assertContains('192x192', $sizes['any']);
        $this->assertContains('512x512', $sizes['any']);
        $this->assertContains('512x512', $sizes['maskable']);
    }

    public function test_le_service_worker_et_la_page_hors_ligne_sont_presents(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        $this->assertFileExists(public_path('offline.html'));
        $this->assertStringContainsString("'/offline.html'", $sw);
        $this->assertStringContainsString("addEventListener('fetch'", $sw);
        $this->assertStringContainsString("addEventListener('push'", $sw);

        foreach (['/icons/icon-192.png', '/icons/icon-512.png', '/icondevroad.png'] as $precached) {
            $this->assertStringContainsString("'{$precached}'", $sw);
            $this->assertFileExists(public_path(ltrim($precached, '/')));
        }
    }

    public function test_les_pages_referencent_le_manifest_et_l_icone_apple(): void
    {
        $this->get('/installer')
            ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false)
            ->assertSee('<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">', false);

        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));
    }
}
