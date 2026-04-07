<?php
/**
 * Oregon Tires — Mobile Element Fit Tests
 *
 * Verifies header rows, refresh buttons, and other interactive elements fit
 * within a 375px mobile viewport. Greps the local repo for anti-patterns
 * that cause elements to overflow off-screen on narrow devices.
 *
 * Run via CLI: php tests/test-element-fit.php
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
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        CURLOPT_COOKIE         => 'humans_21909=1',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$code, (string) $body];
}

echo "\n=== Mobile Element Fit Tests ===\n\n";

// ─── Live URL smoke checks ─────────────────────────────────────────────────
$urls = [
    'https://oregon.tires/'                  => 'home',
    'https://oregon.tires/admin/'            => 'admin',
    'https://oregon.tires/members'           => 'members',
    'https://oregon.tires/book-appointment/' => 'booking',
    'https://oregon.tires/contact'           => 'contact',
];
foreach ($urls as $url => $label) {
    [$code, $body] = curlGet($url);
    test("{$label}: HTTP < 500", $code > 0 && $code < 500, "got {$code}");
    test("{$label}: viewport meta", (bool) preg_match('/name=["\']viewport["\'][^>]*width=device-width/i', $body));
}

// ─── Filesystem grep — mobile element-fit anti-patterns ───────────────────
$root    = realpath(__DIR__ . '/../public_html');
$targets = [
    'admin/index.html',
    'members.php',
    'inspection.php',
    'approve.php',
    'book-appointment/index.html',
    'templates/header.php',
    'templates/footer.php',
];

// 1. Refresh buttons must live in a wrapping parent.
//    For each "↻ Refresh" (or data-t="refresh" button) in admin/index.html,
//    walk back to the nearest enclosing <div class="..."> and ensure it has
//    flex-wrap (so the button can drop below the title on mobile).
$refreshHits = [];
$adminHtml   = (string) @file_get_contents($root . '/admin/index.html');
if ($adminHtml !== '') {
    $lines = explode("\n", $adminHtml);
    foreach ($lines as $idx => $line) {
        if (!preg_match('/↻\s*Refresh|data-t="(refresh|roRefresh|msgRefresh|analyticsRefresh)"/u', $line)) {
            continue;
        }
        if (strpos($line, '<button') === false) continue;

        // Any ancestor flex container within 16 lines should have flex-wrap
        // (wrapping anywhere up the chain means the button can drop to a new row).
        $hasWrap = false;
        for ($j = $idx; $j >= max(0, $idx - 16); $j--) {
            $prev = $lines[$j];
            if (preg_match('/<div[^>]*class="([^"]*\bflex\b[^"]*)"/', $prev, $m)) {
                if (strpos($m[1], 'flex-wrap') !== false) { $hasWrap = true; break; }
            }
        }
        if (!$hasWrap) {
            $refreshHits[] = 'admin/index.html:' . ($idx + 1);
        }
    }
}
test('All refresh buttons inside flex-wrap parent',
    count($refreshHits) === 0,
    count($refreshHits) . ' offenders: ' . implode(', ', array_slice($refreshHits, 0, 6)));

// 2. No layout min-w-[NNNpx] with 3+ digit pixel values (breaks 375px viewports).
$minWHits = [];
foreach ($targets as $rel) {
    $path = $root . '/' . $rel;
    if (!file_exists($path)) continue;
    $content = (string) file_get_contents($path);
    if (preg_match_all('/min-w-\[(\d{3,})px\]/', $content, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[1] as $k => $match) {
            $px = (int) $match[0];
            if ($px >= 376) {
                $minWHits[] = "$rel: min-w-[{$px}px]";
            }
        }
    }
}
test('No layout min-w-[NNNpx] >= 376px', count($minWHits) === 0,
    count($minWHits) . ' hits: ' . implode(', ', array_slice($minWHits, 0, 4)));

// 3. Header flex rows with 3+ sibling <button> children must wrap.
$rowHits = [];
foreach ($targets as $rel) {
    $path = $root . '/' . $rel;
    if (!file_exists($path)) continue;
    $content = (string) file_get_contents($path);
    // Find <div class="..flex.."> ... </div> blocks containing 3+ <button
    if (preg_match_all('/<div[^>]*class="([^"]*\bflex\b[^"]*)"[^>]*>([\s\S]{0,600}?)<\/div>/', $content, $m)) {
        foreach ($m[1] as $i => $cls) {
            $inner = $m[2][$i];
            $btnCount = substr_count($inner, '<button');
            if ($btnCount >= 3 && strpos($cls, 'flex-wrap') === false
                && strpos($cls, 'overflow-x-auto') === false) {
                $rowHits[] = "$rel: $btnCount btns in flex row";
                if (count($rowHits) > 10) break 2;
            }
        }
    }
}
test('No flex rows with 3+ buttons missing flex-wrap/overflow-x-auto',
    count($rowHits) === 0,
    count($rowHits) . ' hits: ' . implode(' | ', array_slice($rowHits, 0, 4)));

// 4. No inline width/min-width pixel styles on layout elements (>= 376px).
$inlineHits = [];
foreach ($targets as $rel) {
    $path = $root . '/' . $rel;
    if (!file_exists($path)) continue;
    $content = (string) file_get_contents($path);
    if (preg_match_all('/<(div|section|header|nav|main|aside|form)[^>]*style="[^"]*(?<![-a-z])(?:min-width|width)\s*:\s*(\d{3,})px/i', $content, $m)) {
        foreach ($m[2] as $k => $match) {
            if ((int) $match >= 376) {
                $inlineHits[] = "$rel: inline {$match}px on <{$m[1][$k]}>";
            }
        }
    }
}
test('No inline pixel widths >= 376px on layout elements',
    count($inlineHits) === 0,
    count($inlineHits) . ' hits: ' . implode(', ', array_slice($inlineHits, 0, 4)));

echo "\n=== {$passed} passed, {$failed} failed ===\n";
exit($failed === 0 ? 0 : 1);
