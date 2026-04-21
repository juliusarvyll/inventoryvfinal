<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Pages;

use App\Filament\Resources\PreventiveMaintenanceChecklists\PreventiveMaintenanceChecklistResource;
use Filament\Resources\Pages\ListRecords;

class ListPreventiveMaintenanceChecklists extends ListRecords
{
    protected static string $resource = PreventiveMaintenanceChecklistResource::class;
}
