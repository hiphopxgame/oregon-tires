<?php
$secret = 'OT_CRON_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$action = $_GET['action'] ?? '';
$phpBin = '/opt/cpanel/ea-php83/root/usr/bin/php';
$home = '/home2/avadpnmy';

switch ($action) {
    case 'add-backup':
        // Add daily health monitor (with DB backup) cron at 3 AM
        $backupCmd = "$phpBin $home/public_html/cli/health-monitor.php --full >> $home/logs/ot-health.log 2>&1";

        exec('crontab -l 2>&1', $existing, $rc);
        $current = ($rc === 0) ? implode("\n", $existing) : '';

        $lines = explode("\n", $current);
        $lines[] = '';
        $lines[] = '# === Oregon Tires daily backup + health check ===';
        $lines[] = "0 3 * * * $backupCmd";

        $newCrontab = implode("\n", $lines) . "\n";
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $newCrontab);
        exec("crontab $tmpFile 2>&1", $out, $rc2);
        unlink($tmpFile);

        if ($rc2 === 0) {
            echo "Backup cron added!\n\n";
            // Create backup directory
            @mkdir("$home/backups/oregon-tires", 0750, true);
            echo "Backup dir created: $home/backups/oregon-tires\n\n";

            exec('crontab -l 2>&1', $verify);
            echo implode("\n", $verify) . "\n";
        } else {
            echo "FAILED: " . implode("\n", $out) . "\n";
        }
        break;

    case 'test-backup':
        // Run a quick backup test
        @mkdir("$home/backups/oregon-tires", 0750, true);
        exec("$phpBin $home/public_html/cli/health-monitor.php --full 2>&1", $out, $rc);
        echo "rc=$rc\n" . implode("\n", array_slice($out, 0, 30)) . "\n";

        // Check if backup was created
        $files = glob("$home/backups/oregon-tires/ot-*.sql.gz");
        if ($files) {
            foreach ($files as $f) {
                echo "\nBackup: " . basename($f) . " (" . round(filesize($f)/1024) . " KB)\n";
            }
        } else {
            echo "\nNo backup files found.\n";
        }
        break;

    case 'cleanup':
        @unlink(__DIR__ . '/_email-check.php');
        @unlink(__FILE__);
        echo "Cleanup scripts removed.";
        break;

    default:
        echo "Actions: add-backup, test-backup, cleanup";
}
