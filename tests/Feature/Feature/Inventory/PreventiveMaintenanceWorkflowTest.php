<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('preventive maintenance creation assigns assets and auto-orders checklist items', function () {
    $actor = User::factory()->admin()->create();
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $selectedCategory = Category::factory()->asset()->create();
    $otherCategory = Category::factory()->asset()->create();

    $assetAtLocation = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $selectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $otherAssetAtLocation = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $selectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $assetAtAnotherLocation = Asset::factory()->create([
        'location_id' => $otherLocation->getKey(),
        'category_id' => $selectedCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $otherCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $maintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$selectedCategory->getKey()],
            'scheduled_for' => '2026-06-01',
            'items' => [
                [
                    'task' => 'Check asset labels and serial numbers.',
                    'is_required' => true,
                ],
                [
                    'task' => 'Inspect network cabling condition.',
                    'is_required' => false,
                ],
            ],
        ],
        $actor,
    );

    expect($maintenance->categories()->pluck('categories.id')->all())
        ->toEqual([$selectedCategory->getKey()]);
    expect($maintenance->assets()->pluck('assets.id')->all())
        ->toEqualCanonicalizing([$assetAtLocation->getKey(), $otherAssetAtLocation->getKey()]);

    expect($maintenance->items()->pluck('sort_order', 'task')->all())
        ->toBe([
            'Check asset labels and serial numbers.' => 1,
            'Inspect network cabling condition.' => 2,
        ]);
});

test('preventive maintenance updates rewrite selected assets and checklist order', function () {
    $actor = User::factory()->admin()->create();
    $location = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $categoryA = Category::factory()->asset()->create();
    $categoryB = Category::factory()->asset()->create();
    $categoryC = Category::factory()->asset()->create();

    $assetA = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $categoryA->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $assetB = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $categoryB->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);
    $assetC = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $categoryC->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $maintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$categoryA->getKey()],
            'items' => [
                [
                    'task' => 'Original first task',
                    'is_required' => true,
                ],
                [
                    'task' => 'Original second task',
                    'is_required' => false,
                ],
            ],
        ],
        $actor,
    );

    $existingItems = $maintenance->items()->get()->values();

    $updatedMaintenance = app(SavePreventiveMaintenancePlan::class)(
        $maintenance,
        [
            'category_ids' => [$categoryB->getKey(), $categoryC->getKey()],
            'items' => [
                [
                    'id' => $existingItems[1]->getKey(),
                    'task' => 'Original second task',
                    'is_required' => false,
                ],
                [
                    'task' => 'New third task',
                    'is_required' => true,
                ],
            ],
        ],
        $actor,
    );

    expect($updatedMaintenance->categories()->pluck('categories.id')->all())
        ->toEqualCanonicalizing([$categoryB->getKey(), $categoryC->getKey()]);
    expect($updatedMaintenance->assets()->pluck('assets.id')->all())
        ->toEqualCanonicalizing([$assetB->getKey(), $assetC->getKey()]);

    expect($updatedMaintenance->items()->pluck('sort_order', 'task')->all())
        ->toBe([
            'Original second task' => 1,
            'New third task' => 2,
        ]);
});
