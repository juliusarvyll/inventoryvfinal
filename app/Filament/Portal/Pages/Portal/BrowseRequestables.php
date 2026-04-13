<?php

namespace App\Filament\Portal\Pages\Portal;

use App\Enums\InventoryCategoryType;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class BrowseRequestables extends Page
{
    protected string $view = 'filament.portal.pages.portal.browse-requestables';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Browse Requestables';

    protected static ?int $navigationSort = 2;

    public function getTitle(): string|Htmlable
    {
        return 'Requestable Inventory';
    }

    /**
     * @return array<string, Collection<int, mixed>>
     */
    protected function getViewData(): array
    {
        return [
            'assets' => Asset::query()
                ->with(['assetModel.manufacturer', 'category', 'statusLabel', 'location'])
                ->where('requestable', true)
                ->latest('asset_tag')
                ->get(),
            'licenses' => License::query()
                ->with(['manufacturer', 'category'])
                ->where('requestable', true)
                ->latest('name')
                ->get(),
            'accessories' => Accessory::query()
                ->with(['category', 'location'])
                ->where('requestable', true)
                ->latest('name')
                ->get(),
            'consumables' => Consumable::query()
                ->with(['category', 'location'])
                ->where('requestable', true)
                ->latest('name')
                ->get(),
            'components' => Component::query()
                ->with(['category', 'location'])
                ->where('requestable', true)
                ->latest('name')
                ->get(),
            'labels' => [
                InventoryCategoryType::Asset->value => 'Assets',
                InventoryCategoryType::License->value => 'Licenses',
                InventoryCategoryType::Accessory->value => 'Accessories',
                InventoryCategoryType::Consumable->value => 'Consumables',
                InventoryCategoryType::Component->value => 'Components',
            ],
        ];
    }
}
