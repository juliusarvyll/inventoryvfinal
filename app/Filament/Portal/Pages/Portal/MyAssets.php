<?php

namespace App\Filament\Portal\Pages\Portal;

use App\Enums\InventoryCategoryType;
use App\Models\AssetCheckout;
use App\Models\LicenseSeat;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MyAssets extends Page
{
    private const MAX_RESULTS = 25;

    protected string $view = 'filament.portal.pages.portal.my-assets';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'My Assets';

    protected static ?int $navigationSort = 1;

    public function getTitle(): string|Htmlable
    {
        return 'Assigned Inventory';
    }

    /**
     * @return array<string, Collection<int, mixed>>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();

        return [
            'assetCheckouts' => AssetCheckout::query()
                ->with(['asset.assetModel.manufacturer', 'asset.statusLabel', 'asset.location'])
                ->where('assigned_to', $user?->getAuthIdentifier())
                ->whereNull('returned_at')
                ->whereHas('asset.category', fn ($query) => $query->where('type', InventoryCategoryType::Asset))
                ->latest('assigned_at')
                ->limit(self::MAX_RESULTS)
                ->get(),
            'licenseSeats' => LicenseSeat::query()
                ->with(['license.manufacturer', 'asset'])
                ->where('assigned_to', $user?->getAuthIdentifier())
                ->latest('assigned_at')
                ->limit(self::MAX_RESULTS)
                ->get(),
            'accessoryCheckouts' => AssetCheckout::query()
                ->with(['asset.category', 'asset.location'])
                ->where('assigned_to', $user?->getAuthIdentifier())
                ->whereNull('returned_at')
                ->whereHas('asset.category', fn ($query) => $query->where('type', InventoryCategoryType::Accessory))
                ->latest('assigned_at')
                ->limit(self::MAX_RESULTS)
                ->get(),
            'consumableAssignments' => AssetCheckout::query()
                ->with(['asset.category', 'asset.location'])
                ->where('assigned_to', $user?->getAuthIdentifier())
                ->whereNull('returned_at')
                ->whereHas('asset.category', fn ($query) => $query->where('type', InventoryCategoryType::Consumable))
                ->latest('assigned_at')
                ->limit(self::MAX_RESULTS)
                ->get(),
        ];
    }
}
