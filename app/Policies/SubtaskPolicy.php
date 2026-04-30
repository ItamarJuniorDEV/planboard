<?php

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;

class SubtaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Subtask $subtask): bool
    {
        return $subtask->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Subtask $subtask): bool
    {
        return $subtask->user_id === $user->id;
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        return $subtask->user_id === $user->id;
    }
}
