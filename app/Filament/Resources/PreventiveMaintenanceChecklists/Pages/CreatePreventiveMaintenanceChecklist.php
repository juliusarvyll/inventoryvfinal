<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Pages;

use App\Filament\Resources\PreventiveMaintenanceChecklists\PreventiveMaintenanceChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePreventiveMaintenanceChecklist extends CreateRecord
{
    protected static string $resource = PreventiveMaintenanceChecklistResource::class;
}
