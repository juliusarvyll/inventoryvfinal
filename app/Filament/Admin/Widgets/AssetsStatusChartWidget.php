<?php

namespace App\Filament\Admin\Widgets;

use App\Models\StatusLabel;
use Filament\Widgets\ChartWidget;

class AssetsStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Asset Status';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = StatusLabel::query()
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Assets',
                    'data' => $statuses->pluck('assets_count')->all(),
                    'backgroundColor' => $statuses->pluck('color')->all(),
                ],
            ],
            'labels' => $statuses->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
