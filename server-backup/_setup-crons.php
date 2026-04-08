<?php
// Setup helper — run cron test and provide cPanel cron setup instructions
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$action = $_GET['action'] ?? '';
$phpBin = PHP_BINDIR . '/php';
$cliDir = __DIR__ . '/cli';

switch ($action) {
    case 'info':
        echo "PHP binary: $phpBin\n";
        echo "CLI dir: $cliDir\n";
        echo "CLI dir exists: " . (is_dir($cliDir) ? 'YES' : 'NO') . "\n";
        exec("which php 2>&1", $out);
        echo "which php: " . implode('', $out) . "\n";
        exec("$phpBin -v 2>&1", $out2);
        echo "php -v: " . ($out2[0] ?? 'N/A') . "\n";

        // List CLI scripts
        echo "\nCLI scripts:\n";
        foreach (glob($cliDir . '/*.php') as $f) {
            echo "  " . basename($f) . "\n";
        }
        break;

    case 'test-bootstrap':
        // Test that CLI scripts can load bootstrap
        echo "Testing CLI bootstrap...\n";
        $cmd = "$phpBin $cliDir/health-monitor.php 2>&1";
        exec($cmd, $out, $rc);
        echo "health-monitor.php rc=$rc\n";
        echo implode("\n", array_slice($out, 0, 20)) . "\n";
        break;

    case 'test-smtp':
        // Test SMTP by loading bootstrap and sending a test email
        require_once __DIR__ . '/includes/bootstrap.php';
        require_once __DIR__ . '/includes/mail.php';

        $to = $_ENV['CONTACT_EMAIL'] ?? 'oregontirespdx@gmail.com';
        echo "Testing SMTP to: $to\n";
        echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? 'not set') . "\n";
        echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? 'not set') . "\n";
        echo "SMTP_FROM: " . ($_ENV['SMTP_FROM'] ?? 'not set') . "\n";

        $result = sendMail(
            $to,
            'Oregon Tires — BlueHost Migration Test',
            '<h2>BlueHost Migration Test</h2><p>If you receive this email, SMTP is working on the new BlueHost server.</p><p>Sent at: ' . date('Y-m-d H:i:s T') . '</p>',
            "BlueHost Migration Test\n\nIf you receive this email, SMTP is working on the new BlueHost server.\n\nSent at: " . date('Y-m-d H:i:s T')
        );
        echo "sendMail result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        break;

    case 'cron-commands':
        // Output the exact cron commands for cPanel
        $home = dirname(__DIR__);
        echo "=== CRON JOBS FOR CPANEL ===\n\n";
        echo "PHP binary: $phpBin\n";
        echo "Home: $home\n\n";

        $crons = [
            ['0', '18', '*', '*', '*', 'send-reminders.php', 'Daily 6PM - appointment reminders'],
            ['0', '10', '*', '*', '*', 'send-review-requests.php', 'Daily 10AM - review requests'],
            ['0', '6', '*', '*', '*', 'fetch-google-reviews.php', 'Daily 6AM - Google reviews'],
            ['*/5', '*', '*', '*', '*', 'send-push-notifications.php', 'Every 5min - push notifications'],
            ['0', '9', '*', '*', '1', 'send-service-reminders.php', 'Mon 9AM - service reminders'],
            ['0', '7', '*', '*', '1', 'sync-google-business.php', 'Mon 7AM - GBP sync'],
            ['*/2', '*', '*', '*', '*', 'fetch-inbound-emails.php', 'Every 2min - inbound email'],
            ['0', '10', '*', '*', '*', 'send-estimate-reminders.php', 'Daily 10AM - estimate reminders'],
        ];

        foreach ($crons as $c) {
            $schedule = "{$c[0]} {$c[1]} {$c[2]} {$c[3]} {$c[4]}";
            $cmd = "$phpBin {$home}/public_html/cli/{$c[5]} >> {$home}/logs/ot-cron.log 2>&1";
            echo "# {$c[6]}\n";
            echo "Schedule: $schedule\n";
            echo "Command:  $cmd\n\n";
        }
        break;

    case 'bump-sw':
        // Bump service worker cache version
        $swFile = __DIR__ . '/sw.js';
        $content = file_get_contents($swFile);
        if (preg_match("/CACHE_VERSION = '(\d+)'/", $content, $m)) {
            $oldVer = (int)$m[1];
            $newVer = $oldVer + 1;
            $content = str_replace("CACHE_VERSION = '{$oldVer}'", "CACHE_VERSION = '{$newVer}'", $content);
            file_put_contents($swFile, $content);
            echo "Service worker cache bumped: v{$oldVer} → v{$newVer}\n";
        } else {
            echo "Could not find CACHE_VERSION in sw.js\n";
        }
        break;

    case 'cleanup':
        @unlink(__FILE__);
        echo "Setup script removed.";
        break;

    default:
        echo "Actions: info, test-bootstrap, test-smtp, cron-commands, bump-sw, cleanup";
}
