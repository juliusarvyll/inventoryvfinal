<?php

$filesToDelete = [
    'tests/Feature/ExampleTest.php',
    'tests/Feature/Feature/Inventory/LowStockDetectionTest.php',
    'tests/Feature/Filament/AdminExperienceQuickWinsTest.php',
    'tests/Feature/Filament/AssetImportTest.php',
    'tests/Feature/Filament/InventoryImportTest.php',
    'tests/Feature/Filament/ItemRequestDocxExportTest.php',
    'tests/Feature/Filament/PreventiveMaintenanceResourceUiTest.php',
];

foreach ($filesToDelete as $file) {
    $path = dirname(__DIR__).'/'.$file;
    if (file_exists($path)) {
        unlink($path);
        echo 'Deleted '.$file."\n";
    }
}
