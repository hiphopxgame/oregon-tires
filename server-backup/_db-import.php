<?php
// Temporary DB import helper — DELETE after use
$secret = 'OT_DBIMPORT_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$action = $_GET['action'] ?? '';

// Load .env manually
$envFile = dirname(__DIR__) . '/.env.oregon-tires';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/.env';
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $v = trim($v, '"\'');
    $env[trim($k)] = $v;
}

$host = $env['DB_HOST'] ?? 'localhost';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

switch ($action) {
    case 'test':
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "DB connected OK\n";
            echo "Host: $host\n";
            echo "Database: $name\n";
            echo "User: $user\n";
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables: " . count($tables) . "\n";
            foreach ($tables as $t) echo "  - $t\n";
        } catch (PDOException $e) {
            echo "DB ERROR: " . $e->getMessage() . "\n";
        }
        break;

    case 'import':
        $sqlFile = __DIR__ . '/oregon_tires_full_20260326.sql';
        if (!file_exists($sqlFile)) {
            die("SQL file not found at: $sqlFile");
        }
        $size = filesize($sqlFile);
        echo "SQL file: " . round($size / 1024 / 1024, 2) . " MB\n";

        // Use mysql CLI for large imports (faster, handles delimiters)
        $cmd = sprintf(
            'mysql -h %s -u %s -p%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($name),
            escapeshellarg($sqlFile)
        );
        exec($cmd, $out, $rc);
        echo "Import rc=$rc\n";
        if ($out) echo implode("\n", $out) . "\n";

        if ($rc === 0) {
            // Verify
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "SUCCESS: " . count($tables) . " tables imported\n";
            } catch (PDOException $e) {
                echo "Verify error: " . $e->getMessage() . "\n";
            }
        }
        break;

    case 'cleanup':
        @unlink(__DIR__ . '/oregon_tires_full_20260326.sql');
        echo "SQL dump cleaned. Now delete _db-import.php manually.";
        break;

    default:
        echo "Actions: test, import, cleanup";
}
