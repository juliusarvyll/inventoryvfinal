<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PreventiveMaintenanceChecklist;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PreventiveMaintenanceChecklistPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PreventiveMaintenanceChecklist');
    }

    public function view(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('View:PreventiveMaintenanceChecklist');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PreventiveMaintenanceChecklist');
    }

    public function update(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('Update:PreventiveMaintenanceChecklist');
    }

    public function delete(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('Delete:PreventiveMaintenanceChecklist');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PreventiveMaintenanceChecklist');
    }

    public function restore(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('Restore:PreventiveMaintenanceChecklist');
    }

    public function forceDelete(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('ForceDelete:PreventiveMaintenanceChecklist');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PreventiveMaintenanceChecklist');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PreventiveMaintenanceChecklist');
    }

    public function replicate(AuthUser $authUser, PreventiveMaintenanceChecklist $preventiveMaintenanceChecklist): bool
    {
        return $authUser->can('Replicate:PreventiveMaintenanceChecklist');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PreventiveMaintenanceChecklist');
    }
}
