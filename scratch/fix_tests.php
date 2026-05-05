<?php

// fix_tests.php
$dir = new RecursiveDirectoryIterator(dirname(__DIR__).'/tests');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        $content = preg_replace(
            '/StatusLabel::factory\(\)->available\(\)->create\(\[?\'?name\'?\s*=>\s*\'?Ready\'?\]?\)/',
            'StatusLabel::firstOrCreate([\'name\' => \'Ready\'], [\'color\' => \'#22c55e\', \'type\' => \'deployable\'])',
            $content
        );

        $content = preg_replace(
            '/StatusLabel::factory\(\)->available\(\)->create\(\)/',
            'StatusLabel::firstOrCreate([\'name\' => \'Available\'], [\'color\' => \'#22c55e\', \'type\' => \'deployable\'])',
            $content
        );

        $content = preg_replace(
            '/StatusLabel::factory\(\)->deployed\(\)->create\(\)/',
            'StatusLabel::firstOrCreate([\'name\' => \'Deployed\'], [\'color\' => \'#3b82f6\', \'type\' => \'deployable\'])',
            $content
        );

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed '.$file->getPathname()."\n";
        }
    }
}
