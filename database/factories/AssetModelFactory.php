<?php

namespace Database\Factories;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetModel>
 */
class AssetModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word().' '.fake()->randomElement(['Pro', 'Air', 'Elite']),
            'manufacturer_id' => Manufacturer::factory(),
            'category_id' => Category::factory()->asset(),
            'model_number' => fake()->bothify('MODEL-###??'),
            'image' => null,
        ];
    }
}
