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
        Schema::create('preventive_maintenance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_maintenance_id')
                ->constrained(indexName: 'pm_sessions_template_fk')
                ->cascadeOnDelete();
            $table->foreignId('asset_id')
                ->constrained(indexName: 'pm_sessions_asset_fk')
                ->cascadeOnDelete();
            $table->unsignedInteger('template_version')->default(1);
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users', indexName: 'pm_sessions_performed_by_fk')
                ->nullOnDelete();
            $table->text('general_notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'created_at'], 'pm_sessions_asset_created_idx');
            $table->index(['preventive_maintenance_id', 'created_at'], 'pm_sessions_template_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_sessions');
    }
};
