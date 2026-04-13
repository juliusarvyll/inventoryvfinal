<?php

namespace App\Filament\Resources\Consumables\Pages;

use App\Filament\Imports\ConsumableImporter;
use App\Filament\Resources\Consumables\ConsumableResource;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

class ListConsumables extends ListRecords
{
    protected static string $resource = ConsumableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ConsumableImporter::class)
                ->fileRules([
                    File::types(['csv', 'txt'])->max(10 * 1024),
                ]),
            CreateAction::make(),
        ];
    }
}
