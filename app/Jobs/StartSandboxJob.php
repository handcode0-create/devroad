<?php

namespace App\Jobs;

use App\Contracts\SandboxExecutor;
use App\Models\SandboxProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class StartSandboxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 1200;

    public function __construct(public int $projectId)
    {
        $this->onQueue('sandbox');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('sandbox-start:' . $this->projectId))
                ->releaseAfter(5)
                ->expireAfter(1800),
        ];
    }

    public function handle(SandboxExecutor $executor): void
    {
        $project = SandboxProject::query()->find($this->projectId);

        if (! $project || in_array($project->status, ['running', 'starting'], true) === false) {
            return;
        }

        try {
            $executor->start($project->fresh());
        } catch (Throwable $exception) {
            $project->fresh()?->update([
                'status' => 'error',
                'metadata' => array_merge($project->metadata ?? [], [
                    'startup_error' => mb_substr($exception->getMessage(), 0, 2000),
                ]),
            ]);

            throw $exception;
        }
    }
}
