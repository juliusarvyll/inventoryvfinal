<?php

namespace Database\Factories;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsumableAssignment>
 */
class ConsumableAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'consumable_id' => Consumable::factory(),
            'assigned_to' => User::factory(),
            'qty' => 1,
            'assigned_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
