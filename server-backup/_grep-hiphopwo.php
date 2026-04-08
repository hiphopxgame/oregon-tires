<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$dirs = [
    '/home2/avadpnmy/shared/engine-kit',
    '/home2/avadpnmy/shared/member-kit',
    '/home2/avadpnmy/shared/form-kit',
    '/home2/avadpnmy/shared/commerce-kit',
    '/home2/avadpnmy/public_html/includes',
    '/home2/avadpnmy/public_html/cli',
];

$pattern = 'hiphopwo';
$results = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        $ext = $file->getExtension();
        if (!in_array($ext, ['php', 'json', 'env', 'sh', 'md', 'txt', 'example', 'sql'])) continue;
        $path = $file->getPathname();
        $content = @file_get_contents($path);
        if ($content && stripos($content, $pattern) !== false) {
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (stripos($line, $pattern) !== false) {
                    $shortPath = str_replace('/home2/avadpnmy/', '~/', $path);
                    $results[] = "$shortPath:" . ($num + 1) . ": " . trim($line);
                }
            }
        }
    }
}

echo count($results) . " references found:\n\n";
foreach ($results as $r) echo "$r\n";
