<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StatusLabel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StatusLabelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StatusLabel');
    }

    public function view(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('View:StatusLabel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StatusLabel');
    }

    public function update(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('Update:StatusLabel');
    }

    public function delete(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('Delete:StatusLabel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StatusLabel');
    }

    public function restore(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('Restore:StatusLabel');
    }

    public function forceDelete(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('ForceDelete:StatusLabel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StatusLabel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StatusLabel');
    }

    public function replicate(AuthUser $authUser, StatusLabel $statusLabel): bool
    {
        return $authUser->can('Replicate:StatusLabel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StatusLabel');
    }
}
