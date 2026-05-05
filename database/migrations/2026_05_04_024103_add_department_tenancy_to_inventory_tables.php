<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the default department
        $defaultDepartment = Department::query()->firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Unassigned',
                'description' => 'Default department for existing records.',
                'is_active' => true,
            ],
        );

        // 2. Migrate existing string department values from users into real departments & pivot entries
        $existingDepartments = DB::table('users')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        $departmentMap = [];
        $departmentMap[''] = $defaultDepartment->getKey();

        foreach ($existingDepartments as $deptName) {
            $dept = Department::query()->firstOrCreate(
                ['code' => Str::upper(Str::slug($deptName, '_'))],
                [
                    'name' => $deptName,
                    'is_active' => true,
                ],
            );
            $departmentMap[$deptName] = $dept->getKey();
        }

        // Create pivot entries for all users
        $users = DB::table('users')->select(['id', 'department'])->get();
        foreach ($users as $user) {
            $deptId = $departmentMap[$user->department ?? ''] ?? $defaultDepartment->getKey();
            DB::table('department_user')->insertOrIgnore([
                'department_id' => $deptId,
                'user_id' => $user->id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Add department_id FK to inventory tables
        $tables = ['assets', 'licenses', 'locations', 'suppliers', 'asset_models'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('department_id');
            });

            // Backfill existing records
            DB::table($tableName)->whereNull('department_id')->update([
                'department_id' => $defaultDepartment->getKey(),
            ]);
        }

        // 4. Handle item_requests: add FK, migrate string data, drop old column
        Schema::table('item_requests', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('department_id');
        });

        // Migrate item_request department strings to FK
        $itemRequests = DB::table('item_requests')->select(['id', 'department'])->get();
        foreach ($itemRequests as $request) {
            $deptId = $departmentMap[$request->department ?? ''] ?? $defaultDepartment->getKey();
            DB::table('item_requests')->where('id', $request->id)->update([
                'department_id' => $deptId,
            ]);
        }

        // 5. Drop old string department columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add string department columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('employee_id');
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->string('department')->nullable()->after('requested_by');
        });

        // Restore data from pivot/FK to string columns
        $departments = DB::table('departments')->pluck('name', 'id');

        $pivotEntries = DB::table('department_user')->where('is_primary', true)->get();
        foreach ($pivotEntries as $entry) {
            DB::table('users')->where('id', $entry->user_id)->update([
                'department' => $departments[$entry->department_id] ?? null,
            ]);
        }

        $itemRequests = DB::table('item_requests')->whereNotNull('department_id')->get();
        foreach ($itemRequests as $request) {
            DB::table('item_requests')->where('id', $request->id)->update([
                'department' => $departments[$request->department_id] ?? null,
            ]);
        }

        // Drop department_id FKs from inventory tables
        $tables = ['assets', 'licenses', 'locations', 'suppliers', 'asset_models', 'item_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }
    }
};
