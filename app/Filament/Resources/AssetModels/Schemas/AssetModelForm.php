<?php

namespace App\Filament\Resources\AssetModels\Schemas;

use App\Services\DepartmentContext;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetModelForm
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
                Select::make('manufacturer_id')
                    ->relationship('manufacturer', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('model_number'),
                Textarea::make('serial_numbers')
                    ->label('Bulk Create Assets')
                    ->helperText('Optional. Paste serial numbers separated by commas or new lines to create assets for this model.')
                    ->rows(4),
                FileUpload::make('image')
                    ->image(),
            ]);
    }
}
