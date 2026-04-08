<?php
$secret = 'OT_CRON_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$action = $_GET['action'] ?? '';
$phpBin = '/opt/cpanel/ea-php83/root/usr/bin/php';
$home = '/home2/avadpnmy';
$logFile = "$home/logs/ot-cron.log";

$cronJobs = [
    "0 18 * * * $phpBin $home/public_html/cli/send-reminders.php >> $logFile 2>&1",
    "0 10 * * * $phpBin $home/public_html/cli/send-review-requests.php >> $logFile 2>&1",
    "0 6 * * * $phpBin $home/public_html/cli/fetch-google-reviews.php >> $logFile 2>&1",
    "*/5 * * * * $phpBin $home/public_html/cli/send-push-notifications.php >> $logFile 2>&1",
    "0 9 * * 1 $phpBin $home/public_html/cli/send-service-reminders.php >> $logFile 2>&1",
    "0 7 * * 1 $phpBin $home/public_html/cli/sync-google-business.php >> $logFile 2>&1",
    "*/2 * * * * $phpBin $home/public_html/cli/fetch-inbound-emails.php >> $logFile 2>&1",
    "0 10 * * * $phpBin $home/public_html/cli/send-estimate-reminders.php >> $logFile 2>&1",
];

switch ($action) {
    case 'list':
        exec('crontab -l 2>&1', $out, $rc);
        echo "Current crontab (rc=$rc):\n";
        echo implode("\n", $out) . "\n";
        break;

    case 'add':
        // Get existing crontab
        exec('crontab -l 2>&1', $existing, $rc);
        $current = ($rc === 0) ? implode("\n", $existing) : '';

        // Filter out any old Oregon Tires entries
        $lines = array_filter(explode("\n", $current), function($line) {
            return !str_contains($line, 'oregon') && !str_contains($line, 'ot-cron') && trim($line) !== '';
        });

        // Add header + new jobs
        $lines[] = '';
        $lines[] = '# === Oregon Tires cron jobs ===';
        foreach ($cronJobs as $job) {
            $lines[] = $job;
        }

        $newCrontab = implode("\n", $lines) . "\n";

        // Write to temp file and install
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $newCrontab);
        exec("crontab $tmpFile 2>&1", $out2, $rc2);
        unlink($tmpFile);

        if ($rc2 === 0) {
            echo "Crontab installed successfully!\n\n";
            exec('crontab -l 2>&1', $verify);
            echo implode("\n", $verify) . "\n";
        } else {
            echo "FAILED (rc=$rc2): " . implode("\n", $out2) . "\n";
        }
        break;

    case 'cleanup':
        @unlink(__FILE__);
        @unlink(str_replace('_cpanel-crons2.php', '_cpanel-crons.php', __FILE__));
        echo "Cron setup scripts removed.";
        break;

    default:
        echo "Actions: list, add, cleanup";
}
