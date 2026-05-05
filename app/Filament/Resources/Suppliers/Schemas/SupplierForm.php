<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Services\DepartmentContext;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
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
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('url')
                    ->url(),
            ]);
    }
}
