<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessoryCheckout>
 */
class AccessoryCheckoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'accessory_id' => Accessory::factory(),
            'assigned_to' => User::factory(),
            'qty' => 1,
            'assigned_at' => now(),
            'returned_at' => null,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
