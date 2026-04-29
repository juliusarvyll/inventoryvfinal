<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LowStockWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.low-stock-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function getViewData(): array
    {
        return [
            'rows' => collect(),
        ];
    }
}
