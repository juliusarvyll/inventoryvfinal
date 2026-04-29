<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('asset_tag')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('asset_model_id')
                    ->relationship('assetModel', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('asset_model_id', null) // Reset model when category changes
                    ),
                Select::make('status_label_id')
                    ->relationship('statusLabel', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('location_id')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('state')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->maxLength(255),
                        Select::make('parent_id')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get) => ! $get('name')) // Only enable when name is filled
                            ->columnSpanFull(),
                    ])
                    ->required(),
                TextInput::make('serial'),
                TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                DatePicker::make('purchase_date'),
                DatePicker::make('warranty_expires'),
                DatePicker::make('eol_date'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Toggle::make('requestable')
                    ->required(),
            ]);
    }
}
