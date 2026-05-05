<?php

use App\Filament\Resources\Assets\AssetResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('admin panel access is controlled by shield roles', function () {
    $user = User::factory()->create();

    expect($user->canAccessPanel(filament()->getPanel('admin')))->toBeFalse();

    Role::create([
        'name' => 'panel_user',
        'guard_name' => 'web',
    ]);

    $user->assignRole('panel_user');

    expect($user->canAccessPanel(filament()->getPanel('admin')))->toBeTrue();
});

test('super admins can access the shield roles resource', function () {
    $role = Role::create([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $permission = Permission::create([
        'name' => 'ViewAny:Role',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($permission);

    $user = User::factory()->create();

    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get('/shield/roles')
        ->assertOk();
});

test('resource navigation and access honor shield resource permissions without registered policies', function () {
    Role::create([
        'name' => 'panel_user',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole('panel_user');

    $this->actingAs($user);

    expect(AssetResource::canAccess())->toBeFalse()
        ->and(AssetResource::shouldRegisterNavigation())->toBeFalse();

    $this
        ->get(route('filament.admin.resources.assets.index', absolute: false))
        ->assertForbidden();

    $permission = Permission::create([
        'name' => 'ViewAny:Asset',
        'guard_name' => 'web',
    ]);

    $user->givePermissionTo($permission);

    expect(AssetResource::canAccess())->toBeTrue()
        ->and(AssetResource::shouldRegisterNavigation())->toBeTrue();

    $this
        ->get(route('filament.admin.resources.assets.index', absolute: false))
        ->assertOk();
});
