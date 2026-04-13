<?php

namespace App\Filament\Resources\Consumables\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConsumableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('-'),
                TextEntry::make('location.name')
                    ->label('Location')
                    ->placeholder('-'),
                TextEntry::make('qty')
                    ->numeric(),
                TextEntry::make('min_qty')
                    ->numeric(),
                TextEntry::make('model_number')
                    ->placeholder('-'),
                TextEntry::make('item_no')
                    ->placeholder('-'),
                TextEntry::make('purchase_cost')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('purchase_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('order_number')
                    ->placeholder('-'),
                IconEntry::make('requestable')
                    ->boolean(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
