<?php

namespace Database\Factories;

use App\Models\PreventiveMaintenanceExecution;
use App\Models\PreventiveMaintenanceExecutionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceExecutionItem>
 */
class PreventiveMaintenanceExecutionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'preventive_maintenance_execution_id' => PreventiveMaintenanceExecution::factory(),
            'preventive_maintenance_checklist_item_id' => null,
            'task' => fake()->sentence(6),
            'input_label' => fake()->optional()->words(2, true),
            'input_value' => fake()->optional()->words(2, true),
            'is_required' => fake()->boolean(80),
            'is_passed' => fake()->optional()->boolean(),
            'item_notes' => fake()->optional()->sentence(),
            'evidence_path' => null,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
