<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->post('/roadmaps', ['title' => 'Laravel'])
            ->assertRedirect('/login');
    }

    public function test_une_roadmap_creee_appartient_a_lutilisateur_connecte(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();

        // On tente de forcer un autre user_id : il doit être ignoré.
        $this->actingAs($user)->post('/roadmaps', [
            'title' => 'Laravel',
            'status' => 'active',
            'user_id' => $autre->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('roadmaps', [
            'title' => 'Laravel',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('roadmaps', ['user_id' => $autre->id]);
    }

    public function test_une_roadmap_sans_titre_est_refusee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/roadmaps', ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->actingAs($user)->post('/roadmaps', ['title' => 'ab'])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('roadmaps', 0);
    }

    public function test_un_statut_invalide_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/roadmaps', [
            'title' => 'Laravel',
            'status' => 'nimporte-quoi',
        ])->assertSessionHasErrors('status');
    }

    public function test_le_proprietaire_peut_modifier_sa_roadmap(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create(['title' => 'Laravel']);

        $this->actingAs($user)
            ->put(route('roadmaps.update', $roadmap), ['title' => 'Laravel avancé'])
            ->assertRedirect();

        $this->assertDatabaseHas('roadmaps', ['id' => $roadmap->id, 'title' => 'Laravel avancé']);
    }

    public function test_un_utilisateur_ne_peut_pas_modifier_la_roadmap_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $roadmap = $proprietaire->roadmaps()->create(['title' => 'Laravel']);

        $this->actingAs($intrus)
            ->put(route('roadmaps.update', $roadmap), ['title' => 'Piratée'])
            ->assertForbidden();

        $this->assertDatabaseHas('roadmaps', ['id' => $roadmap->id, 'title' => 'Laravel']);
    }

    public function test_un_utilisateur_ne_peut_pas_supprimer_la_roadmap_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $roadmap = $proprietaire->roadmaps()->create(['title' => 'Laravel']);

        $this->actingAs($intrus)
            ->delete(route('roadmaps.destroy', $roadmap))
            ->assertForbidden();

        $this->assertDatabaseHas('roadmaps', ['id' => $roadmap->id]);
    }

    public function test_supprimer_une_roadmap_supprime_ses_etapes(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create(['title' => 'Laravel']);
        $roadmap->steps()->create(['title' => 'Introduction', 'position' => 1]);

        $this->actingAs($user)
            ->delete(route('roadmaps.destroy', $roadmap))
            ->assertRedirect(route('roadmaps.index'));

        $this->assertDatabaseCount('roadmaps', 0);
        $this->assertDatabaseCount('roadmap_steps', 0);
    }
}