<?php

namespace App\Filament\Resources\ItemRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ItemRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('requested_by')
                    ->label('Requested By')
                    ->placeholder('-'),
                TextEntry::make('department')
                    ->placeholder('-'),
                TextEntry::make('items')
                    ->columnSpanFull()
                    ->placeholder('-'),
                TextEntry::make('qty')
                    ->numeric(),
                TextEntry::make('unit_cost')
                    ->money('USD')
                    ->placeholder('-'),
                TextEntry::make('source_of_fund')
                    ->label('Source of Fund')
                    ->placeholder('-'),
                TextEntry::make('purpose_project')
                    ->label('Purpose Project')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('deny_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('handler.name')
                    ->label('Handled By')
                    ->placeholder('-'),
                TextEntry::make('handled_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('fulfilled_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
