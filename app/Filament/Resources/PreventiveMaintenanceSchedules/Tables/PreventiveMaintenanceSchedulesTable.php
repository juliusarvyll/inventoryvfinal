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
                TextColumn::make('checklists.category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('checklists')
                    ->label('Checklists')
                    ->formatStateUsing(function ($state) {
                        if ($state->isNotEmpty()) {
                            return $state->pluck('instructions')->take(3)->join(', ');
                        }
                        return '-';
                    })
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->checklists->pluck('instructions')->join(', ');
                    })
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
                        $record->loadMissing('checklists.items', 'location');
                        
                        $checklists = $record->checklists->mapWithKeys(fn ($checklist) => [
                            $checklist->id => $checklist->category->name . ' - ' . ($checklist->instructions ?: 'No instructions'),
                        ])->toArray();
                        
                        $checklistItems = $record->checklists->mapWithKeys(fn ($checklist) => [
                            $checklist->id => $checklist->items->map(fn ($item): array => [
                                'id' => $item->getKey(),
                                'task' => $item->task,
                                'input_label' => $item->input_label,
                            ])->toArray(),
                        ])->toArray();
                        
                        return [
                            \Filament\Forms\Components\Select::make('checklist_id')
                                ->label('Checklist')
                                ->options($checklists)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            Select::make('asset_id')
                                ->label('Asset')
                                ->options(function () use ($record): array {
                                    return Asset::query()
                                        ->where('location_id', $record->location_id)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('Checklist Items')
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->collapsed(false)
                                ->default(function (\Filament\Schemas\Components\Utilities\Get $get) use ($checklistItems): array {
                                    $checklistId = $get('checklist_id');
                                    return $checklistItems[$checklistId] ?? [];
                                })
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
                    ->action(function (PreventiveMaintenanceSchedule $record, array $data): void {
                        $checklist = \App\Models\PreventiveMaintenanceChecklist::find($data['checklist_id']);
                        $asset = \App\Models\Asset::find($data['asset_id']);
                        
                        app(\App\Actions\Inventory\StartPreventiveMaintenanceExecution::class)(
                            schedule: $record,
                            checklist: $checklist,
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
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['location', 'checklists.category']));
    }
}
