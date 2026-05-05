<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

abstract class ShieldResource extends \Filament\Resources\Resource
{
    /**
     * @var array<class-string, array<string, string>>
     */
    protected static array $shieldPermissionsByResource = [];

    public static function shouldRegisterNavigation(): bool
    {
        return parent::shouldRegisterNavigation() && static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::authorizedByShield('viewAny') ?? parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::authorizedByShield('create') ?? parent::canCreate();
    }

    public static function canDeleteAny(): bool
    {
        return static::authorizedByShield('deleteAny') ?? parent::canDeleteAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorizedByShield('delete') ?? parent::canDelete($record);
    }

    public static function canForceDeleteAny(): bool
    {
        return static::authorizedByShield('forceDeleteAny') ?? parent::canForceDeleteAny();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::authorizedByShield('forceDelete') ?? parent::canForceDelete($record);
    }

    public static function canReorder(): bool
    {
        return static::authorizedByShield('reorder') ?? parent::canReorder();
    }

    public static function canReplicate(Model $record): bool
    {
        return static::authorizedByShield('replicate') ?? parent::canReplicate($record);
    }

    public static function canRestoreAny(): bool
    {
        return static::authorizedByShield('restoreAny') ?? parent::canRestoreAny();
    }

    public static function canRestore(Model $record): bool
    {
        return static::authorizedByShield('restore') ?? parent::canRestore($record);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorizedByShield('update') ?? parent::canEdit($record);
    }

    public static function canView(Model $record): bool
    {
        return static::authorizedByShield('view') ?? parent::canView($record);
    }

    protected static function authorizedByShield(string $action): ?bool
    {
        $permission = static::getShieldPermission($action);
        $user = Filament::auth()?->user();

        if (blank($permission) || ($user === null)) {
            return null;
        }

        return $user->can($permission);
    }

    protected static function getShieldPermission(string $action): ?string
    {
        $resourceClass = static::class;

        if (! array_key_exists($resourceClass, static::$shieldPermissionsByResource)) {
            static::$shieldPermissionsByResource[$resourceClass] = FilamentShield::getResourcePolicyActionsWithPermissions($resourceClass) ?? [];
        }

        return static::$shieldPermissionsByResource[$resourceClass][$action] ?? null;
    }
}
