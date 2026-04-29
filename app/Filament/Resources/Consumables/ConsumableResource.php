<?php

namespace App\Filament\Resources\Consumables;

use App\Filament\Resources\Consumables\Pages\CreateConsumable;
use App\Filament\Resources\Consumables\Pages\EditConsumable;
use App\Filament\Resources\Consumables\Pages\ListConsumables;
use App\Filament\Resources\Consumables\Pages\ViewConsumable;
use App\Filament\Resources\Consumables\Schemas\ConsumableForm;
use App\Filament\Resources\Consumables\Schemas\ConsumableInfolist;
use App\Filament\Resources\Consumables\Tables\ConsumablesTable;
use App\Models\Consumable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsumableResource extends Resource
{
    protected static ?string $model = Consumable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'supplier', 'location', 'statusLabel']);
    }

    public static function form(Schema $schema): Schema
    {
        return ConsumableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConsumableInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsumablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsumables::route('/'),
            'create' => CreateConsumable::route('/create'),
            'view' => ViewConsumable::route('/{record}'),
            'edit' => EditConsumable::route('/{record}/edit'),
        ];
    }
}
