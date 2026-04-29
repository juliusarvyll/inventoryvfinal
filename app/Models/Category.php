<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
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
        return $this->assets();
    }

    public function consumables(): HasMany
    {
        return $this->assets();
    }

    public function components(): HasMany
    {
        return $this->assets();
    }

    public function preventiveMaintenances(): BelongsToMany
    {
        return $this->belongsToMany(PreventiveMaintenance::class, 'category_preventive_maintenance')
            ->withTimestamps();
    }

    public function preventiveMaintenanceChecklists(): BelongsToMany
    {
        return $this->belongsToMany(PreventiveMaintenanceChecklist::class, 'category_preventive_maintenance_checklist')
            ->withTimestamps();
    }
}
