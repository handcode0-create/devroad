<?php

namespace App\Policies;

use App\Models\SandboxProject;
use App\Models\User;

class SandboxProjectPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, SandboxProject $project): bool { return $this->owns($user, $project); }
    public function create(User $user): bool { return true; }
    public function update(User $user, SandboxProject $project): bool { return $this->owns($user, $project); }
    public function delete(User $user, SandboxProject $project): bool { return $this->owns($user, $project); }
    public function run(User $user, SandboxProject $project): bool { return $this->owns($user, $project); }

    private function owns(User $user, SandboxProject $project): bool
    {
        return $project->user_id === $user->id;
    }
}
