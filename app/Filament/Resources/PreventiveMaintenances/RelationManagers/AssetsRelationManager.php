<?php

namespace App\Filament\Resources\PreventiveMaintenances\RelationManagers;

use App\Actions\Inventory\StartPreventiveMaintenanceSession;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use App\Models\Asset;
use App\Models\PreventiveMaintenanceSession;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['category', 'statusLabel']))
            ->defaultSort('asset_tag')
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Asset tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('statusLabel.name')
                    ->label('Status')
                    ->badge()
                    ->placeholder('-'),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Start preventive maintenance')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (): bool => auth()->user()?->can('create', PreventiveMaintenanceSession::class) ?? false)
                    ->modalWidth('5xl')
                    ->modalHeading('Start Preventive Maintenance')
                    ->fillForm(fn (): array => PreventiveMaintenanceForm::sessionFormData($this->getOwnerRecord()))
                    ->schema(PreventiveMaintenanceForm::sessionExecutionSchema())
                    ->action(function (Asset $record, array $data): void {
                        app(StartPreventiveMaintenanceSession::class)(
                            $this->getOwnerRecord(),
                            $record,
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
