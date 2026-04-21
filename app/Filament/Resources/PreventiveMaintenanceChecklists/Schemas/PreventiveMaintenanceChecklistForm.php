<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PreventiveMaintenanceChecklistForm
{
    public const CHECKLIST_HELPER_TEXT = 'Task order is saved automatically.';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Each category can have at most one active checklist.'),
            Textarea::make('instructions')
                ->rows(3)
                ->helperText('Optional instructions shown when this checklist is used for an execution.')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->required(),
            Repeater::make('items')
                ->label('Checklist items')
                ->defaultItems(1)
                ->reorderableWithButtons()
                ->helperText('Add all preventive maintenance tasks here. '.static::CHECKLIST_HELPER_TEXT)
                ->schema(static::checklistItemFields())
                ->columnSpanFull()
                ->collapsed(false),
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
