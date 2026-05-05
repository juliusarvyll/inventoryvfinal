<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Models\Asset;
use App\Services\DepartmentContext;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required()
                    ->default(fn () => DepartmentContext::currentId())
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->searchable()
                    ->preload(),
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('country'),
                Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                Repeater::make('assets')
                    ->label('Assign Assets')
                    ->relationship()
                    ->schema([
                        Select::make('asset_id')
                            ->label('Asset')
                            ->options(fn (): Collection => Asset::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->collapsible()
                    ->columnSpanFull()
                    ->hidden(true),
            ]);
    }
}
