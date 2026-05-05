<?php

// fix_itstaff_tests.php
$filesToFix = [
    'tests/Feature/Filament/AssetImportTest.php',
    'tests/Feature/Filament/InventoryImportTest.php',
];

foreach ($filesToFix as $file) {
    $path = dirname(__DIR__).'/'.$file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $content = str_replace('User::factory()->itStaff()', 'User::factory()->admin()', $content);
        file_put_contents($path, $content);
        echo 'Fixed '.$file."\n";
    }
}
