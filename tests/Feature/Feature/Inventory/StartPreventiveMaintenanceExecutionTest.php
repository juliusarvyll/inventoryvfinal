<?php

use App\Actions\Inventory\StartPreventiveMaintenanceExecution;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceChecklistItem;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('starting a preventive maintenance execution snapshots checklist results', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $category->getKey(),
        'is_active' => true,
    ]);

    $checklistItem1 = PreventiveMaintenanceChecklistItem::factory()->create([
        'preventive_maintenance_checklist_id' => $checklist->getKey(),
        'task' => 'Inspect patch panel condition',
        'input_label' => 'Panel label',
        'is_required' => true,
        'sort_order' => 1,
    ]);

    $checklistItem2 = PreventiveMaintenanceChecklistItem::factory()->create([
        'preventive_maintenance_checklist_id' => $checklist->getKey(),
        'task' => 'Check UPS battery health',
        'input_label' => null,
        'is_required' => true,
        'sort_order' => 2,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'scheduled_for' => now()->addDays(7),
        'is_active' => true,
    ]);
    $schedule->checklists()->attach($checklist);

    $execution = app(StartPreventiveMaintenanceExecution::class)(
        $schedule,
        $checklist,
        $asset,
        [
            [
                'id' => $checklistItem1->getKey(),
                'is_passed' => true,
                'input_value' => 'Rack-A',
                'item_notes' => 'No visible wear.',
            ],
            [
                'id' => $checklistItem2->getKey(),
                'is_passed' => false,
                'item_notes' => 'Battery replacement recommended.',
            ],
        ],
        $actor,
        'Second item needs follow-up.',
    );

    expect($execution->asset_id)->toBe($asset->getKey())
        ->and($execution->preventive_maintenance_schedule_id)->toBe($schedule->getKey())
        ->and($execution->preventive_maintenance_checklist_id)->toBe($checklist->getKey())
        ->and($execution->location_id)->toBe($location->getKey())
        ->and($execution->category_id)->toBe($category->getKey())
        ->and($execution->performed_by)->toBe($actor->getKey())
        ->and($execution->status)->toBe('needs_attention')
        ->and($execution->completed_at)->not->toBeNull()
        ->and($execution->items)->toHaveCount(2);

    expect($execution->items[0]->task)->toBe('Inspect patch panel condition')
        ->and($execution->items[0]->input_label)->toBe('Panel label')
        ->and($execution->items[0]->input_value)->toBe('Rack-A')
        ->and($execution->items[0]->is_passed)->toBeTrue();

    expect($execution->items[1]->task)->toBe('Check UPS battery health')
        ->and($execution->items[1]->input_label)->toBeNull()
        ->and($execution->items[1]->is_passed)->toBeFalse()
        ->and($execution->items[1]->item_notes)->toBe('Battery replacement recommended.');
});

test('starting a preventive maintenance execution rejects schedules outside the asset location', function () {
    $actor = User::factory()->itStaff()->create();
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location1->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $category->getKey(),
        'is_active' => true,
    ]);

    $checklistItem = PreventiveMaintenanceChecklistItem::factory()->create([
        'preventive_maintenance_checklist_id' => $checklist->getKey(),
        'task' => 'Check cabinet lock',
        'is_required' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location2->getKey(),
        'is_active' => true,
    ]);
    $schedule->checklists()->attach($checklist);

    expect(fn () => app(StartPreventiveMaintenanceExecution::class)(
        $schedule,
        $checklist,
        $asset,
        [
            [
                'id' => $checklistItem->getKey(),
                'is_passed' => true,
            ],
        ],
        $actor,
    ))->toThrow(RuntimeException::class, 'The selected preventive maintenance schedule does not match the asset location.');
});

test('starting a preventive maintenance execution rejects schedules outside the asset category', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $assetCategory = Category::factory()->asset()->create();
    $otherCategory = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $assetCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $otherCategory->getKey(),
        'is_active' => true,
    ]);

    $checklistItem = PreventiveMaintenanceChecklistItem::factory()->create([
        'preventive_maintenance_checklist_id' => $checklist->getKey(),
        'task' => 'Check cabinet lock',
        'is_required' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'is_active' => true,
    ]);
    $schedule->checklists()->attach($checklist);

    expect(fn () => app(StartPreventiveMaintenanceExecution::class)(
        $schedule,
        $checklist,
        $asset,
        [
            [
                'id' => $checklistItem->getKey(),
                'is_passed' => true,
            ],
        ],
        $actor,
    ))->toThrow(RuntimeException::class, 'The selected preventive maintenance checklist does not match the asset category.');
});

test('starting a preventive maintenance execution rejects inactive checklists', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $category->getKey(),
        'is_active' => false,
    ]);

    $checklistItem = PreventiveMaintenanceChecklistItem::factory()->create([
        'preventive_maintenance_checklist_id' => $checklist->getKey(),
        'task' => 'Check cabinet lock',
        'is_required' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'is_active' => true,
    ]);
    $schedule->checklists()->attach($checklist);

    expect(fn () => app(StartPreventiveMaintenanceExecution::class)(
        $schedule,
        $checklist,
        $asset,
        [
            [
                'id' => $checklistItem->getKey(),
                'is_passed' => true,
            ],
        ],
        $actor,
    ))->toThrow(RuntimeException::class, 'The selected preventive maintenance checklist is inactive.');
});

test('starting a preventive maintenance execution rejects invalid checklist items', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $checklist = PreventiveMaintenanceChecklist::factory()->create([
        'category_id' => $category->getKey(),
        'is_active' => true,
    ]);

    $schedule = PreventiveMaintenanceSchedule::factory()->create([
        'location_id' => $location->getKey(),
        'is_active' => true,
    ]);
    $schedule->checklists()->attach($checklist);

    expect(fn () => app(StartPreventiveMaintenanceExecution::class)(
        $schedule,
        $checklist,
        $asset,
        [
            [
                'id' => 99999,
                'is_passed' => true,
            ],
        ],
        $actor,
    ))->toThrow(RuntimeException::class, 'One or more checklist items are invalid for this preventive maintenance checklist.');
});
