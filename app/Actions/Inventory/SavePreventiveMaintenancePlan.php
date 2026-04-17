<?php

namespace App\Actions\Inventory;

use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use App\Models\Asset;
use App\Models\PreventiveMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SavePreventiveMaintenancePlan
{
    /**
     * @param  array{
     *     location_id?: int|string|null,
     *     category_ids?: array<int, int|string>|null,
     *     scheduled_for?: string|null,
     *     items?: array<int, array{id?: int|string|null, task?: string|null, is_required?: bool|null}>|null
     * }  $data
     */
    public function __invoke(?PreventiveMaintenance $preventiveMaintenance, array $data, ?User $actor = null): PreventiveMaintenance
    {
        $locationId = (int) ($data['location_id'] ?? $preventiveMaintenance?->location_id ?? 0);

        if ($locationId < 1) {
            throw new InvalidArgumentException('A location is required for preventive maintenance.');
        }

        return DB::transaction(function () use ($preventiveMaintenance, $data, $actor, $locationId): PreventiveMaintenance {
            $isExistingTemplate = $preventiveMaintenance?->exists ?? false;

            $preventiveMaintenance ??= new PreventiveMaintenance;

            $preventiveMaintenance->fill([
                'location_id' => $locationId,
                'scheduled_for' => $data['scheduled_for'] ?? $preventiveMaintenance->scheduled_for,
                'version' => $isExistingTemplate
                    ? max(1, (int) $preventiveMaintenance->version) + 1
                    : 1,
                'updated_by' => $actor?->getKey() ?? auth()->id(),
            ]);

            if (! $preventiveMaintenance->exists) {
                $preventiveMaintenance->created_by = $actor?->getKey() ?? auth()->id();
            }

            $preventiveMaintenance->save();

            $selectedCategoryIds = PreventiveMaintenanceForm::sanitizeSelectedCategoryIds(
                $locationId,
                $data['category_ids'] ?? $preventiveMaintenance->categories()->pluck('categories.id')->all(),
            );

            $preventiveMaintenance->categories()->sync($selectedCategoryIds);

            $preventiveMaintenance->assets()->sync(
                Asset::query()
                    ->where('location_id', $locationId)
                    ->whereIn('category_id', $selectedCategoryIds === [] ? [0] : $selectedCategoryIds)
                    ->pluck('id')
                    ->all(),
            );

            if (array_key_exists('items', $data)) {
                $submittedItems = collect(PreventiveMaintenanceForm::normalizeChecklistItems($data['items']));
                $existingItems = $preventiveMaintenance->items()->get()->keyBy('id');
                $keptIds = [];

                foreach ($submittedItems as $itemData) {
                    $itemId = isset($itemData['id']) ? (int) $itemData['id'] : null;
                    $payload = [
                        'task' => $itemData['task'],
                        'input_label' => $itemData['input_label'] ?? null,
                        'sort_order' => $itemData['sort_order'],
                        'is_required' => $itemData['is_required'],
                    ];

                    if ($itemId !== null && $itemId > 0 && $existingItems->has($itemId)) {
                        $existingItems[$itemId]->update($payload);
                        $keptIds[] = $itemId;

                        continue;
                    }

                    $newItem = $preventiveMaintenance->items()->create($payload + [
                        'is_completed' => false,
                    ]);

                    $keptIds[] = $newItem->getKey();
                }

                $preventiveMaintenance->items()
                    ->when(
                        $keptIds === [],
                        fn ($query) => $query,
                        fn ($query) => $query->whereNotIn('id', $keptIds),
                    )
                    ->delete();
            }

            return $preventiveMaintenance->fresh(['assets', 'categories', 'items.completedBy']);
        });
    }
}
