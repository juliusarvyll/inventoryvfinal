<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Department;
use App\Models\Location;
use App\Models\StatusLabel;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'asset_tag' => fake()->unique()->bothify('ICT-#####'),
            'name' => fake()->randomElement(['Laptop', 'Desktop', 'Monitor']).' '.fake()->word(),
            'department_id' => Department::factory(),
            'asset_model_id' => AssetModel::factory(),
            'category_id' => function () {
                return Category::firstOrCreate(
                    ['name' => 'Asset', 'type' => 'asset']
                )->id;
            },
            'status_label_id' => function () {
                return StatusLabel::firstOrCreate(
                    ['name' => 'Available'],
                    ['color' => '#22c55e', 'type' => 'deployable']
                )->id;
            },
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'serial' => fake()->boolean(70) ? fake()->unique()->bothify('SERIAL-####') : null,
            'purchase_cost' => fake()->randomFloat(2, 300, 2500),
            'purchase_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'warranty_expires' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'eol_date' => fake()->dateTimeBetween('+2 years', '+5 years'),
            'notes' => fake()->optional()->sentence(),
            'requestable' => true,
        ];
    }
}
