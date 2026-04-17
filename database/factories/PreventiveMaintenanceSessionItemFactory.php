<?php

namespace Database\Factories;

use App\Models\PreventiveMaintenanceItem;
use App\Models\PreventiveMaintenanceSession;
use App\Models\PreventiveMaintenanceSessionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceSessionItem>
 */
class PreventiveMaintenanceSessionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'preventive_maintenance_session_id' => PreventiveMaintenanceSession::factory(),
            'preventive_maintenance_item_id' => PreventiveMaintenanceItem::factory(),
            'task' => fake()->sentence(),
            'input_label' => fake()->optional()->words(2, true),
            'input_value' => fake()->optional()->word(),
            'is_required' => true,
            'is_passed' => fake()->optional()->boolean(),
            'item_notes' => fake()->optional()->sentence(),
            'evidence_path' => null,
            'sort_order' => 1,
        ];
    }
}
