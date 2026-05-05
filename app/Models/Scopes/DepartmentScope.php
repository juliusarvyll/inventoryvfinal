<?php

namespace App\Models\Scopes;

use App\Services\DepartmentContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DepartmentScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if ($user->hasRole('super_admin')) {
            // Super admins may optionally filter by a selected department
            $departmentId = DepartmentContext::currentId();

            if ($departmentId) {
                $builder->where($model->getTable().'.department_id', $departmentId);
            }

            return;
        }

        $departmentId = DepartmentContext::currentId();

        if ($departmentId) {
            $builder->where($model->getTable().'.department_id', $departmentId);
        }
    }
}
