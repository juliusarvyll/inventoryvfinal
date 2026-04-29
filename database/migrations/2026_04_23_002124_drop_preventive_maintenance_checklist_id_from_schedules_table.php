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
        if (! Schema::hasColumn('preventive_maintenance_schedules', 'preventive_maintenance_checklist_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::withoutForeignKeyConstraints(function (): void {
                Schema::create('preventive_maintenance_schedules_tmp', function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('location_id');
                    $table->foreign('location_id', 'pm_schedules_location_id_foreign')
                        ->references('id')
                        ->on('locations')
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
                });

                DB::statement('insert into preventive_maintenance_schedules_tmp (id, location_id, scheduled_for, is_active, created_by, updated_by, created_at, updated_at) select id, location_id, scheduled_for, is_active, created_by, updated_by, created_at, updated_at from preventive_maintenance_schedules');

                Schema::drop('preventive_maintenance_schedules');
                Schema::rename('preventive_maintenance_schedules_tmp', 'preventive_maintenance_schedules');
            });

            return;
        }

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
        if (Schema::hasColumn('preventive_maintenance_schedules', 'preventive_maintenance_checklist_id')) {
            return;
        }

        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('preventive_maintenance_checklist_id')->nullable();
            $table->foreign('preventive_maintenance_checklist_id', 'pm_schedules_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->nullOnDelete();
        });
    }
};
