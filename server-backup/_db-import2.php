<?php
$secret = 'OT_DBIMPORT_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }
$action = $_GET['action'] ?? '';

$envFile = dirname(__DIR__) . '/.env.oregon-tires';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}
$host = $env['DB_HOST']; $name = $env['DB_NAME']; $user = $env['DB_USER']; $pass = $env['DB_PASSWORD'];

switch ($action) {
    case 'drop-all':
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) $pdo->exec("DROP TABLE `$t`");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "Dropped " . count($tables) . " tables\n";
        break;

    case 'import':
        $cmd = sprintf('mysql -h %s -u %s -p%s %s < %s 2>&1',
            escapeshellarg($host), escapeshellarg($user), escapeshellarg($pass),
            escapeshellarg($name), escapeshellarg(__DIR__ . '/oregon_tires_fixed.sql'));
        exec($cmd, $out, $rc);
        echo "rc=$rc\n" . implode("\n", $out) . "\n";
        break;

    case 'restore-generated':
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("ALTER TABLE `oretir_labor_entries` DROP COLUMN `duration_minutes`");
        $pdo->exec("ALTER TABLE `oretir_labor_entries` ADD COLUMN `duration_minutes` int(10) unsigned GENERATED ALWAYS AS (CASE WHEN `clock_out_at` IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, `clock_in_at`, `clock_out_at`) ELSE NULL END) STORED AFTER `clock_out_at`");
        echo "Generated column restored\n";
        break;

    case 'count':
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo count($tables) . " tables\n";
        foreach ($tables as $t) {
            $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "  $t: $c rows\n";
        }
        break;

    case 'cleanup':
        @unlink(__DIR__ . '/oregon_tires_full_20260326.sql');
        @unlink(__DIR__ . '/oregon_tires_fixed.sql');
        @unlink(__DIR__ . '/_db-import.php');
        @unlink(__DIR__ . '/_db-fix.php');
        @unlink(__DIR__ . '/_db-import2.php');
        echo "All import files cleaned up";
        break;

    default:
        echo "Actions: drop-all, import, restore-generated, count, cleanup";
}
