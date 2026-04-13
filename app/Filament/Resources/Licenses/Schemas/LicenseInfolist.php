<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Manufacturers\ManufacturerResource;
use App\Models\License;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('License')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('product_key')
                            ->placeholder('—'),
                        TextEntry::make('license_type')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Seats')
                    ->schema([
                        TextEntry::make('license_seats_count')
                            ->label('Seats in use')
                            ->numeric(),
                        TextEntry::make('seats')
                            ->label('Seats purchased')
                            ->numeric(),
                        TextEntry::make('seats_available')
                            ->label('Seats available')
                            ->state(fn (License $record): int => $record->seatsAvailable()),
                    ])
                    ->columns(3),
                Section::make('Related')
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->url(fn (License $record): ?string => $record->category_id
                                ? CategoryResource::getUrl('view', ['record' => $record->category_id])
                                : null),
                        TextEntry::make('manufacturer.name')
                            ->label('Manufacturer')
                            ->placeholder('—')
                            ->url(fn (License $record): ?string => $record->manufacturer_id
                                ? ManufacturerResource::getUrl('view', ['record' => $record->manufacturer_id])
                                : null),
                    ])
                    ->columns(2),
                Section::make('Lifecycle')
                    ->schema([
                        TextEntry::make('expiration_date')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('purchase_date')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('purchase_cost')
                            ->money()
                            ->placeholder('—'),
                        TextEntry::make('order_number')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Flags')
                    ->schema([
                        IconEntry::make('maintained')
                            ->boolean(),
                        IconEntry::make('requestable')
                            ->boolean(),
                    ])
                    ->columns(2),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
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
