<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules;

use App\Filament\Resources\PreventiveMaintenanceSchedules\Pages\CreatePreventiveMaintenanceSchedule;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Pages\EditPreventiveMaintenanceSchedule;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Pages\ListPreventiveMaintenanceSchedules;
use App\Filament\Resources\PreventiveMaintenanceSchedules\RelationManagers\ExecutionsRelationManager;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas\PreventiveMaintenanceScheduleForm;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Tables\PreventiveMaintenanceSchedulesTable;
use App\Models\PreventiveMaintenanceSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceScheduleResource extends Resource
{
    protected static ?string $model = PreventiveMaintenanceSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = null;

    public static function getNavigationLabel(): string
    {
        return 'PM Schedules';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['location', 'checklists.categories', 'creator', 'updater'])->withCount('executions');
    }

    public static function form(Schema $schema): Schema
    {
        return PreventiveMaintenanceScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PreventiveMaintenanceSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExecutionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPreventiveMaintenanceSchedules::route('/'),
            'create' => CreatePreventiveMaintenanceSchedule::route('/create'),
            'edit' => EditPreventiveMaintenanceSchedule::route('/{record}/edit'),
        ];
    }
}
