<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category selections derive location assets for a preventive maintenance plan', function () {
    $actor = User::factory()->admin()->create();
    $location = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $selectedCategory = Category::factory()->asset()->create();
    $otherSelectedCategory = Category::factory()->asset()->create();
    $excludedCategory = Category::factory()->asset()->create();

    $assetA = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $selectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $assetB = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $selectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $assetC = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $otherSelectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $excludedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $preventiveMaintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$selectedCategory->getKey(), $otherSelectedCategory->getKey()],
        ],
        $actor,
    );

    expect($preventiveMaintenance->categories()->pluck('categories.id')->all())
        ->toEqualCanonicalizing([$selectedCategory->getKey(), $otherSelectedCategory->getKey()]);
    expect($preventiveMaintenance->assets()->pluck('assets.id')->all())
        ->toEqualCanonicalizing([$assetA->getKey(), $assetB->getKey(), $assetC->getKey()]);
});

test('invalid category selections are ignored for a preventive maintenance plan', function () {
    $actor = User::factory()->admin()->create();
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $foreignCategory = Category::factory()->asset()->create();
    $localCategory = Category::factory()->asset()->create();

    Asset::factory()->create([
        'location_id' => $otherLocation->getKey(),
        'category_id' => $foreignCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $localAsset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $localCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $preventiveMaintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$foreignCategory->getKey(), $localCategory->getKey()],
        ],
        $actor,
    );

    expect($preventiveMaintenance->categories()->pluck('categories.id')->all())
        ->toEqual([$localCategory->getKey()])
        ->and($preventiveMaintenance->assets()->pluck('assets.id')->all())->toEqual([$localAsset->getKey()]);
});
