<?php

namespace App\Filament\Resources\PreventiveMaintenances\Pages;

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Filament\Resources\PreventiveMaintenances\PreventiveMaintenanceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePreventiveMaintenance extends CreateRecord
{
    protected static string $resource = PreventiveMaintenanceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SavePreventiveMaintenancePlan::class)(
            null,
            $data,
            auth()->user(),
        );
    }
}
