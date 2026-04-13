<?php

namespace App\Filament\Resources\Accessories;

use App\Filament\Resources\Accessories\Pages\CreateAccessory;
use App\Filament\Resources\Accessories\Pages\EditAccessory;
use App\Filament\Resources\Accessories\Pages\ListAccessories;
use App\Filament\Resources\Accessories\Pages\ViewAccessory;
use App\Filament\Resources\Accessories\Schemas\AccessoryForm;
use App\Filament\Resources\Accessories\Schemas\AccessoryInfolist;
use App\Filament\Resources\Accessories\Tables\AccessoriesTable;
use App\Models\Accessory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccessoryResource extends Resource
{
    protected static ?string $model = Accessory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AccessoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccessoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessoriesTable::configure($table);
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
            'index' => ListAccessories::route('/'),
            'create' => CreateAccessory::route('/create'),
            'view' => ViewAccessory::route('/{record}'),
            'edit' => EditAccessory::route('/{record}/edit'),
        ];
    }
}
