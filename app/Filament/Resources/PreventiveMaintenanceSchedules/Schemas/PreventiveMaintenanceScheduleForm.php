<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas;

use App\Models\Location;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class PreventiveMaintenanceScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('location_id')
                ->relationship('location', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live(),
            Select::make('preventive_maintenance_checklist_id')
                ->label('Checklist')
                ->relationship('checklist', 'id', fn ($query) => $query->with('category'))
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->category->name . ' - ' . ($record->instructions ?: 'No instructions'))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $checklist = \App\Models\PreventiveMaintenanceChecklist::find($state);
                        if ($checklist) {
                            $set('category_id', $checklist->category_id);
                        }
                    }
                })
                ->helperText('Select the checklist template to use for this schedule. Category will be auto-filled.'),
            DatePicker::make('scheduled_for')
                ->label('Scheduled for')
                ->helperText('Optional one-off date. Leave blank if not scheduled yet.'),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->required(),
        ]);
    }
}
