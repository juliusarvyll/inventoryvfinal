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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('email');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('job_title')->nullable()->after('department');
            $table->string('phone')->nullable()->after('job_title');
            $table->string('location')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('location');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->string('role')->default('End User')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->dropColumn([
                'employee_id',
                'department',
                'job_title',
                'phone',
                'location',
                'avatar',
                'is_active',
                'role',
            ]);
        });
    }
};
