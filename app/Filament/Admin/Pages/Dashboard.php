<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AssetsByCategoryWidget;
use App\Filament\Admin\Widgets\AssetsStatusChartWidget;
use App\Filament\Admin\Widgets\ExpiringLicensesWidget;
use App\Filament\Admin\Widgets\LowStockWidget;
use App\Filament\Admin\Widgets\RecentRequestsWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            AssetsStatusChartWidget::class,
            AssetsByCategoryWidget::class,
            RecentRequestsWidget::class,
            LowStockWidget::class,
            ExpiringLicensesWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}

