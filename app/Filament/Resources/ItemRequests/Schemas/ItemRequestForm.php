<?php

namespace App\Filament\Resources\ItemRequests\Schemas;

use App\Enums\ItemRequestStatus;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ItemRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Requested By')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $user = User::query()->find($state);

                        if (! $user) {
                            return;
                        }

                        $set('requested_by', $user->name);
                        $set('department', $user->department);
                    }),
                TextInput::make('requested_by')
                    ->label('Requested By (User Name)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('department')
                    ->required()
                    ->maxLength(255),
                Textarea::make('items')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                TextInput::make('unit_cost')
                    ->label('Unit Cost')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('source_of_fund')
                    ->label('Source of Fund')
                    ->maxLength(255),
                Textarea::make('purpose_project')
                    ->label('Purpose Project')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('remarks')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(ItemRequestStatus::class)
                    ->default(ItemRequestStatus::Pending->value)
                    ->required(),
                Textarea::make('deny_reason')
                    ->columnSpanFull(),
                Select::make('handled_by')
                    ->relationship('handler', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('handled_at'),
                DateTimePicker::make('fulfilled_at'),
            ]);
    }
}
