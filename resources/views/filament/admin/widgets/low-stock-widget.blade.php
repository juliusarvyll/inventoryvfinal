<x-filament-widgets::widget>
    <x-filament::section heading="Low Stock">
        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500">No inventory items are below their minimum quantity.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2 font-medium">Type</th>
                            <th class="px-3 py-2 font-medium">Name</th>
                            <th class="px-3 py-2 font-medium">Remaining</th>
                            <th class="px-3 py-2 font-medium">Minimum</th>
                            <th class="px-3 py-2 font-medium">Location</th>
                            <th class="px-3 py-2 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['type'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-950">{{ $row['name'] }}</td>
                                <td class="px-3 py-2">{{ $row['remaining'] }}</td>
                                <td class="px-3 py-2">{{ $row['minimum'] }}</td>
                                <td class="px-3 py-2">{{ $row['location'] ?: 'Unassigned' }}</td>
                                <td class="px-3 py-2">
                                    <a
                                        href="{{ $row['manage_url'] }}"
                                        class="text-primary-600 hover:text-primary-500 font-medium"
                                    >
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
