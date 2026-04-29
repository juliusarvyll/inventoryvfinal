<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Pages;

use App\Filament\Resources\PreventiveMaintenanceChecklists\PreventiveMaintenanceChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePreventiveMaintenanceChecklist extends CreateRecord
{
    protected static string $resource = PreventiveMaintenanceChecklistResource::class;

    protected function afterCreate(): void
    {
        $this->syncPrimaryCategory();
    }

    protected function syncPrimaryCategory(): void
    {
        $categoryIds = collect($this->data['category_ids'] ?? [])
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $this->record->forceFill([
            'category_id' => $categoryIds->first(),
        ])->saveQuietly();
    }
}
