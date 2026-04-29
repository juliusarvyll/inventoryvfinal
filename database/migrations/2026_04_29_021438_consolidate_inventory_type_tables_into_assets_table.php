<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        $availableStatusId = $this->resolveAvailableStatusId();
        $catalogManufacturerId = $this->resolveCatalogManufacturerId();

        $accessoryMap = $this->migrateInventoryTable(
            table: 'accessories',
            type: 'accessory',
            requestableType: 'App\\Models\\Accessory',
            tagPrefix: 'ACC',
            serialColumn: 'model_number',
            availableStatusId: $availableStatusId,
            catalogManufacturerId: $catalogManufacturerId,
        );

        $componentMap = $this->migrateInventoryTable(
            table: 'components',
            type: 'component',
            requestableType: 'App\\Models\\Component',
            tagPrefix: 'CMP',
            serialColumn: 'serial',
            availableStatusId: $availableStatusId,
            catalogManufacturerId: $catalogManufacturerId,
        );

        $consumableMap = $this->migrateInventoryTable(
            table: 'consumables',
            type: 'consumable',
            requestableType: 'App\\Models\\Consumable',
            tagPrefix: 'CON',
            serialColumn: 'model_number',
            availableStatusId: $availableStatusId,
            catalogManufacturerId: $catalogManufacturerId,
        );

        $this->migrateAccessoryCheckoutsToAssetCheckouts($accessoryMap);
        $this->migrateConsumableAssignmentsToAssetCheckouts($consumableMap);

        Schema::dropIfExists('accessory_checkouts');
        Schema::dropIfExists('consumable_assignments');
        Schema::dropIfExists('asset_component');
        Schema::dropIfExists('accessories');
        Schema::dropIfExists('components');
        Schema::dropIfExists('consumables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This consolidation intentionally keeps the rollback as a no-op because
        // data is migrated into the assets table and the original stock tables are removed.
    }

    protected function resolveAvailableStatusId(): int
    {
        $existing = DB::table('status_labels')->where('name', 'Available')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('status_labels')->insertGetId([
            'name' => 'Available',
            'color' => '#22c55e',
            'type' => 'deployable',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function resolveCatalogManufacturerId(): int
    {
        $existing = DB::table('manufacturers')->where('name', 'Internal Catalog')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('manufacturers')->insertGetId([
            'name' => 'Internal Catalog',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    protected function migrateInventoryTable(
        string $table,
        string $type,
        string $requestableType,
        string $tagPrefix,
        string $serialColumn,
        int $availableStatusId,
        int $catalogManufacturerId,
    ): array {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $usedSerials = DB::table('assets')
            ->whereNotNull('serial')
            ->pluck('serial')
            ->filter()
            ->all();

        $idMap = [];

        DB::table($table)
            ->orderBy('id')
            ->get()
            ->each(function (object $row) use (
                &$idMap,
                &$usedSerials,
                $requestableType,
                $tagPrefix,
                $serialColumn,
                $availableStatusId,
                $catalogManufacturerId
            ): void {
                $assetModelId = $this->resolveCatalogAssetModelId(
                    categoryId: (int) $row->category_id,
                    manufacturerId: $catalogManufacturerId,
                    label: ucfirst((string) DB::table('categories')->where('id', $row->category_id)->value('type')),
                );

                $serial = $row->{$serialColumn} ?? null;

                if (blank($serial) || in_array($serial, $usedSerials, true)) {
                    $serial = null;
                } else {
                    $usedSerials[] = $serial;
                }

                $newAssetId = (int) DB::table('assets')->insertGetId([
                    'asset_tag' => sprintf('%s-%06d', $tagPrefix, $row->id),
                    'name' => $row->name,
                    'asset_model_id' => $assetModelId,
                    'category_id' => $row->category_id,
                    'status_label_id' => $availableStatusId,
                    'supplier_id' => $row->supplier_id,
                    'location_id' => $row->location_id,
                    'serial' => $serial,
                    'purchase_cost' => $row->purchase_cost,
                    'purchase_date' => $row->purchase_date,
                    'warranty_expires' => null,
                    'eol_date' => null,
                    'notes' => $row->notes,
                    'requestable' => $row->requestable,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                $idMap[(int) $row->id] = $newAssetId;

                DB::table('item_requests')
                    ->where('requestable_type', $requestableType)
                    ->where('requestable_id', $row->id)
                    ->update(['requestable_id' => $newAssetId]);
            });

        return $idMap;
    }

    protected function resolveCatalogAssetModelId(int $categoryId, int $manufacturerId, string $label): int
    {
        $existing = DB::table('asset_models')
            ->where('manufacturer_id', $manufacturerId)
            ->where('category_id', $categoryId)
            ->where('name', $label.' Catalog Item')
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('asset_models')->insertGetId([
            'name' => $label.' Catalog Item',
            'manufacturer_id' => $manufacturerId,
            'category_id' => $categoryId,
            'model_number' => null,
            'image' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<int, int>  $accessoryMap
     */
    protected function migrateAccessoryCheckoutsToAssetCheckouts(array $accessoryMap): void
    {
        if (! Schema::hasTable('accessory_checkouts')) {
            return;
        }

        DB::table('accessory_checkouts')
            ->orderBy('id')
            ->get()
            ->each(function (object $checkout) use ($accessoryMap): void {
                $assetId = $accessoryMap[(int) $checkout->accessory_id] ?? null;

                if (! $assetId) {
                    return;
                }

                DB::table('asset_checkouts')->insert([
                    'asset_id' => $assetId,
                    'assigned_to' => $checkout->assigned_to,
                    'checked_out_by' => null,
                    'assigned_at' => $checkout->assigned_at,
                    'returned_at' => $checkout->returned_at,
                    'note' => trim('Migrated accessory checkout'.($checkout->qty > 1 ? ' (qty: '.$checkout->qty.')' : '').($checkout->note ? ' - '.$checkout->note : '')),
                    'created_at' => $checkout->created_at,
                    'updated_at' => $checkout->updated_at,
                ]);
            });
    }

    /**
     * @param  array<int, int>  $consumableMap
     */
    protected function migrateConsumableAssignmentsToAssetCheckouts(array $consumableMap): void
    {
        if (! Schema::hasTable('consumable_assignments')) {
            return;
        }

        DB::table('consumable_assignments')
            ->orderBy('id')
            ->get()
            ->each(function (object $assignment) use ($consumableMap): void {
                $assetId = $consumableMap[(int) $assignment->consumable_id] ?? null;

                if (! $assetId) {
                    return;
                }

                DB::table('asset_checkouts')->insert([
                    'asset_id' => $assetId,
                    'assigned_to' => $assignment->assigned_to,
                    'checked_out_by' => null,
                    'assigned_at' => $assignment->assigned_at,
                    'returned_at' => null,
                    'note' => trim('Migrated consumable assignment'.($assignment->qty > 1 ? ' (qty: '.$assignment->qty.')' : '').($assignment->note ? ' - '.$assignment->note : '')),
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ]);
            });
    }
};
