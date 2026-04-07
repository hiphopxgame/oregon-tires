<?php
/**
 * Oregon Tires — Accessibility Tests
 *
 * Curls live URLs and asserts core a11y baselines:
 *   1. Exactly one <h1>
 *   2. <html lang="..."> present and non-empty
 *   3. Every <img> has an alt attribute
 *   4. Form inputs have label/aria-label/aria-labelledby
 *   5. Skip-to-content link exists
 *   6. <main element present
 *   7. No tabindex value > 0
 *   8. No raw PHP error text in body
 *
 * Run via CLI: php tests/test-accessibility.php
 */

declare(strict_types=1);

$passed = 0;
$failed = 0;
$failures = [];

function test(string $name, bool $result, string $detail = ''): void
{
    global $passed, $failed, $failures;
    if ($result) {
        echo "PASS: {$name}\n";
        $passed++;
    } else {
        echo "FAIL: {$name}" . ($detail ? " — {$detail}" : '') . "\n";
        $failed++;
        $failures[] = $name . ($detail ? " — {$detail}" : '');
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

function checkPage(string $url, string $label, bool $shellOnly): void
{
    [$code, $body] = curlGet($url);
    test("{$label}: HTTP < 500", $code > 0 && $code < 500, "got {$code}");
    if (strlen($body) < 100) {
        test("{$label}: body has content", false, 'empty/tiny body');
        return;
    }

    // 1. Exactly one <h1>
    $h1Count = preg_match_all('/<h1\b[^>]*>/i', $body, $m);
    test("{$label}: exactly one <h1>", $h1Count === 1, "found {$h1Count}");

    // 2. <html lang="...">
    $hasLang = (bool) preg_match('/<html\b[^>]*\blang\s*=\s*["\'][a-zA-Z][a-zA-Z0-9_-]*["\']/i', $body);
    test("{$label}: <html lang> set", $hasLang);

    if ($shellOnly) {
        // Only check shell-level a11y for SPAs (admin) — skip form/img/skip checks
        // because the entire SPA pre-renders all modals/forms in one page.
        // 8. Errors
        $errorPatterns = [
            '/\bFatal error\s*:/i', '/\bParse error\s*:/i',
            '/<b>\s*Warning\s*<\/b>\s*:/i', '/<b>\s*Notice\s*<\/b>\s*:/i', '/<b>\s*Deprecated\s*<\/b>\s*:/i',
        ];
        $foundErr = '';
        foreach ($errorPatterns as $p) if (preg_match($p, $body)) { $foundErr = $p; break; }
        test("{$label}: no PHP error text", $foundErr === '');
        $hasMain = (bool) preg_match('/<main\b/i', $body);
        test("{$label}: <main> element", $hasMain);
        return;
    }

    // 3. Every <img> has an alt
    $imgs = [];
    preg_match_all('/<img\b[^>]*>/i', $body, $imgs);
    $missingAlt = 0;
    foreach ($imgs[0] as $img) {
        if (!preg_match('/\salt\s*=/i', $img)) {
            $missingAlt++;
        }
    }
    test("{$label}: all <img> have alt", $missingAlt === 0, "{$missingAlt} missing");

    // 4. Form inputs labeled
    $unlabeled = 0;
    $labelFors = [];
    if (preg_match_all('/<label\b[^>]*\bfor\s*=\s*["\']([^"\']+)["\']/i', $body, $lm)) {
        $labelFors = array_flip($lm[1]);
    }
    $inputTypes = ['text', 'email', 'tel', 'password', 'search', 'number', 'url', 'date'];
    preg_match_all('/<input\b[^>]*>/i', $body, $inputs);
    foreach ($inputs[0] as $inp) {
        if (preg_match('/\btype\s*=\s*["\']?(\w+)/i', $inp, $tm)) {
            if (!in_array(strtolower($tm[1]), $inputTypes, true)) continue;
        }
        if (preg_match('/\baria-label(?:ledby)?\s*=\s*["\'][^"\']+["\']/i', $inp)) continue;
        if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $inp, $im) && isset($labelFors[$im[1]])) continue;
        $unlabeled++;
    }
    preg_match_all('/<(select|textarea)\b[^>]*>/i', $body, $st);
    foreach ($st[0] as $el) {
        if (preg_match('/\baria-label(?:ledby)?\s*=\s*["\'][^"\']+["\']/i', $el)) continue;
        if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $el, $im) && isset($labelFors[$im[1]])) continue;
        $unlabeled++;
    }
    test("{$label}: form fields labeled", $unlabeled === 0, "{$unlabeled} unlabeled");

    // 5. Skip-to-content link
    $hasSkip = (bool) preg_match('/<a\s[^>]*href=["\']#main["\']/i', $body)
            || stripos($body, 'Skip to') !== false;
    test("{$label}: skip-to-content link", $hasSkip);

    // 6. <main element
    $hasMain = (bool) preg_match('/<main\b/i', $body);
    test("{$label}: <main> element", $hasMain);

    // 7. tabindex > 0
    $badTab = 0;
    if (preg_match_all('/\btabindex\s*=\s*["\']?(-?\d+)/i', $body, $tm)) {
        foreach ($tm[1] as $v) {
            if ((int) $v > 0) $badTab++;
        }
    }
    test("{$label}: no tabindex > 0", $badTab === 0, "{$badTab} found");

    // 8. No raw PHP error text — match patterns that indicate real PHP errors,
    // not innocuous occurrences like CSS var names (--member-warning) or copy.
    $errorPatterns = [
        '/\bFatal error\s*:/i',
        '/\bParse error\s*:/i',
        '/<b>\s*Warning\s*<\/b>\s*:/i',
        '/\bWarning\s*:[^<\n]{0,200}\bin\s+\/[a-zA-Z0-9_\/.-]+\s+on line\b/i',
        '/<b>\s*Notice\s*<\/b>\s*:/i',
        '/\bNotice\s*:[^<\n]{0,200}\bin\s+\/[a-zA-Z0-9_\/.-]+\s+on line\b/i',
        '/<b>\s*Deprecated\s*<\/b>\s*:/i',
        '/\bDeprecated\s*:[^<\n]{0,200}\bin\s+\/[a-zA-Z0-9_\/.-]+\s+on line\b/i',
    ];
    $foundErr = '';
    foreach ($errorPatterns as $p) {
        if (preg_match($p, $body, $m)) { $foundErr = $m[0]; break; }
    }
    test("{$label}: no PHP error text", $foundErr === '', "found '" . substr($foundErr, 0, 60) . "'");
}

echo "\n=== Accessibility Tests ===\n\n";

$pages = [
    'https://oregon.tires/'                       => 'home',
    'https://oregon.tires/contact.php'            => 'contact',
    'https://oregon.tires/book-appointment/'      => 'booking',
    'https://oregon.tires/blog.php'               => 'blog',
    'https://oregon.tires/faq.php'                => 'faq',
    'https://oregon.tires/why-us.php'             => 'why-us',
    'https://oregon.tires/reviews.php'            => 'reviews',
    'https://oregon.tires/care-plan.php'          => 'care-plan',
    'https://oregon.tires/tires-se-portland.php'  => 'tires-se-portland',
    'https://oregon.tires/tires-clackamas.php'    => 'tires-clackamas',
    'https://oregon.tires/tire-installation.php'  => 'tire-installation',
    'https://oregon.tires/brake-service.php'      => 'brake-service',
    'https://oregon.tires/members.php'            => 'members',
    'https://oregon.tires/admin/'                 => 'admin',
];

foreach ($pages as $url => $label) {
    checkPage($url, $label, $label === 'admin');
}

echo "\n=== {$passed} passed, {$failed} failed ===\n";
if ($failed > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) echo "  - {$f}\n";
}
exit($failed === 0 ? 0 : 1);
