@php
    use Illuminate\Support\Facades\Storage;

    $items = $getState() ?? [];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
            <colgroup>
                <col class="w-36">
                <col>
                <col class="w-64">
                <col class="w-40">
            </colgroup>
            <thead class="bg-gray-50 dark:bg-gray-800/70">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                        Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                        Task
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                        Notes
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                        Evidence
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($items as $item)
                    @php
                        $result = strtolower((string) ($item['result'] ?? 'pending'));
                        $evidencePath = $item['evidence_path'] ?? null;
                        $evidenceUrl = filled($evidencePath) ? Storage::disk('public')->url($evidencePath) : null;
                        $isImage = filled($evidencePath) && preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg)$/i', (string) $evidencePath);
                    @endphp
                    <tr class="align-top odd:bg-white even:bg-gray-50/40 dark:odd:bg-gray-900 dark:even:bg-gray-800/30">
                        <td class="px-4 py-3">
                            <div class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-semibold
                                @if ($result === 'pass') border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/70 dark:bg-emerald-950/30 dark:text-emerald-400
                                @elseif ($result === 'fail') border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/70 dark:bg-rose-950/30 dark:text-rose-400
                                @else border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/70 dark:bg-amber-950/30 dark:text-amber-400 @endif">
                                @if ($result === 'pass')
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.793-1.617-1.618a.75.75 0 1 0-1.06 1.061l2.236 2.236a.75.75 0 0 0 1.137-.09l4-5.5Z" clip-rule="evenodd"/>
                                    </svg>
                                    Passed
                                @elseif ($result === 'fail')
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.03-10.28a.75.75 0 0 0-1.06-1.06L10 8.69 8.03 6.72a.75.75 0 0 0-1.06 1.06L8.94 9.75 6.97 11.72a.75.75 0 1 0 1.06 1.06L10 10.81l1.97 1.97a.75.75 0 0 0 1.06-1.06l-1.97-1.97 1.97-1.97Z" clip-rule="evenodd"/>
                                    </svg>
                                    Failed
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin">
                                        <circle cx="12" cy="12" r="9" class="opacity-20" stroke="currentColor" stroke-width="2"></circle>
                                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    Pending
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                            <span class="block break-words">{{ $item['task'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            <span class="block whitespace-pre-wrap break-words">{{ $item['item_notes'] ?: '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if (filled($evidenceUrl) && $isImage)
                                <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block">
                                    <img
                                        src="{{ $evidenceUrl }}"
                                        alt="Evidence image"
                                        class="h-16 w-28 rounded-lg border border-gray-200 object-cover shadow-sm transition hover:opacity-90 dark:border-gray-700"
                                    >
                                </a>
                            @elseif (filled($evidenceUrl))
                                <a
                                    href="{{ $evidenceUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    View file
                                </a>
                            @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No checklist results available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dynamic-component>
