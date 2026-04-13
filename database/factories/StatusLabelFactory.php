<?php

namespace Database\Factories;

use App\Models\StatusLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatusLabel>
 */
class StatusLabelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Available', 'Deployed', 'In Repair', 'Retired', 'Lost/Stolen']),
            'color' => fake()->safeHexColor(),
            'type' => fake()->randomElement(['deployable', 'pending', 'archived', 'undeployable']),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Available',
            'color' => '#22c55e',
            'type' => 'deployable',
        ]);
    }

    public function deployed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Deployed',
            'color' => '#3b82f6',
            'type' => 'deployable',
        ]);
    }
}
