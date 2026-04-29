<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('category_preventive_maintenance_checklist')) {
            Schema::create('category_preventive_maintenance_checklist', function (Blueprint $table): void {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('preventive_maintenance_checklist_id');
                $table->timestamps();

                $table->primary(
                    ['category_id', 'preventive_maintenance_checklist_id'],
                    'pm_checklist_category_primary',
                );

                $table->foreign('category_id', 'pm_cat_checklist_category_fk')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade');

                $table->foreign('preventive_maintenance_checklist_id', 'pm_cat_checklist_checklist_fk')
                    ->references('id')
                    ->on('preventive_maintenance_checklists')
                    ->onDelete('cascade');
            });
        }

        if (! $this->foreignKeyExists('category_preventive_maintenance_checklist', 'pm_cat_checklist_category_fk')) {
            Schema::table('category_preventive_maintenance_checklist', function (Blueprint $table): void {
                $table->foreign('category_id', 'pm_cat_checklist_category_fk')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade');
            });
        }

        if (! $this->foreignKeyExists('category_preventive_maintenance_checklist', 'pm_cat_checklist_checklist_fk')) {
            Schema::table('category_preventive_maintenance_checklist', function (Blueprint $table): void {
                $table->foreign('preventive_maintenance_checklist_id', 'pm_cat_checklist_checklist_fk')
                    ->references('id')
                    ->on('preventive_maintenance_checklists')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('preventive_maintenance_checklists', 'category_id')) {
            DB::table('preventive_maintenance_checklists')
                ->whereNotNull('category_id')
                ->orderBy('id')
                ->get(['id', 'category_id'])
                ->each(function ($checklist): void {
                    $exists = DB::table('category_preventive_maintenance_checklist')
                        ->where('category_id', $checklist->category_id)
                        ->where('preventive_maintenance_checklist_id', $checklist->id)
                        ->exists();

                    if ($exists) {
                        return;
                    }

                    DB::table('category_preventive_maintenance_checklist')->insert([
                        'category_id' => $checklist->category_id,
                        'preventive_maintenance_checklist_id' => $checklist->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            if (! $this->usesSqlite() && $this->foreignKeyExists('preventive_maintenance_checklists', 'preventive_maintenance_checklists_category_id_foreign')) {
                Schema::table('preventive_maintenance_checklists', function (Blueprint $table): void {
                    $table->dropForeign('preventive_maintenance_checklists_category_id_foreign');
                });
            }

            Schema::table('preventive_maintenance_checklists', function (Blueprint $table): void {
                $table->dropUnique('pm_checklists_category_unique');
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });

            if (! $this->usesSqlite() && ! $this->foreignKeyExists('preventive_maintenance_checklists', 'preventive_maintenance_checklists_category_id_foreign')) {
                Schema::table('preventive_maintenance_checklists', function (Blueprint $table): void {
                    $table->foreign('category_id', 'preventive_maintenance_checklists_category_id_foreign')
                        ->references('id')
                        ->on('categories')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('preventive_maintenance_checklists', function (Blueprint $table): void {
            $table->unique('category_id', 'pm_checklists_category_unique');
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });

        Schema::dropIfExists('category_preventive_maintenance_checklist');
    }

    protected function foreignKeyExists(string $table, string $constraint): bool
    {
        if ($this->usesSqlite()) {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    protected function usesSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }
};
