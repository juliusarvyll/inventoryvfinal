<?php

namespace App\Filament\Resources\AssetModels\Pages;

use App\Actions\Inventory\BulkCreateAssetsForModel;
use App\Filament\Resources\AssetModels\AssetModelResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditAssetModel extends EditRecord
{
    protected static string $resource = AssetModelResource::class;

    protected ?string $serialNumbers = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->serialNumbers = Arr::pull($data, 'serial_numbers');

        return $data;
    }

    protected function afterSave(): void
    {
        app(BulkCreateAssetsForModel::class)($this->record, $this->serialNumbers);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
