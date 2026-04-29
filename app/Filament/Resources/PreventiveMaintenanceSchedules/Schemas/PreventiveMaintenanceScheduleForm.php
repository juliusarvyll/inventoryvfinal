<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Scope')
                    ->description('Choose where this schedule runs and when it should happen.')
                    ->schema([
                        Select::make('location_id')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        DatePicker::make('scheduled_for')
                            ->label('Scheduled for')
                            ->helperText('Optional one-off date. Leave blank if this schedule is run manually.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Checklist Coverage')
                    ->description('Attach one or more active checklist templates. Each checklist can represent a different category.')
                    ->schema([
                        Select::make('checklist_ids')
                            ->label('Checklists')
                            ->relationship(
                                'checklists',
                                'id',
                                fn (Builder $query): Builder => $query
                                    ->where('is_active', true)
                                    ->with('categories'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->categories->pluck('name')->unique()->join(', ') ?: "Checklist #{$record->id}")
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required()
                            ->helperText('Only active checklists are shown.'),
                    ]),
            ]);
    }
}
