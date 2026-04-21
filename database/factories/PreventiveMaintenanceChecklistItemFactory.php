<?php

namespace Database\Factories;

use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceChecklistItem>
 */
class PreventiveMaintenanceChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'preventive_maintenance_checklist_id' => PreventiveMaintenanceChecklist::factory(),
            'task' => fake()->sentence(6),
            'input_label' => fake()->optional()->words(2, true),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_required' => fake()->boolean(80),
        ];
    }
}
