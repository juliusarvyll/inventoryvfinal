<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function table(Table $table): Table
    {
        $ownerLocationId = (int) $this->getOwnerRecord()->getKey();
        $descendantIds = collect($this->getOwnerRecord()->selfAndDescendantIds())
            ->reject(fn (int $id): bool => $id === $ownerLocationId)
            ->values()
            ->all();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->when(
                $descendantIds !== [],
                fn (Builder $query): Builder => $query->orWhereIn('location_id', $descendantIds),
            ))
            ->columns([
                TextColumn::make('asset_tag')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('assetModel.name')
                    ->label('Asset model')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('statusLabel.name')
                    ->label('Status')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('activeCheckout.assignee.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('requestable')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Asset to This Location')
                    ->slideOver()
                    ->form([
                        Select::make('asset_tag')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('name')
                            ->required(),
                        Select::make('asset_model_id')
                            ->relationship('assetModel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('status_label_id')
                            ->relationship('statusLabel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Toggle::make('requestable')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        // Automatically set the location_id to the parent location
                        $data['location_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->visible(fn () => auth()->user()?->can('Create:Asset') ?? false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => auth()->user()?->can('View:Asset') ?? false),
                EditAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure location_id remains consistent when editing
                        $data['location_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->visible(fn () => auth()->user()?->can('Update:Asset') ?? false),
                Action::make('quickStatusUpdate')
                    ->label('Update Status')
                    ->icon('heroicon-m-refresh')
                    ->color('primary')
                    ->slideOver()
                    ->form([
                        Select::make('status_label_id')
                            ->relationship('statusLabel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data): void {
                        $this->getRecord()->update($data);
                    })
                    ->visible(fn () => auth()->user()?->can('Update:Asset') ?? false),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('Delete:Asset') ?? false)
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkAction::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-m-refresh')
                    ->color('primary')
                    ->form([
                        Select::make('status_label_id')
                            ->relationship('statusLabel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data): void {
                        $this->getSelectedRecords()->each->update($data);
                    })
                    ->visible(fn () => auth()->user()?->can('Update:Asset') ?? false)
                    ->requiresConfirmation(),
                BulkAction::make('moveAssets')
                    ->label('Move Assets to Another Location')
                    ->icon('heroicon-m-arrow-right')
                    ->color('warning')
                    ->form([
                        Select::make('location_id')
                            ->relationship('location', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data): void {
                        $this->getSelectedRecords()->each->update([
                            'location_id' => $data['location_id'],
                        ]);
                    })
                    ->visible(fn () => auth()->user()?->can('Update:Asset') ?? false)
                    ->requiresConfirmation()
                    ->deselectSelectedAfterCompletion(),
            ]);
    }
}
