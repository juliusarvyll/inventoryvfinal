<x-filament-widgets::widget>
    <x-filament::section>
        @if ($rows->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white/60 px-4 py-6 text-sm text-gray-600 dark:border-white/15 dark:bg-white/5 dark:text-gray-300">
                All tracked inventory is above its low-stock threshold.
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-white/10 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Type</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Item</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Remaining</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Minimum</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Location</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($rows as $row)
                                <tr class="align-middle">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-300">{{ $row['type'] }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-amber-600 dark:text-amber-400">{{ number_format($row['remaining']) }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-300">{{ number_format($row['minimum']) }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-300">{{ $row['location'] ?: 'Unassigned' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a
                                            href="{{ $row['manage_url'] }}"
                                            class="inline-flex items-center rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                        >
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
