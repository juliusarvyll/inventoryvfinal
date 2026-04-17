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
            $table->index('location_id', 'preventive_maintenances_location_id_index');
            $table->dropUnique('preventive_maintenances_location_id_year_unique');
            $table->dropColumn(['year', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenances', function (Blueprint $table): void {
            $table->unsignedSmallInteger('year')->after('location_id');
            $table->string('title')->after('year');
            $table->unique(['location_id', 'year']);
            $table->dropIndex('preventive_maintenances_location_id_index');
        });
    }
};
