<?php
// Fix generated column issue and import remaining tables
$secret = 'OT_DBIMPORT_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$action = $_GET['action'] ?? '';

// Load .env
$envFile = dirname(__DIR__) . '/.env.oregon-tires';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

$host = $env['DB_HOST'] ?? 'localhost';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

switch ($action) {
    case 'fix-and-import':
        // Read the SQL dump, fix the generated column issue, write a fixed version
        $sqlFile = __DIR__ . '/oregon_tires_full_20260326.sql';
        $fixedFile = __DIR__ . '/oregon_tires_fixed.sql';

        $content = file_get_contents($sqlFile);
        if (!$content) die("Cannot read SQL file");

        // The INSERT into oretir_labor_entries includes the generated duration_minutes column
        // We need to skip that INSERT and do it with explicit column names excluding duration_minutes
        // Or simply set the column to STORED temporarily

        // Strategy: Drop the problematic table's data, alter column, re-insert
        // Actually simpler: just use sed-like replacement to add column list to the INSERT

        // Find the labor_entries INSERT and add explicit columns (excluding duration_minutes which is column index 7, 0-based)
        // Columns: id, ro_id, employee_id, start_time, end_time, duration_minutes(GENERATED), is_active, task_type, created_at, updated_at
        // The INSERT has values with duration_minutes included — we need to specify columns WITHOUT it

        // Replace the INSERT with column-specified version
        $pattern = "INSERT INTO `oretir_labor_entries` VALUES ";
        $replacement = "INSERT INTO `oretir_labor_entries` (`id`,`repair_order_id`,`employee_id`,`clock_in_at`,`clock_out_at`,`is_billable`,`task_description`,`created_at`,`updated_at`) VALUES ";

        // But we also need to remove the duration_minutes value from each row tuple
        // Each tuple: (id, ro_id, employee_id, start_time, end_time, duration_minutes, is_active, task_type, created_at, updated_at)
        // Index 5 (0-based) is duration_minutes — need to remove it

        // Find the INSERT line
        $insertStart = strpos($content, $pattern);
        if ($insertStart === false) {
            echo "No labor_entries INSERT found, trying direct import of remaining...\n";
        } else {
            // Find end of this INSERT statement
            $insertEnd = strpos($content, ";\n", $insertStart);
            $insertLine = substr($content, $insertStart, $insertEnd - $insertStart + 2);

            // Parse and rebuild without column index 5
            $valuesStr = substr($insertLine, strlen($pattern));
            $valuesStr = rtrim($valuesStr, ";\n");

            // Split by "),(" being careful with values containing parens
            preg_match_all('/\(([^)]+)\)/', $valuesStr, $matches);
            $newTuples = [];
            foreach ($matches[1] as $tuple) {
                // Split by comma but respect quoted strings
                $cols = str_getcsv($tuple, ',', "'");
                // Remove index 5 (duration_minutes)
                if (count($cols) >= 10) {
                    array_splice($cols, 5, 1);
                }
                // Rebuild with proper quoting
                $formatted = [];
                foreach ($cols as $v) {
                    $v = trim($v);
                    if ($v === 'NULL') {
                        $formatted[] = 'NULL';
                    } elseif (is_numeric($v)) {
                        $formatted[] = $v;
                    } else {
                        $formatted[] = "'" . addslashes($v) . "'";
                    }
                }
                $newTuples[] = '(' . implode(',', $formatted) . ')';
            }

            $newInsert = "INSERT INTO `oretir_labor_entries` (`id`,`repair_order_id`,`employee_id`,`clock_in_at`,`clock_out_at`,`is_billable`,`task_description`,`created_at`,`updated_at`) VALUES " . implode(',', $newTuples) . ";\n";

            $content = substr($content, 0, $insertStart) . $newInsert . substr($content, $insertEnd + 2);
        }

        file_put_contents($fixedFile, $content);
        echo "Fixed SQL written: " . round(filesize($fixedFile) / 1024 / 1024, 2) . " MB\n";

        // Drop all existing tables first (clean slate)
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $pdo->exec("DROP TABLE IF EXISTS `$t`");
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "Dropped " . count($tables) . " existing tables\n";
        } catch (PDOException $e) {
            echo "Drop error: " . $e->getMessage() . "\n";
        }

        // Import the fixed dump
        $cmd = sprintf(
            'mysql -h %s -u %s -p%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($name),
            escapeshellarg($fixedFile)
        );
        exec($cmd, $out, $rc);
        echo "Import rc=$rc\n";
        if ($out) echo implode("\n", $out) . "\n";

        // Verify
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "SUCCESS: " . count($tables) . " tables\n";
        } catch (PDOException $e) {
            echo "Verify error: " . $e->getMessage() . "\n";
        }

        // Cleanup fixed file
        @unlink($fixedFile);
        break;

    case 'count':
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo count($tables) . " tables\n";
            foreach ($tables as $t) {
                $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                echo "  $t: $count rows\n";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        break;

    default:
        echo "Actions: fix-and-import, count";
}
