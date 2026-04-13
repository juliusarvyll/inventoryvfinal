<?php

namespace App\Filament\Resources\StatusLabels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StatusLabelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('color'),
                TextInput::make('type')
                    ->required(),
            ]);
    }
}
