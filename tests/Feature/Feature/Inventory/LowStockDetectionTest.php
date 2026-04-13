<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
