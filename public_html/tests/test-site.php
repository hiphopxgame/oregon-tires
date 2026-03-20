#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Comprehensive Site Test
 * Tests all public endpoints, pages, APIs, and PHP file syntax.
 * Run: php tests/test-site.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$pass = 0;
$fail = 0;
$errors = [];

function ok(bool $cond, string $label): void {
    global $pass, $fail, $errors;
    if ($cond) { echo "  \033[32m✓\033[0m {$label}\n"; $pass++; }
    else       { echo "  \033[31m✗\033[0m {$label}\n"; $fail++; $errors[] = $label; }
}

function httpTest(string $url, int $expectedCode = 200, string $label = ''): void {
    $label = $label ?: $url;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => false,
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        ok(false, "{$label} — curl error: {$err}");
        return;
    }
    ok($code === $expectedCode, "{$label} → {$code}" . ($code !== $expectedCode ? " (expected {$expectedCode})" : ''));
}

function apiTest(string $url, string $label = ''): ?array {
    $label = $label ?: $url;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) { ok(false, "{$label} — curl error: {$err}"); return null; }
    ok($code === 200, "{$label} → {$code}");

    $json = json_decode($body, true);
    if ($json === null && $code === 200) {
        ok(false, "{$label} — invalid JSON response");
        return null;
    }
    return $json;
}

$base = 'https://oregon.tires';

// ═══════════════════════════════════════════════════════
echo "=== 1. Public Pages ===\n";
// ═══════════════════════════════════════════════════════

$pages = [
    '/', '/book-appointment/', '/contact', '/faq', '/why-us', '/reviews',
    '/guarantee', '/members', '/blog', '/promotions', '/care-plan',
    '/tire-installation', '/tire-repair', '/wheel-alignment',
    '/brake-service', '/oil-change', '/engine-diagnostics',
    '/suspension-repair', '/mobile-service', '/roadside-assistance',
    '/service-areas', '/tires-se-portland', '/tires-woodstock',
];
foreach ($pages as $page) {
    httpTest($base . $page, 200, "GET {$page}");
}

// 404 test
httpTest($base . '/nonexistent-page-xyz', 404, 'GET /nonexistent → 404');

// ═══════════════════════════════════════════════════════
echo "\n=== 2. Public API Endpoints ===\n";
// ═══════════════════════════════════════════════════════

$j = apiTest($base . '/api/health.php', 'health');
if ($j) ok(!empty($j['status']), 'health returns status');

$j = apiTest($base . '/api/settings.php', 'settings');
if ($j) {
    ok(($j['success'] ?? false) === true, 'settings.success = true');
    ok(is_array($j['data'] ?? null) && count($j['data']) > 0, 'settings has data');
}

$j = apiTest($base . '/api/gallery.php', 'gallery');
if ($j) ok(($j['success'] ?? false) === true, 'gallery.success = true');

$j = apiTest($base . '/api/promotions.php', 'promotions');
if ($j) ok(($j['success'] ?? false) === true, 'promotions.success = true');

$j = apiTest($base . '/api/promotions.php?placement=exit_intent', 'promotions exit_intent');
if ($j) ok(($j['success'] ?? false) === true, 'promotions exit_intent.success = true');

$j = apiTest($base . '/api/promotions.php?placement=inline', 'promotions inline');
if ($j) ok(($j['success'] ?? false) === true, 'promotions inline.success = true');

$j = apiTest($base . '/api/faq.php', 'faq');
if ($j) ok(($j['success'] ?? false) === true, 'faq.success = true');

$j = apiTest($base . '/api/testimonials.php', 'testimonials');
if ($j) ok(($j['success'] ?? false) === true, 'testimonials.success = true');

$j = apiTest($base . '/api/blog.php', 'blog');
if ($j) ok(($j['success'] ?? false) === true, 'blog.success = true');

$j = apiTest($base . '/api/services.php', 'services');
if ($j) ok(($j['success'] ?? false) === true, 'services.success = true');

// Available times (requires date)
$tomorrow = date('Y-m-d', strtotime('+1 day'));
// Skip Sunday
$dow = (int) date('w', strtotime($tomorrow));
if ($dow === 0) $tomorrow = date('Y-m-d', strtotime('+2 days'));
$j = apiTest($base . '/api/available-times.php?date=' . $tomorrow, 'available-times');
if ($j) ok(($j['success'] ?? false) === true, 'available-times.success = true');

// Service images
$j = apiTest($base . '/api/service-images.php', 'service-images');
if ($j) ok(($j['success'] ?? false) === true, 'service-images.success = true');

// ═══════════════════════════════════════════════════════
echo "\n=== 3. API Error Handling ===\n";
// ═══════════════════════════════════════════════════════

// POST to book without data should return 400
$ch = curl_init($base . '/api/book.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => true,
]);
$body = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok($code === 400 || $code === 429, "POST /api/book.php with empty body → {$code} (expected 400 or 429)");
$bookErr = json_decode($body, true);
ok(!empty($bookErr['error']), 'book.php returns error message: ' . substr($bookErr['error'] ?? '', 0, 60));

// GET to book should return 405
$ch = curl_init($base . '/api/book.php');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true]);
$body = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok($code === 405, "GET /api/book.php → {$code} (expected 405 Method Not Allowed)");

// ═══════════════════════════════════════════════════════
echo "\n=== 4. PHP Syntax Check (all PHP files) ===\n";
// ═══════════════════════════════════════════════════════

$phpDirs = [
    __DIR__ . '/../includes/',
    __DIR__ . '/../api/',
    __DIR__ . '/../api/admin/',
    __DIR__ . '/../api/member/',
    __DIR__ . '/../api/auth/',
    __DIR__ . '/../api/commerce/',
    __DIR__ . '/../api/form/',
    __DIR__ . '/../cli/',
    __DIR__ . '/../templates/partials/',
];

$syntaxErrors = 0;
$syntaxChecked = 0;

// Also check root PHP files
$rootPhp = glob(__DIR__ . '/../*.php');
foreach ($rootPhp as $file) {
    $syntaxChecked++;
    $output = [];
    $ret = 0;
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $ret);
    if ($ret !== 0) {
        $syntaxErrors++;
        ok(false, 'Syntax error: ' . basename($file) . ' — ' . implode(' ', $output));
    }
}

foreach ($phpDirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '*.php');
    foreach ($files as $file) {
        $syntaxChecked++;
        $output = [];
        $ret = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $ret);
        if ($ret !== 0) {
            $syntaxErrors++;
            ok(false, 'Syntax error: ' . basename(dirname($file)) . '/' . basename($file) . ' — ' . implode(' ', $output));
        }
    }
}

ok($syntaxErrors === 0, "All {$syntaxChecked} PHP files pass syntax check");

// ═══════════════════════════════════════════════════════
echo "\n=== 5. Database Integrity ===\n";
// ═══════════════════════════════════════════════════════

// Check all expected tables exist
$expectedTables = [
    'oretir_appointments', 'oretir_customers', 'oretir_vehicles',
    'oretir_repair_orders', 'oretir_inspections', 'oretir_inspection_items',
    'oretir_estimates', 'oretir_estimate_items', 'oretir_invoices', 'oretir_invoice_items',
    'oretir_employees', 'oretir_site_settings', 'oretir_promotions',
    'oretir_contact_messages', 'oretir_subscribers', 'oretir_blog_posts',
    'oretir_faq', 'oretir_gallery_images', 'oretir_email_logs',
    'oretir_vin_cache', 'oretir_rate_limits',
];

$existingTables = [];
$stmt = $db->query("SHOW TABLES LIKE 'oretir_%'");
while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
    $existingTables[] = $row[0];
}

$missing = array_diff($expectedTables, $existingTables);
ok(empty($missing), 'All ' . count($expectedTables) . ' core tables exist' . ($missing ? ' — MISSING: ' . implode(', ', $missing) : ''));

// Check FK columns reference valid data
$orphanCheck = $db->query(
    "SELECT COUNT(*) as cnt FROM oretir_appointments a
     LEFT JOIN oretir_customers c ON a.customer_id = c.id
     WHERE a.customer_id IS NOT NULL AND c.id IS NULL"
)->fetch();
ok((int)$orphanCheck['cnt'] === 0, 'No orphaned appointment→customer FKs (' . $orphanCheck['cnt'] . ' orphans)');

$orphanRo = $db->query(
    "SELECT COUNT(*) as cnt FROM oretir_repair_orders r
     LEFT JOIN oretir_customers c ON r.customer_id = c.id
     WHERE r.customer_id IS NOT NULL AND c.id IS NULL"
)->fetch();
ok((int)$orphanRo['cnt'] === 0, 'No orphaned RO→customer FKs (' . $orphanRo['cnt'] . ' orphans)');

// ═══════════════════════════════════════════════════════
echo "\n=== 6. Critical File Presence ===\n";
// ═══════════════════════════════════════════════════════

$criticalFiles = [
    __DIR__ . '/../includes/bootstrap.php',
    __DIR__ . '/../includes/mail.php',
    __DIR__ . '/../includes/validate.php',
    __DIR__ . '/../includes/schedule.php',
    __DIR__ . '/../includes/vin-decode.php',
    __DIR__ . '/../includes/invoices.php',
    __DIR__ . '/../includes/auth.php',
    __DIR__ . '/../includes/response.php',
    __DIR__ . '/../includes/rate-limit.php',
    __DIR__ . '/../sw.js',
    __DIR__ . '/../assets/styles.css',
    __DIR__ . '/../assets/js/htmx.min.js',
    __DIR__ . '/../assets/js/offline-booking.js',
    __DIR__ . '/../assets/js/pwa-manager.js',
    __DIR__ . '/../assets/js/exit-intent.js',
    __DIR__ . '/../admin/index.html',
    __DIR__ . '/../admin/js/repair-orders.js',
    __DIR__ . '/../admin/js/kanban.js',
];

$missingFiles = [];
foreach ($criticalFiles as $f) {
    if (!file_exists($f)) $missingFiles[] = basename(dirname($f)) . '/' . basename($f);
}
ok(empty($missingFiles), count($criticalFiles) . ' critical files present' . ($missingFiles ? ' — MISSING: ' . implode(', ', $missingFiles) : ''));

// ═══════════════════════════════════════════════════════
echo "\n=== 7. Service Worker Cache Version ===\n";
// ═══════════════════════════════════════════════════════

$sw = file_get_contents(__DIR__ . '/../sw.js');
preg_match("/CACHE_VERSION\s*=\s*'(\d+)'/", $sw, $m);
$cacheVer = $m[1] ?? '?';
ok(!empty($m[1]), "Service worker CACHE_VERSION = {$cacheVer}");

// ═══════════════════════════════════════════════════════
echo "\n=== 8. Homepage Content Checks ===\n";
// ═══════════════════════════════════════════════════════

$ch = curl_init($base . '/');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => true]);
$html = curl_exec($ch);
curl_close($ch);

ok(str_contains($html, 'Oregon Tires'), 'Homepage contains "Oregon Tires"');
ok(str_contains($html, '(503) 367-9714'), 'Homepage contains phone number');
ok(!str_contains($html, '$29 Oil Change'), 'No hardcoded $29 offer on homepage');
ok(!str_contains($html, 'newCustomerSpecial'), 'No newCustomerSpecial translation key');
ok(str_contains($html, 'id="inline-promo"'), 'Inline promo placeholder present');
ok(str_contains($html, 'id="promo-banner"'), 'Promo banner placeholder present');
ok(str_contains($html, 'show_scarcity'), 'Scarcity gating code present');

// ═══════════════════════════════════════════════════════
echo "\n=== 9. Booking Page Content Checks ===\n";
// ═══════════════════════════════════════════════════════

$ch = curl_init($base . '/book-appointment/');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => true]);
$bookHtml = curl_exec($ch);
curl_close($ch);

ok(str_contains($bookHtml, 'name="services[]"'), 'Booking form uses services[] checkboxes');
ok(!str_contains($bookHtml, 'name="service"'), 'No old radio name="service" inputs');
ok(str_contains($bookHtml, 'selected-services-summary'), 'Services summary chips container present');
ok(str_contains($bookHtml, 'MAX_SERVICES'), 'Max services limit defined in JS');
ok(str_contains($bookHtml, 'maxServices'), 'maxServices translation key present');

// ═══════════════════════════════════════════════════════
echo "\n=== 10. Admin Panel Checks ===\n";
// ═══════════════════════════════════════════════════════

$adminHtml = file_get_contents(__DIR__ . '/../admin/index.html');
ok(str_contains($adminHtml, 'servicesBadgesHtml'), 'Admin uses servicesBadgesHtml for multi-service');
ok(str_contains($adminHtml, 'show_scarcity'), 'Admin has show_scarcity toggle');
ok(str_contains($adminHtml, 'setting-show_scarcity-toggle'), 'Admin scarcity toggle element exists');

$kanbanJs = file_get_contents(__DIR__ . '/../admin/js/kanban.js');
ok(str_contains($kanbanJs, "'invoiced'"), 'Kanban has invoiced column');
ok(str_contains($kanbanJs, 'getNextAction'), 'Kanban has next-action badges');

$roJs = file_get_contents(__DIR__ . '/../admin/js/repair-orders.js');
ok(str_contains($roJs, 'loadEstimateItems'), 'RO detail has inline estimate editor');
ok(str_contains($roJs, 'replace_items'), 'Estimate editor uses replace_items API');
ok(str_contains($roJs, 'roMarkCompleted'), 'RO detail has Mark Completed button');
ok(str_contains($roJs, 'roInvoices'), 'RO detail shows invoices section');
ok(str_contains($roJs, 'roMarkPaid'), 'RO detail has Mark Paid button');

// ═══════════════════════════════════════════════════════
echo "\n=== 11. Error Log Check ===\n";
// ═══════════════════════════════════════════════════════

// Check for recent PHP errors in error_log
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $logSize = filesize($errorLog);
    $recentErrors = [];
    if ($logSize > 0) {
        $tail = file_get_contents($errorLog, false, null, max(0, $logSize - 5000));
        $lines = explode("\n", $tail);
        $today = date('d-M-Y');
        foreach ($lines as $line) {
            if (str_contains($line, $today) && (str_contains($line, 'Fatal') || str_contains($line, 'Parse error'))) {
                $recentErrors[] = trim(substr($line, 0, 150));
            }
        }
    }
    ok(empty($recentErrors), 'No Fatal/Parse errors in error_log today' . ($recentErrors ? ': ' . $recentErrors[0] : ''));
} else {
    echo "  (error_log not found at standard path — skipping)\n";
}

// ═══════════════════════════════════════════════════════
echo "\n" . str_repeat('=', 60) . "\n";
echo "Results: \033[32m{$pass} passed\033[0m, " . ($fail > 0 ? "\033[31m{$fail} failed\033[0m" : "0 failed") . "\n";
if (!empty($errors)) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) echo "  \033[31m✗\033[0m {$e}\n";
}
exit($fail > 0 ? 1 : 0);
