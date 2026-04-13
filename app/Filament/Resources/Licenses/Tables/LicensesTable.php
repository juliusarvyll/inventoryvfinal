<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Enums\InventoryCategoryType;
use App\Filament\Actions\ChangeCategoryBulkAction;
use App\Filament\Actions\ChangeManufacturerBulkAction;
use App\Models\License;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('product_key')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('manufacturer.name')
                    ->searchable(),
                TextColumn::make('license_type')
                    ->searchable(),
                TextColumn::make('seats')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('license_seats_count')
                    ->label('Seats in use')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state, License $record): string => sprintf('%d / %d', $state, $record->seats)),
                TextColumn::make('expiration_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('order_number')
                    ->searchable(),
                IconColumn::make('maintained')
                    ->boolean(),
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
                    ChangeCategoryBulkAction::make(InventoryCategoryType::License, 'licenses'),
                    ChangeManufacturerBulkAction::make('licenses'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
