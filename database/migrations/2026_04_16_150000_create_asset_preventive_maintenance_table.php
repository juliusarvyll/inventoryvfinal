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
        Schema::create('asset_preventive_maintenance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('preventive_maintenance_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('asset_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['preventive_maintenance_id', 'asset_id'], 'asset_pm_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_preventive_maintenance');
    }
};
