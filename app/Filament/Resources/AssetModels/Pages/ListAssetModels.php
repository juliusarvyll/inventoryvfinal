<?php

namespace App\Filament\Resources\AssetModels\Pages;

use App\Filament\Imports\AssetModelImporter;
use App\Filament\Resources\AssetModels\AssetModelResource;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListAssetModels extends ListRecords
{
    protected static string $resource = AssetModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(AssetModelImporter::class)
                ->fileRules([
                    File::types(['csv', 'txt'])->max(10 * 1024),
                ]),
            CreateAction::make(),
        ];
    }
}
