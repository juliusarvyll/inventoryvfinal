<?php

namespace Database\Factories;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(InventoryCategoryType::cases());

        return [
            'name' => ucfirst($type->value).' '.fake()->unique()->word(),
            'type' => $type,
        ];
    }

    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Asset '.fake()->unique()->word(),
            'type' => InventoryCategoryType::Asset,
        ]);
    }

    public function license(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'License '.fake()->unique()->word(),
            'type' => InventoryCategoryType::License,
        ]);
    }

    public function accessory(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Accessory '.fake()->unique()->word(),
            'type' => InventoryCategoryType::Accessory,
        ]);
    }

    public function consumable(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Consumable '.fake()->unique()->word(),
            'type' => InventoryCategoryType::Consumable,
        ]);
    }

    public function component(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Component '.fake()->unique()->word(),
            'type' => InventoryCategoryType::Component,
        ]);
    }
}
