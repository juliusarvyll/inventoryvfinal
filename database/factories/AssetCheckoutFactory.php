<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetCheckout>
 */
class AssetCheckoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'assigned_to' => User::factory(),
            'checked_out_by' => User::factory()->itStaff(),
            'assigned_at' => now(),
            'returned_at' => null,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
