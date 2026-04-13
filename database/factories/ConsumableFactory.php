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
            'qty' => fake()->numberBetween(5, 100),
            'min_qty' => fake()->numberBetween(0, 10),
            'model_number' => fake()->bothify('CON-###'),
            'item_no' => fake()->bothify('SKU-####'),
            'purchase_cost' => fake()->randomFloat(2, 5, 200),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'order_number' => fake()->bothify('PO-####'),
            'requestable' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
