<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Accessory","View:Accessory","Create:Accessory","Update:Accessory","Delete:Accessory","DeleteAny:Accessory","Restore:Accessory","ForceDelete:Accessory","ForceDeleteAny:Accessory","RestoreAny:Accessory","Replicate:Accessory","Reorder:Accessory","ViewAny:AssetModel","View:AssetModel","Create:AssetModel","Update:AssetModel","Delete:AssetModel","DeleteAny:AssetModel","Restore:AssetModel","ForceDelete:AssetModel","ForceDeleteAny:AssetModel","RestoreAny:AssetModel","Replicate:AssetModel","Reorder:AssetModel","ViewAny:Asset","View:Asset","Create:Asset","Update:Asset","Delete:Asset","DeleteAny:Asset","Restore:Asset","ForceDelete:Asset","ForceDeleteAny:Asset","RestoreAny:Asset","Replicate:Asset","Reorder:Asset","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","DeleteAny:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Component","View:Component","Create:Component","Update:Component","Delete:Component","DeleteAny:Component","Restore:Component","ForceDelete:Component","ForceDeleteAny:Component","RestoreAny:Component","Replicate:Component","Reorder:Component","ViewAny:Consumable","View:Consumable","Create:Consumable","Update:Consumable","Delete:Consumable","DeleteAny:Consumable","Restore:Consumable","ForceDelete:Consumable","ForceDeleteAny:Consumable","RestoreAny:Consumable","Replicate:Consumable","Reorder:Consumable","ViewAny:ItemRequest","View:ItemRequest","Create:ItemRequest","Update:ItemRequest","Delete:ItemRequest","DeleteAny:ItemRequest","Restore:ItemRequest","ForceDelete:ItemRequest","ForceDeleteAny:ItemRequest","RestoreAny:ItemRequest","Replicate:ItemRequest","Reorder:ItemRequest","ViewAny:License","View:License","Create:License","Update:License","Delete:License","DeleteAny:License","Restore:License","ForceDelete:License","ForceDeleteAny:License","RestoreAny:License","Replicate:License","Reorder:License","ViewAny:Location","View:Location","Create:Location","Update:Location","Delete:Location","DeleteAny:Location","Restore:Location","ForceDelete:Location","ForceDeleteAny:Location","RestoreAny:Location","Replicate:Location","Reorder:Location","ViewAny:Manufacturer","View:Manufacturer","Create:Manufacturer","Update:Manufacturer","Delete:Manufacturer","DeleteAny:Manufacturer","Restore:Manufacturer","ForceDelete:Manufacturer","ForceDeleteAny:Manufacturer","RestoreAny:Manufacturer","Replicate:Manufacturer","Reorder:Manufacturer","ViewAny:PreventiveMaintenanceChecklist","View:PreventiveMaintenanceChecklist","Create:PreventiveMaintenanceChecklist","Update:PreventiveMaintenanceChecklist","Delete:PreventiveMaintenanceChecklist","DeleteAny:PreventiveMaintenanceChecklist","Restore:PreventiveMaintenanceChecklist","ForceDelete:PreventiveMaintenanceChecklist","ForceDeleteAny:PreventiveMaintenanceChecklist","RestoreAny:PreventiveMaintenanceChecklist","Replicate:PreventiveMaintenanceChecklist","Reorder:PreventiveMaintenanceChecklist","ViewAny:PreventiveMaintenanceSchedule","View:PreventiveMaintenanceSchedule","Create:PreventiveMaintenanceSchedule","Update:PreventiveMaintenanceSchedule","Delete:PreventiveMaintenanceSchedule","DeleteAny:PreventiveMaintenanceSchedule","Restore:PreventiveMaintenanceSchedule","ForceDelete:PreventiveMaintenanceSchedule","ForceDeleteAny:PreventiveMaintenanceSchedule","RestoreAny:PreventiveMaintenanceSchedule","Replicate:PreventiveMaintenanceSchedule","Reorder:PreventiveMaintenanceSchedule","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","ViewAny:StatusLabel","View:StatusLabel","Create:StatusLabel","Update:StatusLabel","Delete:StatusLabel","DeleteAny:StatusLabel","Restore:StatusLabel","ForceDelete:StatusLabel","ForceDeleteAny:StatusLabel","RestoreAny:StatusLabel","Replicate:StatusLabel","Reorder:StatusLabel","ViewAny:Supplier","View:Supplier","Create:Supplier","Update:Supplier","Delete:Supplier","DeleteAny:Supplier","Restore:Supplier","ForceDelete:Supplier","ForceDeleteAny:Supplier","RestoreAny:Supplier","Replicate:Supplier","Reorder:Supplier","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","View:Dashboard","View:AssetsByCategoryWidget","View:AssetsStatusChartWidget","View:ExpiringLicensesWidget","View:LowStockWidget","View:RecentRequestsWidget","View:RequestStatusChartWidget","View:StatsOverviewWidget"]},{"name":"panel_user","guard_name":"web","permissions":[]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
