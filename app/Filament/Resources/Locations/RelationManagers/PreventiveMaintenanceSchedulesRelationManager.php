<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'preventiveMaintenanceSchedules';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_for', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['checklists.categories']))
            ->columns([
                TextColumn::make('categories')
                    ->label('Categories')
                    ->badge()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->checklists->flatMap(fn ($checklist) => $checklist->categories->pluck('name'))->unique()->join(', ')),
                TextColumn::make('checklists_count')
                    ->label('Checklists')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->checklists->count()),
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('executions_count')
                    ->label('Executions')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label('Active')
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
            ]);
    }
}
