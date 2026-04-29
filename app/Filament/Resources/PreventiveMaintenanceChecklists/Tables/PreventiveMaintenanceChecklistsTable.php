<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists\Tables;

use App\Filament\Actions\ExportPdfAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceChecklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('categories')
                    ->label('Categories')
                    ->badge()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->categories->pluck('name')->unique()->join(', ')),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->numeric()
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ExportPdfAction::make(),
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No PM checklists yet')
            ->emptyStateDescription('Create your first checklist template and share it across one or more categories.')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['categories']));
    }
}
