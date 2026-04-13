<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('status_label_id')
                    ->relationship('statusLabel', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name'),
                Select::make('location_id')
                    ->relationship('location', 'name'),
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
