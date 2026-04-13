<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('phone')
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->placeholder('—'),
                        TextEntry::make('url')
                            ->placeholder('—')
                            ->columnSpanFull(),
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
                Section::make('Linked inventory')
                    ->schema([
                        TextEntry::make('assets_count')
                            ->label('Assets')
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
                    ->columns(4),
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
