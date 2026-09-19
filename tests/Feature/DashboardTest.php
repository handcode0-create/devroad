<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_un_nouvel_utilisateur_voit_des_compteurs_a_zero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard', false)
                ->where('stats.roadmaps', 0)
                ->where('stats.memos', 0)
                ->where('stats.steps_total', 0)
                ->where('stats.progress', 0)
                ->has('recent_roadmaps', 0));
    }

    public function test_les_statistiques_ne_comptent_que_les_donnees_de_lutilisateur(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();

        $laravel = $user->roadmaps()->create(['title' => 'Laravel']);
        $laravel->steps()->create(['title' => 'Intro', 'position' => 1, 'status' => 'completed']);
        $laravel->steps()->create(['title' => 'Routage', 'position' => 2]);

        $react = $user->roadmaps()->create(['title' => 'React']);
        $react->steps()->create(['title' => 'JSX', 'position' => 1]);
        $react->steps()->create(['title' => 'Hooks', 'position' => 2]);

        $user->memos()->create(['title' => 'Git', 'content' => 'x']);
        $user->memos()->create(['title' => 'API', 'content' => 'y']);
        $user->memos()->create(['title' => 'Hooks', 'content' => 'z']);

        // Données d'un autre compte : ne doivent apparaître nulle part
        $autreRoadmap = $autre->roadmaps()->create(['title' => 'Vue']);
        $autreRoadmap->steps()->create(['title' => 'Intro', 'position' => 1, 'status' => 'completed']);
        $autre->memos()->create(['title' => 'Secret', 'content' => 'privé']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard', false)
                ->where('stats.roadmaps', 2)
                ->where('stats.memos', 3)
                ->where('stats.steps_total', 4)
                ->where('stats.steps_completed', 1)
                ->where('stats.progress', 25)
                ->has('recent_roadmaps', 2));
    }

    public function test_seules_les_trois_roadmaps_les_plus_recentes_sont_listees_avec_leur_progression(): void
    {
        $user = User::factory()->create();

        foreach (['A', 'B', 'C', 'D'] as $titre) {
            $this->travel(1)->minutes();
            $user->roadmaps()->create(['title' => $titre]);
        }

        $roadmapC = $user->roadmaps()->where('title', 'C')->first();
        $roadmapC->steps()->create(['title' => 'Étape 1', 'position' => 1, 'status' => 'completed']);
        $roadmapC->steps()->create(['title' => 'Étape 2', 'position' => 2]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard', false)
                ->has('recent_roadmaps', 3)
                // C vient d'être modifiée (ajout d'étapes) : elle passe en tête
                ->where('recent_roadmaps.0.title', 'C')
                ->where('recent_roadmaps.0.progress', 50)
                ->where('recent_roadmaps.0.steps_count', 2)
                ->where('recent_roadmaps.0.current_step.title', 'Étape 2')
                ->where('recent_roadmaps.0.current_step.status', 'todo'));

    }

    public function test_terminer_une_etape_remonte_sa_roadmap_en_tete(): void
    {
        $user = User::factory()->create();

        $ancienne = $user->roadmaps()->create(['title' => 'Ancienne']);
        $etape = $ancienne->steps()->create(['title' => 'Intro', 'position' => 1]);

        $this->travel(1)->hours();
        $user->roadmaps()->create(['title' => 'Récente']);

        $this->travel(1)->hours();
        $this->actingAs($user)
            ->patch(route('steps.status', $etape), ['status' => 'completed'])
            ->assertRedirect();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard', false)
                ->where('recent_roadmaps.0.title', 'Ancienne')
                ->where('recent_roadmaps.0.progress', 100));
    }
}
