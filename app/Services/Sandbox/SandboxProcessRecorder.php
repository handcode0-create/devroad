<?php

namespace App\Services\Sandbox;

use App\Models\SandboxInstance;
use App\Models\SandboxProcess;
use App\Models\SandboxProject;
use Illuminate\Support\Str;
use Throwable;

/**
 * Historise les processus lancés dans un Sandbox (installation, serveur de
 * développement, commandes) dans la table sandbox_processes.
 *
 * L'historique est une aide au diagnostic : un échec d'écriture ne doit
 * jamais faire échouer l'action réelle sur le Sandbox.
 */
class SandboxProcessRecorder
{
    /** Taille maximale de sortie conservée par processus (fin de la sortie). */
    public const OUTPUT_LIMIT = 4000;

    /** Nombre de processus conservés par instance, les plus anciens sont purgés. */
    public const KEEP_PER_INSTANCE = 50;

    public function begin(
        SandboxInstance $instance,
        string $name,
        string $command,
        ?int $port = null,
        ?string $providerProcessId = null,
    ): ?SandboxProcess {
        try {
            $process = $instance->processes()->create([
                'name' => Str::limit(trim($name) !== '' ? trim($name) : 'commande', 80, ''),
                'command' => Str::limit($command, 4000, ''),
                'port' => $port,
                'status' => 'running',
                'provider_process_id' => $providerProcessId,
                'started_at' => now(),
            ]);

            $this->prune($instance);

            return $process;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function finish(?SandboxProcess $process, ?int $exitCode, ?string $output = null, ?string $error = null): void
    {
        if ($process === null) {
            return;
        }

        try {
            $stoppedAt = now();
            $metadata = array_filter([
                'output' => $output !== null && $output !== '' ? $this->tail($output) : null,
                'error' => $error !== null && $error !== '' ? Str::limit($error, 1000) : null,
                'duration_ms' => $process->started_at
                    ? (int) $process->started_at->diffInMilliseconds($stoppedAt, true)
                    : null,
            ], fn ($value) => $value !== null);

            $process->update([
                'status' => $error === null && $exitCode === 0 ? 'completed' : 'failed',
                'exit_code' => $exitCode,
                'stopped_at' => $stoppedAt,
                'metadata' => $metadata,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Le serveur de développement tourne en tâche de fond : il reste « running »
     * jusqu'à l'arrêt du Sandbox. Un seul serveur actif par projet.
     */
    public function serverStarted(SandboxInstance $instance, string $command, ?int $port, ?string $providerProcessId): ?SandboxProcess
    {
        $this->stopRunning($instance->project, 'serveur');

        return $this->begin($instance, 'serveur', $command, $port, $providerProcessId);
    }

    /**
     * Marque comme arrêtés les processus encore actifs du projet (arrêt,
     * redémarrage ou suppression du Sandbox).
     */
    public function stopRunning(?SandboxProject $project, ?string $name = null): void
    {
        if ($project === null) {
            return;
        }

        try {
            SandboxProcess::query()
                ->whereIn('sandbox_instance_id', $project->instances()->select('id'))
                ->where('status', 'running')
                ->when($name !== null, fn ($query) => $query->where('name', $name))
                ->update(['status' => 'stopped', 'stopped_at' => now()]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function prune(SandboxInstance $instance): void
    {
        $keepIds = $instance->processes()
            ->latest('id')
            ->limit(self::KEEP_PER_INSTANCE)
            ->pluck('id');

        $instance->processes()->whereNotIn('id', $keepIds)->delete();
    }

    private function tail(string $output): string
    {
        $output = trim($output);

        if (mb_strlen($output) <= self::OUTPUT_LIMIT) {
            return $output;
        }

        return "… (début tronqué)\n" . mb_substr($output, -self::OUTPUT_LIMIT);
    }
}
