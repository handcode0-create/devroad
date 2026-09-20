<?php

namespace App\Services\Sandbox;

use App\Contracts\SandboxExecutor;
use App\Exceptions\SandboxRuntimeUnavailable;
use App\Models\SandboxInstance;
use App\Models\SandboxProject;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DaytonaSandboxExecutor implements SandboxExecutor
{
    public function start(SandboxProject $project): SandboxInstance
    {
        $this->assertConfigured();
        $providerId = $project->metadata['daytona_sandbox_id'] ?? null;

        if (! is_string($providerId) || $providerId === '') {
            return $this->provision($project);
        }

        $sandbox = $this->api()->post('/sandbox/' . rawurlencode($providerId) . '/start')->throw()->json();

        return $this->syncInstance($project, $sandbox, true);
    }

    public function stop(SandboxProject $project): SandboxInstance
    {
        $this->assertConfigured();
        $providerId = $this->providerId($project);

        $this->api()->post('/sandbox/' . rawurlencode($providerId) . '/stop')->throw();

        return $this->syncInstance($project, [
            'id' => $providerId,
            'state' => 'stopped',
        ], false);
    }

    public function restart(SandboxProject $project): SandboxInstance
    {
        $this->stop($project);
        return $this->start($project);
    }

    public function status(SandboxProject $project): SandboxInstance
    {
        $this->assertConfigured();
        $providerId = $this->providerId($project);

        $sandbox = $this->api()
            ->get('/sandbox/' . rawurlencode($providerId))
            ->throw()
            ->json();

        return $this->syncInstance($project, $sandbox, false);
    }

    public function destroy(SandboxProject $project): void
    {
        $this->assertConfigured();
        $providerId = $this->providerId($project);

        $this->api()->delete('/sandbox/' . rawurlencode($providerId))->throw();

        $metadata = $project->metadata ?? [];
        foreach ([
            'daytona_sandbox_id',
            'daytona_toolbox_url',
            'preview_url',
            'preview_token',
            'server_session_id',
            'server_command_id',
            'server_port',
        ] as $key) {
            unset($metadata[$key]);
        }

        $project->updateQuietly([
            'metadata' => $metadata,
            'preview_url' => null,
            'status' => 'stopped',
        ]);
    }

    private function provision(SandboxProject $project): SandboxInstance
    {
        $definition = config('sandbox.templates.' . $project->template);

        if (! is_array($definition)) {
            throw new RuntimeException('Template Sandbox introuvable.');
        }

        $sandbox = $this->api()->post('/sandbox', [
            'name' => $this->providerName($project),
            'image' => $definition['image'],
            'target' => config('sandbox.default_region'),
            'public' => false,
            'resources' => config('sandbox.resources'),
            'autoStopInterval' => 60,
            'labels' => [
                'devroad_project_id' => (string) $project->id,
                'devroad_user_id' => (string) $project->user_id,
                'template' => $project->template,
            ],
        ])->throw()->json();

        $providerId = $sandbox['id'] ?? null;

        if (! is_string($providerId) || $providerId === '') {
            throw new RuntimeException('Daytona n’a pas retourné l’identifiant du Sandbox.');
        }

        $toolboxUrl = $sandbox['toolboxProxyUrl']
            ?? config('sandbox.toolbox_url') . '/' . rawurlencode($providerId);

        $metadata = $project->metadata ?? [];
        $metadata['daytona_sandbox_id'] = $providerId;
        $metadata['daytona_toolbox_url'] = $toolboxUrl;

        $project->updateQuietly(['metadata' => $metadata]);

        $this->syncInstance($project, $sandbox, true);

        $bootstrap = $this->execute($toolboxUrl, '/process/execute', [
            'command' => $definition['bootstrap'],
            'cwd' => 'workspace',
            'timeout' => 900,
        ]);

        if (($bootstrap['exitCode'] ?? 1) !== 0) {
            $project->updateQuietly(['status' => 'error']);
            throw new RuntimeException('Initialisation du projet échouée : ' . ($bootstrap['result'] ?? 'erreur inconnue'));
        }

        $sessionId = 'devroad-server';
        $this->execute($toolboxUrl, '/process/session', ['sessionId' => $sessionId]);

        $server = $this->execute(
            $toolboxUrl,
            '/process/session/' . rawurlencode($sessionId) . '/exec',
            [
                'command' => 'cd workspace && ' . $definition['serve'],
                'runAsync' => true,
            ]
        );

        $metadata = $project->fresh()->metadata ?? [];
        $metadata['server_session_id'] = $sessionId;
        $metadata['server_command_id'] = $server['cmdId'] ?? $server['id'] ?? null;
        $metadata['server_port'] = $definition['port'];

        $preview = $this->api()
            ->get(
                '/sandbox/' . rawurlencode($providerId) . '/ports/' . $definition['port'] . '/signed-preview-url',
                ['expiresInSeconds' => 3600]
            )
            ->throw()
            ->json();

        $metadata['preview_url'] = $preview['url'] ?? null;
        $metadata['preview_token'] = $preview['token'] ?? null;

        $project->updateQuietly([
            'status' => 'running',
            'preview_url' => $preview['url'] ?? null,
            'last_started_at' => now(),
            'metadata' => $metadata,
        ]);

        return $this->syncInstance($project->fresh(), [
            'id' => $providerId,
            'state' => 'started',
            'target' => config('sandbox.default_region'),
            'toolboxProxyUrl' => $toolboxUrl,
        ], true);
    }

    private function syncInstance(SandboxProject $project, array $sandbox, bool $started): SandboxInstance
    {
        $providerId = $sandbox['id'] ?? $this->providerId($project);
        $state = strtolower((string) ($sandbox['state'] ?? 'stopped'));

        $status = match ($state) {
            'started', 'running' => 'running',
            'starting' => 'starting',
            'stopping' => 'stopping',
            'error' => 'error',
            'paused', 'sleeping' => 'sleeping',
            default => 'stopped',
        };

        return $project->instances()->updateOrCreate(
            ['provider_instance_id' => $providerId],
            [
                'driver' => 'daytona',
                'status' => $status,
                'region' => $sandbox['target'] ?? config('sandbox.default_region'),
                'cpu_millicores' => (int) (($sandbox['cpu'] ?? config('sandbox.resources.cpu')) * 1000),
                'memory_mb' => (int) (($sandbox['memory'] ?? config('sandbox.resources.memory')) * 1024),
                'storage_mb' => (int) (($sandbox['disk'] ?? config('sandbox.resources.disk')) * 1024),
                'started_at' => $started ? now() : null,
                'last_heartbeat_at' => now(),
                'metadata' => [
                    'daytona_sandbox_id' => $providerId,
                    'sandbox_state' => $state,
                    'toolbox_url' => $sandbox['toolboxProxyUrl'] ?? null,
                ],
            ]
        );
    }

    private function execute(string $toolboxUrl, string $path, array $payload, string $method = 'post'): array
    {
        $response = $this->toolbox()->{$method}(rtrim($toolboxUrl, '/') . $path, $payload);
        $response->throw();

        return $response->json() ?? [];
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(config('sandbox.api_url'))
            ->withToken((string) config('sandbox.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->retry(2, 500);
    }

    private function toolbox(): PendingRequest
    {
        return Http::withToken((string) config('sandbox.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->retry(2, 500);
    }

    private function providerId(SandboxProject $project): string
    {
        $id = $project->metadata['daytona_sandbox_id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new SandboxRuntimeUnavailable();
        }

        return $id;
    }

    private function providerName(SandboxProject $project): string
    {
        return 'devroad-' . $project->user_id . '-' . $project->id;
    }

    private function assertConfigured(): void
    {
        if (! config('sandbox.enabled')
            || config('sandbox.driver') !== 'daytona'
            || ! is_string(config('sandbox.api_key'))
            || config('sandbox.api_key') === '') {
            throw new SandboxRuntimeUnavailable();
        }
    }
}
