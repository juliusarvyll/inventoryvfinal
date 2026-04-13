<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Imports\AssetImporter;
use App\Filament\Resources\Assets\AssetResource;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(AssetImporter::class)
                ->fileRules([
                    File::types(['csv', 'txt'])->max(10 * 1024),
                ]),
            CreateAction::make(),
        ];
    }
}
