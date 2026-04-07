<?php
/**
 * Oregon Tires — Mobile Responsive Tests
 *
 * Curls live URLs and greps the local repo for known mobile anti-patterns.
 * Run via CLI: php tests/test-mobile-responsive.php
 */

declare(strict_types=1);

$passed = 0;
$failed = 0;

function test(string $name, bool $result, string $detail = ''): void
{
    global $passed, $failed;
    if ($result) {
        echo "PASS: {$name}\n";
        $passed++;
    } else {
        echo "FAIL: {$name}" . ($detail ? " — {$detail}" : '') . "\n";
        $failed++;
    }
}

function curlGet(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$code, (string) $body];
}

echo "\n=== Mobile Responsive Tests ===\n\n";

// ─── Live URL checks ────────────────────────────────────────────────────────
$urls = [
    'https://oregon.tires/'                       => 'home',
    'https://oregon.tires/admin/'                 => 'admin login',
    'https://oregon.tires/members'                => 'members',
    'https://oregon.tires/book-appointment/'      => 'booking',
    'https://oregon.tires/inspection.php?token=test' => 'inspection',
    'https://oregon.tires/approve.php?token=test'    => 'approve',
];
foreach ($urls as $url => $label) {
    [$code, $body] = curlGet($url);
    test("{$label}: HTTP < 500", $code > 0 && $code < 500, "got {$code}");
    test("{$label}: viewport meta", (bool) preg_match('/name=["\']viewport["\'][^>]*width=device-width/i', $body));
}

// ─── Filesystem grep — local repo anti-patterns ────────────────────────────
$root = realpath(__DIR__ . '/../public_html');
$targets = [
    'admin/index.html',
    'inspection.php',
    'approve.php',
    'members.php',
    'book-appointment/index.html',
    'templates/header.php',
    'templates/footer.php',
];

$gridUnbasedHits = [];
$tableUnwrappedHits = [];
foreach ($targets as $rel) {
    $path = $root . '/' . $rel;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);

    // Check for grid md:grid-cols-N or grid-cols-{2,3,4} class strings missing grid-cols-1 base
    if (preg_match_all('/class="([^"]*\bgrid\b[^"]*)"/', $content, $m)) {
        foreach ($m[1] as $cls) {
            $hasMulti = preg_match('/\b(?:md|lg|xl):grid-cols-[2-9]\b/', $cls) || preg_match('/\bgrid-cols-[2-9]\b/', $cls);
            $hasBase  = preg_match('/\bgrid-cols-1\b/', $cls);
            // accept if it has unprefixed grid-cols-N where N is 1, otherwise must contain grid-cols-1
            if ($hasMulti && !$hasBase) {
                $gridUnbasedHits[] = "$rel: $cls";
            }
        }
    }

    // <table not preceded by overflow-x-auto within 80 chars
    if (preg_match_all('/<table\b/', $content, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[0] as $match) {
            $offset = $match[1];
            $start  = max(0, $offset - 200);
            $window = substr($content, $start, $offset - $start);
            if (strpos($window, 'overflow-x-auto') === false) {
                $tableUnwrappedHits[] = "$rel @ offset $offset";
            }
        }
    }
}

test('No grid md:grid-cols-N missing grid-cols-1 base', count($gridUnbasedHits) === 0,
    count($gridUnbasedHits) . " hits, e.g.: " . ($gridUnbasedHits[0] ?? ''));

test('No <table> without overflow-x-auto wrapper', count($tableUnwrappedHits) === 0,
    count($tableUnwrappedHits) . " hits, e.g.: " . ($tableUnwrappedHits[0] ?? ''));

echo "\n=== {$passed} passed, {$failed} failed ===\n";
exit($failed === 0 ? 0 : 1);
