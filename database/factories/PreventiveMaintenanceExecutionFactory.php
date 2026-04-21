<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceExecution>
 */
class PreventiveMaintenanceExecutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'preventive_maintenance_schedule_id' => PreventiveMaintenanceSchedule::factory(),
            'preventive_maintenance_checklist_id' => PreventiveMaintenanceChecklist::factory(),
            'location_id' => Location::factory(),
            'category_id' => Category::factory(),
            'asset_id' => Asset::factory(),
            'status' => fake()->randomElement(['pending', 'completed', 'needs_attention']),
            'scheduled_for' => fake()->optional()->date(),
            'started_at' => now(),
            'completed_at' => fake()->boolean(70) ? now() : null,
            'performed_by' => User::factory(),
            'general_notes' => fake()->optional()->sentence(),
        ];
    }
}
