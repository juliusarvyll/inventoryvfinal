<?php

namespace App\Filament\Resources\StatusLabels;

use App\Filament\Resources\StatusLabels\Pages\CreateStatusLabel;
use App\Filament\Resources\StatusLabels\Pages\EditStatusLabel;
use App\Filament\Resources\StatusLabels\Pages\ListStatusLabels;
use App\Filament\Resources\StatusLabels\Pages\ViewStatusLabel;
use App\Filament\Resources\StatusLabels\Schemas\StatusLabelForm;
use App\Filament\Resources\StatusLabels\Schemas\StatusLabelInfolist;
use App\Filament\Resources\StatusLabels\Tables\StatusLabelsTable;
use App\Models\StatusLabel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatusLabelResource extends Resource
{
    protected static ?string $model = StatusLabel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('assets');
    }

    public static function form(Schema $schema): Schema
    {
        return StatusLabelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StatusLabelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusLabelsTable::configure($table);
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
            'index' => ListStatusLabels::route('/'),
            'create' => CreateStatusLabel::route('/create'),
            'view' => ViewStatusLabel::route('/{record}'),
            'edit' => EditStatusLabel::route('/{record}/edit'),
        ];
    }
}
