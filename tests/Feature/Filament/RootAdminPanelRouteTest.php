<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the admin panel dashboard route is mounted at the root URL', function () {
    expect(route('filament.admin.pages.dashboard', absolute: false))->toBe('/');
});

test('the admin panel login route stays on /login', function () {
    expect(route('filament.admin.auth.login', absolute: false))->toBe('/login');
});

test('the admin panel logout route resolves to the shared logout endpoint', function () {
    expect(route('filament.admin.auth.logout', absolute: false))->toBe('/logout');
});

test('admin users can render the admin panel dashboard', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.pages.dashboard', absolute: false));

    $response->assertOk();
});

test('admin users can render the item request create page', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.item-requests.create', absolute: false));

    $response->assertOk();
});

test('admin users can render the asset create page', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.assets.create', absolute: false));

    $response->assertOk();
});
