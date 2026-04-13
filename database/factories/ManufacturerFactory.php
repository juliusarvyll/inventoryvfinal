<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'url' => fake()->optional()->url(),
            'support_url' => fake()->optional()->url(),
            'support_phone' => fake()->optional()->phoneNumber(),
            'image' => null,
        ];
    }
}
