<?php

namespace App\Actions\Inventory;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use RuntimeException;

class StartPreventiveMaintenanceExecution
{
    /**
     * @param  list<array{
     *     id: int|string,
     *     is_passed?: bool|null,
     *     input_value?: string|null,
     *     item_notes?: string|null,
     *     evidence_path?: string|null
     * }>  $items
     */
    public function __invoke(
        PreventiveMaintenanceSchedule $schedule,
        PreventiveMaintenanceChecklist $checklist,
        Asset $asset,
        array $items,
        ?User $actor = null,
        ?string $generalNotes = null,
    ): PreventiveMaintenanceExecution {
        $schedule->loadMissing('checklists.items', 'location');
        $checklist->loadMissing('items', 'category');

        if ($schedule->location_id !== $asset->location_id) {
            throw new RuntimeException('The selected preventive maintenance schedule does not match the asset location.');
        }

        if ($checklist->category_id !== $asset->category_id) {
            throw new RuntimeException('The selected preventive maintenance checklist does not match the asset category.');
        }

        if (! $schedule->checklists->contains($checklist)) {
            throw new RuntimeException('The selected checklist is not associated with this schedule.');
        }

        if (! $checklist->is_active) {
            throw new RuntimeException('The selected preventive maintenance checklist is inactive.');
        }

        $templateItems = $checklist->items->keyBy('id');

        return DB::transaction(function () use ($schedule, $asset, $items, $actor, $generalNotes, $templateItems, $checklist): PreventiveMaintenanceExecution {
            $execution = PreventiveMaintenanceExecution::query()->create([
                'preventive_maintenance_schedule_id' => $schedule->getKey(),
                'preventive_maintenance_checklist_id' => $checklist->getKey(),
                'location_id' => $schedule->location_id,
                'category_id' => $checklist->category_id,
                'asset_id' => $asset->getKey(),
                'status' => 'pending',
                'scheduled_for' => $schedule->scheduled_for,
                'started_at' => now(),
                'performed_by' => $actor?->getKey() ?? auth()->id(),
                'general_notes' => $generalNotes,
            ]);

            foreach ($items as $itemData) {
                $itemId = (int) ($itemData['id'] ?? 0);
                $templateItem = $templateItems->get($itemId);

                if ($templateItem === null) {
                    throw new RuntimeException('One or more checklist items are invalid for this preventive maintenance checklist.');
                }

                $execution->items()->create([
                    'preventive_maintenance_checklist_item_id' => $templateItem->getKey(),
                    'task' => $templateItem->task,
                    'input_label' => $templateItem->input_label,
                    'input_value' => $itemData['input_value'] ?? null,
                    'is_required' => $templateItem->is_required,
                    'is_passed' => $this->normalizePassedResult($itemData),
                    'item_notes' => $itemData['item_notes'] ?? null,
                    'evidence_path' => $itemData['evidence_path'] ?? null,
                    'sort_order' => $templateItem->sort_order,
                ]);
            }

            $execution->forceFill([
                'status' => $this->resolveStatus($execution),
                'completed_at' => $this->shouldMarkCompleted($execution) ? now() : null,
            ])->save();

            AuditLog::query()->create([
                'user_id' => $actor?->getKey() ?? auth()->id(),
                'action_type' => 'pm_execution_started',
                'subject_type' => PreventiveMaintenanceExecution::class,
                'subject_id' => $execution->getKey(),
                'new_values' => [
                    'asset_name' => $asset->name,
                    'location_name' => $schedule->location->name,
                    'category_name' => $checklist->category->name,
                    'status' => $execution->status,
                    'items_count' => $execution->items->count(),
                    'passed_items' => $execution->items->where('is_passed', true)->count(),
                    'failed_items' => $execution->items->where('is_passed', false)->count(),
                ],
                'description' => "Started PM execution for asset '{$asset->name}' using checklist '{$checklist->category->name}'",
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            return $execution->fresh(['asset', 'performer', 'schedule.location', 'schedule.checklists.category', 'checklist.category', 'items']);
        });
    }

    protected function resolveStatus(PreventiveMaintenanceExecution $execution): string
    {
        $execution->loadMissing('items');

        if ($execution->items->contains(fn ($item): bool => $item->is_passed === false)) {
            return 'needs_attention';
        }

        if ($execution->items->contains(fn ($item): bool => $item->is_passed === null)) {
            return 'pending';
        }

        return 'completed';
    }

    protected function shouldMarkCompleted(PreventiveMaintenanceExecution $execution): bool
    {
        return $this->resolveStatus($execution) !== 'pending';
    }

    /**
     * @param  array{is_passed?: bool|int|string|null}  $itemData
     */
    protected function normalizePassedResult(array $itemData): ?bool
    {
        if (! array_key_exists('is_passed', $itemData)) {
            return null;
        }

        $result = $itemData['is_passed'];

        if ($result === null || $result === '') {
            return null;
        }

        return filter_var($result, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}
