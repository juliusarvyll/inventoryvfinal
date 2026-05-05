<?php

namespace App\Models\Concerns;

use App\Models\Department;
use App\Models\Scopes\DepartmentScope;
use App\Services\DepartmentContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Adds department tenancy to a model.
 *
 * Applies a global scope that filters records by the active department
 * and auto-assigns department_id when creating new records.
 */
trait BelongsToDepartment
{
    public static function bootBelongsToDepartment(): void
    {
        static::addGlobalScope('department', new DepartmentScope);

        static::creating(function (Model $model): void {
            if (! $model->department_id && auth()->check()) {
                $model->department_id = DepartmentContext::currentId();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
