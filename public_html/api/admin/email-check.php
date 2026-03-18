<?php
/**
 * Admin Email Check API
 *
 * GET — Manually trigger IMAP fetch for new inbound emails.
 *       Returns count of processed emails.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/email-fetcher.php';

try {
    requireAdmin();
    requireMethod('GET');
    $db = getDB();

    $fetcher = new EmailFetcher($db);

    if (!$fetcher->connect()) {
        jsonError('Could not connect to email server. Check IMAP configuration.', 500);
    }

    $count = $fetcher->fetchNewEmails();
    $fetcher->disconnect();

    jsonSuccess([
        'message' => "Processed {$count} new email(s).",
        'count'   => $count,
    ]);

} catch (\Throwable $e) {
    error_log('email-check.php (admin) error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
