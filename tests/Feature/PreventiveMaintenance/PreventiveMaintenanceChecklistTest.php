<?php

use App\Models\Category;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->user = User::factory()->admin()->create();
    actingAs($this->user);
});

test('can create preventive maintenance checklist', function (): void {
    $category = Category::factory()->asset()->create();

    $checklist = PreventiveMaintenanceChecklist::factory()->create();
    $checklist->categories()->attach($category);

    assertDatabaseHas('preventive_maintenance_checklists', [
        'id' => $checklist->id,
        'is_active' => true,
    ]);

    assertDatabaseHas('category_preventive_maintenance_checklist', [
        'category_id' => $category->id,
        'preventive_maintenance_checklist_id' => $checklist->id,
    ]);
});

test('can add items to checklist', function (): void {
    $checklist = PreventiveMaintenanceChecklist::factory()->create();

    $item = $checklist->items()->create([
        'task' => 'Check power supply',
        'input_label' => 'Voltage reading',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    assertDatabaseHas('preventive_maintenance_checklist_items', [
        'preventive_maintenance_checklist_id' => $checklist->id,
        'task' => 'Check power supply',
        'is_required' => true,
    ]);

    expect($checklist->items)->toHaveCount(1);
});

test('can deactivate checklist', function (): void {
    $checklist = PreventiveMaintenanceChecklist::factory()->create(['is_active' => true]);

    $checklist->update(['is_active' => false]);

    assertDatabaseHas('preventive_maintenance_checklists', [
        'id' => $checklist->id,
        'is_active' => false,
    ]);
});
