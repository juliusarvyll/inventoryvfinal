<?php

namespace App\Filament\Resources\Manufacturers\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManufacturerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Manufacturer')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('asset_models_count')
                            ->label('Asset models')
                            ->numeric(),
                        TextEntry::make('licenses_count')
                            ->label('Licenses')
                            ->numeric(),
                    ])
                    ->columns(3),
                Section::make('Support & links')
                    ->schema([
                        TextEntry::make('url')
                            ->placeholder('—'),
                        TextEntry::make('support_url')
                            ->placeholder('—'),
                        TextEntry::make('support_phone')
                            ->placeholder('—'),
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
