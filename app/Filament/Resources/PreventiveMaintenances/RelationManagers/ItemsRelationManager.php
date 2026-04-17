<?php

namespace App\Filament\Resources\PreventiveMaintenances\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('task')
                ->required()
                ->maxLength(255),
            TextInput::make('input_label')
                ->label('Optional input label')
                ->maxLength(255),
            Toggle::make('is_required')
                ->default(true)
                ->required(),
            Toggle::make('is_completed')
                ->default(false)
                ->required(),
            Textarea::make('support_notes')
                ->rows(3)
                ->helperText('IT support can capture findings and work notes here.')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('task')->searchable(),
                TextColumn::make('input_label')->label('Input')->placeholder('-')->toggleable(),
                IconColumn::make('is_required')->label('Required')->boolean(),
                IconColumn::make('is_completed')->label('Done')->boolean(),
                TextColumn::make('completedBy.name')->label('Completed by')->placeholder('-'),
                TextColumn::make('completed_at')->dateTime()->placeholder('-'),
                TextColumn::make('support_notes')->limit(40)->tooltip(fn (?string $state): ?string => $state)->toggleable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
