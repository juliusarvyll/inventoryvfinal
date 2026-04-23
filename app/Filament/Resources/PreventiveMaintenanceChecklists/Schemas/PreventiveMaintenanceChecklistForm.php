<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PreventiveMaintenanceChecklistForm
{
    public const CHECKLIST_HELPER_TEXT = 'Task order is saved automatically.';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Checklist Setup')
                    ->description('Define which category this checklist applies to and whether it is currently available.')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Each category can have at most one checklist.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<int, Textarea|TextInput|Toggle>
     */
    public static function checklistItemFields(): array
    {
        return [
            Textarea::make('task')
                ->required()
                ->rows(2)
                ->columnSpanFull(),
            TextInput::make('input_label')
                ->label('Optional input label')
                ->helperText('Example: Serial checked, Temperature reading, Rack label.')
                ->maxLength(255),
            Toggle::make('is_required')
                ->default(true)
                ->required(),
        ];
    }
}
