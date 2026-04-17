<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\ItemRequest;
use App\Models\License;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalAssets = Asset::query()->count();
        $deployedAssets = Asset::query()
            ->whereHas('statusLabel', fn ($query) => $query->where('name', 'Deployed'))
            ->count();
        $availableAssets = Asset::query()
            ->whereHas('statusLabel', fn ($query) => $query->where('name', 'Available'))
            ->count();

        $totalSeats = (int) License::query()->sum('seats');
        $usedSeats = License::query()->get()->sum(fn (License $license): int => $license->assignedSeatsCount());
        $accessoriesRemaining = Accessory::query()->get()->sum(fn (Accessory $accessory): int => $accessory->qtyRemaining());
        $pendingRequests = ItemRequest::query()->where('status', ItemRequestStatus::Pending)->count();

        return [
            Stat::make('Assets', number_format($totalAssets))
                ->description("{$availableAssets} available - {$deployedAssets} deployed")
                ->color('info'),
            Stat::make('License Seats', sprintf('%d / %d', $usedSeats, $totalSeats))
                ->description('Used versus purchased seats')
                ->color('success'),
            Stat::make('Accessories Remaining', number_format($accessoriesRemaining))
                ->description('Current stock across accessory records')
                ->color('warning'),
            Stat::make('Open Requests', number_format($pendingRequests))
                ->description('Pending approvals')
                ->color('danger'),
        ];
    }
}
