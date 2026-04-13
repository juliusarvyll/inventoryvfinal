<?php

namespace App\Filament\Resources\ItemRequests\Pages;

use App\Filament\Resources\ItemRequests\ItemRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItemRequest extends ViewRecord
{
    protected static string $resource = ItemRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
