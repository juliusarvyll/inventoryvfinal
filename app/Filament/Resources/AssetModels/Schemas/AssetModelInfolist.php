<?php

namespace App\Filament\Resources\AssetModels\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Manufacturers\ManufacturerResource;
use App\Models\AssetModel;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Model')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('model_number')
                            ->placeholder('—'),
                        TextEntry::make('assets_count')
                            ->label('Assets using this model')
                            ->numeric(),
                    ])
                    ->columns(2),
                Section::make('Catalog')
                    ->schema([
                        TextEntry::make('manufacturer.name')
                            ->label('Manufacturer')
                            ->url(fn (AssetModel $record): ?string => $record->manufacturer_id
                                ? ManufacturerResource::getUrl('view', ['record' => $record->manufacturer_id])
                                : null),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->url(fn (AssetModel $record): ?string => $record->category_id
                                ? CategoryResource::getUrl('view', ['record' => $record->category_id])
                                : null),
                        ImageEntry::make('image')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Record')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
