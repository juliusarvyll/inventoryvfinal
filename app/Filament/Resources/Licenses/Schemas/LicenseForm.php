<?php

namespace App\Filament\Resources\Licenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('product_key'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('manufacturer_id')
                    ->relationship('manufacturer', 'name'),
                TextInput::make('license_type')
                    ->required(),
                TextInput::make('seats')
                    ->required()
                    ->numeric()
                    ->default(1),
                DatePicker::make('expiration_date'),
                DatePicker::make('purchase_date'),
                TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('order_number'),
                Toggle::make('maintained')
                    ->required(),
                Toggle::make('requestable')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
