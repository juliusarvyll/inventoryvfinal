<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_preventive_maintenance', function (Blueprint $table) {
            $table->foreignId('preventive_maintenance_id')
                ->constrained(indexName: 'cat_pm_pm_fk')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained(indexName: 'cat_pm_category_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['preventive_maintenance_id', 'category_id'], 'category_pm_primary');
        });

        DB::table('preventive_maintenances')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->lazy()
            ->each(function (object $preventiveMaintenance): void {
                DB::table('category_preventive_maintenance')->insert([
                    'preventive_maintenance_id' => $preventiveMaintenance->id,
                    'category_id' => $preventiveMaintenance->category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('location_id')
                ->constrained('categories', indexName: 'preventive_maintenances_category_fk')
                ->nullOnDelete();
        });

        DB::table('category_preventive_maintenance')
            ->orderBy('preventive_maintenance_id')
            ->orderBy('category_id')
            ->lazy()
            ->each(function (object $categoryPreventiveMaintenance): void {
                DB::table('preventive_maintenances')
                    ->where('id', $categoryPreventiveMaintenance->preventive_maintenance_id)
                    ->whereNull('category_id')
                    ->update([
                        'category_id' => $categoryPreventiveMaintenance->category_id,
                    ]);
            });

        Schema::dropIfExists('category_preventive_maintenance');
    }
};
