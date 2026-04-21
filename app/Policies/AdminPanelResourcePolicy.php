<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class AdminPanelResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Admin, UserRole::ItStaff]);
    }

    public function view(User $user, mixed $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, mixed $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, mixed $record): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function reorder(User $user): bool
    {
        return $this->update($user, null);
    }
}
