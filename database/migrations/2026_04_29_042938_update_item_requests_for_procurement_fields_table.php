<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_requests', function (Blueprint $table): void {
            $table->string('requested_by')->nullable()->after('requester_name');
            $table->string('department')->nullable()->after('requested_by');
            $table->text('items')->nullable()->after('qty');
            $table->decimal('unit_cost', 12, 2)->nullable()->after('items');
            $table->text('remarks')->nullable()->after('unit_cost');
            $table->string('source_of_fund')->nullable()->after('remarks');
            $table->text('purpose_project')->nullable()->after('source_of_fund');
            $table->string('requestable_type')->nullable()->change();
            $table->unsignedBigInteger('requestable_id')->nullable()->change();
        });

        DB::table('item_requests')
            ->leftJoin('users', 'users.id', '=', 'item_requests.user_id')
            ->select([
                'item_requests.id',
                'item_requests.requester_name',
                'item_requests.requestable_type',
                'item_requests.requestable_id',
                'item_requests.reason',
                'users.name as user_name',
                'users.department as user_department',
            ])
            ->orderBy('item_requests.id')
            ->chunk(100, function ($requests): void {
                foreach ($requests as $request) {
                    $itemName = match ($request->requestable_type) {
                        'App\\Models\\License' => DB::table('licenses')->where('id', $request->requestable_id)->value('name'),
                        'App\\Models\\Asset',
                        'App\\Models\\Accessory',
                        'App\\Models\\Consumable',
                        'App\\Models\\Component' => DB::table('assets')->where('id', $request->requestable_id)->value('name'),
                        default => null,
                    };

                    DB::table('item_requests')
                        ->where('id', $request->id)
                        ->update([
                            'requested_by' => $request->requester_name ?: $request->user_name,
                            'department' => $request->user_department,
                            'items' => $itemName ?: class_basename((string) $request->requestable_type),
                            'remarks' => $request->reason,
                            'purpose_project' => $request->reason,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('item_requests', function (Blueprint $table): void {
            $table->string('requestable_type')->nullable(false)->change();
            $table->unsignedBigInteger('requestable_id')->nullable(false)->change();
            $table->dropColumn([
                'requested_by',
                'department',
                'items',
                'unit_cost',
                'remarks',
                'source_of_fund',
                'purpose_project',
            ]);
        });
    }
};
