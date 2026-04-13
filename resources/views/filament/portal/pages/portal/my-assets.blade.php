<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-2">
        <x-filament::section heading="Assets" :aside="false">
            @if ($assetCheckouts->isEmpty())
                <p class="text-sm text-gray-500">No active asset checkouts.</p>
            @else
                <div class="space-y-4">
                    @foreach ($assetCheckouts as $checkout)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-950">{{ $checkout->asset->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $checkout->asset->asset_tag }}</p>
                                </div>
                                <x-filament::badge color="info">
                                    {{ $checkout->asset->statusLabel?->name ?? 'Unlabeled' }}
                                </x-filament::badge>
                            </div>

                            <dl class="mt-3 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
                                <div>
                                    <dt class="font-medium text-gray-950">Model</dt>
                                    <dd>{{ $checkout->asset->assetModel?->name ?? 'Unknown model' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-950">Assigned</dt>
                                    <dd>{{ optional($checkout->assigned_at)->format('M d, Y') ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-950">Location</dt>
                                    <dd>{{ $checkout->asset->location?->name ?? 'Unassigned' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-950">Serial</dt>
                                    <dd>{{ $checkout->asset->serial ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="License Seats" :aside="false">
            @if ($licenseSeats->isEmpty())
                <p class="text-sm text-gray-500">No assigned license seats.</p>
            @else
                <div class="space-y-3">
                    @foreach ($licenseSeats as $seat)
                        <div class="rounded-xl border border-gray-200 p-4 text-sm">
                            <p class="font-semibold text-gray-950">{{ $seat->license->name }}</p>
                            <p class="text-gray-500">
                                {{ $seat->license->manufacturer?->name ?? 'No manufacturer' }}
                                @if ($seat->asset)
                                    • Bound to {{ $seat->asset->asset_tag }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Accessories" :aside="false">
            @if ($accessoryCheckouts->isEmpty())
                <p class="text-sm text-gray-500">No active accessory checkouts.</p>
            @else
                <div class="space-y-3">
                    @foreach ($accessoryCheckouts as $checkout)
                        <div class="rounded-xl border border-gray-200 p-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-950">{{ $checkout->accessory->name }}</p>
                                    <p class="text-gray-500">{{ $checkout->accessory->category?->name ?? 'Accessory' }}</p>
                                </div>
                                <x-filament::badge color="gray">
                                    Qty {{ $checkout->qty }}
                                </x-filament::badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Consumables" :aside="false">
            @if ($consumableAssignments->isEmpty())
                <p class="text-sm text-gray-500">No issued consumables.</p>
            @else
                <div class="space-y-3">
                    @foreach ($consumableAssignments as $assignment)
                        <div class="rounded-xl border border-gray-200 p-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-950">{{ $assignment->consumable->name }}</p>
                                    <p class="text-gray-500">{{ $assignment->consumable->category?->name ?? 'Consumable' }}</p>
                                </div>
                                <x-filament::badge color="warning">
                                    Qty {{ $assignment->qty }}
                                </x-filament::badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
