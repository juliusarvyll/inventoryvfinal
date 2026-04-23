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
        Schema::create('preventive_maintenance_schedule_checklist', function (Blueprint $table) {
            $table->unsignedBigInteger('preventive_maintenance_schedule_id');
            $table->unsignedBigInteger('preventive_maintenance_checklist_id');

            $table->foreign('preventive_maintenance_schedule_id', 'pm_schedule_checklist_schedule_fk')
                ->references('id')
                ->on('preventive_maintenance_schedules')
                ->onDelete('cascade');

            $table->foreign('preventive_maintenance_checklist_id', 'pm_schedule_checklist_checklist_fk')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->onDelete('cascade');

            $table->primary(['preventive_maintenance_schedule_id', 'preventive_maintenance_checklist_id'], 'pm_schedule_checklist_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_schedule_checklist');
    }
};
