<?php

namespace Database\Factories;

use App\Models\PreventiveMaintenanceChecklist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreventiveMaintenanceChecklist>
 */
class PreventiveMaintenanceChecklistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
