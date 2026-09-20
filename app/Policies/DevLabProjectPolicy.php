<?php

namespace App\Policies;

use App\Models\DevLabProject;
use App\Models\User;

class DevLabProjectPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, DevLabProject $project): bool { return $this->owns($user, $project); }
    public function create(User $user): bool { return true; }
    public function update(User $user, DevLabProject $project): bool { return $this->owns($user, $project); }
    public function delete(User $user, DevLabProject $project): bool { return $this->owns($user, $project); }
    public function manageFiles(User $user, DevLabProject $project): bool { return $this->owns($user, $project); }
    public function run(User $user, DevLabProject $project): bool { return $this->owns($user, $project); }

    private function owns(User $user, DevLabProject $project): bool
    {
        return $project->user_id === $user->id;
    }
}
