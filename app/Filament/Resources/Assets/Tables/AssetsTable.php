<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Enums\InventoryCategoryType;
use App\Filament\Actions\ChangeCategoryBulkAction;
use App\Filament\Actions\ChangeLocationBulkAction;
use App\Filament\Actions\ChangeSupplierBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('asset_tag')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('assetModel.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('statusLabel.name')
                    ->searchable(),
                TextColumn::make('activeCheckout.assignee.name')
                    ->label('Assigned to')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('activeCheckout.assigned_at')
                    ->label('Checked out')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('serial')
                    ->searchable(),
                TextColumn::make('purchase_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('warranty_expires')
                    ->date()
                    ->sortable(),
                TextColumn::make('eol_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('requestable')
                    ->boolean(),
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
                BulkActionGroup::make([
                    ChangeCategoryBulkAction::make(InventoryCategoryType::Asset, 'assets'),
                    ChangeLocationBulkAction::make('assets'),
                    ChangeSupplierBulkAction::make('assets'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
