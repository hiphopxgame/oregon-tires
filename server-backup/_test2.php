<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Testing bootstrap load...\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "bootstrap path: " . __DIR__ . '/includes/bootstrap.php' . "\n";

// Simulate what bootstrap does
$includesDir = __DIR__ . '/includes';
echo "includes __DIR__ would be: $includesDir\n";
echo "dirname(includesDir, 2) = " . dirname($includesDir, 2) . "\n";
$envDir = dirname($includesDir, 2);
$envFile = $envDir . '/.env.oregon-tires';
echo "Looking for: $envFile\n";
echo "Exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

if (file_exists($envFile)) {
    echo "Contents (first line): " . explode("\n", file_get_contents($envFile))[0] . "\n";
}

echo "\nNow loading actual bootstrap...\n";
require_once __DIR__ . '/includes/bootstrap.php';
echo "Bootstrap loaded OK!\n";
echo "DB_NAME=" . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
