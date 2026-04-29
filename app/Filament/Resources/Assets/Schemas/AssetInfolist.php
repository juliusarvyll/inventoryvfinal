<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Filament\Resources\Accessories\AccessoryResource;
use App\Filament\Resources\AssetModels\AssetModelResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Components\ComponentResource;
use App\Filament\Resources\Consumables\ConsumableResource;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Asset;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->schema([
                        TextEntry::make('asset_tag'),
                        TextEntry::make('name'),
                        TextEntry::make('serial')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Classification')
                    ->schema([
                        TextEntry::make('assetModel.name')
                            ->label('Asset model')
                            ->url(fn (Asset $record): ?string => $record->asset_model_id
                                ? AssetModelResource::getUrl('view', ['record' => $record->asset_model_id])
                                : null),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->url(fn (Asset $record): ?string => $record->category_id
                                ? CategoryResource::getUrl('view', ['record' => $record->category_id])
                                : null),
                        TextEntry::make('statusLabel.name')
                            ->label('Status')
                            ->badge()
                            ->color(function (Asset $record): array|string|null {
                                $hex = $record->statusLabel?->color;

                                if (! is_string($hex) || $hex === '') {
                                    return null;
                                }

                                $normalized = str_starts_with($hex, '#') ? $hex : '#'.$hex;

                                return Color::hex($normalized);
                            }),
                    ])
                    ->columns(2),
                Section::make('Placement')
                    ->schema([
                        TextEntry::make('location.name')
                            ->label('Location')
                            ->placeholder('—')
                            ->url(fn (Asset $record): ?string => $record->location_id
                                ? LocationResource::getUrl('view', ['record' => $record->location_id])
                                : null),
                        TextEntry::make('supplier.name')
                            ->label('Supplier')
                            ->placeholder('—')
                            ->url(fn (Asset $record): ?string => $record->supplier_id
                                ? SupplierResource::getUrl('view', ['record' => $record->supplier_id])
                                : null),
                    ])
                    ->columns(2),
                Section::make('Checkout')
                    ->description('Current assignment when the asset is checked out.')
                    ->schema([
                        TextEntry::make('activeCheckout.assignee.name')
                            ->label('Assigned to')
                            ->placeholder('Not checked out'),
                        TextEntry::make('activeCheckout.checkedOutBy.name')
                            ->label('Checked out by')
                            ->placeholder('—'),
                        TextEntry::make('activeCheckout.assigned_at')
                            ->label('Assigned at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('activeCheckout.note')
                            ->label('Checkout note')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Procurement')
                    ->schema([
                        TextEntry::make('purchase_cost')
                            ->money()
                            ->placeholder('—'),
                        TextEntry::make('purchase_date')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('warranty_expires')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('eol_date')
                            ->date()
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        IconEntry::make('requestable')
                            ->boolean(),
                    ]),
                Section::make('Inventory Views')
                    ->description('Open the consolidated accessory, component, and consumable views that now read from assets.')
                    ->schema([
                        TextEntry::make('accessories_link')
                            ->label('Accessories')
                            ->state('Open accessories view')
                            ->url(AccessoryResource::getUrl('index')),
                        TextEntry::make('components_link')
                            ->label('Components')
                            ->state('Open components view')
                            ->url(ComponentResource::getUrl('index')),
                        TextEntry::make('consumables_link')
                            ->label('Consumables')
                            ->state('Open consumables view')
                            ->url(ConsumableResource::getUrl('index')),
                    ])
                    ->columns(3),
                Section::make('Record')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
