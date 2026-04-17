<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Actions\Inventory\StartPreventiveMaintenanceSession;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceSession;
use Filament\Actions\Action;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with('categories')
                ->withCount('items'))
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->placeholder('-'),
                TextColumn::make('version')
                    ->badge()
                    ->label('Version'),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Start preventive maintenance')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (): bool => auth()->user()?->can('create', PreventiveMaintenanceSession::class) ?? false)
                    ->modalWidth('5xl')
                    ->modalHeading('Start Preventive Maintenance')
                    ->fillForm(fn (PreventiveMaintenance $record): array => PreventiveMaintenanceForm::sessionFormData($record))
                    ->schema(PreventiveMaintenanceForm::sessionExecutionSchema())
                    ->action(function (PreventiveMaintenance $record, array $data): void {
                        app(StartPreventiveMaintenanceSession::class)(
                            $record,
                            $this->getOwnerRecord(),
                            $data['items'] ?? [],
                            auth()->user(),
                            $data['general_notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Preventive maintenance session saved')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
