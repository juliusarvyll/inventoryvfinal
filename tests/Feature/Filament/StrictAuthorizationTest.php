<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('admin resources no longer depend on registered model policies', function () {
    expect(Gate::getPolicyFor(Asset::class))->toBeNull();
});

test('admin users can render the dashboard without strict resource policies', function () {
    $response = $this
        ->actingAs(User::factory()->admin()->create())
        ->get('/');

    $response->assertOk();
});
