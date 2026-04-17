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
        Schema::table('preventive_maintenance_items', function (Blueprint $table) {
            $table->string('input_label')
                ->nullable()
                ->after('task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenance_items', function (Blueprint $table) {
            $table->dropColumn('input_label');
        });
    }
};
