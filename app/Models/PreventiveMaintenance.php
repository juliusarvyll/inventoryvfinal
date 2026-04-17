<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreventiveMaintenance extends Model
{
    /** @use HasFactory<PreventiveMaintenanceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'location_id',
        'instructions',
        'execution_notes',
        'scheduled_for',
        'version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'version' => 'integer',
        ];
    }

    public function scopeForAsset(Builder $query, Asset $asset): Builder
    {
        return $query
            ->where('location_id', $asset->location_id)
            ->whereHas('categories', fn (Builder $categoryQuery): Builder => $categoryQuery->whereKey($asset->category_id));
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_preventive_maintenance')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_preventive_maintenance')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceSession::class)
            ->latest('started_at')
            ->latest('id');
    }
}
