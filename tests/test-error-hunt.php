<?php
/**
 * Oregon Tires — Error Hunt
 *
 * Curls live pages + API endpoints and asserts:
 *   - status code < 500
 *   - body has no PHP error/warning text
 *
 * Run via CLI: php tests/test-error-hunt.php
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
        CURLOPT_COOKIE         => 'humans_21909=1',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$code, (string) $body];
}

$urls = [
    // Pages
    'https://oregon.tires/',
    'https://oregon.tires/contact.php',
    'https://oregon.tires/book-appointment/',
    'https://oregon.tires/blog.php',
    'https://oregon.tires/faq.php',
    'https://oregon.tires/why-us.php',
    'https://oregon.tires/reviews.php',
    'https://oregon.tires/care-plan.php',
    'https://oregon.tires/tires-se-portland.php',
    'https://oregon.tires/tires-clackamas.php',
    'https://oregon.tires/tire-installation.php',
    'https://oregon.tires/brake-service.php',
    'https://oregon.tires/members.php',
    'https://oregon.tires/admin/',
    // APIs
    'https://oregon.tires/api/health.php',
    'https://oregon.tires/api/settings.php',
    'https://oregon.tires/api/services.php',
    'https://oregon.tires/api/faq.php',
    'https://oregon.tires/api/promotions.php',
    'https://oregon.tires/api/testimonials.php',
    'https://oregon.tires/api/sitemap.php',
    'https://oregon.tires/api/blog.php',
    'https://oregon.tires/api/gallery.php',
];

$errorPatterns = [
    '/\bFatal error\s*:/i',
    '/\bParse error\s*:/i',
    '/<b>\s*Warning\s*<\/b>\s*:/i',
    '/<b>\s*Notice\s*<\/b>\s*:/i',
    '/<b>\s*Deprecated\s*<\/b>\s*:/i',
    '/\bStack trace\s*:/i',
    '/\bUncaught\b/i',
];

echo "\n=== Error Hunt ===\n\n";

foreach ($urls as $url) {
    [$code, $body] = curlGet($url);
    test("{$url}: HTTP < 500", $code > 0 && $code < 500, "got {$code}");
    $foundErr = '';
    foreach ($errorPatterns as $p) {
        if (preg_match($p, $body, $m)) { $foundErr = $m[0]; break; }
    }
    test("{$url}: no error text", $foundErr === '', "found '" . substr($foundErr, 0, 60) . "'");
}

echo "\n=== {$passed} passed, {$failed} failed ===\n";
exit($failed === 0 ? 0 : 1);
