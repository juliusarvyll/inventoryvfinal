<?php

namespace App\Filament\Resources\Consumables\Schemas;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConsumableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('category_id')
                    ->options(fn (): array => Category::query()
                        ->ofType(InventoryCategoryType::Consumable)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
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
                Toggle::make('requestable')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
