<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$home = '/home2/avadpnmy';
$replacements = [
    '/home/hiphopwo/shared/'       => '/home2/avadpnmy/shared/',
    '/home/hiphopwo/public_html/'  => '/home2/avadpnmy/public_html/',
    '/home/hiphopwo/backups/'      => '/home2/avadpnmy/backups/',
    '/home/hiphopwo/logs/'         => '/home2/avadpnmy/logs/',
    "'/home/hiphopwo'"             => "'/home2/avadpnmy'",
    '"/home/hiphopwo"'             => '"/home2/avadpnmy"',
    "hiphopworld:"                 => "oregontires:",
    'SSH_HOST="hiphopworld"'       => 'SSH_HOST="oregontires"',
    'REMOTE="hiphopworld"'         => 'REMOTE="oregontires"',
    'ssh hiphopworld'              => 'ssh oregontires',
    'scp '                         => 'scp ', // skip — only in docs
    "= '/home/hiphopwo'"           => "= '/home2/avadpnmy'",
    "'/home/hiphopwo'"             => "'/home2/avadpnmy'",
    '/home/hiphopwo'               => '/home2/avadpnmy',
];

// Files to update (code files only, skip docs for now)
$files = [
    // Engine kit
    "$home/shared/engine-kit/loader.php",
    "$home/shared/engine-kit/templates/deploy.sh",
    "$home/shared/engine-kit/composer.json",
    // Member kit
    "$home/shared/member-kit/deploy.sh",
    "$home/shared/member-kit/.env.example",
    "$home/shared/member-kit/templates/site-auth-boilerplate/.env.example",
    // Form kit CLI
    "$home/shared/form-kit/cli/run-migration-oregon.php",
    "$home/shared/form-kit/cli/run-migration-iwitty.php",
    "$home/shared/form-kit/cli/run-migration-nisatax.php",
    "$home/shared/form-kit/cli/add-calendar-sync-columns.php",
    "$home/shared/form-kit/cli/add-google-event-id.php",
    // Oregon Tires code
    "$home/public_html/includes/member-kit-init.php",
    "$home/public_html/cli/health-monitor.php",
    "$home/public_html/cli/send-reminders.php",
    "$home/public_html/cli/send-review-requests.php",
    "$home/public_html/cli/send-service-reminders.php",
    "$home/public_html/cli/fetch-google-reviews.php",
    "$home/public_html/cli/send-estimate-reminders.php",
    "$home/public_html/cli/migrate-network-integration.sql",
    "$home/public_html/cli/migrate-member-kit.sql",
    // Member kit docs (update these too since they're deployed)
    "$home/shared/member-kit/CLAUDE.md",
    "$home/shared/member-kit/docs/DEPLOYMENT_CHECKLIST.md",
    "$home/shared/member-kit/docs/DEPLOYMENT_COMPLETE.md",
    "$home/shared/member-kit/docs/SITE_CONNECTIONS_IMPLEMENTATION.md",
    // Engine kit migrations (comments only but keep consistent)
    "$home/shared/engine-kit/migrations/001_register_satellite_sites.sql",
];

$updated = 0;
$skipped = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "  SKIP (not found): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    // Apply all replacements
    foreach ($replacements as $old => $new) {
        if ($old === $new) continue;
        $content = str_replace($old, $new, $content);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        $shortPath = str_replace($home . '/', '~/', $file);
        echo "  UPDATED: $shortPath\n";
        $updated++;
    }
}

echo "\n$updated files updated, $skipped skipped.\n";

// Verify no remaining references
echo "\n=== Remaining hiphopwo references ===\n";
$dirs = ["$home/shared/engine-kit", "$home/shared/member-kit", "$home/shared/form-kit", "$home/shared/commerce-kit", "$home/public_html/includes", "$home/public_html/cli"];
$remaining = 0;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isDir()) continue;
        $ext = $f->getExtension();
        if (!in_array($ext, ['php', 'json', 'env', 'sh', 'sql', 'example'])) continue;
        $c = @file_get_contents($f->getPathname());
        if ($c && preg_match('/\/home\/hiphopwo/', $c)) {
            $lines = explode("\n", $c);
            foreach ($lines as $num => $line) {
                if (stripos($line, '/home/hiphopwo') !== false) {
                    $shortPath = str_replace($home . '/', '~/', $f->getPathname());
                    echo "  $shortPath:" . ($num+1) . ": " . trim($line) . "\n";
                    $remaining++;
                }
            }
        }
    }
}
echo $remaining === 0 ? "CLEAN — no references remaining!\n" : "\n$remaining references still remain.\n";
