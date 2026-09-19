<?php

namespace App\Policies;

use App\Models\Memo;
use App\Models\User;

class MemoPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // liste déjà filtrée sur l'utilisateur connecté
    }

    public function view(User $user, Memo $memo): bool
    {
        return $this->owns($user, $memo);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Memo $memo): bool
    {
        return $this->owns($user, $memo);
    }

    public function delete(User $user, Memo $memo): bool
    {
        return $this->owns($user, $memo);
    }

    private function owns(User $user, Memo $memo): bool
    {
        return $memo->user_id === $user->id;
    }
}