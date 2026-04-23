@php
$state = $getState();
@endphp

<div class="fi-fo-field-wrp">
    @if ($state)
        <div class="mt-2">
            <img src="{{ Storage::disk('public')->url($state) }}" alt="Evidence" class="max-w-full rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" style="max-height: 300px;">
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">No evidence uploaded</p>
    @endif
</div>
