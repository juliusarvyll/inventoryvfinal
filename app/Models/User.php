<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'employee_id',
    'department',
    'job_title',
    'phone',
    'location',
    'avatar',
    'is_active',
    'role',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'role' => UserRole::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function assetCheckouts(): HasMany
    {
        return $this->hasMany(AssetCheckout::class, 'assigned_to');
    }

    public function licenseSeats(): HasMany
    {
        return $this->hasMany(LicenseSeat::class, 'assigned_to');
    }

    public function accessoryCheckouts(): HasMany
    {
        return $this->hasMany(AccessoryCheckout::class, 'assigned_to');
    }

    public function consumableAssignments(): HasMany
    {
        return $this->hasMany(ConsumableAssignment::class, 'assigned_to');
    }

    public function itemRequests(): HasMany
    {
        return $this->hasMany(ItemRequest::class);
    }

    public function handledItemRequests(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'handled_by');
    }

    public function hasRole(UserRole|string $role): bool
    {
        $expected = $role instanceof UserRole ? $role : UserRole::tryFrom($role);

        return $expected !== null && $this->role === $expected;
    }

    /**
     * @param  array<int, UserRole|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole([UserRole::Admin, UserRole::ItStaff]),
            'portal' => true,
            default => false,
        };
    }
}
