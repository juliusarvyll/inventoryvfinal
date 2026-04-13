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
                TextEntry::make('requester_display_name')
                    ->label('Requester'),
                TextEntry::make('requestable_display_name')
                    ->label('Asset'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('qty')
                    ->numeric(),
                TextEntry::make('reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
