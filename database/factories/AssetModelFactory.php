<?php

namespace Database\Factories;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Department;
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
            'department_id' => Department::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'category_id' => function () {
                return Category::firstOrCreate(
                    ['name' => 'Asset', 'type' => 'asset']
                )->id;
            },
            'model_number' => fake()->bothify('MODEL-###??'),
            'image' => null,
        ];
    }
}
