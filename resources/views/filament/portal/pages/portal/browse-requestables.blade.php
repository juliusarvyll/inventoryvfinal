<x-filament-panels::page>
    @php
        $sections = [
            'asset' => $assets,
            'license' => $licenses,
            'accessory' => $accessories,
            'consumable' => $consumables,
            'component' => $components,
        ];
    @endphp

    <div class="space-y-8">
        @foreach ($sections as $type => $items)
            <x-filament::section :heading="$labels[$type]" :aside="false">
                @if ($items->isEmpty())
                    <p class="text-sm text-gray-500">No requestable {{ str_replace('_', ' ', $type) }} records are available.</p>
                @else
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $item)
                            <div class="rounded-2xl border border-gray-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-950">{{ $item->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->category?->name ?? class_basename($item) }}</p>
                                    </div>
                                    <x-filament::badge color="success">Requestable</x-filament::badge>
                                </div>

                                <dl class="mt-4 space-y-2 text-sm text-gray-600">
                                    @if (method_exists($item, 'qtyRemaining'))
                                        <div class="flex justify-between gap-3">
                                            <dt class="font-medium text-gray-950">Available</dt>
                                            <dd>{{ $item->qtyRemaining() }}</dd>
                                        </div>
                                    @endif

                                    @if (method_exists($item, 'seatsAvailable'))
                                        <div class="flex justify-between gap-3">
                                            <dt class="font-medium text-gray-950">Seats Available</dt>
                                            <dd>{{ $item->seatsAvailable() }}</dd>
                                        </div>
                                    @endif

                                    @if (isset($item->asset_tag))
                                        <div class="flex justify-between gap-3">
                                            <dt class="font-medium text-gray-950">Asset Tag</dt>
                                            <dd>{{ $item->asset_tag }}</dd>
                                        </div>
                                    @endif

                                    @if (isset($item->location) && $item->location)
                                        <div class="flex justify-between gap-3">
                                            <dt class="font-medium text-gray-950">Location</dt>
                                            <dd>{{ $item->location->name }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
