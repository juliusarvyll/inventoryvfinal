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
            ->defaultSort('category_id')
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
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
            ->emptyStateDescription('Create your first checklist template per category to standardize preventive maintenance checks.')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category']));
    }
}
