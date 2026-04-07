<?php
/**
 * Oregon Tires — Mobile Kanban Test
 * Verifies admin/js/kanban.js ships the mobile list view sentinel.
 * Run via CLI: php tests/test-mobile-kanban.php
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

echo "\n=== Mobile Kanban Tests ===\n\n";

$ch = curl_init('https://oregon.tires/admin/js/kanban.js');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
]);
$body = (string) curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

test('kanban.js HTTP 200', $code === 200, "got {$code}");
test('kanban.js contains // MOBILE_LIST_VIEW sentinel', strpos($body, '// MOBILE_LIST_VIEW') !== false);

// Also assert local file has it (so we can verify pre-deploy)
$local = file_get_contents(__DIR__ . '/../public_html/admin/js/kanban.js');
test('local kanban.js contains // MOBILE_LIST_VIEW sentinel', strpos($local, '// MOBILE_LIST_VIEW') !== false);

echo "\n=== {$passed} passed, {$failed} failed ===\n";
exit($failed === 0 ? 0 : 1);
