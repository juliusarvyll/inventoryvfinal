<?php

use App\Filament\Resources\Locations\Pages\ViewLocation;
use App\Filament\Resources\Locations\RelationManagers\PreventiveMaintenancesRelationManager;
use App\Models\Location;
use App\Models\PreventiveMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('location preventive maintenance relation manager shows one-by-one PM actions for admins', function () {
    $location = Location::factory()->create();
    $maintenance = PreventiveMaintenance::query()->create([
        'location_id' => $location->getKey(),
        'instructions' => 'Monthly checklist',
    ]);

    $maintenance->items()->create([
        'task' => 'Inspect network rack',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(PreventiveMaintenancesRelationManager::class, [
        'ownerRecord' => $location,
        'pageClass' => ViewLocation::class,
    ])
        ->assertSeeText('Categories')
        ->assertSeeText('Checklist');
});

test('location preventive maintenance relation manager hides start action for non-admin users', function () {
    $location = Location::factory()->create();
    $maintenance = PreventiveMaintenance::query()->create([
        'location_id' => $location->getKey(),
        'instructions' => 'Monthly checklist',
    ]);

    $maintenance->items()->create([
        'task' => 'Inspect network rack',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $itStaff = User::factory()->itStaff()->create();

    $this->actingAs($itStaff);

    Livewire::test(PreventiveMaintenancesRelationManager::class, [
        'ownerRecord' => $location,
        'pageClass' => ViewLocation::class,
    ])
        ->assertSeeText('Categories')
        ->assertSeeText('Checklist');
});
