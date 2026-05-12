<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Models\User;

class DepartmentContext
{
    private const SESSION_KEY = 'active_department_id';

    public static function currentId(): ?int
    {
        return session(self::SESSION_KEY);
    }

    public static function set(int $departmentId): void
    {
        session([self::SESSION_KEY => $departmentId]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function current(): ?Department
    {
        $id = self::currentId();

        return $id ? Department::query()->find($id) : null;
    }

    /**
     * Initialize the department context on login.
     * Sets the user's primary department (or first available) as active.
     */
    public static function initializeForUser(User $user): void
    {
        $primary = $user->departments()
            ->wherePivot('is_primary', true)
            ->first();

        if (! $primary) {
            $primary = $user->departments()->first();
        }

        if ($primary) {
            self::set($primary->getKey());
        }
    }
}
