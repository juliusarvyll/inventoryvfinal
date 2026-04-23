<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\RelationManagers;

use App\Filament\Resources\PreventiveMaintenanceChecklists\Schemas\PreventiveMaintenanceChecklistForm;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Checklist Item')
                    ->description('Define the task and whether passing it is required.')
                    ->schema(PreventiveMaintenanceChecklistForm::checklistItemFields())
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->badge()
                    ->sortable(),
                TextColumn::make('task')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('input_label')
                    ->label('Input')
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No checklist items yet')
            ->emptyStateDescription('Add the first task to define what technicians need to verify during execution.');
    }
}
