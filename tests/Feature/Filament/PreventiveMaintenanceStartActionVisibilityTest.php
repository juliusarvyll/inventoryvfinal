<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\PreventiveMaintenancesRelationManager;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('panel users can see the asset-level start preventive maintenance action', function () {
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$category->getKey()],
            'items' => [
                [
                    'task' => 'Inspect network rack',
                    'is_required' => true,
                ],
            ],
        ],
        User::factory()->admin()->create(),
    );

    $itStaff = User::factory()->itStaff()->create();

    $this->actingAs($itStaff);

    Livewire::test(PreventiveMaintenancesRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])->assertSeeText('Start preventive maintenance');
});

test('end users cannot see the asset-level start preventive maintenance action', function () {
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$category->getKey()],
            'items' => [
                [
                    'task' => 'Inspect network rack',
                    'is_required' => true,
                ],
            ],
        ],
        User::factory()->admin()->create(),
    );

    $endUser = User::factory()->create();

    $this->actingAs($endUser);

    Livewire::test(PreventiveMaintenancesRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])->assertDontSeeText('Start preventive maintenance');
});
