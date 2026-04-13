<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\License;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Microsoft 365', 'Adobe Creative Cloud', 'AutoCAD']),
            'product_key' => fake()->optional()->bothify('####-####-####-####'),
            'category_id' => Category::factory()->license(),
            'manufacturer_id' => Manufacturer::factory(),
            'license_type' => fake()->randomElement(['per_seat', 'per_device', 'open_license', 'site_license']),
            'seats' => fake()->numberBetween(1, 25),
            'expiration_date' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'purchase_cost' => fake()->randomFloat(2, 50, 2000),
            'order_number' => fake()->bothify('PO-####'),
            'maintained' => fake()->boolean(),
            'requestable' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
