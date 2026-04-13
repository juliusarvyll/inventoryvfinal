<?php

namespace App\Filament\Resources\Components\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name'),
                Select::make('location_id')
                    ->relationship('location', 'name'),
                TextInput::make('qty')
                    ->required()
                    ->numeric(),
                TextInput::make('min_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('serial'),
                TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                DatePicker::make('purchase_date'),
                TextInput::make('order_number'),
                Toggle::make('requestable')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
