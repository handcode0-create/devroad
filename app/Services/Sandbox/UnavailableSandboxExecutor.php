<?php

namespace App\Services\Sandbox;

use App\Contracts\SandboxExecutor;
use App\Exceptions\SandboxRuntimeUnavailable;
use App\Models\SandboxInstance;
use App\Models\SandboxProject;

class UnavailableSandboxExecutor implements SandboxExecutor
{
    private function unavailable(): never
    {
        throw new SandboxRuntimeUnavailable();
    }

    public function start(SandboxProject $project): SandboxInstance
    {
        $this->unavailable();
    }

    public function stop(SandboxProject $project): SandboxInstance
    {
        $this->unavailable();
    }

    public function restart(SandboxProject $project): SandboxInstance
    {
        $this->unavailable();
    }

    public function status(SandboxProject $project): SandboxInstance
    {
        $this->unavailable();
    }

    public function executeCommand(SandboxProject $project, string $command, int $timeout = 120): array
    {
        $this->unavailable();
    }

    public function destroy(SandboxProject $project): void
    {
        $this->unavailable();
    }
}
