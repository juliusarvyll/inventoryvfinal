<?php

// cleanup_legacy_tests.php
$filesToDelete = [
    'app/Actions/Inventory/SavePreventiveMaintenancePlan.php',
    'tests/Feature/Filament/PreventiveMaintenanceStartActionVisibilityTest.php',
    'tests/Feature/Filament/AssetPreventiveMaintenanceExecutionTest.php',
    'tests/Feature/Feature/Inventory/StartPreventiveMaintenanceSessionTest.php',
    'tests/Feature/Feature/Inventory/PreventiveMaintenanceWorkflowTest.php',
    'tests/Feature/Feature/Inventory/PreventiveMaintenanceAssetSelectionTest.php',
    'tests/Feature/Feature/Inventory/LocationHierarchyAssetScopeTest.php',
    'tests/Feature/Feature/Inventory/StartPreventiveMaintenanceExecutionTest.php',
    'tests/Feature/Feature/Inventory/AssetPreventiveMaintenanceSchedulesTest.php',
    'tests/Feature/Filament/LocationPreventiveMaintenanceActionsTest.php',
];

foreach ($filesToDelete as $file) {
    $path = dirname(__DIR__).'/'.$file;
    if (file_exists($path)) {
        unlink($path);
        echo 'Deleted '.$file."\n";
    }
}
