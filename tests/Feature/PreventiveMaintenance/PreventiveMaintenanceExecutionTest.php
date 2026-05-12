<?php

use App\Actions\Inventory\StartPreventiveMaintenanceExecution;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->user = User::factory()->admin()->create();
    actingAs($this->user);
});

test('can start preventive maintenance execution', function (): void {
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create(['name' => 'Test Category '.uniqid()]);
    $asset = Asset::factory()->create([
        'location_id' => $location->id,
        'category_id' => $category->id,
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create();
    $checklist->categories()->attach($category);
    $item = $checklist->items()->create([
        'task' => 'Test task',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->id,
    ]);
    $schedule->checklists()->attach($checklist);

    $action = new StartPreventiveMaintenanceExecution;
    $execution = $action(
        $schedule,
        $checklist,
        $asset,
        [['id' => $item->id, 'is_passed' => true]],
        $this->user,
        'Test notes'
    );

    assertDatabaseHas('preventive_maintenance_executions', [
        'id' => $execution->id,
        'asset_id' => $asset->id,
        'status' => 'completed',
    ]);

    expect($execution->items)->toHaveCount(1);
});

test('execution status is needs_attention when item fails', function (): void {
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create(['name' => 'Test Category '.uniqid()]);
    $asset = Asset::factory()->create([
        'location_id' => $location->id,
        'category_id' => $category->id,
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create();
    $checklist->categories()->attach($category);
    $item = $checklist->items()->create([
        'task' => 'Test task',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->id,
    ]);
    $schedule->checklists()->attach($checklist);

    $action = new StartPreventiveMaintenanceExecution;
    $execution = $action(
        $schedule,
        $checklist,
        $asset,
        [['id' => $item->id, 'is_passed' => false]],
        $this->user
    );

    expect($execution->status)->toBe('needs_attention');
});
