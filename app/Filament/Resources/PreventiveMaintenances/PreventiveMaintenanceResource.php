<?php

namespace App\Filament\Resources\PreventiveMaintenances;

use App\Filament\Resources\PreventiveMaintenances\Pages\CreatePreventiveMaintenance;
use App\Filament\Resources\PreventiveMaintenances\Pages\EditPreventiveMaintenance;
use App\Filament\Resources\PreventiveMaintenances\Pages\ListPreventiveMaintenances;
use App\Filament\Resources\PreventiveMaintenances\Pages\ViewPreventiveMaintenance;
use App\Filament\Resources\PreventiveMaintenances\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\PreventiveMaintenances\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceForm;
use App\Filament\Resources\PreventiveMaintenances\Schemas\PreventiveMaintenanceInfolist;
use App\Filament\Resources\PreventiveMaintenances\Tables\PreventiveMaintenancesTable;
use App\Models\PreventiveMaintenance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceResource extends Resource
{
    protected static ?string $model = PreventiveMaintenance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = null;

    public static function getNavigationLabel(): string
    {
        return 'Preventive Maintenance';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['location', 'categories', 'creator', 'updater'])
            ->withCount([
                'items',
                'assets',
                'items as completed_items_count' => fn (Builder $query) => $query->where('is_completed', true),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return PreventiveMaintenanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PreventiveMaintenanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PreventiveMaintenancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssetsRelationManager::class,
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPreventiveMaintenances::route('/'),
            'create' => CreatePreventiveMaintenance::route('/create'),
            'view' => ViewPreventiveMaintenance::route('/{record}'),
            'edit' => EditPreventiveMaintenance::route('/{record}/edit'),
        ];
    }
}
