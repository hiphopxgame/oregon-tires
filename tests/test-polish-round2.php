<?php
/**
 * Oregon Tires — Polish Round 2 Test Suite
 * Tests: manifest upgrade, robots.txt, hero CTAs, footer consistency
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

// ─── TEST 1: PWA Manifest ───────────────────────────────────────────────────
echo "\nTEST 1: PWA manifest\n";
$manifest = json_decode(file_get_contents($base . '/manifest.json'), true);

assert_test($manifest !== null, 'manifest.json is valid JSON');
assert_test($manifest['background_color'] === '#0D3618', 'background_color matches brand dark green');
assert_test(in_array('automotive', $manifest['categories'] ?? []), 'Categories include automotive');
assert_test(isset($manifest['shortcuts']) && count($manifest['shortcuts']) >= 2, 'Has 2+ shortcuts');
assert_test($manifest['shortcuts'][0]['url'] === '/book-appointment/', 'First shortcut is Book Appointment');
assert_test($manifest['shortcuts'][1]['url'] === '/status/', 'Second shortcut is Check Status');
assert_test($manifest['display'] === 'standalone', 'Display mode is standalone');
assert_test($manifest['theme_color'] === '#15803d', 'Theme color preserved');

// ─── TEST 2: robots.txt ─────────────────────────────────────────────────────
echo "\nTEST 2: robots.txt security\n";
$robots = file_get_contents($base . '/robots.txt');

assert_test(str_contains($robots, 'Disallow: /admin/'), 'Disallows /admin/');
assert_test(str_contains($robots, 'Disallow: /api/'), 'Disallows /api/');
assert_test(str_contains($robots, 'Disallow: /uploads/'), 'Disallows /uploads/ (inspection photos)');
assert_test(str_contains($robots, 'Disallow: /vendor/'), 'Disallows /vendor/ (Composer)');
assert_test(str_contains($robots, 'Disallow: /sql/'), 'Disallows /sql/ (migrations)');
assert_test(str_contains($robots, 'Sitemap: https://oregon.tires/sitemap.xml'), 'Sitemap URL present');

// ─── TEST 3: Homepage hero CTAs ─────────────────────────────────────────────
echo "\nTEST 3: Hero section CTAs\n";
$homepage = file_get_contents($base . '/index.html');

assert_test(str_contains($homepage, 'href="/book-appointment/"'), 'Schedule Service CTA links to booking');
assert_test(str_contains($homepage, 'href="#services"'), 'View Pricing CTA links to services section');
assert_test(str_contains($homepage, 'href="tel:5033679714"'), 'Call Now CTA has phone link');
assert_test(str_contains($homepage, 'data-t="viewPricing"'), 'viewPricing data-t attribute present');
assert_test(str_contains($homepage, 'data-t="callNow"'), 'callNow data-t attribute on hero CTA');
assert_test(str_contains($homepage, "viewPricing: 'View Services & Pricing'"), 'EN translation for viewPricing');
assert_test(str_contains($homepage, "viewPricing: 'Ver Servicios y Precios'"), 'ES translation for viewPricing');

// ─── TEST 4: Footer consistency ─────────────────────────────────────────────
echo "\nTEST 4: Footer consistency across pages\n";
$footer_pages = [
    'contact.php',
    'cancel.php',
    'reschedule.php',
    'checkout.php',
    'inspection.php',
    'approve.php',
];

foreach ($footer_pages as $page) {
    $content = file_get_contents($base . '/' . $page);
    assert_test(
        str_contains($content, 'bg-brand text-white py-8'),
        "{$page} uses branded footer"
    );
    assert_test(
        str_contains($content, '8536 SE 82nd Ave'),
        "{$page} footer has address"
    );
    assert_test(
        str_contains($content, 'tel:5033679714'),
        "{$page} footer has phone link"
    );
    assert_test(
        str_contains($content, '1vsM.com'),
        "{$page} footer has 1vsM credit"
    );
    assert_test(
        str_contains($content, 'facebook.com') && str_contains($content, 'instagram.com/oregontires'),
        "{$page} footer has social links"
    );
}

// ─── TEST 5: No old bare footers remain ─────────────────────────────────────
echo "\nTEST 5: No bare footers remain\n";
foreach ($footer_pages as $page) {
    $content = file_get_contents($base . '/' . $page);
    assert_test(
        !str_contains($content, 'bg-gray-100 dark:bg-[#111827] border-t'),
        "{$page} no longer uses bare gray footer"
    );
}

// ─── SUMMARY ────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('=', 50) . "\n";
echo "RESULTS: {$passed}/{$total} passed";
if ($failed > 0) echo " ({$failed} FAILED)";
echo "\n" . str_repeat('=', 50) . "\n";

exit($failed > 0 ? 1 : 0);
