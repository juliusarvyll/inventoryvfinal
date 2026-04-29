<?php

namespace App\Actions\Inventory;

use App\Models\Asset;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StartPreventiveMaintenanceSession
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
        PreventiveMaintenance $preventiveMaintenance,
        Asset $asset,
        array $items,
        ?User $actor = null,
        ?string $generalNotes = null,
    ): PreventiveMaintenanceSession {
        $preventiveMaintenance->loadMissing('categories', 'items');
        $scopeLocationIds = $preventiveMaintenance->location?->selfAndDescendantIds() ?? [$preventiveMaintenance->location_id];

        if (! in_array((int) $asset->location_id, $scopeLocationIds, true)) {
            throw new RuntimeException('The selected preventive maintenance template does not match the asset location.');
        }

        if (! $preventiveMaintenance->categories->contains($asset->category_id)) {
            throw new RuntimeException('The selected preventive maintenance template does not match the asset category.');
        }

        $templateItems = $preventiveMaintenance->items->keyBy('id');

        return DB::transaction(function () use ($preventiveMaintenance, $asset, $items, $actor, $generalNotes, $templateItems): PreventiveMaintenanceSession {
            $session = PreventiveMaintenanceSession::query()->create([
                'preventive_maintenance_id' => $preventiveMaintenance->getKey(),
                'asset_id' => $asset->getKey(),
                'template_version' => $preventiveMaintenance->version,
                'status' => 'pending',
                'started_at' => now(),
                'performed_by' => $actor?->getKey() ?? auth()->id(),
                'general_notes' => $generalNotes,
            ]);

            foreach ($items as $itemData) {
                $itemId = (int) ($itemData['id'] ?? 0);
                $templateItem = $templateItems->get($itemId);

                if ($templateItem === null) {
                    throw new RuntimeException('One or more checklist items are invalid for this preventive maintenance template.');
                }

                $session->items()->create([
                    'preventive_maintenance_item_id' => $templateItem->getKey(),
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

            $session->forceFill([
                'status' => $this->resolveStatus($session),
                'completed_at' => $this->shouldMarkCompleted($session) ? now() : null,
            ])->save();

            return $session->fresh(['asset', 'performer', 'preventiveMaintenance.categories', 'items']);
        });
    }

    protected function resolveStatus(PreventiveMaintenanceSession $session): string
    {
        $session->loadMissing('items');

        if ($session->items->contains(fn ($item): bool => $item->is_passed === false)) {
            return 'needs_attention';
        }

        if ($session->items->contains(fn ($item): bool => $item->is_passed === null)) {
            return 'pending';
        }

        return 'completed';
    }

    protected function shouldMarkCompleted(PreventiveMaintenanceSession $session): bool
    {
        return $this->resolveStatus($session) !== 'pending';
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
