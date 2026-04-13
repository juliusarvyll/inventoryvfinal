<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Location;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('parent.name')
                            ->label('Parent')
                            ->placeholder('—')
                            ->url(fn (Location $record): ?string => $record->parent_id
                                ? LocationResource::getUrl('view', ['record' => $record->parent_id])
                                : null),
                    ])
                    ->columns(2),
                Section::make('Address')
                    ->schema([
                        TextEntry::make('address')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('city')
                            ->placeholder('—'),
                        TextEntry::make('state')
                            ->placeholder('—'),
                        TextEntry::make('country')
                            ->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make('Inventory at this location')
                    ->schema([
                        TextEntry::make('assets_count')
                            ->label('Assets')
                            ->numeric(),
                        TextEntry::make('children_count')
                            ->label('Sublocations')
                            ->numeric(),
                        TextEntry::make('accessories_count')
                            ->label('Accessories')
                            ->numeric(),
                        TextEntry::make('consumables_count')
                            ->label('Consumables')
                            ->numeric(),
                        TextEntry::make('components_count')
                            ->label('Components')
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
