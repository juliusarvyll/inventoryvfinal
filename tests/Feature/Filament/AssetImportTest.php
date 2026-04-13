<?php

use App\Filament\Imports\AssetImporter;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

uses(RefreshDatabase::class);

test('admin users can render the assets index page with import support', function () {
    $response = $this
        ->actingAs(User::factory()->itStaff()->create())
        ->get(route('filament.admin.resources.assets.index', absolute: false));

    $response->assertOk();
});

function makeAssetImporter(): AssetImporter
{
    return new AssetImporter(
        import: new Import(),
        columnMap: [
            'asset_tag' => 'asset_tag',
            'name' => 'name',
            'assetModel' => 'asset_model',
            'category' => 'category',
            'statusLabel' => 'status_label',
            'supplier' => 'supplier',
            'location' => 'location',
            'serial' => 'serial',
            'purchase_cost' => 'purchase_cost',
            'purchase_date' => 'purchase_date',
            'warranty_expires' => 'warranty_expires',
            'eol_date' => 'eol_date',
            'notes' => 'notes',
            'requestable' => 'requestable',
        ],
        options: [],
    );
}

function makeLegacyAssetImporter(): AssetImporter
{
    return new AssetImporter(
        import: new Import(),
        columnMap: [
            'name' => 'Name of Equipment',
            'assetModel' => 'Description/Specification',
            'location' => 'Location/Room',
            'serial' => 'Serial No.',
            'purchase_date' => 'Date Delivered',
            'remarks' => 'Remarks',
            'qty' => 'Qty',
            'unit' => 'Unit',
        ],
        options: [],
    );
}

test('asset importer parses purchase and related dates in common non-iso formats', function () {
    $category = Category::factory()->asset()->create(['name' => 'Laptops']);
    $assetModel = AssetModel::factory()->create([
        'name' => 'Latitude 7440',
        'model_number' => 'LAT-7440',
        'category_id' => $category->getKey(),
    ]);
    $statusLabel = StatusLabel::factory()->available()->create(['name' => 'Available']);

    $excelSerial = (int) ExcelDate::PHPToExcel(new \DateTimeImmutable('2027-06-30'));

    $importer = makeAssetImporter();

    $importer([
        'asset_tag' => 'ICT-DATEFMT-1',
        'name' => 'Date format laptop',
        'asset_model' => 'LAT-7440',
        'category' => 'Laptops',
        'status_label' => 'Available',
        'supplier' => null,
        'location' => null,
        'serial' => 'SER-DATE-1',
        'purchase_cost' => null,
        'purchase_date' => '15/04/2026',
        'warranty_expires' => 'April 15, 2028',
        'eol_date' => (string) $excelSerial,
        'notes' => null,
        'requestable' => 'no',
    ]);

    $asset = Asset::query()->where('asset_tag', 'ICT-DATEFMT-1')->first();

    expect($asset)->not->toBeNull()
        ->and($asset->purchase_date?->toDateString())->toBe('2026-04-15')
        ->and($asset->warranty_expires?->toDateString())->toBe('2028-04-15')
        ->and($asset->eol_date?->toDateString())->toBe('2027-06-30');
});

test('asset importer rejects values that are not parseable as dates', function () {
    $category = Category::factory()->asset()->create(['name' => 'Laptops']);
    AssetModel::factory()->create([
        'name' => 'Latitude 7440',
        'model_number' => 'LAT-7440',
        'category_id' => $category->getKey(),
    ]);
    StatusLabel::factory()->available()->create(['name' => 'Available']);

    $importer = makeAssetImporter();

    expect(fn () => $importer([
        'asset_tag' => 'ICT-BADDATE',
        'name' => 'Bad date asset',
        'asset_model' => 'LAT-7440',
        'category' => 'Laptops',
        'status_label' => 'Available',
        'supplier' => null,
        'location' => null,
        'serial' => null,
        'purchase_cost' => null,
        'purchase_date' => 'not-a-real-date',
        'warranty_expires' => null,
        'eol_date' => null,
        'notes' => null,
        'requestable' => 'no',
    ]))->toThrow(ValidationException::class);
});

test('asset importer creates an asset from csv-friendly relationship values', function () {
    $category = Category::factory()->asset()->create(['name' => 'Laptops']);
    $assetModel = AssetModel::factory()->create([
        'name' => 'Latitude 7440',
        'model_number' => 'LAT-7440',
        'category_id' => $category->getKey(),
    ]);
    $statusLabel = StatusLabel::factory()->available()->create(['name' => 'Available']);
    $supplier = Supplier::factory()->create(['name' => 'Acme Supplies']);
    $location = Location::factory()->create(['name' => 'HQ Stockroom']);

    $importer = makeAssetImporter();

    $importer([
        'asset_tag' => 'ICT-90001',
        'name' => 'Dell Latitude 7440',
        'asset_model' => 'LAT-7440',
        'category' => 'Laptops',
        'status_label' => 'Available',
        'supplier' => 'Acme Supplies',
        'location' => 'HQ Stockroom',
        'serial' => 'SER-90001',
        'purchase_cost' => '1899.50',
        'purchase_date' => '2026-04-01',
        'warranty_expires' => '2028-04-01',
        'eol_date' => '2030-04-01',
        'notes' => 'Imported from CSV',
        'requestable' => 'yes',
    ]);

    $this->assertDatabaseHas('assets', [
        'asset_tag' => 'ICT-90001',
        'name' => 'Dell Latitude 7440',
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $statusLabel->getKey(),
        'supplier_id' => $supplier->getKey(),
        'location_id' => $location->getKey(),
        'serial' => 'SER-90001',
        'requestable' => 1,
    ]);
});

test('asset importer updates an existing asset when asset tag matches', function () {
    $category = Category::factory()->asset()->create(['name' => 'Monitors']);
    $assetModel = AssetModel::factory()->create([
        'name' => 'UltraSharp',
        'model_number' => 'U2723',
        'category_id' => $category->getKey(),
    ]);
    $statusLabel = StatusLabel::factory()->available()->create(['name' => 'Ready']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'ICT-22222',
        'name' => 'Old Name',
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $statusLabel->getKey(),
        'requestable' => false,
    ]);

    $importer = makeAssetImporter();

    $importer([
        'asset_tag' => 'ICT-22222',
        'name' => 'Updated Monitor',
        'asset_model' => 'U2723',
        'category' => 'Monitors',
        'status_label' => 'Ready',
        'supplier' => null,
        'location' => null,
        'serial' => null,
        'purchase_cost' => null,
        'purchase_date' => null,
        'warranty_expires' => null,
        'eol_date' => null,
        'notes' => null,
        'requestable' => 'true',
    ]);

    expect($asset->refresh()->name)->toBe('Updated Monitor')
        ->and($asset->requestable)->toBeTrue();
});

test('asset importer supports legacy csv rows and fills missing required data with warnings', function () {
    $importer = makeLegacyAssetImporter();

    $importer([
        'Qty' => '2',
        'Unit' => 'unit',
        'Name of Equipment' => 'Monitor',
        'Description/Specification' => 'View Sonic',
        'Location/Room' => 'ICT Department',
        'Date Delivered' => '',
        'Serial No.' => 'T6Q131341113',
        'Remarks' => 'Functional',
    ]);

    $asset = Asset::query()->where('serial', 'T6Q131341113')->first();

    expect($asset)->not->toBeNull()
        ->and($asset->name)->toBe('Monitor')
        ->and($asset->asset_tag)->toStartWith('IMP-')
        ->and($asset->category?->name)->toBe('Monitor')
        ->and($asset->statusLabel?->name)->toBe('Available')
        ->and($asset->location?->name)->toBe('ICT Department')
        ->and($asset->notes)->toContain('Imported remarks: Functional')
        ->and($asset->notes)->toContain('Import warnings:');
});

test('asset importer uses the selected default category when the csv row has no category', function () {
    $defaultCategory = Category::factory()->asset()->create(['name' => 'Shared Devices']);

    $importer = new AssetImporter(
        import: new Import(),
        columnMap: [
            'name' => 'name',
            'assetModel' => 'asset_model',
            'statusLabel' => 'status_label',
            'requestable' => 'requestable',
        ],
        options: [
            'default_category_id' => $defaultCategory->getKey(),
        ],
    );

    $importer([
        'name' => 'Conference Tablet',
        'asset_model' => 'Tab X',
        'status_label' => 'Available',
        'requestable' => 'false',
    ]);

    $asset = Asset::query()->where('name', 'Conference Tablet')->first();

    expect($asset)->not->toBeNull()
        ->and($asset->category?->name)->toBe('Shared Devices')
        ->and($asset->notes)->toContain('category defaulted from the import option');
});

test('asset importer completion message warns when rows were skipped', function () {
    $import = new Import([
        'successful_rows' => 3,
        'total_rows' => 5,
    ]);

    $body = AssetImporter::getCompletedNotificationBody($import);

    expect($body)->toContain('Import completed with warnings')
        ->and($body)->toContain('2')
        ->and($body)->toContain('skipped');
});
