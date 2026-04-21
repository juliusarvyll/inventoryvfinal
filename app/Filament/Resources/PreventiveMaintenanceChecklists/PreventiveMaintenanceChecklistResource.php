<?php

namespace App\Filament\Resources\PreventiveMaintenanceChecklists;

use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\CreatePreventiveMaintenanceChecklist;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\EditPreventiveMaintenanceChecklist;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\ListPreventiveMaintenanceChecklists;
use App\Filament\Resources\PreventiveMaintenanceChecklists\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Schemas\PreventiveMaintenanceChecklistForm;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Tables\PreventiveMaintenanceChecklistsTable;
use App\Models\PreventiveMaintenanceChecklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceChecklistResource extends Resource
{
    protected static ?string $model = PreventiveMaintenanceChecklist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = null;

    public static function getNavigationLabel(): string
    {
        return 'PM Checklists';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'creator', 'updater'])->withCount('items');
    }

    public static function form(Schema $schema): Schema
    {
        return PreventiveMaintenanceChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PreventiveMaintenanceChecklistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPreventiveMaintenanceChecklists::route('/'),
            'create' => CreatePreventiveMaintenanceChecklist::route('/create'),
            'edit' => EditPreventiveMaintenanceChecklist::route('/{record}/edit'),
        ];
    }
}
