<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\StatusLabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('all strict Filament resources have registered policies with the required methods', function () {
    $resources = [
        Accessory::class,
        Asset::class,
        AssetModel::class,
        Category::class,
        Component::class,
        Consumable::class,
        ItemRequest::class,
        License::class,
        Location::class,
        Manufacturer::class,
        StatusLabel::class,
        Supplier::class,
        User::class,
    ];

    foreach ($resources as $resource) {
        $policy = Gate::getPolicyFor($resource);

        expect($policy)->not->toBeNull("Missing policy for [{$resource}].");
        expect(method_exists($policy, 'viewAny'))->toBeTrue();
        expect(method_exists($policy, 'view'))->toBeTrue();
        expect(method_exists($policy, 'create'))->toBeTrue();
        expect(method_exists($policy, 'update'))->toBeTrue();
        expect(method_exists($policy, 'delete'))->toBeTrue();
        expect(method_exists($policy, 'deleteAny'))->toBeTrue();
    }
});

test('admin users can render the strict-authorization dashboard', function () {
    $response = $this
        ->actingAs(User::factory()->admin()->create())
        ->get('/');

    $response->assertOk();
});
