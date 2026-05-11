<?php

use App\Filament\Imports\AssetImporter;
use App\Models\Asset;
use App\Models\Category;
use App\Models\StatusLabel;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('asset importer does not persist import-only supplier and location columns', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Category::query()->firstOrCreate(['name' => 'Computers'], [
        'type' => 'asset',
    ]);
    StatusLabel::query()->firstOrCreate(['name' => 'Available'], [
        'color' => '#22c55e',
        'type' => 'deployable',
    ]);

    $import = Import::query()->create([
        'file_name' => 'assets.csv',
        'file_path' => 'imports/assets.csv',
        'importer' => AssetImporter::class,
        'total_rows' => 1,
        'user_id' => $user->getKey(),
    ]);

    $columnMap = [
        'asset_tag' => 'asset_tag',
        'name' => 'name',
        'assetModel' => 'assetModel',
        'import_category' => 'import_category',
        'import_status_label' => 'import_status_label',
        'import_supplier' => 'import_supplier',
        'import_location' => 'import_location',
        'serial' => 'serial',
        'notes' => 'notes',
        'requestable' => 'requestable',
        'description_specification' => 'description_specification',
        'remarks' => 'remarks',
    ];

    $importer = $import->getImporter($columnMap, []);

    $importer([
        'asset_tag' => '',
        'name' => 'KIRN-PC01',
        'assetModel' => 'Lenovo Desktop',
        'import_category' => 'Computers',
        'import_status_label' => 'Available',
        'import_supplier' => 'LENOVO',
        'import_location' => 'KIRN-2nd Floor',
        'serial' => 'S1H007F3',
        'notes' => null,
        'requestable' => '0',
        'description_specification' => 'Intel i3-4150 @3.5GHz | 4GB RAM',
        'remarks' => '',
    ]);

    $asset = Asset::query()->firstOrFail();

    expect($asset)
        ->asset_tag->toBe('IMP-S1H007F3')
        ->supplier->name->toBe('LENOVO')
        ->location->name->toBe('KIRN-2nd Floor')
        ->getAttributes()->not->toHaveKeys(['import_supplier', 'import_location']);
});
