<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenance_schedule_checklist', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('preventive_maintenance_schedule_id');
            $table->foreign('preventive_maintenance_schedule_id', 'pm_schedule_checklist_schedule_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_schedules')
                ->onDelete('cascade');
            $table->unsignedBigInteger('preventive_maintenance_checklist_id');
            $table->foreign('preventive_maintenance_checklist_id', 'pm_schedule_checklist_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['preventive_maintenance_schedule_id', 'preventive_maintenance_checklist_id'], 'pm_schedule_checklist_unique');
            $table->index('preventive_maintenance_schedule_id', 'pm_schedule_checklist_schedule_idx');
            $table->index('preventive_maintenance_checklist_id', 'pm_schedule_checklist_checklist_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_schedule_checklist');
    }
};
