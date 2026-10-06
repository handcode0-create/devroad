<?php

namespace Tests\Feature;

use App\Models\SandboxInstance;
use App\Models\SandboxProcess;
use App\Models\SandboxProject;
use App\Models\User;
use App\Services\Sandbox\DaytonaSandboxExecutor;
use App\Services\Sandbox\SandboxProcessRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Historique des processus d'un Sandbox (table sandbox_processes) :
 * installation, serveur de développement et commandes.
 */
class SandboxProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.api_key', 'test-key');
        config()->set('sandbox.default_region', 'us');
        config()->set('sandbox.toolbox_url', 'https://proxy.app.daytona.io/toolbox');
    }

    public function test_le_provisionnement_enregistre_l_installation_et_le_serveur(): void
    {
        $this->fauxDaytona('sbx_p', bootstrapExit: 0, bootstrapOutput: "added 120 packages\nterminé");
        $project = $this->projet(User::factory()->create());

        app(DaytonaSandboxExecutor::class)->start($project);

        $processes = $project->processes()->orderBy('sandbox_processes.id')->get();
        $this->assertCount(2, $processes);

        [$installation, $serveur] = $processes;

        $this->assertSame('installation', $installation->name);
        $this->assertSame('completed', $installation->status);
        $this->assertSame(0, $installation->exit_code);
        $this->assertNotNull($installation->stopped_at);
        $this->assertStringContainsString('added 120 packages', $installation->metadata['output']);
        $this->assertArrayHasKey('duration_ms', $installation->metadata);

        $this->assertSame('serveur', $serveur->name);
        $this->assertSame('running', $serveur->status);
        $this->assertSame(5173, $serveur->port);
        $this->assertSame('cmd_srv', $serveur->provider_process_id);
        $this->assertSame('cd workspace && npm run dev -- --host 0.0.0.0', $serveur->command);
        $this->assertNull($serveur->stopped_at);
    }

    public function test_une_installation_en_echec_est_enregistree_avec_son_code_de_sortie(): void
    {
        $this->fauxDaytona('sbx_ko', bootstrapExit: 1, bootstrapOutput: 'npm ERR! code ENOTFOUND');
        $project = $this->projet(User::factory()->create());

        try {
            app(DaytonaSandboxExecutor::class)->start($project);
            $this->fail('Le démarrage aurait dû échouer.');
        } catch (\RuntimeException) {
            // attendu : l'installation a échoué
        }

        $installation = $project->processes()->where('name', 'installation')->sole();

        $this->assertSame('failed', $installation->status);
        $this->assertSame(1, $installation->exit_code);
        $this->assertStringContainsString('ENOTFOUND', $installation->metadata['output']);
        $this->assertFalse($project->processes()->where('name', 'serveur')->exists());
    }

    public function test_arreter_le_sandbox_arrete_ses_processus(): void
    {
        $this->fauxDaytona('sbx_stop');
        $project = $this->projet(User::factory()->create());
        $executor = app(DaytonaSandboxExecutor::class);

        $executor->start($project);
        $executor->stop($project->fresh());

        $serveur = $project->processes()->where('name', 'serveur')->sole();
        $this->assertSame('stopped', $serveur->status);
        $this->assertNotNull($serveur->stopped_at);
        $this->assertFalse($project->processes()->where('sandbox_processes.status', 'running')->exists());
    }

    public function test_redemarrer_garde_un_seul_serveur_actif(): void
    {
        $this->fauxDaytona('sbx_rs');
        $project = $this->projet(User::factory()->create());
        $executor = app(DaytonaSandboxExecutor::class);

        $executor->start($project);
        $executor->restart($project->fresh());

        $serveurs = $project->processes()->where('name', 'serveur')->get();
        $this->assertCount(2, $serveurs);
        $this->assertSame(1, $serveurs->where('status', 'running')->count());
        $this->assertSame(1, $serveurs->where('status', 'stopped')->count());
    }

    public function test_une_commande_executee_est_historisee(): void
    {
        $this->fauxDaytona('sbx_cmd', commandExit: 0, commandOutput: 'v22.1.0');
        [$user, $project] = $this->projetEnMarche('sbx_cmd');

        $response = $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/command', ['command' => 'node --version'])
            ->assertOk()
            ->assertJsonPath('output', 'v22.1.0')
            ->assertJsonPath('exit_code', 0);

        $process = SandboxProcess::findOrFail($response->json('process_id'));

        $this->assertSame('node', $process->name);
        $this->assertSame('node --version', $process->command);
        $this->assertSame('completed', $process->status);
        $this->assertSame(0, $process->exit_code);
        $this->assertSame('v22.1.0', $process->metadata['output']);
    }

    public function test_une_commande_avec_un_code_de_sortie_non_nul_est_en_echec(): void
    {
        $this->fauxDaytona('sbx_fail', commandExit: 127, commandOutput: 'sh: foo: not found');
        [$user, $project] = $this->projetEnMarche('sbx_fail');

        $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/command', ['command' => 'foo --bar'])
            ->assertOk()
            ->assertJsonPath('exit_code', 127);

        $process = $project->processes()->where('name', 'foo')->sole();
        $this->assertSame('failed', $process->status);
        $this->assertSame(127, $process->exit_code);
    }

    public function test_une_erreur_reseau_marque_la_commande_en_echec(): void
    {
        $this->fauxDaytona('sbx_net', commandHttpStatus: 502);
        [$user, $project] = $this->projetEnMarche('sbx_net');

        $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/command', ['command' => 'ls'])
            ->assertServerError();

        $process = $project->processes()->where('name', 'ls')->sole();
        $this->assertSame('failed', $process->status);
        $this->assertNull($process->exit_code);
        $this->assertNotEmpty($process->metadata['error']);
    }

    public function test_l_historique_est_renvoye_du_plus_recent_au_plus_ancien(): void
    {
        [$user, $project, $instance] = $this->projetEnMarche('sbx_hist');
        $recorder = app(SandboxProcessRecorder::class);

        $recorder->finish($recorder->begin($instance, 'npm', 'npm install'), 0, 'ok');
        $recorder->serverStarted($instance, 'npm run dev', 5173, 'cmd_x');

        $this->actingAs($user)
            ->getJson('/sandbox/projects/' . $project->id . '/processes')
            ->assertOk()
            ->assertJsonCount(2, 'processes')
            ->assertJsonPath('processes.0.name', 'serveur')
            ->assertJsonPath('processes.0.status', 'running')
            ->assertJsonPath('processes.0.port', 5173)
            ->assertJsonPath('processes.1.name', 'npm')
            ->assertJsonPath('processes.1.status', 'completed')
            ->assertJsonPath('processes.1.output', 'ok')
            ->assertJsonMissingPath('processes.0.metadata')
            ->assertJsonMissingPath('processes.0.provider_process_id');
    }

    public function test_un_utilisateur_ne_peut_pas_lire_l_historique_d_un_autre(): void
    {
        [, $project, $instance] = $this->projetEnMarche('sbx_priv');
        app(SandboxProcessRecorder::class)->begin($instance, 'secret', 'cat .env');

        $this->actingAs(User::factory()->create())
            ->getJson('/sandbox/projects/' . $project->id . '/processes')
            ->assertForbidden();

        $this->app['auth']->forgetGuards();

        $this->getJson('/sandbox/projects/' . $project->id . '/processes')
            ->assertUnauthorized();
    }

    public function test_seuls_les_derniers_processus_sont_conserves_et_la_sortie_est_tronquee(): void
    {
        [, $project, $instance] = $this->projetEnMarche('sbx_prune');
        $recorder = app(SandboxProcessRecorder::class);

        for ($i = 0; $i < SandboxProcessRecorder::KEEP_PER_INSTANCE + 5; $i++) {
            $recorder->begin($instance, 'cmd' . $i, 'echo ' . $i);
        }

        $this->assertSame(SandboxProcessRecorder::KEEP_PER_INSTANCE, $instance->processes()->count());
        $this->assertFalse($instance->processes()->where('name', 'cmd0')->exists());
        $this->assertTrue($instance->processes()->where('name', 'cmd54')->exists());

        $long = $recorder->begin($instance, 'long', 'yes');
        $recorder->finish($long, 0, str_repeat('a', 10000) . 'FIN');

        $output = $long->fresh()->metadata['output'];
        $this->assertStringEndsWith('FIN', $output);
        $this->assertLessThan(SandboxProcessRecorder::OUTPUT_LIMIT + 50, mb_strlen($output));
    }

    private function projet(User $user): SandboxProject
    {
        return $user->sandboxProjects()->create([
            'name' => 'React',
            'template' => 'react',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'starting',
        ]);
    }

    /** @return array{0: User, 1: SandboxProject, 2: SandboxInstance} */
    private function projetEnMarche(string $sandboxId): array
    {
        $user = User::factory()->create();
        $project = $user->sandboxProjects()->create([
            'name' => 'En marche',
            'template' => 'node',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'running',
            'metadata' => [
                'daytona_sandbox_id' => $sandboxId,
                'daytona_toolbox_url' => 'https://proxy.app.daytona.io/toolbox/' . $sandboxId,
            ],
        ]);

        $instance = $project->instances()->create([
            'driver' => 'daytona',
            'provider_instance_id' => $sandboxId,
            'status' => 'running',
        ]);

        return [$user, $project, $instance];
    }

    private function fauxDaytona(
        string $sandboxId,
        int $bootstrapExit = 0,
        string $bootstrapOutput = '',
        int $commandExit = 0,
        string $commandOutput = '',
        int $commandHttpStatus = 200,
    ): void {
        $sandbox = [
            'id' => $sandboxId,
            'state' => 'started',
            'target' => 'us',
            'cpu' => 1,
            'memory' => 2,
            'disk' => 5,
            'toolboxProxyUrl' => 'https://proxy.app.daytona.io/toolbox',
        ];

        Http::fake(function ($request) use ($sandboxId, $sandbox, $bootstrapExit, $bootstrapOutput, $commandExit, $commandOutput, $commandHttpStatus) {
            $url = $request->url();
            $api = 'https://app.daytona.io/api/sandbox';
            $toolbox = 'https://proxy.app.daytona.io/toolbox/' . $sandboxId;

            if ($request->method() === 'POST' && $url === $api) {
                return Http::response($sandbox, 200);
            }

            if ($request->method() === 'GET' && $url === $api . '/' . $sandboxId) {
                return Http::response($sandbox, 200);
            }

            if ($url === $api . '/' . $sandboxId . '/stop') {
                return Http::response(['id' => $sandboxId, 'state' => 'stopped'], 200);
            }

            if ($url === $api . '/' . $sandboxId . '/start') {
                return Http::response($sandbox, 200);
            }

            if (str_contains($url, '/signed-preview-url')) {
                return Http::response(['url' => 'https://preview.test/' . $sandboxId, 'token' => 't'], 200);
            }

            if ($url === $toolbox . '/process/execute') {
                $isBootstrap = str_contains((string) ($request->data()['command'] ?? ''), 'npm create vite');

                if (! $isBootstrap && $commandHttpStatus !== 200) {
                    return Http::response(['message' => 'Bad gateway'], $commandHttpStatus);
                }

                return $isBootstrap
                    ? Http::response(['result' => $bootstrapOutput, 'exitCode' => $bootstrapExit], 200)
                    : Http::response(['result' => $commandOutput, 'exitCode' => $commandExit], 200);
            }

            if ($url === $toolbox . '/process/session') {
                return Http::response([], 200);
            }

            if (str_ends_with($url, '/process/session/devroad-server/exec')) {
                return Http::response(['cmdId' => 'cmd_srv'], 200);
            }

            return Http::response([], 404);
        });
    }
}
