<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\LicenseSeat;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = today();
        $totalAssets = Asset::query()->count();
        $deployedAssets = Asset::query()
            ->whereHas('statusLabel', fn ($query) => $query->where('name', 'Deployed'))
            ->count();
        $availableAssets = Asset::query()
            ->whereHas('statusLabel', fn ($query) => $query->where('name', 'Available'))
            ->count();
        $checkedOutAssets = AssetCheckout::query()
            ->whereNull('returned_at')
            ->distinct('asset_id')
            ->count('asset_id');
        $assetUtilization = $totalAssets > 0
            ? (int) round(($checkedOutAssets / $totalAssets) * 100)
            : 0;

        $totalSeats = (int) License::query()->sum('seats');
        $totalLicenses = License::query()->count();
        $usedSeats = LicenseSeat::query()->count();
        $seatUtilization = $totalSeats > 0
            ? (int) round(($usedSeats / $totalSeats) * 100)
            : 0;
        $expiringLicenses = License::query()
            ->whereDate('expiration_date', '>=', $today)
            ->whereDate('expiration_date', '<=', $today->copy()->addDays(30))
            ->count();
        $accessoryAssets = Accessory::query()->count();
        $consumableAssets = Consumable::query()->count();
        $componentAssets = Component::query()->count();
        $requestCounts = ItemRequest::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $pendingRequests = (int) ($requestCounts[ItemRequestStatus::Pending->value] ?? 0);
        $approvedRequests = (int) ($requestCounts[ItemRequestStatus::Approved->value] ?? 0);
        $fulfilledRequests = (int) ($requestCounts[ItemRequestStatus::Fulfilled->value] ?? 0);

        return [
            Stat::make('Assets', number_format($totalAssets))
                ->description("{$availableAssets} available - {$deployedAssets} deployed")
                ->chart([$availableAssets, $checkedOutAssets, $deployedAssets])
                ->color('info'),
            Stat::make('Checked Out Assets', number_format($checkedOutAssets))
                ->description("{$assetUtilization}% of inventory is currently assigned")
                ->chart([$checkedOutAssets, max(0, $totalAssets - $checkedOutAssets)])
                ->color('warning'),
            Stat::make('License Seats', sprintf('%d / %d', $usedSeats, $totalSeats))
                ->description("{$seatUtilization}% utilized - {$expiringLicenses} expiring in 30 days")
                ->chart([$usedSeats, max(0, $totalSeats - $usedSeats), $expiringLicenses])
                ->color('success'),
            Stat::make('Expiring Licenses', number_format($expiringLicenses))
                ->description('Licenses ending within the next 30 days')
                ->chart([$expiringLicenses, max(0, $totalLicenses - $expiringLicenses)])
                ->color($expiringLicenses > 0 ? 'danger' : 'success'),
            Stat::make('Catalog Inventory', number_format($accessoryAssets + $consumableAssets + $componentAssets))
                ->description("{$accessoryAssets} accessories - {$consumableAssets} consumables - {$componentAssets} components")
                ->chart([$accessoryAssets, $consumableAssets, $componentAssets])
                ->color('warning'),
            Stat::make('Requests Pipeline', number_format($pendingRequests))
                ->description("{$approvedRequests} approved - {$fulfilledRequests} fulfilled")
                ->chart([$pendingRequests, $approvedRequests, $fulfilledRequests])
                ->color('danger'),
        ];
    }
}
