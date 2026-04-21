<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenance_executions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('preventive_maintenance_schedule_id')->nullable();
            $table->foreign('preventive_maintenance_schedule_id', 'pm_exec_schedule_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_schedules')
                ->nullOnDelete();
            $table->unsignedBigInteger('preventive_maintenance_checklist_id');
            $table->foreign('preventive_maintenance_checklist_id', 'pm_exec_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->onDelete('cascade');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id', 'pm_exec_location_id_foreign')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id', 'pm_exec_category_id_foreign')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id', 'pm_exec_asset_id_foreign')
                ->references('id')
                ->on('assets')
                ->onDelete('cascade');
            $table->string('status', 32)->default('pending')->index();
            $table->date('scheduled_for')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->foreign('performed_by', 'pm_exec_performed_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->text('general_notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'created_at'], 'pm_exec_asset_created_idx');
            $table->index(['location_id', 'category_id', 'created_at'], 'pm_exec_loc_cat_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_executions');
    }
};
