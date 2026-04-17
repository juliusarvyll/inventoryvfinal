<?php

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Actions\Inventory\StartPreventiveMaintenanceSession;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('starting an asset preventive maintenance session snapshots checklist results', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->asset()->create();
    $availableStatus = StatusLabel::factory()->available()->create();

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
                    'task' => 'Inspect patch panel condition',
                    'input_label' => 'Panel label',
                    'is_required' => true,
                ],
                [
                    'task' => 'Check UPS battery health',
                    'is_required' => true,
                ],
            ],
        ],
        $actor,
    );

    $templateItems = $preventiveMaintenance->items()->get()->values();

    $session = app(StartPreventiveMaintenanceSession::class)(
        $preventiveMaintenance,
        $asset,
        [
            [
                'id' => $templateItems[0]->getKey(),
                'is_passed' => true,
                'input_value' => 'Rack-A',
                'item_notes' => 'No visible wear.',
            ],
            [
                'id' => $templateItems[1]->getKey(),
                'is_passed' => false,
                'item_notes' => 'Battery replacement recommended.',
            ],
        ],
        $actor,
        'Second item needs follow-up.',
    );

    expect($session->asset_id)->toBe($asset->getKey())
        ->and($session->preventive_maintenance_id)->toBe($preventiveMaintenance->getKey())
        ->and($session->template_version)->toBe($preventiveMaintenance->version)
        ->and($session->performed_by)->toBe($actor->getKey())
        ->and($session->status)->toBe('needs_attention')
        ->and($session->completed_at)->not->toBeNull()
        ->and($session->items)->toHaveCount(2);

    expect($session->items[0]->task)->toBe('Inspect patch panel condition')
        ->and($session->items[0]->input_label)->toBe('Panel label')
        ->and($session->items[0]->input_value)->toBe('Rack-A')
        ->and($session->items[0]->is_passed)->toBeTrue();

    expect($session->items[1]->task)->toBe('Check UPS battery health')
        ->and($session->items[1]->is_passed)->toBeFalse()
        ->and($session->items[1]->item_notes)->toBe('Battery replacement recommended.');
});

test('starting an asset preventive maintenance session rejects templates outside the asset category', function () {
    $actor = User::factory()->itStaff()->create();
    $location = Location::factory()->create();
    $availableStatus = StatusLabel::factory()->available()->create();
    $assetCategory = Category::factory()->asset()->create();
    $otherCategory = Category::factory()->asset()->create();

    $asset = Asset::factory()->create([
        'location_id' => $location->getKey(),
        'category_id' => $assetCategory->getKey(),
        'status_label_id' => $availableStatus->getKey(),
    ]);

    $preventiveMaintenance = app(SavePreventiveMaintenancePlan::class)(
        null,
        [
            'location_id' => $location->getKey(),
            'category_ids' => [$otherCategory->getKey()],
            'items' => [
                [
                    'task' => 'Check cabinet lock',
                    'is_required' => true,
                ],
            ],
        ],
        $actor,
    );

    $templateItem = $preventiveMaintenance->items()->firstOrFail();

    expect(fn () => app(StartPreventiveMaintenanceSession::class)(
        $preventiveMaintenance,
        $asset,
        [
            [
                'id' => $templateItem->getKey(),
                'is_passed' => true,
            ],
        ],
        $actor,
    ))->toThrow(RuntimeException::class, 'The selected preventive maintenance template does not match the asset category.');
});
