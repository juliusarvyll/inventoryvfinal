<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Information & Communications Technology', 'code' => 'ICT'],
            ['name' => 'SNAHS', 'code' => 'SNAHS'],
            ['name' => 'SBAHM', 'code' => 'SBAHM'],
            ['name' => 'SITE', 'code' => 'SITE'],
            ['name' => 'SOM', 'code' => 'SOM'],
            ['name' => 'GRADUATE SCHOOL', 'code' => 'GS'],
            ['name' => 'BEU', 'code' => 'BEU'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                [
                    'name' => $dept['name'],
                    'is_active' => true,
                    'description' => fake()->sentence(),
                ]
            );
        }
    }
}
