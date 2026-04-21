<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use Filament\Widgets\ChartWidget;

class RequestStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Request Pipeline';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $counts = ItemRequest::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statuses = collect(ItemRequestStatus::cases());

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => $statuses
                        ->map(fn (ItemRequestStatus $status): int => (int) ($counts[$status->value] ?? 0))
                        ->all(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#3b82f6',
                        '#ef4444',
                        '#10b981',
                        '#6b7280',
                    ],
                ],
            ],
            'labels' => $statuses
                ->map(fn (ItemRequestStatus $status): string => $status->name)
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
