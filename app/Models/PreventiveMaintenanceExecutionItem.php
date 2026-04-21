<?php

namespace App\Models;

use Database\Factories\PreventiveMaintenanceExecutionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreventiveMaintenanceExecutionItem extends Model
{
    /** @use HasFactory<PreventiveMaintenanceExecutionItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preventive_maintenance_execution_id',
        'preventive_maintenance_checklist_item_id',
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

    public function execution(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceExecution::class, 'preventive_maintenance_execution_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(PreventiveMaintenanceChecklistItem::class, 'preventive_maintenance_checklist_item_id');
    }
}
