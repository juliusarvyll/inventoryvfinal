<?php

use App\Filament\Imports\AssetModelImporter;
use App\Filament\Imports\ComponentImporter;
use App\Filament\Imports\ConsumableImporter;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin users can render the asset models index page with import support', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.asset-models.index', absolute: false));

    $response->assertOk();
});

test('admin users can render the components index page with import support', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.components.index', absolute: false));

    $response->assertOk();
});

test('admin users can render the consumables index page with import support', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.consumables.index', absolute: false));

    $response->assertOk();
});

function makeAssetModelImporter(): AssetModelImporter
{
    return new AssetModelImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'manufacturer' => 'manufacturer',
            'category' => 'category',
            'model_number' => 'model_number',
            'serial_numbers' => 'serial_numbers',
        ],
        options: [],
    );
}

function makeComponentImporter(): ComponentImporter
{
    return new ComponentImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'category' => 'category',
            'supplier' => 'supplier',
            'location' => 'location',
            'qty' => 'qty',
            'min_qty' => 'min_qty',
            'serial' => 'serial',
            'purchase_cost' => 'purchase_cost',
            'purchase_date' => 'purchase_date',
            'order_number' => 'order_number',
            'requestable' => 'requestable',
            'notes' => 'notes',
        ],
        options: [],
    );
}

function makeConsumableImporter(): ConsumableImporter
{
    return new ConsumableImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'category' => 'category',
            'supplier' => 'supplier',
            'location' => 'location',
            'qty' => 'qty',
            'min_qty' => 'min_qty',
            'model_number' => 'model_number',
            'item_no' => 'item_no',
            'purchase_cost' => 'purchase_cost',
            'purchase_date' => 'purchase_date',
            'order_number' => 'order_number',
            'requestable' => 'requestable',
            'notes' => 'notes',
        ],
        options: [],
    );
}

test('asset model importer creates a record from csv-friendly relationship values', function () {
    $category = Category::factory()->asset()->create(['name' => 'Laptops']);
    $manufacturer = Manufacturer::factory()->create(['name' => 'Dell']);

    $importer = makeAssetModelImporter();

    $importer([
        'name' => 'Latitude 7450',
        'manufacturer' => 'Dell',
        'category' => 'Laptops',
        'model_number' => 'LAT-7450',
    ]);

    $this->assertDatabaseHas('asset_models', [
        'name' => 'Latitude 7450',
        'manufacturer_id' => $manufacturer->getKey(),
        'category_id' => $category->getKey(),
        'model_number' => 'LAT-7450',
    ]);
});

test('asset model importer bulk creates assets from comma separated serial numbers', function () {
    $category = Category::factory()->asset()->create(['name' => 'Laptops']);
    $manufacturer = Manufacturer::factory()->create(['name' => 'Dell']);

    $importer = makeAssetModelImporter();

    $importer([
        'name' => 'Latitude 7450',
        'manufacturer' => 'Dell',
        'category' => 'Laptops',
        'model_number' => 'LAT-7450',
        'serial_numbers' => 'SER-1001, SER-1002, SER-1003',
    ]);

    $assetModel = AssetModel::query()->where('model_number', 'LAT-7450')->firstOrFail();

    expect(Asset::query()->where('asset_model_id', $assetModel->getKey())->count())->toBe(3);

    $this->assertDatabaseHas('assets', [
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $category->getKey(),
        'serial' => 'SER-1001',
        'name' => 'Latitude 7450',
    ]);
});

test('asset model importer updates an existing record when model number matches within the category', function () {
    $category = Category::factory()->asset()->create(['name' => 'Monitors']);
    $manufacturer = Manufacturer::factory()->create(['name' => 'Dell']);
    $assetModel = AssetModel::factory()->create([
        'name' => 'Old Model Name',
        'manufacturer_id' => $manufacturer->getKey(),
        'category_id' => $category->getKey(),
        'model_number' => 'U2723',
    ]);

    $importer = makeAssetModelImporter();

    $importer([
        'name' => 'UltraSharp 27',
        'manufacturer' => 'Dell',
        'category' => 'Monitors',
        'model_number' => 'U2723',
    ]);

    expect($assetModel->refresh()->name)->toBe('UltraSharp 27');
});

test('asset model importer uses the selected default category when the csv row has no category', function () {
    $defaultCategory = Category::factory()->asset()->create(['name' => 'Docking Stations']);

    $importer = new AssetModelImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'manufacturer' => 'manufacturer',
            'model_number' => 'model_number',
        ],
        options: [
            'default_category_id' => $defaultCategory->getKey(),
        ],
    );

    $importer([
        'name' => 'USB-C Dock',
        'manufacturer' => 'Dell',
        'model_number' => 'WD22TB4',
    ]);

    $this->assertDatabaseHas('asset_models', [
        'name' => 'USB-C Dock',
        'category_id' => $defaultCategory->getKey(),
        'model_number' => 'WD22TB4',
    ]);
});

test('component importer creates a record and defaults optional stock fields', function () {
    $category = Category::factory()->component()->create(['name' => 'Memory']);
    $supplier = Supplier::factory()->create(['name' => 'Parts Depot']);
    $location = Location::factory()->create(['name' => 'Main Storage']);

    $importer = makeComponentImporter();

    $importer([
        'name' => 'Laptop RAM',
        'category' => 'Memory',
        'supplier' => 'Parts Depot',
        'location' => 'Main Storage',
        'qty' => '12',
        'min_qty' => null,
        'serial' => 'CMP-1001',
        'purchase_cost' => '49.99',
        'purchase_date' => '2026-04-01',
        'order_number' => 'PO-1234',
        'requestable' => null,
        'notes' => 'Stock import',
    ]);

    $this->assertDatabaseHas('components', [
        'name' => 'Laptop RAM',
        'category_id' => $category->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
        'qty' => 12,
        'min_qty' => 0,
        'serial' => 'CMP-1001',
        'requestable' => 0,
    ]);
});

test('component importer updates an existing record when serial matches', function () {
    $category = Category::factory()->component()->create(['name' => 'Power']);
    $component = Component::factory()->create([
        'name' => 'Old Battery',
        'category_id' => $category->getKey(),
        'serial' => 'CMP-2001',
        'qty' => 2,
        'requestable' => false,
    ]);

    $importer = makeComponentImporter();

    $importer([
        'name' => 'Replacement Battery',
        'category' => 'Power',
        'supplier' => null,
        'location' => null,
        'qty' => '8',
        'min_qty' => '1',
        'serial' => 'CMP-2001',
        'purchase_cost' => null,
        'purchase_date' => null,
        'order_number' => null,
        'requestable' => 'true',
        'notes' => null,
    ]);

    expect($component->refresh()->name)->toBe('Replacement Battery')
        ->and($component->qty)->toBe(8)
        ->and($component->requestable)->toBeTrue();
});

test('component importer uses the selected default category when the csv row has no category', function () {
    $defaultCategory = Category::factory()->component()->create(['name' => 'Adapters']);

    $importer = new ComponentImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'qty' => 'qty',
            'requestable' => 'requestable',
        ],
        options: [
            'default_category_id' => $defaultCategory->getKey(),
        ],
    );

    $importer([
        'name' => 'USB Adapter',
        'qty' => '10',
        'requestable' => 'false',
    ]);

    $this->assertDatabaseHas('components', [
        'name' => 'USB Adapter',
        'category_id' => $defaultCategory->getKey(),
    ]);
});

test('consumable importer creates a record from csv-friendly relationship values', function () {
    $category = Category::factory()->consumable()->create(['name' => 'Printer Supplies']);
    $supplier = Supplier::factory()->create(['name' => 'Office Hub']);
    $location = Location::factory()->create(['name' => 'Supply Closet']);

    $importer = makeConsumableImporter();

    $importer([
        'name' => 'Black Toner',
        'category' => 'Printer Supplies',
        'supplier' => 'Office Hub',
        'location' => 'Supply Closet',
        'qty' => '24',
        'min_qty' => '4',
        'model_number' => 'TN-360',
        'item_no' => 'SKU-360',
        'purchase_cost' => '79.99',
        'purchase_date' => '2026-04-03',
        'order_number' => 'PO-4433',
        'requestable' => 'yes',
        'notes' => 'Initial import',
    ]);

    $this->assertDatabaseHas('consumables', [
        'name' => 'Black Toner',
        'category_id' => $category->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
        'model_number' => 'TN-360',
        'item_no' => 'SKU-360',
        'requestable' => 1,
    ]);
});

test('consumable importer updates an existing record when item number matches', function () {
    $category = Category::factory()->consumable()->create(['name' => 'Batteries']);
    $consumable = Consumable::factory()->create([
        'name' => 'Old Battery',
        'category_id' => $category->getKey(),
        'item_no' => 'SKU-9001',
        'qty' => 5,
        'requestable' => false,
    ]);

    $importer = makeConsumableImporter();

    $importer([
        'name' => 'AA Battery Pack',
        'category' => 'Batteries',
        'supplier' => null,
        'location' => null,
        'qty' => '30',
        'min_qty' => '3',
        'model_number' => 'AA-24',
        'item_no' => 'SKU-9001',
        'purchase_cost' => null,
        'purchase_date' => null,
        'order_number' => null,
        'requestable' => 'true',
        'notes' => null,
    ]);

    expect($consumable->refresh()->name)->toBe('AA Battery Pack')
        ->and($consumable->qty)->toBe(30)
        ->and($consumable->requestable)->toBeTrue();
});

test('consumable importer uses the selected default category when the csv row has no category', function () {
    $defaultCategory = Category::factory()->consumable()->create(['name' => 'Labels']);

    $importer = new ConsumableImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'qty' => 'qty',
            'requestable' => 'requestable',
        ],
        options: [
            'default_category_id' => $defaultCategory->getKey(),
        ],
    );

    $importer([
        'name' => 'Shipping Label Roll',
        'qty' => '40',
        'requestable' => 'true',
    ]);

    $this->assertDatabaseHas('consumables', [
        'name' => 'Shipping Label Roll',
        'category_id' => $defaultCategory->getKey(),
    ]);
});
