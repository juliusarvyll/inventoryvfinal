<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PreventiveMaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Preventive Maintenance Schedule Model.
 *
 * Defines when and where preventive maintenance should be performed.
 * Links locations with checklists and tracks execution history.
 *
 * @property int $id
 * @property int $department_id
 * @property int $location_id
 * @property Carbon|null $scheduled_for
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Department $department
 * @property-read Location $location
 * @property-read Collection<int, PreventiveMaintenanceChecklist> $checklists
 * @property-read Collection<int, PreventiveMaintenanceExecution> $executions
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
class PreventiveMaintenanceSchedule extends Model
{
    /** @use HasFactory<PreventiveMaintenanceScheduleFactory> */
    use Concerns\BelongsToDepartment, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'location_id',
        'scheduled_for',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(PreventiveMaintenanceChecklist::class, 'preventive_maintenance_schedule_checklist')
            ->with('categories')
            ->withTimestamps();
    }

    public function executions(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceExecution::class, 'preventive_maintenance_schedule_id')
            ->latest('started_at')
            ->latest('id');
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
