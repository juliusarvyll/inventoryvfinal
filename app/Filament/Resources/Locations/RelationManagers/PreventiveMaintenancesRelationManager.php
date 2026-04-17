<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Actions\Inventory\SavePreventiveMaintenancePlan;
use App\Filament\Resources\PreventiveMaintenances\PreventiveMaintenanceResource;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use App\Models\PreventiveMaintenance;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'preventiveMaintenances';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount([
                'items',
                'assets',
                'items as completed_items_count' => fn (Builder $itemsQuery): Builder => $itemsQuery->where('is_completed', true),
            ]))
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('assets_count')
                    ->label('Assets')
                    ->numeric(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->numeric(),
                TextColumn::make('completed_items_count')
                    ->label('Completed')
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add preventive maintenance')
                    ->schema([
                        DatePicker::make('scheduled_for'),
                        Select::make('category_ids')
                            ->label('Categories')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => PreventiveMaintenanceForm::categoryOptionsForLocation($this->getOwnerRecord()->getKey()))
                            ->columnSpanFull(),
                        PreventiveMaintenanceForm::checklistRepeater(includeId: false),
                    ])
                    ->action(function (array $data): void {
                        app(SavePreventiveMaintenancePlan::class)(
                            null,
                            $data + [
                                'location_id' => $this->getOwnerRecord()->getKey(),
                            ],
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('Preventive maintenance created')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('categories')
                    ->label('Categories')
                    ->icon('heroicon-o-tag')
                    ->modalHeading('Select Categories For PM')
                    ->fillForm(fn (PreventiveMaintenance $record): array => [
                        'category_ids' => $record->categories()->pluck('categories.id')->all(),
                    ])
                    ->schema([
                        Select::make('category_ids')
                            ->label('Categories')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => PreventiveMaintenanceForm::categoryOptionsForLocation($this->getOwnerRecord()->getKey())),
                    ])
                    ->action(function (PreventiveMaintenance $record, array $data): void {
                        app(SavePreventiveMaintenancePlan::class)(
                            $record,
                            [
                                'category_ids' => $data['category_ids'] ?? [],
                            ],
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('PM categories updated')
                            ->success()
                            ->send();
                    }),
                Action::make('checklist')
                    ->label('Checklist')
                    ->icon('heroicon-o-list-bullet')
                    ->modalWidth('4xl')
                    ->modalHeading('Manage Checklist Items')
                    ->fillForm(fn (PreventiveMaintenance $record): array => [
                        'items' => PreventiveMaintenanceForm::editableChecklistItems($record),
                    ])
                    ->schema([
                        PreventiveMaintenanceForm::checklistRepeater(),
                    ])
                    ->action(function (PreventiveMaintenance $record, array $data): void {
                        app(SavePreventiveMaintenancePlan::class)(
                            $record,
                            [
                                'items' => $data['items'] ?? [],
                            ],
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('Checklist updated')
                            ->success()
                            ->send();
                    }),
                ViewAction::make()
                    ->url(fn (PreventiveMaintenance $record): string => PreventiveMaintenanceResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (PreventiveMaintenance $record): string => PreventiveMaintenanceResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
