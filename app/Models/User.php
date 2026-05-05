<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'job_title',
        'phone',
        'location',
        'avatar',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryDepartment(): ?Department
    {
        return $this->departments()
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Check if this user is the head of any department.
     */
    public function isDepartmentHead(): bool
    {
        return Department::query()->where('head_id', $this->getKey())->exists();
    }

    /**
     * Get departments where this user is the head.
     */
    public function headOfDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
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

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['super_admin', 'panel_user']),
            'portal' => true,
            default => false,
        };
    }
}
