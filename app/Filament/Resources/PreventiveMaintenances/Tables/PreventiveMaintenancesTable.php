<?php

namespace App\Filament\Resources\PreventiveMaintenances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PreventiveMaintenancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('location.name')->label('Location')->searchable(),
                TextColumn::make('version')->badge(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),
                TextColumn::make('scheduled_for')->date()->sortable()->placeholder('-'),
                TextColumn::make('assets_count')->label('Assets')->numeric()->sortable(),
                TextColumn::make('items_count')->label('Items')->numeric()->sortable(),
                TextColumn::make('completed_items_count')->label('Completed')->numeric()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
