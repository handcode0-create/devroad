<?php

namespace App\Services\Sandbox;

use App\Contracts\SandboxExecutor;
use App\Exceptions\SandboxRuntimeUnavailable;
use App\Models\SandboxInstance;
use App\Models\SandboxProject;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
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

        $project->updateQuietly([
            'status' => 'starting',
            'metadata' => array_merge($project->metadata ?? [], [
                'startup_phase' => 'provisioning',
                'startup_error' => null,
            ]),
        ]);

        $sandbox = $this->api()
            ->post('/sandbox/' . rawurlencode($providerId) . '/start')
            ->throw()
            ->json();

        $sandbox = $this->waitUntilStarted($providerId, $sandbox);

        $project->updateQuietly([
            'metadata' => array_merge($project->fresh()->metadata ?? [], [
                'startup_phase' => 'starting_server',
            ]),
        ]);

        $this->startServer($project->fresh(), $sandbox);

        return $this->syncInstance($project->fresh(), $sandbox, true);
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

    public function executeCommand(SandboxProject $project, string $command, int $timeout = 120): array
    {
        $this->assertConfigured();
        $providerId = $this->providerId($project);
        $metadata = $project->metadata ?? [];
        $toolboxUrl = $this->toolboxUrlFor($providerId, $metadata['daytona_toolbox_url'] ?? null);

        $result = $this->execute($toolboxUrl, '/process/execute', [
            'command' => $command,
            'cwd' => 'workspace',
            'timeout' => max(1, min($timeout, 300)),
        ]);

        $project->updateQuietly(['last_started_at' => $project->last_started_at ?: now()]);

        return [
            'output' => $result['result'] ?? $result['output'] ?? '',
            'exit_code' => $result['exitCode'] ?? null,
        ];
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

        $project->updateQuietly([
            'status' => 'starting',
            'metadata' => array_merge($project->metadata ?? [], [
                'startup_phase' => 'provisioning',
                'startup_error' => null,
            ]),
        ]);

        $resources = config('sandbox.resources');

        $sandbox = $this->api()->post('/sandbox', [
            'name' => $this->providerName($project),
            'image' => $definition['image'],
            'target' => config('sandbox.default_region'),
            'public' => false,
            'cpu' => (int) ($resources['cpu'] ?? 1),
            'memory' => (int) ($resources['memory'] ?? 1),
            'disk' => (int) ($resources['disk'] ?? 3),
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

        $toolboxUrl = $this->toolboxUrlFor($providerId, $sandbox['toolboxProxyUrl'] ?? null);

        $metadata = $project->fresh()->metadata ?? [];
        $metadata['startup_phase'] = 'provisioning';
        $metadata['daytona_sandbox_id'] = $providerId;
        $metadata['daytona_toolbox_url'] = $toolboxUrl;

        $project->updateQuietly(['metadata' => $metadata]);

        $sandbox = $this->waitUntilStarted($providerId, $sandbox);
        $this->syncInstance($project->fresh(), $sandbox, true);

        $project->updateQuietly([
            'status' => 'starting',
            'metadata' => array_merge($project->fresh()->metadata ?? [], [
                'startup_phase' => 'installing',
            ]),
        ]);

        $bootstrap = $this->execute($toolboxUrl, '/process/execute', [
            'command' => $definition['bootstrap'],
            'cwd' => 'workspace',
            'timeout' => 900,
        ]);

        if (($bootstrap['exitCode'] ?? 1) !== 0) {
            $project->updateQuietly(['status' => 'error']);
            throw new RuntimeException(
                'Initialisation du projet échouée : ' . ($bootstrap['result'] ?? 'erreur inconnue')
            );
        }

        $project->updateQuietly([
            'metadata' => array_merge($project->fresh()->metadata ?? [], [
                'startup_phase' => 'starting_server',
            ]),
        ]);

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
        $metadata['startup_phase'] = 'ready';

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

    private function startServer(SandboxProject $project, array $sandbox): void
    {
        $definition = config('sandbox.templates.' . $project->template);

        if (! is_array($definition)) {
            throw new RuntimeException('Template Sandbox introuvable.');
        }

        $providerId = $sandbox['id'] ?? $this->providerId($project);
        $toolboxUrl = $this->toolboxUrlFor(
            $providerId,
            $sandbox['toolboxProxyUrl'] ?? ($project->metadata['daytona_toolbox_url'] ?? null)
        );

        $sessionId = 'devroad-server';
        $this->execute(
            $toolboxUrl,
            '/process/session',
            ['sessionId' => $sessionId]
        );

        $server = $this->execute(
            $toolboxUrl,
            '/process/session/' . rawurlencode($sessionId) . '/exec',
            [
                'command' => 'cd workspace && ' . $definition['serve'],
                'runAsync' => true,
            ]
        );

        $preview = $this->api()
            ->get(
                '/sandbox/' . rawurlencode($providerId) . '/ports/' . $definition['port'] . '/signed-preview-url',
                ['expiresInSeconds' => 3600]
            )
            ->throw()
            ->json();

        $metadata = $project->metadata ?? [];
        $metadata['server_session_id'] = $sessionId;
        $metadata['server_command_id'] = $server['cmdId'] ?? $server['id'] ?? null;
        $metadata['server_port'] = $definition['port'];
        $metadata['daytona_toolbox_url'] = $toolboxUrl;
        $metadata['preview_url'] = $preview['url'] ?? null;
        $metadata['preview_token'] = $preview['token'] ?? null;
        $metadata['startup_phase'] = 'ready';

        $project->updateQuietly([
            'status' => 'running',
            'preview_url' => $preview['url'] ?? null,
            'last_started_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    private function waitUntilStarted(string $providerId, array $initialSandbox, int $timeout = 180): array
    {
        $sandbox = $initialSandbox;
        $startedAt = microtime(true);

        while (true) {
            $state = strtolower((string) ($sandbox['state'] ?? ''));

            if (in_array($state, ['started', 'running'], true)) {
                return $sandbox;
            }

            if ($state === 'error') {
                throw new RuntimeException(
                    'Daytona a placé le Sandbox en erreur : ' . $this->sandboxError($sandbox)
                );
            }

            if ((microtime(true) - $startedAt) >= $timeout) {
                throw new RuntimeException(
                    'Le Sandbox Daytona n’est pas prêt après ' . $timeout . ' secondes (état : ' . ($state ?: 'inconnu') . ').'
                );
            }

            sleep(2);

            try {
                $sandbox = $this->api()
                    ->get('/sandbox/' . rawurlencode($providerId))
                    ->throw()
                    ->json();
            } catch (RequestException $exception) {
                $message = $exception->response
                    ? mb_substr($exception->response->body(), 0, 1200)
                    : $exception->getMessage();

                throw new RuntimeException('Impossible de vérifier l’état Daytona : ' . $message, 0, $exception);
            }
        }
    }

    private function sandboxError(array $sandbox): string
    {
        foreach (['error', 'message', 'errorMessage', 'reason'] as $key) {
            if (isset($sandbox[$key]) && is_scalar($sandbox[$key])) {
                return mb_substr((string) $sandbox[$key], 0, 1200);
            }
        }

        return 'raison inconnue';
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
                    'toolbox_url' => $this->toolboxUrlFor($providerId, $sandbox['toolboxProxyUrl'] ?? null),
                ],
            ]
        );
    }

    private function execute(string $toolboxUrl, string $path, array $payload, string $method = 'post'): array
    {
        $timeout = isset($payload['timeout'])
            ? max(30, min((int) $payload['timeout'] + 30, 960))
            : 30;

        $response = $this->toolbox($timeout)->{$method}(rtrim($toolboxUrl, '/') . $path, $payload);
        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * URL de base du Toolbox d'UN sandbox : {proxy}/toolbox/{sandboxId}.
     *
     * Daytona renvoie toolboxProxyUrl SANS l'identifiant du sandbox (par exemple
     * « https://proxy.app.daytona.io/toolbox »). Les appels doivent viser
     * « .../toolbox/{sandboxId}/process/... » ; sans l'identifiant, le proxy lit
     * « process » comme identifiant de sandbox et répond 401 « Bearer token is
     * invalid ». On ajoute donc l'identifiant, sauf s'il est déjà présent.
     */
    private function toolboxUrlFor(string $providerId, ?string $base = null): string
    {
        $base = rtrim($base ?: (string) config('sandbox.toolbox_url'), '/');
        $suffix = '/' . rawurlencode($providerId);

        return str_ends_with($base, $suffix) ? $base : $base . $suffix;
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(config('sandbox.api_url'))
            ->withToken((string) config('sandbox.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(120)
            ->retry(3, 1000);
    }

    private function toolbox(int $timeout = 30): PendingRequest
    {
        return Http::withToken((string) config('sandbox.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
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
