<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_tag')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('assetModel.name')
                    ->label('Asset model')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('statusLabel.name')
                    ->label('Status label')
                    ->searchable(),
                IconColumn::make('requestable')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
