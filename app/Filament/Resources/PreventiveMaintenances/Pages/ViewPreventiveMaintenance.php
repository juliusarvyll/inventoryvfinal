<?php

namespace App\Filament\Resources\PreventiveMaintenances\Pages;

use App\Filament\Resources\PreventiveMaintenances\PreventiveMaintenanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPreventiveMaintenance extends ViewRecord
{
    protected static string $resource = PreventiveMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
