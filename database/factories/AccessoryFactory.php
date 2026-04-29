<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accessory>
 */
class AccessoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Keyboard', 'Mouse', 'Dock']).' '.fake()->word(),
            'category_id' => Category::factory()->accessory(),
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'serial' => fake()->boolean(70) ? fake()->unique()->bothify('ACC-####') : null,
            'purchase_cost' => fake()->randomFloat(2, 10, 300),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'requestable' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
