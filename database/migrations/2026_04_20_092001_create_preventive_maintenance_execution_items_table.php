<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenance_execution_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('preventive_maintenance_execution_id');
            $table->foreign('preventive_maintenance_execution_id', 'pm_exec_items_exec_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_executions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('preventive_maintenance_checklist_item_id')->nullable();
            $table->foreign('preventive_maintenance_checklist_item_id', 'pm_exec_items_checklist_item_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklist_items')
                ->nullOnDelete();
            $table->string('task');
            $table->string('input_label')->nullable();
            $table->text('input_value')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_passed')->nullable()->index();
            $table->text('item_notes')->nullable();
            $table->string('evidence_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['preventive_maintenance_execution_id', 'sort_order'], 'pm_exec_items_exec_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_execution_items');
    }
};
