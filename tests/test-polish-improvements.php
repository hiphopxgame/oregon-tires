<?php
/**
 * Oregon Tires — Polish Improvements Test Suite
 * Tests: sitemap completeness, dark mode fix, open/closed badge, service meta
 */

$passed = 0;
$failed = 0;
$total  = 0;

function assert_test(bool $condition, string $label): void {
    global $passed, $failed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  PASS: {$label}\n";
    } else {
        $failed++;
        echo "  FAIL: {$label}\n";
    }
}

$base = __DIR__ . '/../public_html';

// ─── TEST 1: Sitemap contains all public pages ──────────────────────────────
echo "\nTEST 1: Sitemap completeness\n";
$sitemap = file_get_contents($base . '/sitemap.xml');

assert_test(str_contains($sitemap, '<loc>https://oregon.tires/</loc>'), 'Homepage in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/book-appointment/</loc>'), '/book-appointment/ in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/services/</loc>'), '/services/ in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/about/</loc>'), '/about/ in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/faq/</loc>'), '/faq/ in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/status/</loc>'), '/status/ in sitemap');
assert_test(str_contains($sitemap, '<loc>https://oregon.tires/feedback/</loc>'), '/feedback/ in sitemap');
assert_test(str_contains($sitemap, 'hreflang="en"'), 'Sitemap has EN hreflang');
assert_test(str_contains($sitemap, 'hreflang="es"'), 'Sitemap has ES hreflang');

// ─── TEST 2: Dark mode NOT hardcoded on inspection/approve ──────────────────
echo "\nTEST 2: Dark mode respects user preference\n";
$inspection = file_get_contents($base . '/inspection.php');
$approve    = file_get_contents($base . '/approve.php');

assert_test(!str_contains($inspection, '<html lang="en" class="dark">'), 'inspection.php does NOT force dark mode');
assert_test(!str_contains($approve, '<html lang="en" class="dark">'), 'approve.php does NOT force dark mode');
assert_test(str_contains($inspection, "localStorage.getItem('theme')==='dark'"), 'inspection.php has localStorage dark mode init');
assert_test(str_contains($approve, "localStorage.getItem('theme')==='dark'"), 'approve.php has localStorage dark mode init');
assert_test(str_contains($inspection, '<html lang="en">'), 'inspection.php starts without dark class');
assert_test(str_contains($approve, '<html lang="en">'), 'approve.php starts without dark class');

// ─── TEST 3: Open/Closed badge present on all top-bar pages ─────────────────
echo "\nTEST 3: Open/Closed status badge\n";
$pages_with_topbar = [
    'index.html',
    'book-appointment/index.html',
    'about/index.html',
    'faq/index.html',
    'services/index.html',
    'feedback/index.html',
    'status/index.html',
];

foreach ($pages_with_topbar as $page) {
    $content = file_get_contents($base . '/' . $page);
    assert_test(
        str_contains($content, 'id="open-status"'),
        "{$page} has open-status badge element"
    );
    assert_test(
        str_contains($content, 'updateOpenStatus'),
        "{$page} has updateOpenStatus JS function"
    );
}

// ─── TEST 4: Open/Closed badge logic correctness ────────────────────────────
echo "\nTEST 4: Badge logic details\n";
$homepage = file_get_contents($base . '/index.html');

assert_test(str_contains($homepage, "timeZone: 'America/Los_Angeles'"), 'Uses Portland timezone');
assert_test(str_contains($homepage, 'day >= 1 && day <= 6'), 'Mon-Sat check (0=Sun excluded)');
assert_test(str_contains($homepage, 'h >= 7 && h < 19'), '7AM-7PM hours check');
assert_test(str_contains($homepage, "Abierto"), 'Spanish "Open" translation');
assert_test(str_contains($homepage, "Cerrado"), 'Spanish "Closed" translation');
assert_test(str_contains($homepage, 'setTimeout(updateOpenStatus, 60000)'), 'Auto-refreshes every 60s');
assert_test(str_contains($homepage, 'bg-green-500/20'), 'Green badge for open');
assert_test(str_contains($homepage, 'bg-red-500/20'), 'Red badge for closed');

// ─── TEST 5: Service duration + pricing already present ─────────────────────
echo "\nTEST 5: Booking service info (pre-existing)\n";
$booking = file_get_contents($base . '/book-appointment/index.html');

assert_test(str_contains($booking, 'var serviceInfo'), 'serviceInfo object exists');
assert_test(str_contains($booking, 'updateServiceMeta'), 'updateServiceMeta function exists');
assert_test(str_contains($booking, "duration: '45-90'"), 'Tire installation duration defined');
assert_test(str_contains($booking, 'priceMin: 25'), 'Tire installation price defined');
assert_test(str_contains($booking, 'fromPrice'), 'Bilingual price labels exist');
assert_test(str_contains($booking, 'callForQuote'), 'Call for quote fallback exists');

// ─── SUMMARY ────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('=', 50) . "\n";
echo "RESULTS: {$passed}/{$total} passed";
if ($failed > 0) echo " ({$failed} FAILED)";
echo "\n" . str_repeat('=', 50) . "\n";

exit($failed > 0 ? 1 : 0);
