<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Pages;

use App\Filament\Resources\PreventiveMaintenanceChecklists\PreventiveMaintenanceChecklistResource;
use Filament\Resources\Pages\EditRecord;

class EditPreventiveMaintenanceChecklist extends EditRecord
{
    protected static string $resource = PreventiveMaintenanceChecklistResource::class;
}
