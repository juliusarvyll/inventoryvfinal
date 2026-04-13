<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Component>
 */
class ComponentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['RAM', 'SSD', 'Battery']).' '.fake()->word(),
            'category_id' => Category::factory()->component(),
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'qty' => fake()->numberBetween(2, 40),
            'min_qty' => fake()->numberBetween(0, 5),
            'serial' => fake()->boolean(70) ? fake()->unique()->bothify('CMP-####') : null,
            'purchase_cost' => fake()->randomFloat(2, 20, 600),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'order_number' => fake()->bothify('PO-####'),
            'requestable' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
