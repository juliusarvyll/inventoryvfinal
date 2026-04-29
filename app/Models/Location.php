<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Accessory));
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Consumable));
    }

    public function components(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Component));
    }

    public function preventiveMaintenances(): HasMany
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    public function preventiveMaintenanceSchedules(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class);
    }

    /**
     * @return list<int>
     */
    public function selfAndDescendantIds(): array
    {
        $descendantIds = [];
        $stack = [$this->getKey()];

        while ($stack !== []) {
            $currentId = array_pop($stack);

            if ($currentId === null || in_array($currentId, $descendantIds, true)) {
                continue;
            }

            $descendantIds[] = (int) $currentId;

            Location::query()
                ->where('parent_id', $currentId)
                ->pluck('id')
                ->each(function ($childId) use (&$stack): void {
                    $stack[] = (int) $childId;
                });
        }

        return $descendantIds;
    }

    /**
     * @return list<int>
     */
    public function selfAndAncestorIds(): array
    {
        $ancestorIds = [];
        $currentLocation = $this;

        while ($currentLocation) {
            $currentId = $currentLocation->getKey();

            if ($currentId === null || in_array((int) $currentId, $ancestorIds, true)) {
                break;
            }

            $ancestorIds[] = (int) $currentId;
            $currentLocation = $currentLocation->parent()->first();
        }

        return $ancestorIds;
    }
}
