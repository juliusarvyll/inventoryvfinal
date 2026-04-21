<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenance_schedules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id', 'pm_schedules_location_id_foreign')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
            $table->unsignedBigInteger('preventive_maintenance_checklist_id');
            $table->foreign('preventive_maintenance_checklist_id', 'pm_schedules_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->onDelete('cascade');
            $table->date('scheduled_for')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by', 'pm_schedules_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by', 'pm_schedules_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('location_id', 'pm_schedules_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_schedules');
    }
};
