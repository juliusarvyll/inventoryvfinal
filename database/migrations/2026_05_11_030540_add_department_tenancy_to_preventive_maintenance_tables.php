<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultDepartmentId = Department::query()
            ->where('code', 'DEFAULT')
            ->value('id');

        foreach (['preventive_maintenance_schedules', 'preventive_maintenance_executions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('department_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('department_id');
            });

            if ($defaultDepartmentId) {
                DB::table($tableName)->whereNull('department_id')->update([
                    'department_id' => $defaultDepartmentId,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (['preventive_maintenance_schedules', 'preventive_maintenance_executions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('department_id');
            });
        }
    }
};
