<?php
/**
 * Oregon Tires — Fetch Inbound Emails (Cron)
 *
 * Connects to IMAP, fetches unseen emails, threads them into conversations.
 * Uses flock() to prevent concurrent runs.
 *
 * Cron: every 2 minutes via crontab
 */

declare(strict_types=1);

// Lock file to prevent concurrent runs
$lockFile = '/tmp/ot-email-fetch.lock';
$lockHandle = fopen($lockFile, 'w');
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo date('Y-m-d H:i:s') . " — Another instance is running. Skipping.\n";
    exit(0);
}

// Server: flat layout (cli/ and includes/ are siblings under ---oregon.tires/)
// Local dev: cli/ is outside public_html/, includes/ is under public_html/
$includesDir = file_exists(__DIR__ . '/../includes/bootstrap.php')
    ? __DIR__ . '/../includes'
    : __DIR__ . '/../public_html/includes';

require_once $includesDir . '/bootstrap.php';
require_once $includesDir . '/mail.php';
require_once $includesDir . '/email-fetcher.php';

try {
    $db = getDB();
    $fetcher = new EmailFetcher($db);

    if (!$fetcher->connect()) {
        echo date('Y-m-d H:i:s') . " — IMAP connection failed. Check .env configuration.\n";
        exit(1);
    }

    $count = $fetcher->fetchNewEmails();
    $fetcher->disconnect();

    echo date('Y-m-d H:i:s') . " — Processed {$count} new email(s).\n";

} catch (\Throwable $e) {
    echo date('Y-m-d H:i:s') . " — Error: " . $e->getMessage() . "\n";
    error_log('fetch-inbound-emails.php error: ' . $e->getMessage());
    exit(1);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}
