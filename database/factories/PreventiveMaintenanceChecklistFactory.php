<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceChecklist>
 */
class PreventiveMaintenanceChecklistFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (PreventiveMaintenanceChecklist $checklist): void {
            if ($checklist->category_id) {
                $checklist->categories()->syncWithoutDetaching([$checklist->category_id]);
            }
        });
    }

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'instructions' => fake()->optional()->sentence(),
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
