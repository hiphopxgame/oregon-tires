<?php
/**
 * Oregon Tires — Estimate Expiry Reminder Emails
 * Sends a reminder to customers whose estimates expire in 2 days,
 * prompting them to review and approve before the estimate expires.
 *
 * Usage:  php cli/send-estimate-reminders.php [--dry-run]
 * Cron:   0 10 * * *   php /home/hiphopwo/public_html/---oregon.tires/cli/send-estimate-reminders.php
 *
 * Idempotent: skips estimates that already have an 'estimate_expiry_reminder'
 * email log entry within the last 3 days.
 */

declare(strict_types=1);

// ─── Bootstrap ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/../public_html/includes/bootstrap.php';
require_once __DIR__ . '/../public_html/includes/mail.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

echo "Oregon Tires — Estimate Expiry Reminders\n";
echo str_repeat('=', 50) . "\n";
echo $dryRun ? "MODE: DRY RUN (no emails will be sent)\n\n" : "MODE: LIVE\n\n";

try {
    $db = getDB();

    // ─── Query estimates expiring in 2 days ──────────────────────────────
    // Only target estimates in 'sent' or 'viewed' status (waiting for customer action)
    // with valid_until exactly 2 days from now.
    $stmt = $db->prepare(
        "SELECT e.id AS estimate_id,
                e.estimate_number,
                e.approval_token,
                e.total,
                e.valid_until,
                r.ro_number,
                c.first_name,
                c.last_name,
                c.email,
                c.language,
                v.year AS vehicle_year,
                v.make AS vehicle_make,
                v.model AS vehicle_model
         FROM oretir_estimates e
         JOIN oretir_repair_orders r ON r.id = e.repair_order_id
         JOIN oretir_customers c ON c.id = r.customer_id
         LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
         WHERE e.status IN ('sent', 'viewed')
           AND DATE(e.valid_until) = DATE(NOW() + INTERVAL 2 DAY)
           AND e.approval_token IS NOT NULL
         ORDER BY e.id ASC"
    );
    $stmt->execute();
    $estimates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($estimates) . " estimate(s) expiring in 2 days.\n\n";

    if (empty($estimates)) {
        echo "Nothing to do.\n";
        exit(0);
    }

    // ─── Prepare idempotency check ───────────────────────────────────────
    // Skip if an 'estimate_expiry_reminder' log entry already exists for this
    // estimate's RO number within the last 3 days.
    $alreadySentStmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_email_logs
         WHERE log_type = 'estimate_expiry_reminder'
           AND description LIKE ?
           AND created_at >= NOW() - INTERVAL 3 DAY"
    );

    $sent    = 0;
    $skipped = 0;
    $errors  = 0;

    foreach ($estimates as $est) {
        $roNumber       = $est['ro_number'];
        $estimateNumber = $est['estimate_number'];
        $customerName   = trim($est['first_name'] . ' ' . $est['last_name']);
        $customerEmail  = $est['email'];
        $customerLang   = ($est['language'] === 'spanish') ? 'es' : 'en';
        $vehicleStr     = trim(($est['vehicle_year'] ?? '') . ' ' . ($est['vehicle_make'] ?? '') . ' ' . ($est['vehicle_model'] ?? ''));
        $total          = '$' . number_format((float) $est['total'], 2);
        $validUntil     = $est['valid_until'];
        $approvalToken  = $est['approval_token'];

        echo "  [{$estimateNumber}] RO {$roNumber} — {$customerName} <{$customerEmail}>\n";
        echo "    Vehicle: {$vehicleStr} | Total: {$total} | Expires: {$validUntil}\n";

        // ── Idempotency: check if reminder already sent for this RO ──
        $alreadySentStmt->execute(["%{$roNumber}%"]);
        $alreadySent = (int) $alreadySentStmt->fetchColumn() > 0;

        if ($alreadySent) {
            echo "    SKIPPED (reminder already sent within last 3 days)\n\n";
            $skipped++;
            continue;
        }

        if ($dryRun) {
            echo "    WOULD SEND reminder email\n\n";
            $sent++;
            continue;
        }

        // ── Build approval URL ───────────────────────────────────────
        $baseUrl    = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $approveUrl = $baseUrl . '/approve.php?token=' . urlencode($approvalToken);

        // ── Send using sendEstimateEmail() ───────────────────────────
        $result = sendEstimateEmail(
            $customerEmail,
            $customerName,
            $roNumber,
            $vehicleStr,
            $total,
            $approveUrl,
            $customerLang
        );

        if ($result['success']) {
            logEmail(
                'estimate_expiry_reminder',
                "Expiry reminder sent to {$customerEmail} for {$roNumber} estimate {$estimateNumber} (expires: {$validUntil})"
            );
            echo "    SENT successfully\n\n";
            $sent++;
        } else {
            logEmail(
                'estimate_expiry_reminder_failed',
                "Expiry reminder FAILED for {$customerEmail} {$roNumber} estimate {$estimateNumber}: " . ($result['error'] ?? 'unknown')
            );
            echo "    ERROR: " . ($result['error'] ?? 'unknown') . "\n\n";
            $errors++;
        }
    }

    // ─── Summary ─────────────────────────────────────────────────────────
    echo str_repeat('=', 50) . "\n";
    echo "Summary:\n";
    echo "  Estimates found:  " . count($estimates) . "\n";
    echo "  Reminders sent:   {$sent}\n";
    echo "  Skipped:          {$skipped}\n";
    echo "  Errors:           {$errors}\n";

    if ($dryRun) {
        echo "\n  ** DRY RUN — no emails were sent **\n";
        echo "  Run without --dry-run to send reminders.\n";
    }

    echo "\n";

    // Exit with error code if any failures occurred
    exit($errors > 0 ? 1 : 0);

} catch (\Throwable $e) {
    error_log("Oregon Tires send-estimate-reminders.php error: " . $e->getMessage());
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
