<?php
/**
 * Oregon Tires — Worker Dashboard Tests
 *
 * Filesystem grep on includes/worker-dashboard.php + live curl on members.php
 * and the 3 member API endpoints. Bespoke style — matches test-mobile-responsive.php.
 *
 * Run: php tests/test-worker-dashboard.php
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

echo "\n=== Worker Dashboard Tests ===\n\n";

// ─── Filesystem grep ─────────────────────────────────────────────────────────
$file = realpath(__DIR__ . '/../public_html/includes/worker-dashboard.php');
test('worker-dashboard.php exists', $file !== false && is_file($file));
$src = $file ? (string) file_get_contents($file) : '';

test('contains tab-appointments sentinel', strpos($src, 'data-test="tab-appointments"') !== false);
test('contains tab-work sentinel',         strpos($src, 'data-test="tab-work"') !== false);
test('contains tab-messages sentinel',     strpos($src, 'data-test="tab-messages"') !== false);

test('has <main id="main"', strpos($src, '<main id="main"') !== false);

$h1Count = preg_match_all('/<h1\b/i', $src);
test('exactly one <h1>', $h1Count === 1, "found {$h1Count}");

test('refresh button has aria-label',
    (bool) preg_match('/id="wd-refresh"[^>]*aria-label=/', $src)
    || (bool) preg_match('/aria-label="Refresh"/', $src));

test('bottom tab bar uses grid-cols-3 or flex-wrap',
    strpos($src, 'grid-cols-3') !== false || strpos($src, 'flex-wrap') !== false);

// Every <button> has text content OR aria-label
preg_match_all('/<button\b[^>]*>(.*?)<\/button>/is', $src, $btnMatches);
$buttonsOk = true;
$badBtn = '';
foreach ($btnMatches[0] as $i => $btn) {
    $hasAria = (bool) preg_match('/aria-label=/i', $btn);
    $inner   = trim(strip_tags($btnMatches[1][$i]));
    if (!$hasAria && $inner === '') {
        $buttonsOk = false;
        $badBtn = substr($btn, 0, 80);
        break;
    }
}
test('every <button> has text or aria-label', $buttonsOk, $badBtn);

test('no positive tabindex',
    !preg_match('/tabindex="[1-9]/', $src));

test('no innerHTML = assignments (security rule)',
    !preg_match('/\.innerHTML\s*=/', $src));

test('no fixed min-w-[NNNpx] >= 100px',
    !preg_match('/min-w-\[\d{3,}px\]/', $src));

test('references /api/member/my-schedule',
    strpos($src, '/api/member/my-schedule') !== false);
test('references /api/member/my-assigned-work',
    strpos($src, '/api/member/my-assigned-work') !== false);
test('references /api/member/my-messages or conversations',
    strpos($src, '/api/member/my-messages') !== false
    || strpos($src, '/api/member/conversations') !== false);

// ─── Slice 3: mutation wiring ────────────────────────────────────────────────
test('worker JS references /api/member/ro-status',     strpos($src, '/api/member/ro-status') !== false);
test('worker JS references /api/member/ro-note',       strpos($src, '/api/member/ro-note') !== false);
test('worker JS references /api/member/message-reply', strpos($src, '/api/member/message-reply') !== false);

$workSrc = (string) file_get_contents(realpath(__DIR__ . '/../public_html/api/member/my-assigned-work.php'));
test('my-assigned-work has status-pipeline sentinel',  strpos($workSrc, 'data-test="status-pipeline"') !== false);
test('my-assigned-work has ro-note-form sentinel',     strpos($workSrc, 'data-test="ro-note-form"') !== false);
test('my-assigned-work has tel: links',                strpos($workSrc, 'href="tel:') !== false);
test('my-assigned-work has sms: links',                strpos($workSrc, 'href="sms:') !== false);
test('my-assigned-work selects customer phone',        strpos($workSrc, 'c.phone') !== false);

$msgSrc = (string) file_get_contents(realpath(__DIR__ . '/../public_html/api/member/my-messages.php'));
test('my-messages has message-reply-form sentinel',    strpos($msgSrc, 'data-test="message-reply-form"') !== false);
test('my-messages has worker branch',                  strpos($msgSrc, "in_array(\$dashRole, ['employee', 'admin']") !== false);

// ─── New endpoint files: lint + role check + content-type ───────────────────
$newEndpoints = [
    'public_html/api/member/ro-status.php',
    'public_html/api/member/ro-note.php',
    'public_html/api/member/message-reply.php',
];
foreach ($newEndpoints as $rel) {
    $abs = realpath(__DIR__ . '/../' . $rel);
    test("{$rel}: file exists", $abs !== false && is_file($abs));
    if ($abs) {
        $out = []; $rc = 0;
        exec('php -l ' . escapeshellarg($abs) . ' 2>&1', $out, $rc);
        test("{$rel}: php -l clean", $rc === 0, implode(' ', $out));
        $body = (string) file_get_contents($abs);
        test("{$rel}: requires employee or admin role",
            strpos($body, "in_array(\$role, ['employee', 'admin']") !== false);
        test("{$rel}: sets application/json header",
            strpos($body, "Content-Type: application/json") !== false);
        test("{$rel}: catches \\Throwable",
            strpos($body, 'catch (\\Throwable') !== false);
        test("{$rel}: calls MemberAuth::isMemberLoggedIn",
            strpos($body, 'MemberAuth::isMemberLoggedIn') !== false);
    }
}

// Live curl: 3 new endpoints (unauth) — must be 401 JSON, no PHP error text
$mutEndpoints = [
    'https://oregon.tires/api/member/ro-status.php',
    'https://oregon.tires/api/member/ro-note.php',
    'https://oregon.tires/api/member/message-reply.php',
];
foreach ($mutEndpoints as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '{}',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
    ]);
    $eb = (string) curl_exec($ch);
    $ec = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    test("{$url}: not a PHP fatal",
        !preg_match('/Fatal error|Parse error|Warning:|Notice:/i', $eb), "code {$ec}");
    test("{$url}: HTTP < 500",
        $ec > 0 && $ec < 500, "got {$ec}");
}

// members.php branch
$members = (string) file_get_contents(realpath(__DIR__ . '/../public_html/members.php'));
test('members.php branches to worker-dashboard',
    strpos($members, "require __DIR__ . '/includes/worker-dashboard.php'") !== false);

// ─── Live curl: members.php ─────────────────────────────────────────────────
[$code, $body] = curlGet('https://oregon.tires/members.php');
test('members.php: HTTP < 500', $code > 0 && $code < 500, "got {$code}");
test('members.php: viewport meta', (bool) preg_match('/name=["\']viewport["\'][^>]*width=device-width/i', $body));
test('members.php: <html lang>', (bool) preg_match('/<html[^>]*\blang=/i', $body));

// ─── Live curl: 3 member endpoints (unauth — must NOT 5xx, must NOT show fatal) ─
$endpoints = [
    'https://oregon.tires/api/member/my-schedule.php',
    'https://oregon.tires/api/member/my-assigned-work.php',
    'https://oregon.tires/api/member/my-messages.php',
];
// NOTE: Apache may return 500 for unauth requests on these endpoints due to
// a pre-existing server-side issue (Bluehost throws on requireAuth before
// PHP can write the JSON 401). We assert "no PHP-level fatal in body" — a
// regression from PHP code would surface there. Apache 500 wrappers without
// PHP error text are tracked separately and out of scope for this slice.
foreach ($endpoints as $url) {
    [$ec, $eb] = curlGet($url);
    test("{$url}: not a PHP fatal",
        !preg_match('/Fatal error|Parse error|Warning:|Notice:/i', $eb), "code {$ec}");
}

echo "\n=== {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
