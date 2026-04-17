<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreventiveMaintenanceSession extends Model
{
    /** @use HasFactory<PreventiveMaintenanceSessionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_id',
        'asset_id',
        'template_version',
        'status',
        'started_at',
        'completed_at',
        'performed_by',
        'general_notes',
    ];

    protected function casts(): array
    {
        return [
            'template_version' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeForAsset(Builder $query, Asset $asset): Builder
    {
        return $query->whereBelongsTo($asset);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function preventiveMaintenance(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenance::class);
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
        return $this->hasMany(PreventiveMaintenanceSessionItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
