<?php

namespace App\Models\Concerns;

use App\Enums\InventoryCategoryType;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\StatusLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait BehavesAsTypedAsset
{
    protected static function bootBehavesAsTypedAsset(): void
    {
        static::addGlobalScope('typed_category', function (Builder $query): void {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('type', static::inventoryCategoryType()->value));
        });

        static::saving(function ($record): void {
            $record->initializeTypedAssetDefaults();
        });
    }

    abstract protected static function inventoryCategoryType(): InventoryCategoryType;

    protected function initializeTypedAssetDefaults(): void
    {
        if (! $this->category_id) {
            return;
        }

        $category = Category::query()->find($this->category_id);

        if (! $category) {
            return;
        }

        if (! $this->asset_tag) {
            $this->asset_tag = static::assetTagPrefix().'-'.str_pad((string) (static::query()->withoutGlobalScopes()->max('id') + 1), 6, '0', STR_PAD_LEFT);
        }

        if (! $this->status_label_id) {
            DB::table('status_labels')->insertOrIgnore([
                'name' => 'Available',
                'color' => '#22c55e',
                'type' => 'deployable',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->status_label_id = (int) StatusLabel::query()
                ->where('name', 'Available')
                ->value('id');
        }

        if (! $this->asset_model_id) {
            $manufacturer = Manufacturer::query()->firstOrCreate([
                'name' => 'Internal Catalog',
            ]);

            $this->asset_model_id = AssetModel::query()
                ->firstOrCreate([
                    'manufacturer_id' => $manufacturer->getKey(),
                    'category_id' => $category->getKey(),
                    'name' => ucfirst(static::inventoryCategoryType()->value).' Catalog Item',
                    'model_number' => null,
                ])
                ->getKey();
        }
    }

    protected static function assetTagPrefix(): string
    {
        return strtoupper(substr(static::inventoryCategoryType()->value, 0, 3));
    }
}
