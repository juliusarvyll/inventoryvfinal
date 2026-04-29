<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Pages;

use App\Filament\Resources\PreventiveMaintenanceChecklists\PreventiveMaintenanceChecklistResource;
use Filament\Resources\Pages\EditRecord;

class EditPreventiveMaintenanceChecklist extends EditRecord
{
    protected static string $resource = PreventiveMaintenanceChecklistResource::class;

    protected function afterSave(): void
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
