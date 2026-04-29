<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PreventiveMaintenanceSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PreventiveMaintenanceSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PreventiveMaintenanceSchedule');
    }

    public function view(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('View:PreventiveMaintenanceSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PreventiveMaintenanceSchedule');
    }

    public function update(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('Update:PreventiveMaintenanceSchedule');
    }

    public function delete(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('Delete:PreventiveMaintenanceSchedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PreventiveMaintenanceSchedule');
    }

    public function restore(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('Restore:PreventiveMaintenanceSchedule');
    }

    public function forceDelete(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('ForceDelete:PreventiveMaintenanceSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PreventiveMaintenanceSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PreventiveMaintenanceSchedule');
    }

    public function replicate(AuthUser $authUser, PreventiveMaintenanceSchedule $preventiveMaintenanceSchedule): bool
    {
        return $authUser->can('Replicate:PreventiveMaintenanceSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PreventiveMaintenanceSchedule');
    }
}
