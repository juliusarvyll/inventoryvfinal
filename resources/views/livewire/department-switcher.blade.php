<div class="px-4 py-2 mt-4">
    <label for="department_switcher" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
        Active Department
    </label>
    <select 
        id="department_switcher" 
        wire:model.live="departmentId" 
        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
    >
        @if($isSuperAdmin)
            <option value="">All Departments (Global)</option>
        @endif
        @foreach($departments as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
</div>
