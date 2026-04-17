<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\PreventiveMaintenanceSessionsRelationManager;
use App\Filament\Resources\Assets\RelationManagers\PreventiveMaintenancesRelationManager;
use App\Filament\Resources\PreventiveMaintenances\Pages\ViewPreventiveMaintenance;
use App\Filament\Resources\PreventiveMaintenances\RelationManagers\AssetsRelationManager;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceSession;
use App\Models\StatusLabel;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('asset preventive maintenance relation manager shows matching templates and start action to panel users', function () {
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
                    'task' => 'Inspect switch uplinks',
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
    ])
        ->assertSeeText('Start preventive maintenance')
        ->assertSeeText('Categories');

    Livewire::test(PreventiveMaintenanceSessionsRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])
        ->assertSeeText('Started')
        ->assertSeeText('Status');
});

test('preventive maintenance view can start sessions for included assets', function () {
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $admin = User::factory()->admin()->create();
    $itStaff = User::factory()->itStaff()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $preventiveMaintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$category->getKey()],
            'items' => [
                [
                    'task' => 'Inspect power supply',
                    'input_label' => 'Voltage reading',
                    'is_required' => true,
                ],
            ],
        ],
        $admin,
    );

    $templateItem = $preventiveMaintenance->items()->firstOrFail();

    $this->actingAs($itStaff);

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $preventiveMaintenance,
        'pageClass' => ViewPreventiveMaintenance::class,
    ])
        ->assertCanSeeTableRecords([$asset])
        ->assertActionVisible(TestAction::make('start')->table($asset))
        ->callAction(TestAction::make('start')->table($asset), data: [
            'items' => [
                [
                    'id' => $templateItem->getKey(),
                    'is_passed' => '1',
                    'input_value' => '229V',
                    'item_notes' => 'Stable output.',
                ],
            ],
            'general_notes' => 'Completed from PM view.',
        ])
        ->assertNotified('Preventive maintenance session saved');

    $session = PreventiveMaintenanceSession::query()->first();

    expect($session)->not->toBeNull();
    expect($session->asset_id)->toBe($asset->getKey())
        ->and($session->preventive_maintenance_id)->toBe($preventiveMaintenance->getKey())
        ->and($session->performed_by)->toBe($itStaff->getKey())
        ->and($session->general_notes)->toBe('Completed from PM view.');

    expect($session->items)->not->toBeEmpty()
        ->and($session->items->contains(fn ($item): bool => $item->preventive_maintenance_item_id === $templateItem->getKey()))
        ->toBeTrue()
        ->and($session->items->contains(fn ($item): bool => $item->task === 'Inspect power supply'))
        ->toBeTrue();
});
