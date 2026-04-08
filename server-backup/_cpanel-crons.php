<?php
// Set up cron jobs via cPanel UAPI (runs as cPanel user)
$secret = 'OT_CRON_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$action = $_GET['action'] ?? '';
$phpBin = '/opt/cpanel/ea-php83/root/usr/bin/php';
$home = '/home2/avadpnmy';
$logFile = "$home/logs/ot-cron.log";

// cPanel UAPI helper - uses local socket when running as cPanel user
function cpanel_uapi(string $module, string $function, array $params = []): array {
    $query = http_build_query($params);
    $url = "https://127.0.0.1:2083/execute/{$module}/{$function}?{$query}";

    // Try using cpanel binary first (most reliable on shared hosting)
    $paramStr = '';
    foreach ($params as $k => $v) {
        $paramStr .= ' ' . escapeshellarg("$k=$v");
    }
    $cmd = "/usr/local/cpanel/bin/uapi {$module} {$function}{$paramStr} --output=json 2>&1";
    exec($cmd, $out, $rc);
    $json = implode('', $out);
    $result = json_decode($json, true);
    if ($result !== null) {
        return $result;
    }

    // Fallback: try cpanel CLI
    $cmd2 = "uapi {$module} {$function}{$paramStr} --output=json 2>&1";
    exec($cmd2, $out2, $rc2);
    $json2 = implode('', $out2);
    $result2 = json_decode($json2, true);
    if ($result2 !== null) {
        return $result2;
    }

    return ['status' => 0, 'errors' => ["UAPI call failed (rc=$rc): " . substr($json, 0, 500)]];
}

switch ($action) {
    case 'list':
        $result = cpanel_uapi('Cron', 'list_cron');
        if (!empty($result['result']['data'])) {
            echo "Current cron jobs:\n";
            foreach ($result['result']['data'] as $job) {
                echo "  [{$job['linekey']}] {$job['minute']} {$job['hour']} {$job['day']} {$job['month']} {$job['weekday']} {$job['command']}\n";
            }
        } elseif (!empty($result['errors'])) {
            echo "Error: " . implode(', ', $result['errors']) . "\n";
        } else {
            echo "No cron jobs found or UAPI not available.\n";
            echo "Raw: " . json_encode($result) . "\n";
        }
        break;

    case 'add':
        $crons = [
            ['0', '18', '*', '*', '*', "$phpBin $home/public_html/cli/send-reminders.php >> $logFile 2>&1"],
            ['0', '10', '*', '*', '*', "$phpBin $home/public_html/cli/send-review-requests.php >> $logFile 2>&1"],
            ['0', '6', '*', '*', '*', "$phpBin $home/public_html/cli/fetch-google-reviews.php >> $logFile 2>&1"],
            ['*/5', '*', '*', '*', '*', "$phpBin $home/public_html/cli/send-push-notifications.php >> $logFile 2>&1"],
            ['0', '9', '*', '*', '1', "$phpBin $home/public_html/cli/send-service-reminders.php >> $logFile 2>&1"],
            ['0', '7', '*', '*', '1', "$phpBin $home/public_html/cli/sync-google-business.php >> $logFile 2>&1"],
            ['*/2', '*', '*', '*', '*', "$phpBin $home/public_html/cli/fetch-inbound-emails.php >> $logFile 2>&1"],
            ['0', '10', '*', '*', '*', "$phpBin $home/public_html/cli/send-estimate-reminders.php >> $logFile 2>&1"],
        ];

        $added = 0;
        $failed = 0;
        foreach ($crons as $c) {
            $result = cpanel_uapi('Cron', 'add_line', [
                'minute'  => $c[0],
                'hour'    => $c[1],
                'day'     => $c[2],
                'month'   => $c[3],
                'weekday' => $c[4],
                'command' => $c[5],
            ]);

            $status = $result['result']['status'] ?? ($result['status'] ?? 0);
            if ($status) {
                $added++;
                echo "  OK: {$c[0]} {$c[1]} {$c[2]} {$c[3]} {$c[4]} — " . basename(explode(' ', $c[5])[1]) . "\n";
            } else {
                $failed++;
                $err = $result['result']['errors'] ?? $result['errors'] ?? ['unknown'];
                echo "  FAIL: " . basename(explode(' ', $c[5])[1]) . " — " . implode(', ', (array)$err) . "\n";
            }
        }
        echo "\nAdded: $added, Failed: $failed\n";
        break;

    case 'cleanup':
        @unlink(__FILE__);
        echo "Cron setup script removed.";
        break;

    default:
        echo "Actions: list, add, cleanup";
}
