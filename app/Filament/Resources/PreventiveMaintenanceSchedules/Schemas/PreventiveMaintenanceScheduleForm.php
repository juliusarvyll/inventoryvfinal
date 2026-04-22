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
            Select::make('checklist_ids')
                ->label('Checklists')
                ->options(fn () => \App\Models\PreventiveMaintenanceChecklist::with('category')
                    ->get()
                    ->mapWithKeys(fn ($checklist) => [
                        $checklist->id => $checklist->category->name . ' - ' . ($checklist->instructions ?: 'No instructions'),
                    ])
                    ->toArray())
                ->multiple()
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state && is_array($state) && count($state) > 0) {
                        $checklist = \App\Models\PreventiveMaintenanceChecklist::find($state[0]);
                        if ($checklist) {
                            $set('category_id', $checklist->category_id);
                        }
                    }
                })
                ->helperText('Select one or more checklist templates to use for this schedule. Category will be auto-filled from the first checklist.'),
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
