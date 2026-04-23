<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Actions\Inventory\StartPreventiveMaintenanceExecution;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceExecution;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['checklists.category']))
            ->defaultSort('scheduled_for', 'desc')
            ->columns([
                TextColumn::make('categories')
                    ->label('Categories')
                    ->badge()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->checklists->pluck('category.name')->unique()->join(', ')),
                TextColumn::make('checklists_count')
                    ->label('Checklists')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->checklists->count()),
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
                    ->visible(fn ($record): bool => auth()->user()?->can('create', PreventiveMaintenanceExecution::class) ?? false && $record->checklists->pluck('category_id')->contains($this->getOwnerRecord()->category_id))
                    ->modalWidth('5xl')
                    ->modalHeading('Start Preventive Maintenance')
                    ->form(function ($record): array {
                        $record->loadMissing(['checklists.category', 'checklists.items']);

                        $checklistOptions = $record->checklists
                            ->filter(fn ($checklist) => $checklist->category_id === $this->getOwnerRecord()->category_id)
                            ->mapWithKeys(fn ($checklist) => [
                                $checklist->id => $checklist->category->name,
                            ])->toArray();

                        return [
                            Select::make('checklist_id')
                                ->label('Checklist')
                                ->options($checklistOptions)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) use ($record): void {
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
                            Repeater::make('items')
                                ->label('Checklist Items')
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->collapsed(false)
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
                                    Hidden::make('id')->required(),
                                    Textarea::make('task')->disabled()->dehydrated(false)->rows(2)->columnSpanFull(),
                                    Select::make('is_passed')->label('Result')->options(['1' => 'Pass', '0' => 'Fail'])->placeholder('Pending'),
                                    FileUpload::make('evidence_path')->label('Evidence')->disk('public')->directory('preventive-maintenance/evidence')->visibility('public')->acceptedFileTypes(['image/*', 'application/pdf'])->maxSize(5120)->columnSpanFull(),
                                ])->columnSpanFull(),
                            Textarea::make('general_notes')->label('General notes')->rows(4)->columnSpanFull(),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        $checklist = PreventiveMaintenanceChecklist::find($data['checklist_id']);
                        app(StartPreventiveMaintenanceExecution::class)(
                            schedule: $record,
                            checklist: $checklist,
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
