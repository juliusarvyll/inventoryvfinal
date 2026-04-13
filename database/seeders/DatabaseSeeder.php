<?php

namespace Database\Seeders;

use App\Enums\InventoryCategoryType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        collect(InventoryCategoryType::cases())->each(function (InventoryCategoryType $type): void {
            Category::factory()->create([
                'name' => ucfirst($type->value),
                'type' => $type,
            ]);
        });

        collect([
            ['name' => 'Available', 'color' => '#22c55e', 'type' => 'deployable'],
            ['name' => 'Deployed', 'color' => '#3b82f6', 'type' => 'deployable'],
            ['name' => 'In Repair', 'color' => '#f59e0b', 'type' => 'pending'],
            ['name' => 'Retired', 'color' => '#6b7280', 'type' => 'archived'],
            ['name' => 'Lost/Stolen', 'color' => '#ef4444', 'type' => 'undeployable'],
        ])->each(fn (array $status) => StatusLabel::query()->create($status));

        User::factory()->admin()->create([
            'name' => 'Inventory Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->itStaff()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->count(5)->create([
            'role' => UserRole::EndUser,
        ]);
    }
}
