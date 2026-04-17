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
        Schema::create('preventive_maintenance_session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_maintenance_session_id')
                ->constrained(indexName: 'pm_session_items_session_fk')
                ->cascadeOnDelete();
            $table->foreignId('preventive_maintenance_item_id')
                ->nullable()
                ->constrained(indexName: 'pm_session_items_template_item_fk')
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

            $table->index(['preventive_maintenance_session_id', 'sort_order'], 'pm_session_items_session_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_session_items');
    }
};
