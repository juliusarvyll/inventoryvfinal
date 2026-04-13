<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseSeat>
 */
class LicenseSeatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'assigned_to' => User::factory(),
            'asset_id' => null,
            'assigned_at' => now(),
        ];
    }
}
