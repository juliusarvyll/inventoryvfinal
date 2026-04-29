<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('accessories are flagged as low stock when active checkouts reach the threshold', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'min_qty' => 2]);

    AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->getKey(),
        'qty' => 3,
    ]);

    expect($accessory->qtyRemaining())->toBe(2)
        ->and($accessory->isLowStock())->toBeTrue();

    expect(Accessory::query()->lowStock()->pluck('id'))->toContain($accessory->getKey());
});

test('consumables are flagged as low stock when assignments reach the threshold', function () {
    $consumable = Consumable::factory()->create(['qty' => 8, 'min_qty' => 3]);

    ConsumableAssignment::factory()->create([
        'consumable_id' => $consumable->getKey(),
        'qty' => 5,
    ]);

    expect($consumable->qtyRemaining())->toBe(3)
        ->and($consumable->isLowStock())->toBeTrue();

    expect(Consumable::query()->lowStock()->pluck('id'))->toContain($consumable->getKey());
});

test('components are flagged as low stock when installed quantity reaches the threshold', function () {
    $component = Component::factory()->create(['qty' => 6, 'min_qty' => 2]);
    $asset = Asset::factory()->create();

    $component->assets()->attach($asset, [
        'qty' => 4,
        'installed_at' => now(),
    ]);

    expect($component->qtyRemaining())->toBe(2)
        ->and($component->isLowStock())->toBeTrue();

    expect(Component::query()->lowStock()->pluck('id'))->toContain($component->getKey());
});

test('low stock aggregate helpers reuse preloaded quantities without extra queries', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'min_qty' => 2]);
    $consumable = Consumable::factory()->create(['qty' => 8, 'min_qty' => 3]);
    $component = Component::factory()->create(['qty' => 6, 'min_qty' => 2]);
    $asset = Asset::factory()->create();

    AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->getKey(),
        'qty' => 3,
    ]);

    ConsumableAssignment::factory()->create([
        'consumable_id' => $consumable->getKey(),
        'qty' => 5,
    ]);

    $component->assets()->attach($asset, [
        'qty' => 4,
        'installed_at' => now(),
    ]);

    $hydratedAccessory = Accessory::query()
        ->select('accessories.*')
        ->selectSub(
            DB::table('accessory_checkouts')
                ->selectRaw('coalesce(sum(qty), 0)')
                ->whereColumn('accessory_checkouts.accessory_id', 'accessories.id')
                ->whereNull('returned_at'),
            'active_checked_out_quantity',
        )
        ->findOrFail($accessory->getKey());

    $hydratedConsumable = Consumable::query()
        ->select('consumables.*')
        ->selectSub(
            DB::table('consumable_assignments')
                ->selectRaw('coalesce(sum(qty), 0)')
                ->whereColumn('consumable_assignments.consumable_id', 'consumables.id'),
            'assigned_quantity',
        )
        ->findOrFail($consumable->getKey());

    $hydratedComponent = Component::query()
        ->select('components.*')
        ->selectSub(
            DB::table('asset_component')
                ->selectRaw('coalesce(sum(qty), 0)')
                ->whereColumn('asset_component.component_id', 'components.id'),
            'installed_quantity',
        )
        ->findOrFail($component->getKey());

    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    expect($hydratedAccessory->qtyRemaining())->toBe(2)
        ->and($hydratedConsumable->qtyRemaining())->toBe(3)
        ->and($hydratedComponent->qtyRemaining())->toBe(2)
        ->and($connection->getQueryLog())->toHaveCount(0);
});
