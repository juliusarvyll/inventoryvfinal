<?php

namespace App\Livewire;

use App\Models\Department;
use App\Services\DepartmentContext;
use Livewire\Component;

class DepartmentSwitcher extends Component
{
    public ?int $departmentId = null;

    public function mount()
    {
        $this->departmentId = DepartmentContext::currentId();
    }

    public function updatedDepartmentId($value)
    {
        if ($value) {
            DepartmentContext::set((int) $value);
        } else {
            DepartmentContext::clear();
        }

        $this->redirect(request()->header('Referer') ?? '/admin');
    }

    public function render()
    {
        $user = auth()->user();

        if (! $user) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        if ($user->hasRole('super_admin')) {
            $departments = Department::orderBy('name')->pluck('name', 'id');
        } else {
            $departments = $user->departments()->orderBy('name')->pluck('name', 'departments.id');
        }

        if ($departments->count() <= 1 && ! $user->hasRole('super_admin')) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        return view('livewire.department-switcher', [
            'departments' => $departments,
            'isSuperAdmin' => $user->hasRole('super_admin'),
        ]);
    }
}
