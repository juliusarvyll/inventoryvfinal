<?php

$files = [
    'Assets' => 'app/Filament/Resources/Assets/Tables/AssetsTable.php',
    'Accessories' => 'app/Filament/Resources/Accessories/Tables/AccessoriesTable.php',
    'Components' => 'app/Filament/Resources/Components/Tables/ComponentsTable.php',
    'Consumables' => 'app/Filament/Resources/Consumables/Tables/ConsumablesTable.php',
    'Licenses' => 'app/Filament/Resources/Licenses/Tables/LicensesTable.php',
    'Locations' => 'app/Filament/Resources/Locations/Tables/LocationsTable.php',
    'Suppliers' => 'app/Filament/Resources/Suppliers/Tables/SuppliersTable.php',
    'AssetModels' => 'app/Filament/Resources/AssetModels/Tables/AssetModelsTable.php',
    'ItemRequests' => 'app/Filament/Resources/ItemRequests/Tables/ItemRequestsTable.php',
];

foreach ($files as $type => $file) {
    $path = dirname(__DIR__).'/'.$file;
    if (! file_exists($path)) {
        continue;
    }

    $content = file_get_contents($path);

    // Add use statement if not exists
    if (! str_contains($content, 'use App\Filament\Actions\ChangeDepartmentBulkAction;')) {
        $content = preg_replace(
            '/(use Filament\\\\Actions\\\\BulkActionGroup;)/',
            "use App\\Filament\\Actions\\ChangeDepartmentBulkAction;\n$1",
            $content
        );
    }

    $resourceName = strtolower($type);

    // If string ends with 's', leave it, otherwise it's fine. Wait, ItemRequests -> item requests
    // Let's format the resource string properly:
    $recordStr = strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $type));

    // Add the bulk action inside BulkActionGroup::make([
    if (! str_contains($content, 'ChangeDepartmentBulkAction::make')) {
        $content = preg_replace(
            '/(BulkActionGroup::make\(\[)/',
            "$1\n                    ChangeDepartmentBulkAction::make('$recordStr'),",
            $content
        );
    }

    file_put_contents($path, $content);
    echo 'Updated '.$path."\n";
}
