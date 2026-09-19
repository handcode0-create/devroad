<?php

namespace App\Policies;

use App\Models\RoadmapStep;
use App\Models\User;

class RoadmapStepPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoadmapStep $step): bool
    {
        return $step->roadmap->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RoadmapStep $step): bool
    {
        return $step->roadmap->user_id === $user->id;
    }

    public function delete(User $user, RoadmapStep $step): bool
    {
        return $step->roadmap->user_id === $user->id;
    }
}