<?php
/**
 * Oregon Tires — Wipe Test Data
 *
 * Truncates all operational/transactional tables while preserving:
 *   - User accounts (admins, employees, employee groups/skills)
 *   - Curated content (blog, FAQ, promotions, testimonials, gallery, services)
 *   - Configuration (site settings, business hours, holidays, schedules, care plans)
 *   - Subscribers (newsletter list)
 *
 * Refuses to run without --confirm=yes-wipe-oregon-tires.
 *
 * Run on server:
 *   php cli/wipe-test-data.php --confirm=yes-wipe-oregon-tires
 */

declare(strict_types=1);

// Server: cli/ is sibling to includes/; Local: cli/ is sibling to public_html/includes/
$bootstrapPath = __DIR__ . '/../includes/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    $bootstrapPath = __DIR__ . '/../public_html/includes/bootstrap.php';
}
require_once $bootstrapPath;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$confirm = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--confirm=')) {
        $confirm = substr($arg, 10);
    }
}
if ($confirm !== 'yes-wipe-oregon-tires') {
    fwrite(STDERR, "Refusing to run without --confirm=yes-wipe-oregon-tires\n");
    exit(1);
}

$pdo = getDB();

/** Tables to TRUNCATE — operational/transactional only. */
$wipe = [
    // Phase 1 — leaf caches & logs
    'oretir_contact_messages',
    'oretir_feedback',
    'oretir_email_logs',
    'oretir_sms_logs',
    'oretir_email_message_ids',
    'oretir_rate_limits',
    'oretir_form_rate_limits',
    'oretir_form_submissions',
    'oretir_vin_cache',
    'oretir_plate_cache',
    'oretir_tire_fitment_cache',
    'oretir_health_checks',
    'oretir_visit_log',
    'oretir_offline_sync_log',
    'oretir_push_subscriptions',
    'oretir_notification_queue',
    'oretir_waitlist',
    'oretir_tire_quotes',

    // Phase 2 — messaging
    'oretir_conversation_messages',
    'oretir_conversations',

    // Phase 3 — shop ops
    'oretir_labor_entries',
    'oretir_service_reminders',
    'oretir_loyalty_points',
    'oretir_referrals',

    // Phase 4 — estimates / invoices
    'oretir_estimate_items',
    'oretir_estimates',
    'oretir_invoice_items',
    'oretir_invoices',

    // Phase 5 — inspections
    'oretir_inspection_photos',
    'oretir_inspection_items',
    'oretir_inspections',

    // Phase 6 — ROs + appointments
    'oretir_repair_orders',
    'oretir_appointments',

    // Phase 7 — core customer/vehicle (FK roots — last)
    'oretir_vehicles',
    'oretir_customers',
];

/** Tables to PRESERVE — verified after wipe (count must not change). */
$keep = [
    'oretir_admins',
    'oretir_employees',
    'oretir_employee_groups',
    'oretir_employee_skills',
    'oretir_subscribers',
    'oretir_blog_posts',
    'oretir_blog_categories',
    'oretir_blog_post_categories',
    'oretir_faq',
    'oretir_promotions',
    'oretir_testimonials',
    'oretir_gallery_images',
    'oretir_service_images',
    'oretir_services',
    'oretir_service_faqs',
    'oretir_service_related',
    'oretir_care_plans',
    'oretir_estimate_templates',
    'oretir_business_hours',
    'oretir_holidays',
    'oretir_schedules',
    'oretir_schedule_overrides',
    'oretir_site_settings',
    'oretir_loyalty_rewards',
    'oretir_form_configs',
];

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

function rowCount(PDO $pdo, string $table): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
}

echo "=== WIPE TEST DATA — oregon.tires ===\n";
echo "DB: " . ($_ENV['DB_NAME'] ?? '?') . "\n\n";

echo "Pre-wipe counts:\n";
$pre = [];
foreach ($wipe as $t) {
    if (!tableExists($pdo, $t)) { echo "  [skip] {$t} (does not exist)\n"; continue; }
    $pre[$t] = rowCount($pdo, $t);
    printf("  %-40s %8d\n", $t, $pre[$t]);
}
$keepPre = [];
foreach ($keep as $t) {
    if (!tableExists($pdo, $t)) continue;
    $keepPre[$t] = rowCount($pdo, $t);
}

echo "\nTruncating...\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($wipe as $t) {
    if (!tableExists($pdo, $t)) continue;
    $pdo->exec("TRUNCATE TABLE `{$t}`");
    echo "  [done] {$t}\n";
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

echo "\nPost-wipe verification:\n";
$failed = 0;
foreach ($wipe as $t) {
    if (!tableExists($pdo, $t)) continue;
    $n = rowCount($pdo, $t);
    if ($n !== 0) { echo "  [FAIL] {$t} = {$n} (expected 0)\n"; $failed++; }
}
foreach ($keep as $t) {
    if (!tableExists($pdo, $t)) continue;
    $n = rowCount($pdo, $t);
    if ($n !== ($keepPre[$t] ?? -1)) {
        echo "  [FAIL] {$t} changed: was {$keepPre[$t]}, now {$n}\n";
        $failed++;
    }
}

if ($failed) {
    fwrite(STDERR, "\n{$failed} verification failure(s).\n");
    exit(2);
}
echo "\nAll wipe tables empty. All keep tables unchanged. ✓\n";
exit(0);
