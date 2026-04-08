<?php
declare(strict_types=1);

/**
 * Member Kit Installer
 *
 * Copies member kit files into a target site directory.
 *
 * Usage:
 *   php install.php /path/to/site/public_html
 *
 * This copies:
 *   - includes/member-kit/*.php  → target/includes/member-kit/
 *   - api/member/*.php           → target/api/member/
 *   - templates/member/*.php     → target/templates/member/
 *   - js/member.js               → target/js/
 *   - css/member.css             → target/css/
 *   - migrations/                → target/migrations/
 *   - config/database.php        → target/config/database.php (if not exists)
 */

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$targetDir = $argv[1] ?? null;

if (!$targetDir) {
    echo "Usage: php install.php /path/to/site/public_html\n";
    echo "\nExample:\n";
    echo "  php install.php /Users/hiphop/Desktop/____1vsM____/---1oh6.events/public_html\n";
    exit(1);
}

$targetDir = rtrim($targetDir, '/');
if (!is_dir($targetDir)) {
    echo "ERROR: Target directory does not exist: {$targetDir}\n";
    exit(1);
}

$sourceDir = __DIR__;

// Files to copy (source relative path => target relative path)
$filesToCopy = [];

// PHP classes
foreach (glob($sourceDir . '/includes/member-kit/*.php') as $file) {
    $basename = basename($file);
    $filesToCopy["includes/member-kit/{$basename}"] = "includes/member-kit/{$basename}";
}

// API endpoints
foreach (glob($sourceDir . '/api/member/*.php') as $file) {
    $basename = basename($file);
    $filesToCopy["api/member/{$basename}"] = "api/member/{$basename}";
}

// Templates
foreach (glob($sourceDir . '/templates/member/*.php') as $file) {
    $basename = basename($file);
    $filesToCopy["templates/member/{$basename}"] = "templates/member/{$basename}";
}

// Migrations
foreach (glob($sourceDir . '/migrations/*.php') as $file) {
    $basename = basename($file);
    $filesToCopy["migrations/{$basename}"] = "migrations/{$basename}";
}

// Frontend
$filesToCopy['js/member.js'] = 'js/member.js';
$filesToCopy['css/member.css'] = 'css/member.css';

echo "Installing Member Kit to: {$targetDir}\n\n";

$copied = 0;
$skipped = 0;

foreach ($filesToCopy as $src => $dst) {
    $srcPath = $sourceDir . '/' . $src;
    $dstPath = $targetDir . '/' . $dst;
    $dstDir = dirname($dstPath);

    // Create directory if needed
    if (!is_dir($dstDir)) {
        mkdir($dstDir, 0755, true);
        echo "  mkdir: {$dst}\n";
    }

    if (!file_exists($srcPath)) {
        echo "  SKIP (missing): {$src}\n";
        $skipped++;
        continue;
    }

    copy($srcPath, $dstPath);
    echo "  copy: {$dst}\n";
    $copied++;
}

// Config: only copy if not exists (don't overwrite site's config)
$configSrc = $sourceDir . '/config/database.php';
$configDst = $targetDir . '/config/database.php';
if (!file_exists($configDst)) {
    if (!is_dir(dirname($configDst))) {
        mkdir(dirname($configDst), 0755, true);
    }
    copy($configSrc, $configDst);
    echo "  copy: config/database.php (new)\n";
    $copied++;
} else {
    echo "  skip: config/database.php (already exists)\n";
    $skipped++;
}

echo "\nDone! Copied {$copied} files, skipped {$skipped}.\n";
echo "\nNext steps:\n";
echo "  1. Configure .env with MEMBER_MODE, database, and SMTP settings\n";
echo "  2. Run: php {$targetDir}/migrations/001_member_tables.php\n";
echo "  3. Add member CSS/JS to your site's layout template\n";
echo "  4. Wire up member routes in your site's router\n";
if (!file_exists($targetDir . '/vendor/autoload.php') && !file_exists(dirname($targetDir) . '/vendor/autoload.php')) {
    echo "  5. Install dependencies: composer require vlucas/phpdotenv phpmailer/phpmailer\n";
}
