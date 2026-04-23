<?php

test('pdf export template renders institutional header and table rows', function () {
    $html = view('exports.table-pdf', [
        'header' => 'St. Paul University Philippines',
        'generatedAt' => now(),
        'headings' => ['Name', 'Status'],
        'rows' => [
            ['Router A', 'Active'],
            ['Switch B', 'Inactive'],
        ],
    ])->render();

    expect($html)
        ->toContain('St. Paul University Philippines')
        ->toContain('<table>')
        ->toContain('Router A')
        ->toContain('Switch B');
});
