<?php

// fix_itstaff_tests_all.php
$dir = new RecursiveDirectoryIterator(dirname(__DIR__).'/tests');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, 'User::factory()->itStaff()')) {
            $content = str_replace('User::factory()->itStaff()', 'User::factory()->admin()', $content);
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed '.$file->getPathname()."\n";
        }
    }
}
