<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
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
            'asset_model_id' => AssetModel::factory(),
            'category_id' => Category::factory()->asset(),
            'status_label_id' => StatusLabel::factory()->available(),
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
