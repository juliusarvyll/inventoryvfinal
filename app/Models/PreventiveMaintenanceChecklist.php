<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PreventiveMaintenanceChecklistFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Preventive Maintenance Checklist Model.
 *
 * Represents a reusable checklist template that can be applied to multiple
 * asset categories. Contains checklist items that define maintenance tasks.
 *
 * @property int $id
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, PreventiveMaintenanceChecklistItem> $items
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
class PreventiveMaintenanceChecklist extends Model
{
    /** @use HasFactory<PreventiveMaintenanceChecklistFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_preventive_maintenance_checklist')
            ->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceChecklistItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
