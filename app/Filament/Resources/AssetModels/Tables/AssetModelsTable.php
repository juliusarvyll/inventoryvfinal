<?php

namespace App\Filament\Resources\AssetModels\Tables;

use App\Enums\InventoryCategoryType;
use App\Filament\Actions\ChangeCategoryBulkAction;
use App\Filament\Actions\ChangeManufacturerBulkAction;
use App\Filament\Actions\ExportCsvAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('manufacturer.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('model_number')
                    ->searchable(),
                TextColumn::make('assets_count')
                    ->label('Assets')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportCsvAction::make(),
                BulkActionGroup::make([
                    ChangeCategoryBulkAction::make(InventoryCategoryType::Asset, 'asset models'),
                    ChangeManufacturerBulkAction::make('asset models'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['category', 'manufacturer']));
    }
}
