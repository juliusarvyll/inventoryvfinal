<?php

namespace App\Filament\Resources\AssetModels\Pages;

use App\Filament\Resources\AssetModels\AssetModelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetModel extends ViewRecord
{
    protected static string $resource = AssetModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
