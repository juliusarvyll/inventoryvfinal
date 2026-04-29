<?php

namespace App\Filament\Resources\ItemRequests;

use App\Filament\Resources\ItemRequests\Pages\CreateItemRequest;
use App\Filament\Resources\ItemRequests\Pages\EditItemRequest;
use App\Filament\Resources\ItemRequests\Pages\ListItemRequests;
use App\Filament\Resources\ItemRequests\Pages\ViewItemRequest;
use App\Filament\Resources\ItemRequests\Schemas\ItemRequestForm;
use App\Filament\Resources\ItemRequests\Schemas\ItemRequestInfolist;
use App\Filament\Resources\ItemRequests\Tables\ItemRequestsTable;
use App\Models\ItemRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemRequestResource extends Resource
{
    protected static ?string $model = ItemRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'handler']);
    }

    public static function form(Schema $schema): Schema
    {
        return ItemRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ItemRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemRequestsTable::configure($table);
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
            'index' => ListItemRequests::route('/'),
            'create' => CreateItemRequest::route('/create'),
            'view' => ViewItemRequest::route('/{record}'),
            'edit' => EditItemRequest::route('/{record}/edit'),
        ];
    }
}
