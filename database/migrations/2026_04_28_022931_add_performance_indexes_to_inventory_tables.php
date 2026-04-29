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
        Schema::table('assets', function (Blueprint $table): void {
            $table->index(['requestable', 'asset_tag'], 'assets_requestable_asset_tag_idx');
        });

        Schema::table('asset_checkouts', function (Blueprint $table): void {
            $table->index(['assigned_to', 'returned_at', 'assigned_at'], 'asset_checkouts_assigned_active_idx');
            $table->index(['asset_id', 'returned_at', 'assigned_at'], 'asset_checkouts_asset_active_idx');
        });

        Schema::table('accessory_checkouts', function (Blueprint $table): void {
            $table->index(['accessory_id', 'returned_at'], 'accessory_checkouts_accessory_returned_idx');
            $table->index(['assigned_to', 'returned_at', 'assigned_at'], 'accessory_checkouts_assigned_active_idx');
        });

        Schema::table('consumable_assignments', function (Blueprint $table): void {
            $table->index(['assigned_to', 'assigned_at'], 'consumable_assignments_assigned_at_idx');
        });

        Schema::table('licenses', function (Blueprint $table): void {
            $table->index('expiration_date', 'licenses_expiration_date_idx');
            $table->index(['requestable', 'name'], 'licenses_requestable_name_idx');
        });

        Schema::table('license_seats', function (Blueprint $table): void {
            $table->index(['assigned_to', 'assigned_at'], 'license_seats_assigned_at_idx');
        });

        Schema::table('item_requests', function (Blueprint $table): void {
            $table->index(['status', 'created_at'], 'item_requests_status_created_at_idx');
            $table->index(['user_id', 'created_at'], 'item_requests_user_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_requests', function (Blueprint $table): void {
            $table->dropIndex('item_requests_status_created_at_idx');
            $table->dropIndex('item_requests_user_created_at_idx');
        });

        Schema::table('license_seats', function (Blueprint $table): void {
            $table->dropIndex('license_seats_assigned_at_idx');
        });

        Schema::table('licenses', function (Blueprint $table): void {
            $table->dropIndex('licenses_expiration_date_idx');
            $table->dropIndex('licenses_requestable_name_idx');
        });

        Schema::table('consumable_assignments', function (Blueprint $table): void {
            $table->dropIndex('consumable_assignments_assigned_at_idx');
        });

        Schema::table('accessory_checkouts', function (Blueprint $table): void {
            $table->dropIndex('accessory_checkouts_accessory_returned_idx');
            $table->dropIndex('accessory_checkouts_assigned_active_idx');
        });

        Schema::table('asset_checkouts', function (Blueprint $table): void {
            $table->dropIndex('asset_checkouts_assigned_active_idx');
            $table->dropIndex('asset_checkouts_asset_active_idx');
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropIndex('assets_requestable_asset_tag_idx');
        });
    }
};
