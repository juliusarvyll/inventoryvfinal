<?php

use App\Models\PreventiveMaintenance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        PreventiveMaintenance::query()
            ->with(['categories', 'items'])
            ->orderBy('id')
            ->lazyById()
            ->each(function (PreventiveMaintenance $preventiveMaintenance): void {
                foreach ($preventiveMaintenance->categories as $category) {
                    $checklistId = DB::table('preventive_maintenance_checklists')
                        ->where('category_id', $category->getKey())
                        ->value('id');

                    if (! $checklistId) {
                        $checklistId = (int) DB::table('preventive_maintenance_checklists')->insertGetId([
                            'category_id' => $category->getKey(),
                            'instructions' => $preventiveMaintenance->instructions,
                            'is_active' => true,
                            'created_by' => $preventiveMaintenance->created_by,
                            'updated_by' => $preventiveMaintenance->updated_by,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($preventiveMaintenance->items as $item) {
                            DB::table('preventive_maintenance_checklist_items')->insert([
                                'preventive_maintenance_checklist_id' => $checklistId,
                                'task' => $item->task,
                                'input_label' => $item->input_label,
                                'sort_order' => $item->sort_order,
                                'is_required' => $item->is_required,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    DB::table('preventive_maintenance_schedules')->insert([
                        'location_id' => $preventiveMaintenance->location_id,
                        'category_id' => $category->getKey(),
                        'preventive_maintenance_checklist_id' => $checklistId,
                        'scheduled_for' => $preventiveMaintenance->scheduled_for,
                        'is_active' => true,
                        'created_by' => $preventiveMaintenance->created_by,
                        'updated_by' => $preventiveMaintenance->updated_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('preventive_maintenance_schedules')->truncate();
        DB::table('preventive_maintenance_checklist_items')->truncate();
        DB::table('preventive_maintenance_checklists')->truncate();
    }
};
