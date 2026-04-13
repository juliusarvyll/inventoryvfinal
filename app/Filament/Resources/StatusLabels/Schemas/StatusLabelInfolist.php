<?php

namespace App\Filament\Resources\StatusLabels\Schemas;

use App\Models\StatusLabel;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class StatusLabelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status label')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('type')
                            ->placeholder('—'),
                        TextEntry::make('color')
                            ->badge()
                            ->placeholder('—')
                            ->color(function (StatusLabel $record): array|string|null {
                                $hex = $record->color;

                                if (! is_string($hex) || $hex === '') {
                                    return null;
                                }

                                $normalized = str_starts_with($hex, '#') ? $hex : '#'.$hex;

                                return Color::hex($normalized);
                            }),
                        TextEntry::make('assets_count')
                            ->label('Assets with this status')
                            ->numeric(),
                    ])
                    ->columns(2),
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
