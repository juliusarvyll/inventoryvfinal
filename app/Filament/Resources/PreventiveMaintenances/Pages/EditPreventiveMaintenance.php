<?php

namespace App\Filament\Resources\PreventiveMaintenances\Pages;

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Filament\Resources\PreventiveMaintenances\PreventiveMaintenanceResource;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPreventiveMaintenance extends EditRecord
{
    protected static string $resource = PreventiveMaintenanceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data + PreventiveMaintenanceForm::editFormData($this->getRecord());
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SavePreventiveMaintenancePlan::class)(
            $record,
            $data,
            auth()->user(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
