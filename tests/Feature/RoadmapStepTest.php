<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RoadmapStepTest extends TestCase
{
    use RefreshDatabase;

    private function roadmapAvecEtapes(User $user, int $nombre = 3)
    {
        $roadmap = $user->roadmaps()->create(['title' => 'Laravel']);

        for ($i = 1; $i <= $nombre; $i++) {
            $roadmap->steps()->create(['title' => "Étape $i", 'position' => $i]);
        }

        return $roadmap;
    }

    public function test_une_etape_creee_apparait_dans_la_bonne_roadmap_a_la_fin(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 2);

        $this->actingAs($user)
            ->post(route('roadmaps.steps.store', $roadmap), ['title' => 'Routage'])
            ->assertRedirect(route('roadmaps.show', $roadmap));

        $this->assertDatabaseHas('roadmap_steps', [
            'roadmap_id' => $roadmap->id,
            'title' => 'Routage',
            'position' => 3,
            'status' => 'todo',
        ]);
    }

    public function test_une_etape_sans_titre_est_refusee(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 0);

        $this->actingAs($user)
            ->post(route('roadmaps.steps.store', $roadmap), ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('roadmap_steps', 0);
    }

    public function test_on_ne_peut_pas_ajouter_une_etape_a_la_roadmap_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($proprietaire, 0);

        $this->actingAs($intrus)
            ->post(route('roadmaps.steps.store', $roadmap), ['title' => 'Intrusion'])
            ->assertForbidden();

        $this->assertDatabaseCount('roadmap_steps', 0);
    }

    public function test_terminer_une_etape_augmente_la_progression(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $premiere = $roadmap->steps()->first();

        $this->assertSame(0, $roadmap->fresh()->progress);

        $this->actingAs($user)
            ->patch(route('steps.status', $premiere), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame(33, $roadmap->fresh()->progress);
    }

    public function test_terminer_une_etape_lance_automatiquement_la_suivante(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $premiere = $roadmap->steps()->where('position', 1)->first();
        $deuxieme = $roadmap->steps()->where('position', 2)->first();

        $this->actingAs($user)
            ->patch(route('steps.status', $premiere), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $premiere->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $deuxieme->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_reouvrir_une_etape_ne_modifie_pas_le_statut_de_la_suivante(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $premiere = $roadmap->steps()->where('position', 1)->first();
        $deuxieme = $roadmap->steps()->where('position', 2)->first();

        $premiere->update(['status' => 'completed']);
        $deuxieme->update(['status' => 'in_progress']);

        $this->actingAs($user)
            ->patch(route('steps.status', $premiere), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $premiere->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $deuxieme->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_une_etape_suivante_est_verrouillee_tant_que_la_courante_n_est_pas_terminee(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $premiere = $roadmap->steps()->where('position', 1)->first();
        $deuxieme = $roadmap->steps()->where('position', 2)->first();

        $this->actingAs($user)
            ->get(route('steps.show', $deuxieme))
            ->assertRedirect(route('steps.show', $premiere));
    }

    public function test_consulter_l_etape_courante_enregistre_le_suivi(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 1);
        $step = $roadmap->steps()->first();

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $step))
            ->assertOk();

        $step->refresh();

        $this->assertSame('in_progress', $step->status);
        $this->assertNotNull($step->last_viewed_at);
    }

    public function test_le_cours_expose_la_progression_de_sa_roadmap(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $premiere = $roadmap->steps()->where('position', 1)->first();
        $premiere->update(['status' => 'completed']);
        $roadmap->steps()->where('position', 2)->first()->update(['status' => 'in_progress']);

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $premiere))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Steps/Show', false)
                ->where('roadmap.steps_count', 3)
                ->where('roadmap.completed_steps_count', 1)
                ->where('roadmap.technology', null)
            );
    }

    public function test_une_lecon_php_expose_un_ide_et_un_terminal(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'PHP',
            'technology' => 'php',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Variables',
            'position' => 1,
            'code_example' => "<?php\n\n\$name = 'DevRoad';",
        ]);

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $step))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Steps/Show', false)
                ->where('step.workspace.enabled', true)
                ->where('step.workspace.language', 'php')
                ->where('step.workspace.filename', 'main.php')
                ->where('step.workspace.run_command', 'php main.php')
            );
    }

    public function test_une_lecon_laravel_expose_un_workspace_complet(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'Laravel',
            'technology' => 'laravel',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Les routes',
            'position' => 1,
            'code_example' => "use Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/hello', fn () => 'Hello');",
        ]);

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $step))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Steps/Show', false)
                ->where('step.workspace.enabled', true)
                ->where('step.workspace.language', 'laravel')
                ->where('step.workspace.filename', 'routes/web.php')
                ->where('step.workspace.run_command', 'php artisan route:list')
            );
    }

    public function test_le_runner_laravel_est_isole_par_utilisateur_et_etape(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'Laravel',
            'technology' => 'laravel',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Routes',
            'position' => 1,
        ]);

        $this->mock(\App\Services\CodeRunnerService::class, function ($mock) use ($user, $step) {
            $mock->shouldReceive('runLaravel')
                ->once()
                ->with(
                    $user->id,
                    $step->id,
                    'php artisan route:list',
                    'routes/web.php',
                    "Route::get('/hello', fn () => 'Hello');"
                )
                ->andReturn([
                    'status' => 'success',
                    'stdout' => 'GET /hello',
                    'stderr' => '',
                    'exit_code' => 0,
                    'duration_ms' => 40,
                ]);
        });

        $this->actingAs($user)
            ->postJson(route('steps.run', $step), [
                'language' => 'laravel',
                'command' => 'php artisan route:list',
                'file_path' => 'routes/web.php',
                'code' => "Route::get('/hello', fn () => 'Hello');",
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'exit_code' => 0,
            ]);
    }

    public function test_une_lecon_laravel_utilise_l_ide_navigateur_sans_docker(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'Laravel',
            'technology' => 'laravel',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Les routes',
            'position' => 1,
        ]);

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $step))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Steps/Show', false)
                ->where('step.workspace.runtime', 'browser')
                ->where('step.workspace.preview_enabled', false)
                ->where('step.workspace.language', 'laravel')
            );
    }

    public function test_une_lecon_html_expose_un_apercu_navigateur(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'HTML',
            'technology' => 'html',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Structure HTML',
            'position' => 1,
            'code_example' => '<!doctype html><html><body><h1>DevRoad</h1></body></html>',
        ]);

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('steps.show', $step))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Steps/Show', false)
                ->where('step.workspace.runtime', 'browser')
                ->where('step.workspace.preview_enabled', true)
                ->where('step.workspace.filename', 'index.html')
            );
    }

    public function test_un_utilisateur_ne_peut_pas_executer_le_code_d_un_autre(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $roadmap = $owner->roadmaps()->create([
            'title' => 'PHP',
            'technology' => 'php',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Variables',
            'position' => 1,
        ]);

        $this->actingAs($intruder)
            ->postJson(route('steps.run', $step), [
                'language' => 'php',
                'code' => "<?php echo 'x';",
            ])
            ->assertForbidden();
    }

    public function test_le_runner_peut_etre_moque_pour_tester_l_execution(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'PHP',
            'technology' => 'php',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Variables',
            'position' => 1,
        ]);

        $this->mock(\App\Services\CodeRunnerService::class, function ($mock) {
            $mock->shouldReceive('run')
                ->once()
                ->with('php', "<?php echo 'Hello';")
                ->andReturn([
                    'status' => 'success',
                    'stdout' => 'Hello',
                    'stderr' => '',
                    'exit_code' => 0,
                    'duration_ms' => 25,
                ]);
        });

        $this->actingAs($user)
            ->postJson(route('steps.run', $step), [
                'language' => 'php',
                'code' => "<?php echo 'Hello';",
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'stdout' => 'Hello',
                'exit_code' => 0,
            ]);
    }

    public function test_un_exercice_peut_etre_valide_et_reouvert(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 1);
        $step = $roadmap->steps()->first();

        $step->update([
            'exercise_title' => 'Créer une route',
            'exercise_description' => 'Ajoute une route GET /hello.',
            'exercise_hint' => 'Utilise Route::get().',
            'exercise_solution' => "Route::get('/hello', fn () => 'Hello');",
        ]);

        $this->actingAs($user)
            ->patch(route('steps.exercise', $step), ['completed' => true])
            ->assertRedirect();

        $this->assertNotNull($step->fresh()->exercise_completed_at);

        $this->actingAs($user)
            ->patch(route('steps.exercise', $step), ['completed' => false])
            ->assertRedirect();

        $this->assertNull($step->fresh()->exercise_completed_at);
    }

    public function test_une_etape_avec_exercice_ne_peut_pas_etre_terminee_sans_valider_l_exercice(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 1);
        $step = $roadmap->steps()->first();

        $step->update([
            'exercise_title' => 'Créer une route',
            'exercise_description' => 'Ajoute une route.',
            'exercise_hint' => 'Utilise Route::get().',
            'exercise_solution' => 'Route::get(...);',
        ]);

        $this->actingAs($user)
            ->patch(route('steps.status', $step), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('todo', $step->fresh()->status);
    }

    public function test_une_etape_future_ne_peut_pas_etre_terminee_directement(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 3);
        $deuxieme = $roadmap->steps()->where('position', 2)->first();

        $this->actingAs($user)
            ->patch(route('steps.status', $deuxieme), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $deuxieme->id,
            'status' => 'todo',
        ]);
    }

    public function test_un_statut_invalide_est_refuse(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 1);
        $etape = $roadmap->steps()->first();

        $this->actingAs($user)
            ->patch(route('steps.status', $etape), ['status' => 'fini-ou-presque'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('roadmap_steps', ['id' => $etape->id, 'status' => 'todo']);
    }

    public function test_un_intrus_ne_peut_ni_changer_le_statut_ni_modifier_ni_supprimer(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $etape = $this->roadmapAvecEtapes($proprietaire, 1)->steps()->first();

        $this->actingAs($intrus)
            ->patch(route('steps.status', $etape), ['status' => 'completed'])
            ->assertForbidden();

        $this->actingAs($intrus)
            ->put(route('steps.update', $etape), ['title' => 'Piratée'])
            ->assertForbidden();

        $this->actingAs($intrus)
            ->delete(route('steps.destroy', $etape))
            ->assertForbidden();

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $etape->id,
            'title' => 'Étape 1',
            'status' => 'todo',
        ]);
    }

    public function test_roadmap_id_ne_peut_pas_etre_modifie_depuis_le_formulaire(): void
    {
        $user = User::factory()->create();
        $roadmapA = $this->roadmapAvecEtapes($user, 1);
        $roadmapB = $user->roadmaps()->create(['title' => 'React']);
        $etape = $roadmapA->steps()->first();

        $this->actingAs($user)
            ->put(route('steps.update', $etape), [
                'title' => 'Renommée',
                'roadmap_id' => $roadmapB->id,
            ])->assertRedirect();

        $this->assertDatabaseHas('roadmap_steps', [
            'id' => $etape->id,
            'title' => 'Renommée',
            'roadmap_id' => $roadmapA->id,
        ]);
    }

    public function test_le_proprietaire_peut_supprimer_une_etape(): void
    {
        $user = User::factory()->create();
        $roadmap = $this->roadmapAvecEtapes($user, 2);
        $etape = $roadmap->steps()->first();

        $this->actingAs($user)
            ->delete(route('steps.destroy', $etape))
            ->assertRedirect(route('roadmaps.show', $roadmap));

        $this->assertDatabaseMissing('roadmap_steps', ['id' => $etape->id]);
        $this->assertDatabaseCount('roadmap_steps', 1);
    }
}