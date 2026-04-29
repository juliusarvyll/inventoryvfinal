<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Overview')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('type')
                            ->badge(),
                    ])
                    ->columns(2),
                Section::make('Inventory in this category')
                    ->schema([
                        TextEntry::make('assets_count')
                            ->label('Assets')
                            ->numeric(),
                        TextEntry::make('asset_models_count')
                            ->label('Asset models')
                            ->numeric(),
                        TextEntry::make('licenses_count')
                            ->label('Licenses')
                            ->numeric(),
                    ])
                    ->columns(3),
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
