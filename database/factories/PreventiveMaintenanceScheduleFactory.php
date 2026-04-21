<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Location;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceSchedule>
 */
class PreventiveMaintenanceScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'category_id' => Category::factory(),
            'preventive_maintenance_checklist_id' => PreventiveMaintenanceChecklist::factory(),
            'scheduled_for' => fake()->optional()->date(),
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
