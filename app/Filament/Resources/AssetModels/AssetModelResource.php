<?php

namespace App\Filament\Resources\AssetModels;

use App\Filament\Resources\AssetModels\Pages\CreateAssetModel;
use App\Filament\Resources\AssetModels\Pages\EditAssetModel;
use App\Filament\Resources\AssetModels\Pages\ListAssetModels;
use App\Filament\Resources\AssetModels\Pages\ViewAssetModel;
use App\Filament\Resources\AssetModels\Schemas\AssetModelForm;
use App\Filament\Resources\AssetModels\Schemas\AssetModelInfolist;
use App\Filament\Resources\AssetModels\Tables\AssetModelsTable;
use App\Models\AssetModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetModelResource extends Resource
{
    protected static ?string $model = AssetModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['manufacturer', 'category'])
            ->withCount('assets');
    }

    public static function form(Schema $schema): Schema
    {
        return AssetModelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetModelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetModelsTable::configure($table);
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
            'index' => ListAssetModels::route('/'),
            'create' => CreateAssetModel::route('/create'),
            'view' => ViewAssetModel::route('/{record}'),
            'edit' => EditAssetModel::route('/{record}/edit'),
        ];
    }
}
