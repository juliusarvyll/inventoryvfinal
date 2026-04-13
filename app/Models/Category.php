<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryCategoryType::class,
        ];
    }

    public function scopeOfType(Builder $query, InventoryCategoryType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function assetModels(): HasMany
    {
        return $this->hasMany(AssetModel::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class);
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(Consumable::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }
}
