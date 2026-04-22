<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Actions\Inventory\StartPreventiveMaintenanceExecution;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas\PreventiveMaintenanceExecutionForm;
use App\Models\PreventiveMaintenanceExecution;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'preventiveMaintenanceSchedules';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['checklist', 'category']))
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('checklist.category.name')
                    ->label('Checklist')
                    ->badge()
                    ->searchable(),
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Start preventive maintenance')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn ($record): bool => auth()->user()?->can('create', PreventiveMaintenanceExecution::class) ?? false && $this->getOwnerRecord()->category_id === $record->category)
                    ->modalWidth('5xl')
                    ->modalHeading('Start Preventive Maintenance')
                    ->fillForm(fn ($record): array => PreventiveMaintenanceExecutionForm::executionFormData($record))
                    ->schema(PreventiveMaintenanceExecutionForm::executionSchema())
                    ->action(function ($record, array $data): void {
                        app(StartPreventiveMaintenanceExecution::class)(
                            schedule: $record,
                            checklist: $record->checklist,
                            asset: $this->getOwnerRecord(),
                            items: $data['items'] ?? [],
                            actor: auth()->user(),
                            generalNotes: $data['general_notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Preventive maintenance execution saved')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
