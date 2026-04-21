<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceExecutionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreventiveMaintenanceExecution extends Model
{
    /** @use HasFactory<PreventiveMaintenanceExecutionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_schedule_id',
        'preventive_maintenance_checklist_id',
        'location_id',
        'category_id',
        'asset_id',
        'status',
        'scheduled_for',
        'started_at',
        'completed_at',
        'performed_by',
        'general_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeForAsset(Builder $query, Asset $asset): Builder
    {
        return $query->whereBelongsTo($asset);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceSchedule::class, 'preventive_maintenance_schedule_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceChecklist::class, 'preventive_maintenance_checklist_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceExecutionItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
