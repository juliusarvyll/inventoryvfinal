<?php

namespace App\Filament\Resources\PreventiveMaintenances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PreventiveMaintenanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Plan')
                ->schema([
                    TextEntry::make('location.name')->label('Location'),
                    TextEntry::make('version')->badge(),
                    TextEntry::make('categories.name')
                        ->label('Categories')
                        ->badge()
                        ->separator(', ')
                        ->placeholder('-'),
                    TextEntry::make('scheduled_for')->date()->placeholder('-'),
                    TextEntry::make('assets_count')->label('Assets')->numeric(),
                ])
                ->columns(2),
            Section::make('Progress')
                ->schema([
                    TextEntry::make('items_count')->label('Checklist items')->numeric(),
                    TextEntry::make('completed_items_count')->label('Completed')->numeric(),
                    TextEntry::make('execution_notes')
                        ->label('Work notes')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Audit')
                ->schema([
                    TextEntry::make('creator.name')->label('Created by')->placeholder('-'),
                    TextEntry::make('updater.name')->label('Updated by')->placeholder('-'),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('updated_at')->dateTime(),
                ])
                ->columns(2)
                ->collapsed(),
        ]);
    }
}
