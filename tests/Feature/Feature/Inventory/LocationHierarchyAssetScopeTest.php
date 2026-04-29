<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('parent location preventive maintenance plans include child location assets', function () {
    $actor = User::factory()->admin()->create();
    $parentLocation = Location::factory()->create();
    $childLocationA = Location::factory()->create(['parent_id' => $parentLocation->getKey()]);
    $childLocationB = Location::factory()->create(['parent_id' => $parentLocation->getKey()]);
    $outsideLocation = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $category = Category::factory()->asset()->create();

    $assetInChildA = Asset::factory()->create([
        'location_id' => $childLocationA->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $assetInChildB = Asset::factory()->create([
        'location_id' => $childLocationB->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    Asset::factory()->create([
        'location_id' => $outsideLocation->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $preventiveMaintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $parentLocation->getKey(),
            'category_ids' => [$category->getKey()],
        ],
        $actor,
    );

    expect($preventiveMaintenance->assets()->pluck('assets.id')->all())
        ->toEqualCanonicalizing([$assetInChildA->getKey(), $assetInChildB->getKey()]);
});
