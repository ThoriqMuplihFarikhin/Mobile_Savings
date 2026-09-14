<?php

/**
 * replace_admin_ui.php
 *
 * Scan all Blade files in resources/views/livewire/admin and replace legacy Tailwind classes
 * with the new semantic utility classes defined in app.css (.card and .btn-primary).
 */
$baseDir = __DIR__.'/resources/views/livewire/admin';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));

$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade.php') {
        $files[] = $file->getRealPath();
    }
}

$replacements = [
    // Replace top-level container (mx-auto max-w-7xl ...) with .card wrapper
    [
        'pattern' => '/class="([^"]*\bm(x-auto)\b[^"]*)"/',
        'replacement' => 'class="card $1"',
    ],
    // Replace any button that contains bg-indigo-800 with btn-primary
    [
        'pattern' => '/class="[^"]*bg-indigo-800[^"]*"/',
        'replacement' => 'class="btn-primary"',
    ],
    // Replace container divs with rounded-xl and background utilities with .card
    [
        'pattern' => '/class="([^"]*\brounded-xl\b[^"]*\bbg-(gray-50|white)\b[^"]*)"/',
        'replacement' => 'class="card"',
    ],
    // Replace any other rounded-xl with shadow etc. to .card
    [
        'pattern' => '/class="([^"]*\brounded-xl\b[^"]*\bshadow-[^\"]*\b[^"]*)"/',
        'replacement' => 'class="card"',
    ],
];

$totalFiles = 0;
$totalReplacements = 0;
foreach ($files as $filePath) {
    $content = file_get_contents($filePath);
    $newContent = $content;
    $fileReplacements = 0;
    foreach ($replacements as $rep) {
        $newContent = preg_replace($rep['pattern'], $rep['replacement'], $newContent, -1, $count);
        $fileReplacements += $count;
    }
    if ($fileReplacements > 0) {
        file_put_contents($filePath, $newContent);
        $totalFiles++;
        $totalReplacements += $fileReplacements;
        echo "Updated {$filePath}: {$fileReplacements} replacements\n";
    }
}

echo "\nSummary: {$totalFiles} files updated, {$totalReplacements} total replacements.\n";
