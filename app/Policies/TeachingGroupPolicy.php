<?php

namespace App\Policies;

use App\Models\TeachingGroup;
use App\Models\User;

class TeachingGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    // Seul le professeur propriétaire voit les statistiques et gère le groupe.
    public function view(User $user, TeachingGroup $group): bool
    {
        return $user->isTeacher() && $group->user_id === $user->id;
    }

    public function update(User $user, TeachingGroup $group): bool
    {
        return $this->view($user, $group);
    }

    public function delete(User $user, TeachingGroup $group): bool
    {
        return $this->view($user, $group);
    }
}
