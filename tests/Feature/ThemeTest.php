<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_nouvel_utilisateur_a_le_theme_nuit(): void
    {
        $this->assertSame('nuit', User::factory()->create()->fresh()->theme);
    }

    public function test_chaque_theme_peut_etre_choisi_sans_quitter_la_page(): void
    {
        $user = User::factory()->create();

        foreach (User::THEMES as $theme) {
            $this->actingAs($user)
                ->from('/memos')
                ->patch('/profile/theme', ['theme' => $theme])
                ->assertSessionHasNoErrors()
                ->assertRedirect('/memos');

            $this->assertSame($theme, $user->fresh()->theme);
        }
    }

    public function test_un_theme_inconnu_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/memos')
            ->patch('/profile/theme', ['theme' => 'rose-fluo'])
            ->assertSessionHasErrors('theme');

        $this->assertSame('nuit', $user->fresh()->theme);
    }

    public function test_les_themes_clairs_gardent_le_mode_clair_synchronise(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile/theme', ['theme' => 'sable']);
        $this->assertTrue($user->fresh()->light_mode);

        $this->actingAs($user)->patch('/profile/theme', ['theme' => 'minuit']);
        $this->assertFalse($user->fresh()->light_mode);
    }

    public function test_l_ancien_interrupteur_mode_clair_choisit_clair_ou_nuit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile/theme', ['light_mode' => true]);
        $this->assertSame('clair', $user->fresh()->theme);

        $this->actingAs($user)->patch('/profile/theme', ['light_mode' => false]);
        $this->assertSame('nuit', $user->fresh()->theme);
    }

    public function test_un_visiteur_ne_peut_pas_changer_de_theme(): void
    {
        $this->patch('/profile/theme', ['theme' => 'clair'])->assertRedirect('/login');
    }
}
