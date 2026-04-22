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
        Schema::dropIfExists('preventive_maintenance_session_items');
        Schema::dropIfExists('preventive_maintenance_sessions');
        Schema::dropIfExists('preventive_maintenance_items');
        Schema::dropIfExists('category_preventive_maintenance');
        Schema::dropIfExists('asset_preventive_maintenance');
        Schema::dropIfExists('preventive_maintenances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - data would be lost
    }
};
