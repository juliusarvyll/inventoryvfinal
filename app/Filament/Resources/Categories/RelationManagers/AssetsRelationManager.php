<?php

namespace App\Filament\Resources\Categories\RelationManagers;

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
                    ->label('Location')
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
                    ->label('Add Asset to This Category')
                    ->slideOver()
                    ->schema([
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
                            ->default($this->getOwnerRecord()->getKey())
                            ->required(),
                        Select::make('status_label_id')
                            ->relationship('statusLabel', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Toggle::make('requestable')
                            ->required(),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['category_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->visible(fn () => auth()->user()?->can('Create:Asset') ?? false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => auth()->user()?->can('View:Asset') ?? false),
                EditAction::make()
                    ->slideOver()
                    ->visible(fn () => auth()->user()?->can('Update:Asset') ?? false),
                Action::make('quickStatusUpdate')
                    ->label('Update Status')
                    ->color('primary')
                    ->slideOver()
                    ->schema([
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
                    ->color('primary')
                    ->schema([
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
            ]);
    }
}
