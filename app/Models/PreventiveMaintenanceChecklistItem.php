<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreventiveMaintenanceChecklistItem extends Model
{
    /** @use HasFactory<PreventiveMaintenanceChecklistItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_checklist_id',
        'task',
        'input_label',
        'sort_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceChecklist::class, 'preventive_maintenance_checklist_id');
    }
}
