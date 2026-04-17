<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceSessionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreventiveMaintenanceSessionItem extends Model
{
    /** @use HasFactory<PreventiveMaintenanceSessionItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_session_id',
        'preventive_maintenance_item_id',
        'task',
        'input_label',
        'input_value',
        'is_required',
        'is_passed',
        'item_notes',
        'evidence_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_passed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceSession::class, 'preventive_maintenance_session_id');
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceItem::class, 'preventive_maintenance_item_id');
    }
}
