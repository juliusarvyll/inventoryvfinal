<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Asset Tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statusLabel.name')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('assetModel.name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('asset_tag')
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
