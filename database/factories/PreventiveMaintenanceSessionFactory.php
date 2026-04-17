<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceSession>
 */
class PreventiveMaintenanceSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'preventive_maintenance_id' => PreventiveMaintenance::factory(),
            'asset_id' => Asset::factory(),
            'template_version' => 1,
            'status' => fake()->randomElement(['pending', 'completed', 'needs_attention']),
            'started_at' => now(),
            'completed_at' => fake()->boolean(70) ? now() : null,
            'performed_by' => User::factory(),
            'general_notes' => fake()->optional()->sentence(),
        ];
    }
}
