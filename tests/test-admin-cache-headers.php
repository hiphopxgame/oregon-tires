<?php
/**
 * TDD test for admin no-cache headers.
 *
 * Verifies that /api/admin/* endpoints and /admin/index.html send strict
 * no-cache headers, preventing the stale-permissions render bug.
 *
 * Run: php tests/test-admin-cache-headers.php [https://oregon.tires]
 */

declare(strict_types=1);

$base = $argv[1] ?? 'https://oregon.tires';
$urls = [
    $base . '/api/admin/session.php',
    $base . '/api/admin/login.php',
    $base . '/admin/index.html',
    $base . '/admin/',
];

$pass = 0; $fail = 0;
foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY         => true,
        CURLOPT_HEADER         => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'oregon-tires-tdd/1.0',
    ]);
    $headers = (string) curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "  [ERR ] {$url} — {$err}\n"; $fail++; continue;
    }

    $cc = '';
    foreach (preg_split('/\r?\n/', $headers) as $line) {
        if (stripos($line, 'cache-control:') === 0) {
            $cc = trim(substr($line, 14));
            break;
        }
    }

    $ok = stripos($cc, 'no-store') !== false || stripos($cc, 'no-cache') !== false;
    if ($ok) {
        echo "  [PASS] {$url}\n        {$cc}\n";
        $pass++;
    } else {
        echo "  [FAIL] {$url}\n        Cache-Control: {$cc}\n";
        $fail++;
    }
}

echo "\n{$pass} passed, {$fail} failed.\n";
exit($fail === 0 ? 0 : 1);
