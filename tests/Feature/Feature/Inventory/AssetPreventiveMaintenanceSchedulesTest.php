<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\StatusLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('asset preventive maintenance schedules are filtered through checklist categories', function () {
    $location = Location::factory()->create();
    $matchingCategory = Category::factory()->asset()->create();
    $otherCategory = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $matchingCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $matchingChecklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $matchingCategory->getKey(),
        'is_active' => true,
    ]);

    $otherChecklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $otherCategory->getKey(),
        'is_active' => true,
    ]);

    $matchingSchedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'is_active' => true,
    ]);
    $matchingSchedule->checklists()->attach($matchingChecklist);

    $otherSchedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'is_active' => true,
    ]);
    $otherSchedule->checklists()->attach($otherChecklist);

    expect($asset->preventiveMaintenanceSchedules()->pluck('preventive_maintenance_schedules.id')->all())
        ->toBe([$matchingSchedule->getKey()]);
});
