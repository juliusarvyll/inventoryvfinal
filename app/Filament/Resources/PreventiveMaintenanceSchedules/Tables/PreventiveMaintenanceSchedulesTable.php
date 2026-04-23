<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Tables;

use App\Actions\Inventory\StartPreventiveMaintenanceExecution;
use App\Filament\Actions\ExportPdfAction;
use App\Models\Asset;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceSchedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('location.name')
                    ->label('Location')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories')
                    ->label('Categories')
                    ->wrap()
                    ->placeholder('No categories')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->checklists->pluck('category.name')->unique()->join(', ')),
                TextColumn::make('checklists_count')
                    ->label('Checklists')
                    ->numeric()
                    ->badge()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->checklists->count()),
                TextColumn::make('scheduled_for')
                    ->label('Scheduled')
                    ->date()
                    ->badge()
                    ->sortable()
                    ->placeholder('Not scheduled'),
                TextColumn::make('executions_count')
                    ->label('Executions')
                    ->numeric()
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
                    ->modalHeading('Start PM execution')
                    ->modalDescription('Select the checklist and asset, then capture item-level results.')
                    ->modalSubmitActionLabel('Start execution')
                    ->form(function ($record): array {
                        $record->loadMissing(['checklists.category', 'checklists.items', 'location']);

                        $checklistOptions = $record->checklists->mapWithKeys(fn ($checklist) => [
                            $checklist->id => $checklist->category?->name ?? "Checklist #{$checklist->id}",
                        ])->toArray();

                        return [
                            Section::make('Execution Target')
                                ->schema([
                                    Select::make('checklist_id')
                                        ->label('Checklist')
                                        ->options($checklistOptions)
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) use ($record): void {
                                            if (! $state) {
                                                $set('items', []);

                                                return;
                                            }

                                            $checklist = $record->checklists->find($state);
                                            if (! $checklist) {
                                                $set('items', []);

                                                return;
                                            }

                                            $checklist->load('items');

                                            $set('items', $checklist->items->map(fn ($item): array => [
                                                'id' => $item->getKey(),
                                                'task' => $item->task,
                                            ])->toArray());
                                        }),
                                    Select::make('asset_id')
                                        ->label('Asset')
                                        ->options(function (callable $get) use ($record): array {
                                            $checklistId = $get('checklist_id');
                                            if (! $checklistId) {
                                                return [];
                                            }

                                            $checklist = $record->checklists->find($checklistId);
                                            if (! $checklist) {
                                                return [];
                                            }

                                            return Asset::query()
                                                ->where('location_id', $record->location_id)
                                                ->where('category_id', $checklist->category_id)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live(),
                                ])
                                ->columns(2),
                            Section::make('Checklist Results')
                                ->schema([
                                    Repeater::make('items')
                                        ->label('Checklist Items')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->collapsed(false)
                                        ->reactive()
                                        ->default(function (callable $get) use ($record): array {
                                            $checklistId = $get('checklist_id');
                                            if (! $checklistId) {
                                                return [];
                                            }

                                            $checklist = $record->checklists->find($checklistId);
                                            if (! $checklist) {
                                                return [];
                                            }

                                            $checklist->load('items');

                                            return $checklist->items->map(fn ($item): array => [
                                                'id' => $item->getKey(),
                                                'task' => $item->task,
                                            ])->toArray();
                                        })
                                        ->schema([
                                            Hidden::make('id')
                                                ->required(),
                                            Textarea::make('task')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->rows(2)
                                                ->columnSpanFull(),
                                            Select::make('is_passed')
                                                ->label('Result')
                                                ->options([
                                                    '1' => 'Pass',
                                                    '0' => 'Fail',
                                                ])
                                                ->placeholder('Pending'),
                                            FileUpload::make('evidence_path')
                                                ->label('Evidence')
                                                ->disk('public')
                                                ->directory('preventive-maintenance/evidence')
                                                ->visibility('public')
                                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                                ->maxSize(5120)
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                            Section::make('Completion Notes')
                                ->schema([
                                    Textarea::make('general_notes')
                                        ->label('General notes')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                ]),
                        ];
                    })
                    ->action(function (PreventiveMaintenanceSchedule $record, array $data): void {
                        $asset = Asset::find($data['asset_id']);
                        $checklist = PreventiveMaintenanceChecklist::find($data['checklist_id']);

                        app(StartPreventiveMaintenanceExecution::class)(
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
                ExportPdfAction::make(),
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No PM schedules yet')
            ->emptyStateDescription('Create a schedule, attach checklist templates, then start executions from this table.')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['location', 'checklists.category']));
    }
}
