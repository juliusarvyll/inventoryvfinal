<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ShieldSeeder::class);

        User::factory()->admin()->create([
            'name' => 'Inventory Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->itStaff()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
