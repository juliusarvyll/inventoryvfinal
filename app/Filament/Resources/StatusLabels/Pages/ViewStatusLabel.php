<?php

namespace App\Filament\Resources\StatusLabels\Pages;

use App\Filament\Resources\StatusLabels\StatusLabelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStatusLabel extends ViewRecord
{
    protected static string $resource = StatusLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
