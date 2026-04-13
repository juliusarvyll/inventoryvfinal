<x-filament-panels::page>
    <x-filament::section heading="Submitted Requests" :aside="false">
        @if ($requests->isEmpty())
            <p class="text-sm text-gray-500">You have not submitted any inventory requests yet.</p>
        @else
            <div class="space-y-4">
                @foreach ($requests as $request)
                    <div class="rounded-2xl border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-950">
                                    {{ $request->requestable?->name ?? class_basename($request->requestable_type) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Submitted {{ $request->created_at?->diffForHumans() }}
                                    @if ($request->handler)
                                        • handled by {{ $request->handler->name }}
                                    @endif
                                </p>
                            </div>
                            <x-filament::badge
                                :color="match ($request->status->value) {
                                    'pending' => 'warning',
                                    'approved', 'fulfilled' => 'success',
                                    'denied' => 'danger',
                                    'cancelled' => 'gray',
                                    default => 'gray',
                                }"
                            >
                                {{ str($request->status->value)->headline() }}
                            </x-filament::badge>
                        </div>

                        <dl class="mt-4 grid gap-2 text-sm text-gray-600 md:grid-cols-2">
                            <div>
                                <dt class="font-medium text-gray-950">Quantity</dt>
                                <dd>{{ $request->qty }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-950">Handled At</dt>
                                <dd>{{ optional($request->handled_at)->format('M d, Y g:i A') ?? 'Pending' }}</dd>
                            </div>
                            @if ($request->reason)
                                <div class="md:col-span-2">
                                    <dt class="font-medium text-gray-950">Reason</dt>
                                    <dd>{{ $request->reason }}</dd>
                                </div>
                            @endif
                            @if ($request->deny_reason)
                                <div class="md:col-span-2">
                                    <dt class="font-medium text-gray-950">Denial Reason</dt>
                                    <dd>{{ $request->deny_reason }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
