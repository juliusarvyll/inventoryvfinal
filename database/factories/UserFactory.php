<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'employee_id' => fake()->unique()->numerify('EMP-####'),
            'job_title' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city(),
            'avatar' => null,
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->departments()->count() === 0) {
                $department = Department::firstOrCreate(
                    ['code' => 'ICT'],
                    ['name' => 'ICT', 'is_active' => true]
                );
                $user->departments()->attach($department->id, ['is_primary' => true]);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            Role::findOrCreate('super_admin', 'web');

            $user->syncRoles(['super_admin']);
        });
    }

    public function itStaff(): static
    {
        return $this->afterCreating(function (User $user): void {
            Role::findOrCreate('panel_user', 'web');

            $user->syncRoles(['panel_user']);
        });
    }
}
