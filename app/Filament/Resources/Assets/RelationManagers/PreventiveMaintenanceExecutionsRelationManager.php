<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceExecutionsRelationManager extends RelationManager
{
    protected static string $relationship = 'preventiveMaintenanceExecutions';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['performer', 'schedule.checklists.category', 'checklist.category'])
                ->withCount([
                    'items',
                    'items as passed_items_count' => fn (Builder $itemQuery): Builder => $itemQuery->where('is_passed', true),
                    'items as failed_items_count' => fn (Builder $itemQuery): Builder => $itemQuery->where('is_passed', false),
                ]))
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'needs_attention' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('checklist.category.name')
                    ->label('Category')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('performer.name')
                    ->label('Performed by')
                    ->placeholder('-'),
                TextColumn::make('passed_items_count')
                    ->label('Passed')
                    ->numeric(),
                TextColumn::make('failed_items_count')
                    ->label('Failed')
                    ->numeric(),
                TextColumn::make('general_notes')
                    ->label('Notes')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('viewResults')
                    ->label('Results')
                    ->modalWidth('5xl')
                    ->modalHeading('PM Execution Results')
                    ->fillForm(fn ($record): array => [
                        'general_notes' => $record->general_notes,
                        'items' => $record->items
                            ->map(fn ($item): array => [
                                'task' => $item->task,
                                'result' => match ($item->is_passed) {
                                    true => 'Pass',
                                    false => 'Fail',
                                    default => 'Pending',
                                },
                                'item_notes' => $item->item_notes,
                                'evidence_path' => $item->evidence_path,
                            ])
                            ->all(),
                    ])
                    ->schema([
                        ViewField::make('items')
                            ->label('Checklist Results')
                            ->view('filament.forms.components.pm-results-table')
                            ->columnSpanFull(),
                        ViewField::make('general_notes')
                            ->label('General notes')
                            ->view('filament.forms.components.read-only-text')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
