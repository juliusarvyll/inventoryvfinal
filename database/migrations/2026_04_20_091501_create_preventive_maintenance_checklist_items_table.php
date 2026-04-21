<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenance_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('preventive_maintenance_checklist_id');
            $table->foreign('preventive_maintenance_checklist_id', 'pm_checklist_items_checklist_id_foreign')
                ->references('id')
                ->on('preventive_maintenance_checklists')
                ->onDelete('cascade');
            $table->string('task');
            $table->string('input_label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(
                ['preventive_maintenance_checklist_id', 'sort_order'],
                'pm_checklist_items_checklist_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_checklist_items');
    }
};
