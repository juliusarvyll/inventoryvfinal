<?php

namespace App\Filament\Resources\AssetModels\Pages;

use App\Actions\Inventory\BulkCreateAssetsForModel;
use App\Filament\Resources\AssetModels\AssetModelResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateAssetModel extends CreateRecord
{
    protected static string $resource = AssetModelResource::class;

    protected ?string $serialNumbers = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->serialNumbers = Arr::pull($data, 'serial_numbers');

        return $data;
    }

    protected function afterCreate(): void
    {
        app(BulkCreateAssetsForModel::class)($this->record, $this->serialNumbers);
    }
}
