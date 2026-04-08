<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "PHP " . PHP_VERSION . "\n";
echo "CWD: " . __DIR__ . "\n";

// Check vendor
$vendor = __DIR__ . '/vendor/autoload.php';
echo "vendor/autoload.php: " . (file_exists($vendor) ? 'EXISTS' : 'MISSING') . "\n";

// Check .env
$envDir = dirname(__DIR__, 2);
$envFile = $envDir . '/.env.oregon-tires';
echo "Env dir: $envDir\n";
echo "Env file ($envFile): " . (file_exists($envFile) ? 'EXISTS (' . filesize($envFile) . ' bytes)' : 'MISSING') . "\n";

// Check includes
$bootstrap = __DIR__ . '/includes/bootstrap.php';
echo "bootstrap.php: " . (file_exists($bootstrap) ? 'EXISTS' : 'MISSING') . "\n";

// Check key directories
foreach (['api', 'includes', 'admin', 'assets', 'vendor', 'uploads', 'cli'] as $d) {
    echo "$d/: " . (is_dir(__DIR__ . '/' . $d) ? 'EXISTS' : 'MISSING') . "\n";
}

// Try loading bootstrap
echo "\n--- Loading bootstrap ---\n";
try {
    require $vendor;
    echo "Autoloader loaded OK\n";

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2), '.env.oregon-tires');
    $dotenv->load();
    echo "Dotenv loaded OK\n";
    echo "DB_NAME=" . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
    echo "DB_HOST=" . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";

    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD']
    );
    echo "DB connected OK\n";
    $count = $pdo->query("SHOW TABLES")->rowCount();
    echo "Tables: $count\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
