<?php

namespace App\Policies;

use App\Models\Column;
use App\Models\User;

class ColumnPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Column $column): bool
    {
        return $column->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Column $column): bool
    {
        return $column->user_id === $user->id;
    }

    public function delete(User $user, Column $column): bool
    {
        return $column->user_id === $user->id;
    }
}
