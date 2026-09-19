<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get('/search?q=laravel')->assertRedirect('/login');
    }

    public function test_un_terme_trop_court_ne_lance_pas_de_recherche(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('search', ['q' => 'a']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->where('results', null)
                ->where('counts', null));
    }

    public function test_la_recherche_trouve_roadmaps_memos_et_etapes(): void
    {
        $user = User::factory()->create();

        $roadmap = $user->roadmaps()->create(['title' => 'Laravel : le routage']);
        $roadmap->steps()->create(['title' => 'Routage', 'position' => 1]);
        $user->memos()->create(['title' => 'Cycle de vie', 'content' => 'Le routage vient en premier']);
        $user->memos()->create(['title' => 'Le routage expliqué', 'content' => 'autre chose']);
        $user->memos()->create(['title' => 'Hors sujet', 'content' => 'rien à voir']);

        $this->actingAs($user)->get(route('search', ['q' => 'routage']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->where('query', 'routage')
                ->has('results.roadmaps.data', 1)
                ->has('results.memos.data', 2)   // trouvé par titre ET par contenu
                ->has('results.steps.data', 1)
                ->where('results.steps.data.0.roadmap_title', 'Laravel : le routage')
                ->where('counts.roadmaps', 1)
                ->where('counts.memos', 2)
                ->where('counts.steps', 1));
    }

    public function test_la_recherche_ne_retourne_pas_les_donnees_des_autres(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();

        $autre->memos()->create(['title' => 'Secret routage', 'content' => 'privé']);
        $roadmapAutre = $autre->roadmaps()->create(['title' => 'Routage secret']);
        $roadmapAutre->steps()->create(['title' => 'Routage caché', 'position' => 1]);

        $this->actingAs($user)->get(route('search', ['q' => 'routage']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->has('results.roadmaps.data', 0)
                ->has('results.memos.data', 0)
                ->has('results.steps.data', 0)
                ->where('counts.memos', 0));
    }

    public function test_le_filtre_de_type_limite_les_sections_retournees(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create(['title' => 'Routage']);
        $roadmap->steps()->create(['title' => 'Routage', 'position' => 1]);
        $user->memos()->create(['title' => 'Routage', 'content' => 'x']);

        $this->actingAs($user)->get(route('search', ['q' => 'routage', 'type' => 'memos']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->where('type', 'memos')
                ->where('results.roadmaps', null)
                ->where('results.steps', null)
                ->has('results.memos.data', 1));
    }

    public function test_un_type_invalide_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('search', ['q' => 'routage', 'type' => 'nimporte']))
            ->assertSessionHasErrors('type');
    }

    public function test_les_jokers_saisis_sont_traites_comme_du_texte(): void
    {
        $user = User::factory()->create();
        $user->memos()->create(['title' => 'Remise', 'content' => 'promo 100% garantie']);
        $user->memos()->create(['title' => 'Nombre', 'content' => 'valeur 1000 environ']);

        // Sans échappement, « 100% » agirait comme un joker et trouverait les deux.
        $this->actingAs($user)->get(route('search', ['q' => '100%']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->has('results.memos.data', 1)
                ->where('results.memos.data.0.title', 'Remise'));
    }

    public function test_les_resultats_sont_pagines(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 7; $i++) {
            $user->memos()->create(['title' => "Zebra $i", 'content' => 'x']);
        }

        // Onglet « Tout » : 5 par section, total = 7
        $this->actingAs($user)->get(route('search', ['q' => 'zebra']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->has('results.memos.data', 5)
                ->where('results.memos.total', 7)
                ->where('counts.memos', 7));

        // Onglet « memos » : 10 par page
        $this->actingAs($user)->get(route('search', ['q' => 'zebra', 'type' => 'memos']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Search/Index', false)
                ->has('results.memos.data', 7));
    }
}