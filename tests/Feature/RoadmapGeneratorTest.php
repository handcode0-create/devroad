<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_creation_d_une_roadmap_genere_automatiquement_ses_cours(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('roadmaps.store'), [
                'title' => 'Mon parcours Laravel',
                'technology' => 'laravel',
                'status' => 'active',
            ])
            ->assertRedirect();

        $roadmap = $user->roadmaps()->first();

        $this->assertNotNull($roadmap);
        $this->assertSame('Mon parcours Laravel', $roadmap->title);
        $this->assertSame('laravel', $roadmap->technology);
        $this->assertSame(13, $roadmap->steps()->count());

        $first = $roadmap->steps()->where('position', 1)->first();
        $last = $roadmap->steps()->where('position', 13)->first();

        $this->assertSame('Découvrir Laravel', $first->title);
        $this->assertSame('in_progress', $first->status);
        $this->assertNotEmpty($first->objective);
        $this->assertNotEmpty($first->content);
        $this->assertNotEmpty($first->code_example);
        $this->assertSame('routes/web.php', $first->workspace_file);
        $this->assertSame('laravel', $first->workspace_language);
        $this->assertNotEmpty($first->exercise_title);
        $this->assertNotEmpty($first->exercise_description);
        $this->assertNotEmpty($first->exercise_hint);
        $this->assertNotEmpty($first->exercise_solution);
        $this->assertNull($first->exercise_completed_at);

        $this->assertSame('Projet final : construire un module', $last->title);
        $this->assertSame(60, $last->estimated_minutes);
        $this->assertSame('todo', $last->status);
    }

    public function test_la_creation_d_un_parcours_inconnu_est_refusee_par_la_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('roadmaps.store'), [
                'technology' => 'inconnue',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('technology');

        $this->assertDatabaseCount('roadmaps', 0);
        $this->assertDatabaseCount('roadmap_steps', 0);
    }

    public function test_le_generateur_preserve_la_propriete_du_parcours(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->post(route('roadmaps.store'), [
                'technology' => 'react',
                'title' => 'Mon parcours React',
                'status' => 'active',
                'user_id' => $other->id,
            ])
            ->assertRedirect();

        $roadmap = $user->roadmaps()->first();

        $this->assertNotNull($roadmap);
        $this->assertSame($user->id, $roadmap->user_id);
        $this->assertSame(7, $roadmap->steps()->count());
        $this->assertDatabaseMissing('roadmaps', [
            'user_id' => $other->id,
            'title' => 'Mon parcours React',
        ]);
    }
}
