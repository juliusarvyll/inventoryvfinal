<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign('pm_schedules_checklist_id_foreign');
            $table->dropColumn('preventive_maintenance_checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('preventive_maintenance_checklist_id')->nullable();
            $table->foreign('preventive_maintenance_checklist_id', 'pm_schedules_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->nullOnDelete();
        });
    }
};
