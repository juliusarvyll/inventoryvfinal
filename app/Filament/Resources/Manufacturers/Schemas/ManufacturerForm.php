<?php

namespace App\Filament\Resources\Manufacturers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ManufacturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('url')
                    ->url(),
                TextInput::make('support_url')
                    ->url(),
                TextInput::make('support_phone')
                    ->tel(),
                FileUpload::make('image')
                    ->image(),
            ]);
    }
}
