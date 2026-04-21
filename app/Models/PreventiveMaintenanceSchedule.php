<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreventiveMaintenanceSchedule extends Model
{
    /** @use HasFactory<PreventiveMaintenanceScheduleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'location_id',
        'preventive_maintenance_checklist_id',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getCategoryAttribute(): ?int
    {
        return $this->checklist?->category_id;
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceChecklist::class, 'preventive_maintenance_checklist_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class)
            ->where('location_id', $this->location_id)
            ->where('category_id', $this->category);
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
