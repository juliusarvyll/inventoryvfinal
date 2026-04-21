<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Tables;

use App\Filament\Actions\ExportCsvAction;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas\PreventiveMaintenanceExecutionForm;
use App\Models\Asset;
use App\Models\PreventiveMaintenanceChecklist;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PreventiveMaintenanceSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('checklist.category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('checklist.instructions')
                    ->label('Checklist')
                    ->limit(30)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('executions_count')
                    ->label('Executions')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('start')
                    ->label('Start Execution')
                    ->icon('heroicon-o-play')
                    ->form(function ($record): array {
                        $checklist = $record->checklist;
                        $checklist->loadMissing('items');
                        return [
                            Select::make('asset_id')
                                ->label('Asset')
                                ->options(function () use ($record): array {
                                    return Asset::query()
                                        ->where('location_id', $record->location_id)
                                        ->where('category_id', $record->category)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('Checklist')
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->collapsed(false)
                                ->default($checklist->items->map(fn ($item): array => [
                                    'id' => $item->getKey(),
                                    'task' => $item->task,
                                    'input_label' => $item->input_label,
                                ])->toArray())
                                ->schema([
                                    \Filament\Forms\Components\Hidden::make('id')
                                        ->required(),
                                    \Filament\Forms\Components\Hidden::make('input_label'),
                                    \Filament\Forms\Components\Textarea::make('task')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->rows(2)
                                        ->columnSpanFull(),
                                    \Filament\Forms\Components\Select::make('is_passed')
                                        ->label('Result')
                                        ->options([
                                            '1' => 'Pass',
                                            '0' => 'Fail',
                                        ])
                                        ->placeholder('Pending'),
                                    \Filament\Forms\Components\TextInput::make('input_value')
                                        ->label(fn (\Filament\Schemas\Components\Utilities\Get $get): string => (string) $get('input_label'))
                                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => filled($get('input_label'))),
                                    \Filament\Forms\Components\FileUpload::make('evidence_path')
                                        ->label('Evidence')
                                        ->disk('public')
                                        ->directory('preventive-maintenance/evidence')
                                        ->visibility('public')
                                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                                        ->maxSize(5120)
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('general_notes')
                                ->label('General notes')
                                ->rows(4)
                                ->columnSpanFull(),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        $asset = Asset::findOrFail($data['asset_id']);
                        app(\App\Actions\Inventory\StartPreventiveMaintenanceExecution::class)(
                            schedule: $record,
                            asset: $asset,
                            items: $data['items'] ?? [],
                            actor: auth()->user(),
                            generalNotes: $data['general_notes'] ?? null,
                        );
                    }),
            ])
            ->toolbarActions([
                ExportCsvAction::make(),
                DeleteBulkAction::make(),
            ])
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['location', 'checklist.category']));
    }
}
