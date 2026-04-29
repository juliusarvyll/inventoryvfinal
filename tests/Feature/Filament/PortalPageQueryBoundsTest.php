<?php

use App\Filament\Portal\Pages\Portal\BrowseRequestables;
use App\Filament\Portal\Pages\Portal\MyAssets;
use App\Filament\Portal\Pages\Portal\MyRequests;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\StatusLabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('browse requestables limits each inventory section', function () {
    $manufacturer = Manufacturer::factory()->create();
    $supplier = Supplier::factory()->create();
    $location = Location::factory()->create();
    $availableStatus = StatusLabel::query()->firstOrCreate([
        'name' => 'Available',
    ], [
        'color' => '#22c55e',
        'type' => 'deployable',
    ]);
    $assetCategory = Category::factory()->asset()->create();
    $licenseCategory = Category::factory()->license()->create();
    $accessoryCategory = Category::factory()->accessory()->create();
    $consumableCategory = Category::factory()->consumable()->create();
    $componentCategory = Category::factory()->component()->create();
    $assetModel = AssetModel::factory()->create([
        'manufacturer_id' => $manufacturer->getKey(),
        'category_id' => $assetCategory->getKey(),
    ]);

    Asset::factory()->count(30)->create([
        'requestable' => true,
        'status_label_id' => $availableStatus->getKey(),
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $assetCategory->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
    ]);
    License::factory()->count(30)->create([
        'requestable' => true,
        'category_id' => $licenseCategory->getKey(),
        'manufacturer_id' => $manufacturer->getKey(),
    ]);
    Accessory::factory()->count(30)->create([
        'requestable' => true,
        'category_id' => $accessoryCategory->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
    ]);
    Consumable::factory()->count(30)->create([
        'requestable' => true,
        'category_id' => $consumableCategory->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
    ]);
    Component::factory()->count(30)->create([
        'requestable' => true,
        'category_id' => $componentCategory->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
    ]);

    $viewData = pageViewData(BrowseRequestables::class);

    expect($viewData['assets'])->toHaveCount(25)
        ->and($viewData['licenses'])->toHaveCount(25)
        ->and($viewData['accessories'])->toHaveCount(25)
        ->and($viewData['consumables'])->toHaveCount(25)
        ->and($viewData['components'])->toHaveCount(25);
});

test('my assets limits each assigned inventory section', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $checkedOutBy = User::factory()->itStaff()->create();
    $manufacturer = Manufacturer::factory()->create();
    $supplier = Supplier::factory()->create();
    $location = Location::factory()->create();
    $availableStatus = StatusLabel::query()->firstOrCreate([
        'name' => 'Available',
    ], [
        'color' => '#22c55e',
        'type' => 'deployable',
    ]);
    $assetCategory = Category::factory()->asset()->create();
    $assetModel = AssetModel::factory()->create([
        'manufacturer_id' => $manufacturer->getKey(),
        'category_id' => $assetCategory->getKey(),
    ]);
    $accessoryCategory = Category::factory()->accessory()->create();
    $consumableCategory = Category::factory()->consumable()->create();
    $licenseCategory = Category::factory()->license()->create();
    $license = License::factory()->create([
        'category_id' => $licenseCategory->getKey(),
        'manufacturer_id' => $manufacturer->getKey(),
    ]);
    foreach (range(1, 30) as $index) {
        $asset = Asset::factory()->create([
            'status_label_id' => $availableStatus->getKey(),
            'asset_model_id' => $assetModel->getKey(),
            'category_id' => $assetCategory->getKey(),
            'supplier_id' => $supplier->getKey(),
            'location_id' => $location->getKey(),
        ]);
        $accessory = Accessory::factory()->create([
            'category_id' => $accessoryCategory->getKey(),
            'supplier_id' => $supplier->getKey(),
            'location_id' => $location->getKey(),
        ]);
        $consumable = Consumable::factory()->create([
            'category_id' => $consumableCategory->getKey(),
            'supplier_id' => $supplier->getKey(),
            'location_id' => $location->getKey(),
        ]);

        AssetCheckout::factory()->create([
            'asset_id' => $asset->getKey(),
            'assigned_to' => $user->getKey(),
            'checked_out_by' => $checkedOutBy->getKey(),
            'assigned_at' => now()->subMinutes($index),
        ]);

        LicenseSeat::factory()->create([
            'license_id' => $license->getKey(),
            'assigned_to' => $user->getKey(),
            'assigned_at' => now()->subMinutes($index),
        ]);

        AssetCheckout::factory()->create([
            'asset_id' => $accessory->getKey(),
            'assigned_to' => $user->getKey(),
            'checked_out_by' => $checkedOutBy->getKey(),
            'assigned_at' => now()->subMinutes($index),
        ]);

        AssetCheckout::factory()->create([
            'asset_id' => $consumable->getKey(),
            'assigned_to' => $user->getKey(),
            'checked_out_by' => $checkedOutBy->getKey(),
            'assigned_at' => now()->subMinutes($index),
        ]);
    }

    $viewData = pageViewData(MyAssets::class);

    expect($viewData['assetCheckouts'])->toHaveCount(25)
        ->and($viewData['licenseSeats'])->toHaveCount(25)
        ->and($viewData['accessoryCheckouts'])->toHaveCount(25)
        ->and($viewData['consumableAssignments'])->toHaveCount(25);
});

test('my requests limits request history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $license = License::factory()->create();

    ItemRequest::factory()->count(30)->create([
        'user_id' => $user->getKey(),
        'requestable_type' => License::class,
        'requestable_id' => $license->getKey(),
    ]);

    $viewData = pageViewData(MyRequests::class);

    expect($viewData['requests'])->toHaveCount(25);
});

/**
 * @param  class-string  $pageClass
 * @return array<string, mixed>
 */
function pageViewData(string $pageClass): array
{
    $page = app($pageClass);
    $method = new ReflectionMethod($page, 'getViewData');
    $method->setAccessible(true);

    /** @var array<string, mixed> $viewData */
    $viewData = $method->invoke($page);

    return $viewData;
}
