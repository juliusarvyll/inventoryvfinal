<?php

namespace App\Filament\Resources\Components\Pages;

use App\Filament\Imports\ComponentImporter;
use App\Filament\Resources\Components\ComponentResource;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListComponents extends ListRecords
{
    protected static string $resource = ComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ComponentImporter::class)
                ->fileRules([
                    File::types(['csv', 'txt'])->max(10 * 1024),
                ]),
            CreateAction::make(),
        ];
    }
}
