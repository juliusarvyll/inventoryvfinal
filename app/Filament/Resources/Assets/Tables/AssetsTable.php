<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Enums\InventoryCategoryType;
use App\Filament\Actions\ChangeCategoryBulkAction;
use App\Filament\Actions\ChangeLocationBulkAction;
use App\Filament\Actions\ChangeSupplierBulkAction;
use App\Filament\Actions\ExportPdfAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Asset Model')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->assetModel ? $record->assetModel->name : 'Deleted'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->category ? $record->category->name : 'Deleted'),
                TextColumn::make('statusLabel.name')
                    ->label('Status')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->statusLabel ? $record->statusLabel->name : 'Deleted'),
                TextColumn::make('activeCheckout.assignee.name')
                    ->label('Assigned to')
                    ->placeholder('-')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->activeCheckout?->assignee ? $record->activeCheckout->assignee->name : 'Unassigned'),
                TextColumn::make('activeCheckout.assigned_at')
                    ->label('Checked out')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->supplier ? $record->supplier->name : 'Deleted'),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->location ? $record->location->name : 'Deleted'),
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
                SelectFilter::make('status_label_id')
                    ->label('Status')
                    ->relationship('statusLabel', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('requestable'),
                Filter::make('checked_out')
                    ->label('Checked Out')
                    ->query(fn (Builder $query): Builder => $query->whereHas('activeCheckout')),
                Filter::make('purchase_date')
                    ->schema([
                        DatePicker::make('purchased_from'),
                        DatePicker::make('purchased_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['purchased_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['purchased_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportPdfAction::make(),
                BulkActionGroup::make([
                    ChangeCategoryBulkAction::make(InventoryCategoryType::Asset, 'assets'),
                    ChangeLocationBulkAction::make('assets'),
                    ChangeSupplierBulkAction::make('assets'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'location', 'statusLabel', 'assetModel', 'supplier']));
    }
}
