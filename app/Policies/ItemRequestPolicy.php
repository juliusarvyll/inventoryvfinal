<?php

namespace App\Policies;

use App\Enums\ItemRequestStatus;
use App\Enums\UserRole;
use App\Models\ItemRequest;
use App\Models\User;

class ItemRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ItemRequest $itemRequest): bool
    {
        return $user->hasAnyRole([UserRole::Admin, UserRole::ItStaff])
            || $itemRequest->user_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, ItemRequest $itemRequest): bool
    {
        return $user->hasAnyRole([UserRole::Admin, UserRole::ItStaff]);
    }

    public function delete(User $user, ItemRequest $itemRequest): bool
    {
        return $itemRequest->user_id === $user->getKey()
            && $itemRequest->status === ItemRequestStatus::Pending;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
