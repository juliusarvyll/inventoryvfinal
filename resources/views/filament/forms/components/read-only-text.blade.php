@php
    $state = $getState();
@endphp

<div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words">
    {{ filled($state) ? $state : '-' }}
</div>
