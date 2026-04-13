<?php

namespace App\Filament\Resources\ItemRequests\Schemas;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ItemRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('requester_name')
                    ->label('Requester Name')
                    ->required(),
                MorphToSelect::make('requestable')
                    ->label('Requested Item')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->types([
                        Type::make(Asset::class)
                            ->label('Asset')
                            ->titleAttribute('name')
                            ->modifyOptionsQueryUsing(fn (Builder $query): Builder => $query
                                ->where('requestable', true)
                                ->orderBy('asset_tag')
                                ->orderBy('name'))
                            ->getOptionLabelFromRecordUsing(fn (Asset $record): string => "{$record->asset_tag} - {$record->name}"),
                        Type::make(License::class)
                            ->label('License')
                            ->titleAttribute('name')
                            ->modifyOptionsQueryUsing(fn (Builder $query): Builder => $query
                                ->where('requestable', true)
                                ->orderBy('name')),
                        Type::make(Accessory::class)
                            ->label('Accessory')
                            ->titleAttribute('name')
                            ->modifyOptionsQueryUsing(fn (Builder $query): Builder => $query
                                ->where('requestable', true)
                                ->orderBy('name')),
                        Type::make(Consumable::class)
                            ->label('Consumable')
                            ->titleAttribute('name')
                            ->modifyOptionsQueryUsing(fn (Builder $query): Builder => $query
                                ->where('requestable', true)
                                ->orderBy('name')),
                        Type::make(Component::class)
                            ->label('Component')
                            ->titleAttribute('name')
                            ->modifyOptionsQueryUsing(fn (Builder $query): Builder => $query
                                ->where('requestable', true)
                                ->orderBy('name')),
                    ]),
                Select::make('status')
                    ->options(ItemRequestStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('reason')
                    ->columnSpanFull(),
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
