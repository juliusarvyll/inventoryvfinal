<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PreventiveMaintenanceExecutionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Preventive Maintenance Execution Model.
 *
 * Records a completed or in-progress preventive maintenance session
 * for a specific asset, including pass/fail results for each checklist item.
 *
 * @property int $id
 * @property int $department_id
 * @property int $preventive_maintenance_schedule_id
 * @property int $preventive_maintenance_checklist_id
 * @property int $location_id
 * @property int $category_id
 * @property int $asset_id
 * @property string $status
 * @property Carbon|null $scheduled_for
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property int|null $performed_by
 * @property string|null $general_notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Department $department
 * @property-read PreventiveMaintenanceSchedule $schedule
 * @property-read PreventiveMaintenanceChecklist $checklist
 * @property-read Location $location
 * @property-read Category $category
 * @property-read Asset $asset
 * @property-read User|null $performer
 * @property-read Collection<int, PreventiveMaintenanceExecutionItem> $items
 */
class PreventiveMaintenanceExecution extends Model
{
    /** @use HasFactory<PreventiveMaintenanceExecutionFactory> */
    use Concerns\BelongsToDepartment, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
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
