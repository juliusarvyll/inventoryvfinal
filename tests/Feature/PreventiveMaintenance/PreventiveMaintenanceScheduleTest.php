<?php

use App\Models\Department;
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

test('can create preventive maintenance schedule', function (): void {
    $department = Department::factory()->create();
    $location = Location::factory()->create(['department_id' => $department->id]);
    $checklist = PreventiveMaintenanceChecklist::factory()->create();

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'department_id' => $department->id,
        'location_id' => $location->id,
        'scheduled_for' => now()->addDays(7),
        'is_active' => true,
    ]);

    $schedule->checklists()->attach($checklist);

    assertDatabaseHas('preventive_maintenance_schedules', [
        'id' => $schedule->id,
        'location_id' => $location->id,
        'is_active' => true,
    ]);

    expect($schedule->checklists)->toHaveCount(1);
});

test('can attach multiple checklists to schedule', function (): void {
    $schedule = PreventiveMaintenanceSchedule::factory()->create();
    $checklists = PreventiveMaintenanceChecklist::factory()->count(3)->create();

    $schedule->checklists()->attach($checklists->pluck('id'));

    expect($schedule->fresh()->checklists)->toHaveCount(3);
});

test('can deactivate schedule', function (): void {
    $schedule = PreventiveMaintenanceSchedule::factory()->create(['is_active' => true]);

    $schedule->update(['is_active' => false]);

    assertDatabaseHas('preventive_maintenance_schedules', [
        'id' => $schedule->id,
        'is_active' => false,
    ]);
});
