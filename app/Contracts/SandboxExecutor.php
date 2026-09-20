<?php

namespace App\Contracts;

use App\Models\SandboxInstance;
use App\Models\SandboxProject;

interface SandboxExecutor
{
    public function start(SandboxProject $project): SandboxInstance;

    public function stop(SandboxProject $project): SandboxInstance;

    public function restart(SandboxProject $project): SandboxInstance;

    public function status(SandboxProject $project): SandboxInstance;

    public function destroy(SandboxProject $project): void;
}
