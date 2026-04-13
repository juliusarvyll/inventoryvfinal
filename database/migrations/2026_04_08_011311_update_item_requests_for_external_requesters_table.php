<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->string('requester_name')->nullable()->after('user_id');
            $table->dropForeign(['user_id']);
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        DB::table('item_requests')
            ->leftJoin('users', 'users.id', '=', 'item_requests.user_id')
            ->select('item_requests.id', 'users.name')
            ->orderBy('item_requests.id')
            ->chunk(100, function ($requests): void {
                foreach ($requests as $request) {
                    DB::table('item_requests')
                        ->where('id', $request->id)
                        ->update([
                            'requester_name' => $request->name,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('item_requests')->whereNull('user_id')->exists()) {
            throw new \RuntimeException('Cannot roll back while item requests without linked users exist.');
        }

        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn('requester_name');
        });
    }
};
