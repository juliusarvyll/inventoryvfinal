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
        Schema::table('preventive_maintenances', function (Blueprint $table): void {
            $table->text('execution_notes')
                ->nullable()
                ->after('instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenances', function (Blueprint $table): void {
            $table->dropColumn('execution_notes');
        });
    }
};
