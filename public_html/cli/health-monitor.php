#!/usr/bin/env php
<?php
// Oregon Tires — Health Monitor
//
// Quick mode (--quick): uptime, DB, disk — run every 5 min
// Full mode  (--full):  all checks + backup + daily report — run daily at 6 AM
//
// Cron:
//   every 5 min:  php .../cli/health-monitor.php --quick
//   daily 6 AM:   php .../cli/health-monitor.php --full

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

$mode = in_array('--full', $argv, true) ? 'full' : 'quick';
$db   = getDB();
$siteUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
$results = [];

echo date('Y-m-d H:i:s') . " Health monitor starting ({$mode} mode)\n";

// ─── Helper: record a check ─────────────────────────────────────────────────

function recordCheck(PDO $db, string $type, string $status, string $label, ?array $details = null, ?int $ms = null): array {
    $row = [
        'check_type'       => $type,
        'status'           => $status,
        'label'            => $label,
        'details'          => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
        'response_time_ms' => $ms,
    ];
    try {
        $db->prepare(
            'INSERT INTO oretir_health_checks (check_type, status, label, details, response_time_ms) VALUES (?,?,?,?,?)'
        )->execute([$type, $status, $label, $row['details'], $ms]);
    } catch (\Throwable $e) {
        error_log("health-monitor: failed to record check {$label}: " . $e->getMessage());
    }
    return $row;
}

// ─── Helper: HTTP fetch with timing ──────────────────────────────────────────

function httpCheck(string $url, int $timeout = 10): array {
    $start = microtime(true);
    $ctx = stream_context_create([
        'http' => [
            'timeout'       => $timeout,
            'user_agent'    => 'OregonTires-HealthMonitor/1.0',
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);

    $body = @file_get_contents($url, false, $ctx);
    $ms   = (int) round((microtime(true) - $start) * 1000);
    $code = 0;

    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/[\d.]+ (\d+)/', $header, $m)) {
                $code = (int) $m[1];
            }
        }
    }

    return ['code' => $code, 'body' => $body ?: '', 'ms' => $ms];
}

// ═════════════════════════════════════════════════════════════════════════════
// CHECKS — Quick mode (run every 5 min)
// ═════════════════════════════════════════════════════════════════════════════

// 1. HTTP Uptime checks
$uptimeTargets = [
    ['url' => $siteUrl,                   'label' => 'Homepage',   'expect' => 'Oregon Tires'],
    ['url' => $siteUrl . '/api/health.php', 'label' => 'Health API', 'expect' => '"status"'],
    ['url' => $siteUrl . '/admin/',        'label' => 'Admin Panel', 'expect' => 'admin'],
];

foreach ($uptimeTargets as $target) {
    $resp = httpCheck($target['url']);
    $contentOk = $resp['code'] === 200 && stripos($resp['body'], $target['expect']) !== false;
    $status = $contentOk ? 'ok' : ($resp['code'] === 200 ? 'warn' : 'fail');
    $results[] = recordCheck($db, 'uptime', $status, $target['label'], [
        'url'           => $target['url'],
        'http_code'     => $resp['code'],
        'content_match' => $contentOk,
    ], $resp['ms']);
    $icon = $status === 'ok' ? "\u{2713}" : ($status === 'warn' ? '!' : "\u{2717}");
    echo "  {$icon} Uptime: {$target['label']} — HTTP {$resp['code']} ({$resp['ms']}ms)\n";
}

// 2. Database connectivity
$dbStart = microtime(true);
try {
    $db->query('SELECT 1');
    $dbMs = (int) round((microtime(true) - $dbStart) * 1000);
    $tableCount = (int) $db->query(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()"
    )->fetchColumn();
    $dbStatus = $dbMs < 100 ? 'ok' : ($dbMs < 500 ? 'warn' : 'fail');
    $results[] = recordCheck($db, 'database', $dbStatus, 'Database Ping', [
        'latency_ms'  => $dbMs,
        'table_count' => $tableCount,
    ], $dbMs);
    echo "  \u{2713} Database: {$dbMs}ms, {$tableCount} tables\n";
} catch (\Throwable $e) {
    $results[] = recordCheck($db, 'database', 'fail', 'Database Ping', [
        'error' => $e->getMessage(),
    ]);
    echo "  \u{2717} Database: FAILED — {$e->getMessage()}\n";
}

// 3. Disk usage — try cPanel quota first, fall back to system disk
$webRoot = dirname(__DIR__);
$usedMb = 0;
$totalMb = 0;
$pct = 0;
$diskMethod = 'system';

// Try cPanel quota via repquota or quota command
$quotaUsed = null;
if (function_exists('exec')) {
    @exec('quota -g 2>/dev/null | tail -1', $quotaOut);
    if (!empty($quotaOut[0]) && preg_match('/(\d+)\s+(\d+)/', $quotaOut[0], $qm)) {
        $quotaUsed = (int) $qm[1]; // KB used
        $quotaLimit = (int) $qm[2]; // KB limit
        if ($quotaLimit > 0) {
            $usedMb = round($quotaUsed / 1024, 1);
            $totalMb = round($quotaLimit / 1024, 1);
            $pct = round($quotaUsed / $quotaLimit * 100, 1);
            $diskMethod = 'quota';
        }
    }
}

// Fall back to du for home directory size (capped estimate)
if ($diskMethod === 'system') {
    $homeDir = '/home/hiphopwo';
    if (function_exists('exec') && is_dir($homeDir)) {
        @exec('du -sm ' . escapeshellarg($homeDir) . ' 2>/dev/null', $duOut);
        if (!empty($duOut[0]) && preg_match('/^(\d+)/', $duOut[0], $dm)) {
            $usedMb = (int) $dm[1];
            $totalMb = 0; // Unknown quota on shared hosting
            $pct = 0;
            $diskMethod = 'du';
        }
    }
}

// Final fallback: system disk (less useful on shared hosting)
if ($diskMethod === 'system') {
    $freeBytes  = @disk_free_space($webRoot);
    $totalBytes = @disk_total_space($webRoot);
    if ($freeBytes !== false && $totalBytes !== false && $totalBytes > 0) {
        $usedMb  = round(($totalBytes - $freeBytes) / 1048576, 1);
        $totalMb = round($totalBytes / 1048576, 1);
        $pct     = round(($totalBytes - $freeBytes) / $totalBytes * 100, 1);
    }
}

if ($usedMb > 0 || $totalMb > 0) {
    if ($totalMb > 0 && $pct > 0) {
        $dStatus = $pct < 80 ? 'ok' : ($pct < 90 ? 'warn' : 'fail');
    } else {
        $dStatus = 'ok'; // Can't assess % without quota, just report size
    }
    $results[] = recordCheck($db, 'disk', $dStatus, 'Disk Usage', [
        'used_mb'  => $usedMb,
        'total_mb' => $totalMb > 0 ? $totalMb : null,
        'percent'  => $totalMb > 0 ? $pct : null,
        'method'   => $diskMethod,
    ]);
    $pctStr = $totalMb > 0 ? "{$pct}% " : '';
    echo "  \u{2713} Disk: {$pctStr}{$usedMb}MB used [{$diskMethod}]\n";
} else {
    $results[] = recordCheck($db, 'disk', 'skip', 'Disk Usage', ['error' => 'Cannot read disk stats']);
    echo "  ! Disk: unable to read\n";
}

// ═════════════════════════════════════════════════════════════════════════════
// CHECKS — Full mode only (run daily)
// ═════════════════════════════════════════════════════════════════════════════

if ($mode === 'full') {

    // 4. SSL Certificate
    echo "\n  [Full mode checks]\n";
    $sslDomain = parse_url($siteUrl, PHP_URL_HOST) ?: 'oregon.tires';
    if (extension_loaded('openssl')) {
        try {
            $ctx = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false]]);
            $client = @stream_socket_client(
                'ssl://' . $sslDomain . ':443', $errno, $errstr, 10,
                STREAM_CLIENT_CONNECT, $ctx
            );
            if ($client) {
                $params = stream_context_get_params($client);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                fclose($client);
                $expiresTs = $cert['validTo_time_t'] ?? 0;
                $daysLeft  = max(0, (int) floor(($expiresTs - time()) / 86400));
                $issuer    = $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'Unknown';
                $expiresAt = date('Y-m-d', $expiresTs);
                $sslStatus = $daysLeft > 30 ? 'ok' : ($daysLeft > 14 ? 'warn' : 'fail');
                $results[] = recordCheck($db, 'ssl', $sslStatus, 'SSL Certificate', [
                    'domain'         => $sslDomain,
                    'expires_at'     => $expiresAt,
                    'days_remaining' => $daysLeft,
                    'issuer'         => $issuer,
                ]);
                echo "  \u{2713} SSL: {$daysLeft} days remaining (expires {$expiresAt}, {$issuer})\n";
            } else {
                $results[] = recordCheck($db, 'ssl', 'fail', 'SSL Certificate', [
                    'error' => "Connection failed: {$errstr}",
                ]);
                echo "  \u{2717} SSL: connection failed — {$errstr}\n";
            }
        } catch (\Throwable $e) {
            $results[] = recordCheck($db, 'ssl', 'fail', 'SSL Certificate', ['error' => $e->getMessage()]);
            echo "  \u{2717} SSL: {$e->getMessage()}\n";
        }
    } else {
        $results[] = recordCheck($db, 'ssl', 'skip', 'SSL Certificate', ['error' => 'OpenSSL extension not loaded']);
        echo "  ! SSL: OpenSSL extension not available\n";
    }

    // 5. Feature tests
    $featureTests = [
        ['url' => $siteUrl . '/api/health.php',                                'label' => 'Health API',    'expect_code' => 200],
        ['url' => $siteUrl . '/api/settings.php',                              'label' => 'Settings API',  'expect_code' => 200],
        ['url' => $siteUrl . '/api/services.php',                              'label' => 'Services API',  'expect_code' => 200],
        ['url' => $siteUrl . '/api/faq.php',                                   'label' => 'FAQ API',       'expect_code' => 200],
        ['url' => $siteUrl . '/api/blog.php',                                  'label' => 'Blog API',      'expect_code' => 200],
        ['url' => $siteUrl . '/api/available-times.php?date=' . date('Y-m-d', strtotime('+1 day')), 'label' => 'Availability API', 'expect_code' => 200],
    ];

    foreach ($featureTests as $ft) {
        $resp = httpCheck($ft['url']);
        $passed = $resp['code'] === $ft['expect_code'];
        $results[] = recordCheck($db, 'feature_test', $passed ? 'ok' : 'fail', $ft['label'], [
            'endpoint'      => str_replace($siteUrl, '', $ft['url']),
            'expected_code' => $ft['expect_code'],
            'actual_code'   => $resp['code'],
        ], $resp['ms']);
        $icon = $passed ? "\u{2713}" : "\u{2717}";
        echo "  {$icon} Feature: {$ft['label']} — HTTP {$resp['code']} ({$resp['ms']}ms)\n";
    }

    // 6. Cron freshness
    $cronChecks = [
        ['label' => 'Appointment Reminders', 'sql' => "SELECT MAX(created_at) FROM oretir_email_logs WHERE log_type LIKE '%reminder%'", 'max_hours' => 36],
        ['label' => 'Email System',          'sql' => "SELECT MAX(created_at) FROM oretir_email_logs", 'max_hours' => 48],
    ];

    foreach ($cronChecks as $cc) {
        try {
            $lastRun = $db->query($cc['sql'])->fetchColumn();
            if ($lastRun) {
                $hoursAgo = round((time() - strtotime($lastRun)) / 3600, 1);
                $cronStatus = $hoursAgo <= $cc['max_hours'] ? 'ok' : 'warn';
            } else {
                $hoursAgo = null;
                $cronStatus = 'warn';
            }
            $results[] = recordCheck($db, 'cron_freshness', $cronStatus, $cc['label'], [
                'last_run'  => $lastRun,
                'hours_ago' => $hoursAgo,
                'max_hours' => $cc['max_hours'],
            ]);
            $icon = $cronStatus === 'ok' ? "\u{2713}" : '!';
            echo "  {$icon} Cron: {$cc['label']} — " . ($hoursAgo !== null ? "{$hoursAgo}h ago" : 'never run') . "\n";
        } catch (\Throwable $e) {
            $results[] = recordCheck($db, 'cron_freshness', 'skip', $cc['label'], ['error' => $e->getMessage()]);
            echo "  ! Cron: {$cc['label']} — error: {$e->getMessage()}\n";
        }
    }

    // 7. SMTP connectivity
    try {
        $vendorAutoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($vendorAutoload)) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
            $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? 465);
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
            $mail->SMTPSecure = ((int) ($_ENV['SMTP_PORT'] ?? 465)) === 465
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Timeout = 10;
            $smtpStart = microtime(true);
            $mail->smtpConnect();
            $smtpMs = (int) round((microtime(true) - $smtpStart) * 1000);
            $mail->smtpClose();
            $results[] = recordCheck($db, 'email', 'ok', 'SMTP Connection', ['latency_ms' => $smtpMs], $smtpMs);
            echo "  \u{2713} SMTP: connected ({$smtpMs}ms)\n";
        } else {
            $results[] = recordCheck($db, 'email', 'skip', 'SMTP Connection', ['error' => 'Vendor not installed']);
            echo "  ! SMTP: vendor not installed\n";
        }
    } catch (\Throwable $e) {
        $results[] = recordCheck($db, 'email', 'fail', 'SMTP Connection', ['error' => $e->getMessage()]);
        echo "  \u{2717} SMTP: {$e->getMessage()}\n";
    }

    // 8. Database backup
    echo "\n  [Database backup]\n";
    $backupDir = '/home/hiphopwo/backups/oregon-tires';
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0750, true);
    }

    $backupFile = $backupDir . '/ot-' . date('Y-m-d') . '.sql.gz';
    $backupOk   = false;
    $backupSize = 0;
    $backupStart = microtime(true);

    if (function_exists('exec') && !empty($_ENV['DB_HOST'])) {
        $cmd = sprintf(
            'mysqldump --single-transaction -h %s -u %s -p%s %s 2>/dev/null | gzip > %s',
            escapeshellarg($_ENV['DB_HOST']),
            escapeshellarg($_ENV['DB_USER']),
            escapeshellarg($_ENV['DB_PASSWORD']),
            escapeshellarg($_ENV['DB_NAME']),
            escapeshellarg($backupFile)
        );
        exec($cmd, $output, $exitCode);
        $backupOk = ($exitCode === 0 && file_exists($backupFile) && filesize($backupFile) > 100);
        if ($backupOk) {
            $backupSize = filesize($backupFile);
        }
    }

    // Fallback: PHP-based dump
    if (!$backupOk) {
        try {
            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $sql = "-- Oregon Tires DB Backup " . date('Y-m-d H:i:s') . "\n-- Tables: " . count($tables) . "\n\n";
            foreach ($tables as $table) {
                $create = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n" . $create['Create Table'] . ";\n\n";
                $rows = $db->query("SELECT * FROM `{$table}`");
                while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
                    $vals = array_map(function ($v) use ($db) {
                        return $v === null ? 'NULL' : $db->quote((string) $v);
                    }, $row);
                    $sql .= "INSERT INTO `{$table}` VALUES(" . implode(',', $vals) . ");\n";
                }
                $sql .= "\n";
            }
            $gz = gzopen($backupFile, 'w9');
            if ($gz) {
                gzwrite($gz, $sql);
                gzclose($gz);
                $backupOk = file_exists($backupFile) && filesize($backupFile) > 100;
                if ($backupOk) $backupSize = filesize($backupFile);
            }
        } catch (\Throwable $e) {
            error_log("health-monitor: PHP backup failed: " . $e->getMessage());
        }
    }

    $backupDuration = (int) round(microtime(true) - $backupStart);
    if ($backupOk) {
        $sizeMb = round($backupSize / 1048576, 2);
        $results[] = recordCheck($db, 'backup', 'ok', 'Database Backup', [
            'file'         => basename($backupFile),
            'size_bytes'   => $backupSize,
            'size_mb'      => $sizeMb,
            'duration_sec' => $backupDuration,
            'method'       => function_exists('exec') ? 'mysqldump' : 'php',
        ]);
        echo "  \u{2713} Backup: {$sizeMb}MB ({$backupDuration}s) → {$backupFile}\n";
    } else {
        $results[] = recordCheck($db, 'backup', 'fail', 'Database Backup', [
            'error' => 'Backup failed or empty file',
        ]);
        echo "  \u{2717} Backup: FAILED\n";
    }

    // Backup retention — delete files older than 30 days
    if (is_dir($backupDir)) {
        $cutoff = time() - (30 * 86400);
        foreach (glob($backupDir . '/ot-*.sql.gz') as $oldFile) {
            if (filemtime($oldFile) < $cutoff) {
                @unlink($oldFile);
                echo "  \u{2713} Deleted old backup: " . basename($oldFile) . "\n";
            }
        }
    }

    // 9. Data retention — prune health checks older than 90 days
    try {
        $pruned = $db->exec("DELETE FROM oretir_health_checks WHERE checked_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        if ($pruned > 0) echo "  \u{2713} Pruned {$pruned} old health check records\n";
    } catch (\Throwable $e) {
        error_log("health-monitor: prune failed: " . $e->getMessage());
    }

    // 10. Daily report email
    echo "\n  [Sending daily report]\n";

    // Aggregate stats
    $uptime24h = (float) ($db->query(
        "SELECT COALESCE(COUNT(CASE WHEN status = 'ok' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0)
         FROM oretir_health_checks WHERE check_type = 'uptime' AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    )->fetchColumn());

    $uptime7d = (float) ($db->query(
        "SELECT COALESCE(COUNT(CASE WHEN status = 'ok' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0)
         FROM oretir_health_checks WHERE check_type = 'uptime' AND checked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    )->fetchColumn());

    $incidents24h = $db->query(
        "SELECT COUNT(*) FROM oretir_health_checks WHERE status IN ('warn','fail') AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    )->fetchColumn();

    // Overall status
    $hasFailures = false;
    $hasWarnings = false;
    foreach ($results as $r) {
        if ($r['status'] === 'fail') $hasFailures = true;
        if ($r['status'] === 'warn') $hasWarnings = true;
    }
    $overallEmoji = $hasFailures ? "\xF0\x9F\x94\xB4" : ($hasWarnings ? "\xF0\x9F\x9F\xA1" : "\xF0\x9F\x9F\xA2");
    $overallText  = $hasFailures ? 'Issues Detected' : ($hasWarnings ? 'Warnings' : 'All Systems Operational');

    // Build report HTML
    $date = date('F j, Y');
    $featurePassed = 0;
    $featureFailed = 0;
    $featureHtml   = '';
    $cronHtml      = '';
    foreach ($results as $r) {
        if ($r['check_type'] === 'feature_test') {
            if ($r['status'] === 'ok') $featurePassed++;
            else $featureFailed++;
            $icon = $r['status'] === 'ok' ? "\u{2713}" : "\u{2717}";
            $featureHtml .= "<tr><td style=\"padding:4px 12px;\">{$icon} {$r['label']}</td><td style=\"padding:4px 12px;color:" . ($r['status'] === 'ok' ? '#16a34a' : '#dc2626') . ";font-weight:bold;\">" . strtoupper($r['status']) . "</td></tr>";
        }
        if ($r['check_type'] === 'cron_freshness') {
            $d = $r['details'] ? json_decode($r['details'], true) : [];
            $ago = $d['hours_ago'] !== null ? round($d['hours_ago'], 1) . 'h ago' : 'never';
            $icon = $r['status'] === 'ok' ? "\u{2713}" : '!';
            $cronHtml .= "<tr><td style=\"padding:4px 12px;\">{$icon} {$r['label']}</td><td style=\"padding:4px 12px;\">{$ago}</td></tr>";
        }
    }

    // SSL details
    $sslDetail = '';
    foreach ($results as $r) {
        if ($r['check_type'] === 'ssl') {
            $d = $r['details'] ? json_decode($r['details'], true) : [];
            $sslDetail = ($r['status'] === 'ok' ? "\u{2713} Valid" : "\u{26A0} Warning") . " — " . ($d['days_remaining'] ?? '?') . " days remaining";
            if (!empty($d['expires_at'])) $sslDetail .= " (expires {$d['expires_at']})";
            break;
        }
    }

    // Backup details
    $backupDetail = '';
    foreach ($results as $r) {
        if ($r['check_type'] === 'backup') {
            $d = $r['details'] ? json_decode($r['details'], true) : [];
            $backupDetail = $r['status'] === 'ok'
                ? "\u{2713} Completed — " . ($d['size_mb'] ?? '?') . " MB (" . ($d['duration_sec'] ?? '?') . "s)"
                : "\u{2717} Failed";
            break;
        }
    }

    // Disk details
    $diskDetail = '';
    foreach ($results as $r) {
        if ($r['check_type'] === 'disk') {
            $d = $r['details'] ? json_decode($r['details'], true) : [];
            $diskDetail = ($d['percent'] ?? '?') . "% used (" . ($d['used_mb'] ?? '?') . " MB / " . ($d['total_mb'] ?? '?') . " MB)";
            break;
        }
    }

    $html = <<<HTML
<div style="font-family:system-ui,sans-serif;max-width:600px;margin:0 auto;">
  <div style="background:linear-gradient(135deg,#15803d,#166534);color:white;padding:24px;border-radius:12px 12px 0 0;">
    <h1 style="margin:0;font-size:20px;">Oregon Tires — Health Report</h1>
    <p style="margin:4px 0 0;opacity:0.9;">{$date}</p>
  </div>
  <div style="background:#f8fafc;padding:20px;border:1px solid #e2e8f0;border-top:none;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:16px;text-align:center;">
      <p style="font-size:24px;margin:0 0 4px;">{$overallEmoji}</p>
      <p style="font-weight:bold;font-size:16px;margin:0;">{$overallText}</p>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <tr style="border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 12px;font-weight:600;">Uptime</td>
        <td style="padding:10px 12px;">24h: <b>{$uptime24h}%</b> | 7d: <b>{$uptime7d}%</b></td>
      </tr>
      <tr style="border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 12px;font-weight:600;">SSL Certificate</td>
        <td style="padding:10px 12px;">{$sslDetail}</td>
      </tr>
      <tr style="border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 12px;font-weight:600;">Backup</td>
        <td style="padding:10px 12px;">{$backupDetail}</td>
      </tr>
      <tr style="border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 12px;font-weight:600;">Disk</td>
        <td style="padding:10px 12px;">{$diskDetail}</td>
      </tr>
      <tr style="border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 12px;font-weight:600;">Incidents (24h)</td>
        <td style="padding:10px 12px;">{$incidents24h}</td>
      </tr>
    </table>
HTML;

    if ($featureHtml) {
        $html .= "<h3 style=\"font-size:14px;margin:16px 0 8px;font-weight:600;\">Feature Tests ({$featurePassed}/{" . ($featurePassed + $featureFailed) . "} passed)</h3>";
        $html .= "<table style=\"width:100%;border-collapse:collapse;font-size:13px;\">{$featureHtml}</table>";
    }
    if ($cronHtml) {
        $html .= "<h3 style=\"font-size:14px;margin:16px 0 8px;font-weight:600;\">Cron Jobs</h3>";
        $html .= "<table style=\"width:100%;border-collapse:collapse;font-size:13px;\">{$cronHtml}</table>";
    }

    $html .= '</div></div>';

    $subject = "[Oregon Tires] Health Report — {$overallText} — " . date('Y-m-d');

    if (function_exists('notifyOwner')) {
        $result = notifyOwner($subject, $html, '');
        echo $result['success'] ? "  \u{2713} Report email sent\n" : "  \u{2717} Report email failed: " . ($result['error'] ?? '') . "\n";
    } else {
        echo "  ! notifyOwner() not available\n";
    }
}

// Summary
$okCount   = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
$warnCount = count(array_filter($results, fn($r) => $r['status'] === 'warn'));
$failCount = count(array_filter($results, fn($r) => $r['status'] === 'fail'));
echo "\n" . date('Y-m-d H:i:s') . " Complete: {$okCount} ok, {$warnCount} warn, {$failCount} fail\n";
