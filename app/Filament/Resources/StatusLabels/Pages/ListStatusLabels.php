<?php

namespace App\Filament\Resources\StatusLabels\Pages;

use App\Filament\Resources\StatusLabels\StatusLabelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStatusLabels extends ListRecords
{
    protected static string $resource = StatusLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
