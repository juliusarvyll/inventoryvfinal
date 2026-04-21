<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExecutionsRelationManager extends RelationManager
{
    protected static string $relationship = 'executions';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['performer', 'asset'])
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
                TextColumn::make('asset.asset_tag')
                    ->label('Asset')
                    ->searchable()
                    ->sortable(),
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
                                'input_label' => $item->input_label,
                                'input_value' => $item->input_value,
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
                        Repeater::make('items')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsed(false)
                            ->schema([
                                Textarea::make('task')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('input_label')
                                    ->label('Input label')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('input_value')
                                    ->label('Input value')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('result')
                                    ->disabled()
                                    ->dehydrated(false),
                                Textarea::make('item_notes')
                                    ->label('Notes')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('evidence_path')
                                    ->label('Evidence')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                        Textarea::make('general_notes')
                            ->label('General notes')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
