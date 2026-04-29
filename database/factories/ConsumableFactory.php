<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consumable>
 */
class ConsumableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Toner', 'Paper', 'Battery']).' '.fake()->word(),
            'category_id' => Category::factory()->consumable(),
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'serial' => fake()->boolean(60) ? fake()->unique()->bothify('CON-####') : null,
            'purchase_cost' => fake()->randomFloat(2, 5, 200),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'requestable' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
