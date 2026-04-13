<?php

namespace App\Filament\Resources\Consumables\Tables;

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

class ConsumablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('model_number')
                    ->searchable(),
                TextColumn::make('item_no')
                    ->searchable(),
                TextColumn::make('purchase_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('order_number')
                    ->searchable(),
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
                    ChangeCategoryBulkAction::make(InventoryCategoryType::Consumable, 'consumables'),
                    ChangeLocationBulkAction::make('consumables'),
                    ChangeSupplierBulkAction::make('consumables'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
