<?php

$dir = new RecursiveDirectoryIterator(__DIR__.'/resources/views/livewire/admin');
$it = new RecursiveIteratorIterator($dir);
$search = [
    'bg-[#171717]' => 'bg-indigo-800',
    'bg-[#fafafa]' => 'bg-gray-50',
    'bg-[#d3e5ff]' => 'bg-indigo-100',
    'bg-[#d8ccf1]' => 'bg-purple-100',
    'bg-[#ffefcf]' => 'bg-amber-100',
    'bg-[#ebebeb]' => 'border-gray-200',
    'text-[#171717]' => 'text-gray-900',
    'text-[#4d4d4d]' => 'text-gray-600',
    'text-[#888888]' => 'text-gray-500',
    'text-[#0761d1]' => 'text-indigo-600',
];
$filesProcessed = 0;
$totalReplacements = 0;
foreach ($it as $file) {
    if ($file->isFile() && substr($file->getFilename(), -10) === '.blade.php') {
        $content = file_get_contents($file->getPathname());
        $newContent = $content;
        foreach ($search as $old => $new) {
            $newContent = str_replace($old, $new, $newContent, $count);
            $totalReplacements += $count;
        }
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            $filesProcessed++;
        }
    }
}
echo "Processed {$filesProcessed} files, made {$totalReplacements} replacements.\n";
