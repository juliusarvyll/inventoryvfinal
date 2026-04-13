<?php

namespace App\Filament\Resources\StatusLabels\Pages;

use App\Filament\Resources\StatusLabels\StatusLabelResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStatusLabel extends EditRecord
{
    protected static string $resource = StatusLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
