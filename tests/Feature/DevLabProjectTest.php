<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevLabProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_ne_peut_pas_lister_les_projets_devlab(): void
    {
        $this->getJson('/devlab/projects')->assertUnauthorized();
    }

    public function test_un_projet_est_cree_avec_ses_fichiers_de_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/devlab/projects', ['name' => 'Mon projet', 'template' => 'html'])
            ->assertCreated()->assertJsonPath('project.name', 'Mon projet')->assertJsonPath('project.runtime', 'browser');
        $this->assertDatabaseHas('devlab_projects', ['user_id' => $user->id, 'name' => 'Mon projet']);
        $this->assertDatabaseHas('devlab_files', ['path' => 'index.html']);
    }

    public function test_un_utilisateur_ne_peut_pas_acceder_au_projet_dun_autre(): void
    {
        $owner = User::factory()->create(); $intruder = User::factory()->create();
        $project = $owner->devLabProjects()->create(['name' => 'Privé', 'template' => 'html', 'runtime' => 'browser']);
        $project->files()->create(['path' => 'index.html', 'content' => 'A', 'size' => 1]);
        $this->actingAs($intruder)->getJson(route('devlab.projects.show', $project))->assertForbidden();
        $this->actingAs($intruder)->patchJson(route('devlab.projects.update', $project), ['name' => 'Piraté'])->assertForbidden();
        $this->actingAs($intruder)->deleteJson(route('devlab.projects.destroy', $project))->assertForbidden();
        $this->assertDatabaseHas('devlab_projects', ['id' => $project->id, 'name' => 'Privé']);
    }

    public function test_un_workspace_legacy_peut_etre_importe(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('devlab.projects.import-legacy'), [
                'name' => 'Workspace importé',
                'template' => 'html',
                'files' => [
                    ['path' => 'index.html', 'content' => '<h1>DevRoad</h1>'],
                    ['path' => 'main.js', 'content' => 'console.log("ok");'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('project.name', 'Workspace importé')
            ->assertJsonPath('project.runtime', 'browser');

        $this->assertDatabaseHas('devlab_projects', [
            'user_id' => $user->id,
            'name' => 'Workspace importé',
        ]);
        $this->assertDatabaseHas('devlab_files', [
            'path' => 'index.html',
            'content' => '<h1>DevRoad</h1>',
        ]);
        $this->assertDatabaseHas('devlab_files', [
            'path' => 'main.js',
            'content' => 'console.log("ok");',
        ]);
    }

    public function test_un_import_legacy_refuse_un_chemin_dangereux(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('devlab.projects.import-legacy'), [
                'name' => 'Import invalide',
                'template' => 'html',
                'files' => [
                    ['path' => '../secret.php', 'content' => 'secret'],
                ],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('devlab_projects', 0);
    }

    public function test_une_etape_peut_ouvrir_un_projet_devlab_lie(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create([
            'title' => 'Laravel',
            'technology' => 'laravel',
            'status' => 'active',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'Installer Laravel',
            'position' => 1,
            'status' => 'in_progress',
            'code_example' => "<?php\\n\\necho 'DevRoad';",
            'workspace_language' => 'php',
            'workspace_file' => 'main.php',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('devlab.projects.for-step', $step));

        $response
            ->assertCreated()
            ->assertJsonPath('created', true)
            ->assertJsonPath('project.roadmap_step_id', $step->id)
            ->assertJsonPath('project.template', 'php');

        $projectId = $response->json('project.id');

        $this->actingAs($user)
            ->postJson(route('devlab.projects.for-step', $step))
            ->assertOk()
            ->assertJsonPath('created', false)
            ->assertJsonPath('project.id', $projectId);

        $this->assertDatabaseCount('devlab_projects', 1);
        $this->assertDatabaseHas('devlab_files', [
            'devlab_project_id' => $projectId,
            'path' => 'main.php',
            'content' => "<?php\\n\\necho 'DevRoad';",
        ]);
    }

    public function test_un_utilisateur_ne_peut_pas_lier_un_projet_a_une_etape_dun_autre(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $roadmap = $owner->roadmaps()->create([
            'title' => 'PHP',
            'technology' => 'php',
            'status' => 'active',
        ]);
        $step = $roadmap->steps()->create([
            'title' => 'PHP',
            'position' => 1,
            'status' => 'in_progress',
        ]);

        $this->actingAs($intruder)
            ->postJson(route('devlab.projects.for-step', $step))
            ->assertForbidden();

        $this->assertDatabaseCount('devlab_projects', 0);
    }

    public function test_les_modeles_devlab_utilisent_les_tables_persistantes(): void
    {
        $this->assertSame('devlab_projects', (new \\App\\Models\\DevLabProject())->getTable());
        $this->assertSame('devlab_files', (new \\App\\Models\\DevLabFile())->getTable());
    }

    public function test_un_projet_peut_etre_renomme_et_duplique(): void
    {
        $user = User::factory()->create();
        $project = $user->devLabProjects()->create(['name' => 'Original', 'template' => 'php', 'runtime' => 'server']);
        $project->files()->create(['path' => 'main.php', 'content' => '<?php echo "ok";', 'size' => 16]);
        $this->actingAs($user)->patchJson(route('devlab.projects.update', $project), ['name' => 'Renommé'])->assertOk();
        $this->actingAs($user)->postJson(route('devlab.projects.duplicate', $project))->assertCreated()->assertJsonPath('project.name', 'Renommé — copie');
        $this->assertDatabaseCount('devlab_projects', 2); $this->assertDatabaseCount('devlab_files', 2);
    }

    public function test_un_chemin_dangereux_est_refuse(): void
    {
        $user = User::factory()->create();
        $project = $user->devLabProjects()->create(['name' => 'Sécurité', 'template' => 'html', 'runtime' => 'browser']);
        $this->actingAs($user)->postJson(route('devlab.projects.files.store', $project), ['path' => '../secret.php', 'content' => 'secret'])->assertStatus(422);
        $this->actingAs($user)->postJson(route('devlab.projects.files.store', $project), ['path' => '.env', 'content' => 'APP_KEY=secret'])->assertStatus(422);
    }

    public function test_un_fichier_ne_peut_pas_etre_utilise_via_un_autre_projet(): void
    {
        $user = User::factory()->create();
        $first = $user->devLabProjects()->create(['name' => 'A', 'template' => 'html', 'runtime' => 'browser']);
        $second = $user->devLabProjects()->create(['name' => 'B', 'template' => 'html', 'runtime' => 'browser']);
        $file = $first->files()->create(['path' => 'index.html', 'content' => 'A', 'size' => 1]);
        $this->actingAs($user)->patchJson(route('devlab.projects.files.update', [$second, $file]), ['path' => 'index.html', 'content' => 'B'])->assertNotFound();
        $this->assertDatabaseHas('devlab_files', ['id' => $file->id, 'content' => 'A']);
    }
}
