<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\StartSandboxJob;
use Tests\TestCase;

class SandboxProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_ne_peut_pas_acceder_au_sandbox(): void
    {
        $this->get('/sandbox')->assertRedirect('/login');
        $this->postJson('/sandbox/projects', [
            'name' => 'React App',
            'template' => 'react',
        ])->assertUnauthorized();
    }

    public function test_un_projet_sandbox_est_cree_avec_un_template_valide(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/sandbox/projects', [
                'name' => 'Mon Next',
                'template' => 'nextjs',
            ])
            ->assertCreated()
            ->assertJsonPath('project.name', 'Mon Next')
            ->assertJsonPath('project.template', 'nextjs')
            ->assertJsonPath('project.runtime', 'node')
            ->assertJsonPath('project.runtime_version', '22')
            ->assertJsonPath('project.status', 'stopped');

        $this->assertDatabaseHas('sandbox_projects', [
            'user_id' => $user->id,
            'name' => 'Mon Next',
            'template' => 'nextjs',
        ]);
    }

    public function test_un_template_invalide_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/sandbox/projects', [
                'name' => 'Projet invalide',
                'template' => 'python',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('sandbox_projects', 0);
    }

    public function test_un_utilisateur_ne_peut_pas_acceder_au_sandbox_d_un_autre(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = $owner->sandboxProjects()->create([
            'name' => 'Privé',
            'template' => 'react',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'stopped',
        ]);

        $this->actingAs($intruder)
            ->getJson('/sandbox/projects/' . $project->id)
            ->assertForbidden();

        $this->actingAs($intruder)
            ->postJson('/sandbox/projects/' . $project->id . '/start')
            ->assertForbidden();
    }

    public function test_le_demarrage_reste_desactive_tant_que_le_runtime_n_est_pas_configure(): void
    {
        $user = User::factory()->create();

        $project = $user->sandboxProjects()->create([
            'name' => 'React',
            'template' => 'react',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'stopped',
        ]);

        $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/start')
            ->assertStatus(503)
            ->assertJsonPath('runtime_available', false);

        $this->assertDatabaseHas('sandbox_projects', [
            'id' => $project->id,
            'status' => 'stopped',
        ]);
    }

    public function test_daytona_peut_provisionner_un_sandbox_isole(): void
    {
        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.api_key', 'test-key');
        config()->set('sandbox.default_region', 'us');

        Http::fake([
            'https://app.daytona.io/api/sandbox' => Http::response([
                'id' => 'sbx_test_123',
                'state' => 'started',
                'target' => 'us',
                'cpu' => 1,
                'memory' => 2,
                'disk' => 5,
                'toolboxProxyUrl' => 'https://proxy.app.daytona.io/toolbox/sbx_test_123',
            ], 200),
            'https://app.daytona.io/api/sandbox/sbx_test_123/ports/5173/signed-preview-url*' => Http::response([
                'url' => 'https://5173-sbx_test_123.proxy.daytona.work',
                'token' => 'preview-token',
            ], 200),
            'https://proxy.app.daytona.io/toolbox/sbx_test_123/process/execute' => Http::response([
                'result' => '',
                'exitCode' => 0,
            ], 200),
            'https://proxy.app.daytona.io/toolbox/sbx_test_123/process/session' => Http::response([], 200),
            'https://proxy.app.daytona.io/toolbox/sbx_test_123/process/session/devroad-server/exec' => Http::response([
                'cmdId' => 'cmd_123',
            ], 200),
        ]);

        $user = User::factory()->create();
        $project = $user->sandboxProjects()->create([
            'name' => 'React Daytona',
            'template' => 'react',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'stopped',
        ]);

        Queue::fake();

        $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/start')
            ->assertStatus(202)
            ->assertJsonPath('project.status', 'starting')
            ->assertJsonPath('queued', true);

        Queue::assertPushed(StartSandboxJob::class, fn ($job) => $job->projectId === $project->id);

        $this->assertDatabaseHas('sandbox_projects', [
            'id' => $project->id,
            'status' => 'starting',
        ]);
    }

    public function test_daytona_attend_que_le_sandbox_soit_pret_avant_d_installer_le_template(): void
    {
        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.api_key', 'test-key');
        config()->set('sandbox.default_region', 'us');
        config()->set('sandbox.resources', [
            'cpu' => 2,
            'memory' => 4,
            'disk' => 8,
        ]);

        $statusCalls = 0;

        Http::fake(function ($request) use (&$statusCalls) {
            if ($request->method() === 'POST' && $request->url() === 'https://app.daytona.io/api/sandbox') {
                return Http::response([
                    'id' => 'sbx_wait',
                    'state' => 'starting',
                    'target' => 'us',
                    'cpu' => 2,
                    'memory' => 4,
                    'disk' => 8,
                    'toolboxProxyUrl' => 'https://proxy.app.daytona.io/toolbox/sbx_wait',
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === 'https://app.daytona.io/api/sandbox/sbx_wait') {
                $statusCalls++;

                return Http::response([
                    'id' => 'sbx_wait',
                    'state' => $statusCalls === 1 ? 'starting' : 'started',
                    'target' => 'us',
                    'cpu' => 2,
                    'memory' => 4,
                    'disk' => 8,
                    'toolboxProxyUrl' => 'https://proxy.app.daytona.io/toolbox/sbx_wait',
                ], 200);
            }

            if (str_ends_with($request->url(), '/process/execute')) {
                return Http::response([
                    'result' => '',
                    'exitCode' => 0,
                ], 200);
            }

            if (str_ends_with($request->url(), '/process/session')) {
                return Http::response([], 200);
            }

            if (str_ends_with($request->url(), '/process/session/devroad-server/exec')) {
                return Http::response([
                    'cmdId' => 'cmd_wait',
                ], 200);
            }

            if (str_contains($request->url(), '/signed-preview-url')) {
                return Http::response([
                    'url' => 'https://preview.test/sbx_wait',
                    'token' => 'preview-token',
                ], 200);
            }

            return Http::response([], 404);
        });

        $user = User::factory()->create();
        $project = $user->sandboxProjects()->create([
            'name' => 'React Daytona',
            'template' => 'react',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'starting',
        ]);

        $instance = app(\App\Services\Sandbox\DaytonaSandboxExecutor::class)->start($project);

        $this->assertSame('running', $instance->status);
        $this->assertSame(2, $statusCalls);
        $this->assertDatabaseHas('sandbox_projects', [
            'id' => $project->id,
            'status' => 'running',
            'preview_url' => 'https://preview.test/sbx_wait',
        ]);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://app.daytona.io/api/sandbox'
                && $request->data()['cpu'] === 2
                && $request->data()['memory'] === 4
                && $request->data()['disk'] === 8
                && ! array_key_exists('resources', $request->data());
        });
    }


    public function test_une_commande_est_executee_uniquement_dans_le_sandbox_daytona(): void
    {
        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.api_key', 'test-key');

        Http::fake([
            'https://proxy.app.daytona.io/toolbox/sbx_cmd/process/execute' => Http::response([
                'result' => "Node v22",
                'exitCode' => 0,
            ], 200),
        ]);

        $user = User::factory()->create();
        $project = $user->sandboxProjects()->create([
            'name' => 'Node',
            'template' => 'node',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'running',
            'metadata' => [
                'daytona_sandbox_id' => 'sbx_cmd',
                'daytona_toolbox_url' => 'https://proxy.app.daytona.io/toolbox/sbx_cmd',
            ],
        ]);

        $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/command', [
                'command' => 'node --version',
                'timeout' => 30,
            ])
            ->assertOk()
            ->assertJsonPath('output', 'Node v22')
            ->assertJsonPath('exit_code', 0);

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/process/execute')
            && $request->data()['command'] === 'node --version'
            && $request->data()['cwd'] === 'workspace'
        );
    }


    public function test_un_terminal_pty_genere_un_token_signe_sans_exposer_la_cle_daytona(): void
    {
        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.bridge_url', 'https://terminal.devroad.test');
        config()->set('sandbox.bridge_secret', 'bridge-secret');

        $user = User::factory()->create();
        $project = $user->sandboxProjects()->create([
            'name' => 'PTY',
            'template' => 'node',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'running',
            'metadata' => [
                'daytona_sandbox_id' => 'sbx_pty',
            ],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/sandbox/projects/' . $project->id . '/terminal')
            ->assertOk()
            ->assertJsonStructure(['url', 'expires_at']);

        $url = $response->json('url');

        $this->assertStringStartsWith('wss://terminal.devroad.test/terminal?token=', $url);
        $this->assertStringNotContainsString('bridge-secret', $url);
        $this->assertStringNotContainsString('test-key', $url);
    }

    public function test_un_utilisateur_ne_peut_pas_obtenir_le_terminal_pty_d_un_autre(): void
    {
        config()->set('sandbox.enabled', true);
        config()->set('sandbox.driver', 'daytona');
        config()->set('sandbox.bridge_url', 'https://terminal.devroad.test');
        config()->set('sandbox.bridge_secret', 'bridge-secret');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = $owner->sandboxProjects()->create([
            'name' => 'Privé',
            'template' => 'node',
            'runtime' => 'node',
            'runtime_version' => '22',
            'status' => 'running',
            'metadata' => [
                'daytona_sandbox_id' => 'sbx_private',
            ],
        ]);

        $this->actingAs($intruder)
            ->postJson('/sandbox/projects/' . $project->id . '/terminal')
            ->assertForbidden();
    }

    public function test_la_page_sandbox_expose_les_templates_et_l_etat_du_runtime(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/sandbox')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Sandbox/Index')
                ->has('templates.react')
                ->has('templates.nextjs')
                ->has('templates.laravel')
                ->where('runtime_enabled', false)
            );
    }
}
