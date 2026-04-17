<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreventiveMaintenanceItem extends Model
{
    /** @use HasFactory<PreventiveMaintenanceItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_id',
        'task',
        'input_label',
        'instructions',
        'sort_order',
        'is_required',
        'is_completed',
        'support_notes',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if ($item->sort_order > 0 || ! $item->preventive_maintenance_id) {
                return;
            }

            $item->sort_order = self::query()
                ->where('preventive_maintenance_id', $item->preventive_maintenance_id)
                ->max('sort_order') + 1;
        });

        static::saving(function (self $item): void {
            if (! $item->isDirty('is_completed')) {
                return;
            }

            if ($item->is_completed) {
                $item->completed_at ??= now();
                $item->completed_by ??= auth()->id();

                return;
            }

            $item->completed_at = null;
            $item->completed_by = null;
        });
    }

    public function preventiveMaintenance(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenance::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
