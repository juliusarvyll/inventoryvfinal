<?php

use App\Actions\Inventory\BulkCreateAssetsForModel;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\StatusLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('bulk create assets for model creates one asset per pasted serial number', function () {
    $category = Category::factory()->asset()->create();
    $assetModel = AssetModel::factory()->create([
        'name' => 'Latitude 7450',
        'category_id' => $category->getKey(),
    ]);

    $createdCount = app(BulkCreateAssetsForModel::class)(
        $assetModel,
        'SER-1001, SER-1002, SER-1003',
    );

    expect($createdCount)->toBe(3)
        ->and(Asset::query()->where('asset_model_id', $assetModel->getKey())->count())->toBe(3);

    $availableStatus = StatusLabel::query()->where('name', 'Available')->firstOrFail();

    $this->assertDatabaseHas('assets', [
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $category->getKey(),
        'status_label_id' => $availableStatus->getKey(),
        'serial' => 'SER-1001',
        'name' => 'Latitude 7450',
        'requestable' => 0,
    ]);
});

test('bulk create assets for model ignores duplicate serial numbers for the same model', function () {
    $assetModel = AssetModel::factory()->create();
    $available = StatusLabel::factory()->available()->create();

    Asset::factory()->create([
        'asset_model_id' => $assetModel->getKey(),
        'category_id' => $assetModel->category_id,
        'status_label_id' => $available->getKey(),
        'serial' => 'SER-2001',
    ]);

    $createdCount = app(BulkCreateAssetsForModel::class)(
        $assetModel,
        'SER-2001, SER-2002, SER-2002',
    );

    expect($createdCount)->toBe(1)
        ->and(Asset::query()->where('asset_model_id', $assetModel->getKey())->count())->toBe(2);
});

test('bulk create assets for model rejects serial numbers already assigned to another model', function () {
    $available = StatusLabel::factory()->available()->create();
    $assetModel = AssetModel::factory()->create();
    $otherAssetModel = AssetModel::factory()->create();

    Asset::factory()->create([
        'asset_model_id' => $otherAssetModel->getKey(),
        'category_id' => $otherAssetModel->category_id,
        'status_label_id' => $available->getKey(),
        'serial' => 'SER-3001',
    ]);

    expect(fn () => app(BulkCreateAssetsForModel::class)(
        $assetModel,
        'SER-3001, SER-3002',
    ))->toThrow(ValidationException::class, 'These serial numbers already belong to another asset model');
});
