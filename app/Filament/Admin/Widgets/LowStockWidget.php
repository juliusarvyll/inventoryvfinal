<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Accessory;
use App\Models\Component;
use App\Models\Consumable;
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
        $rows = collect();

        Accessory::query()->with('location')->lowStock()->get()->each(function (Accessory $accessory) use ($rows): void {
            $rows->push([
                'type' => 'Accessory',
                'name' => $accessory->name,
                'remaining' => $accessory->qtyRemaining(),
                'minimum' => $accessory->min_qty,
                'location' => $accessory->location?->name,
                'manage_url' => route('filament.admin.resources.accessories.edit', ['record' => $accessory]),
            ]);
        });

        Consumable::query()->with('location')->lowStock()->get()->each(function (Consumable $consumable) use ($rows): void {
            $rows->push([
                'type' => 'Consumable',
                'name' => $consumable->name,
                'remaining' => $consumable->qtyRemaining(),
                'minimum' => $consumable->min_qty,
                'location' => $consumable->location?->name,
                'manage_url' => route('filament.admin.resources.consumables.edit', ['record' => $consumable]),
            ]);
        });

        Component::query()->with('location')->lowStock()->get()->each(function (Component $component) use ($rows): void {
            $rows->push([
                'type' => 'Component',
                'name' => $component->name,
                'remaining' => $component->qtyRemaining(),
                'minimum' => $component->min_qty,
                'location' => $component->location?->name,
                'manage_url' => route('filament.admin.resources.components.edit', ['record' => $component]),
            ]);
        });

        return [
            'rows' => $rows->sortBy(['type', 'name'])->values(),
        ];
    }
}
