<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('country'),
                Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                // Add a repeater for asset assignment during location creation/editing
                Repeater::make('assets')
                    ->label('Assign Assets')
                    ->relationship()
                    ->schema([
                        Select::make('asset_id')
                            ->relationship('assets', 'asset_tag')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->collapsible()
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => ! $get('id')) // Only show when creating new location
                    ->disabled(fn (Get $get) => ! $get('name')), // Disable until location has a name
            ]);
    }
}
