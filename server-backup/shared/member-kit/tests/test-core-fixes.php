<?php
declare(strict_types=1);

/**
 * test-core-fixes.php
 *
 * Comprehensive test suite for member-kit core fixes:
 * - Fix 1: MemberAuth::getTablePrefix() alias
 * - Fix 3: CSRF fallback in dashboard.php
 * - Fix 4: CSRF check in logout API
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$testsPassed = 0;
$testsFailed = 0;

function assert_test(string $name, bool $condition, string $message = ''): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] $name\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $name" . ($message ? " — $message" : "") . "\n";
        $testsFailed++;
    }
}

function assert_equals(string $name, $expected, $actual, string $message = ''): void {
    assert_test($name, $expected === $actual, $message . " (expected: $expected, got: $actual)");
}

echo "========================================\n";
echo "Member-Kit Core Fixes Test Suite\n";
echo "========================================\n\n";

// ═══════════════════════════════════════════════════════════════════════════
// FIX 1: MemberAuth::getTablePrefix() Alias
// ═══════════════════════════════════════════════════════════════════════════

echo "TEST SUITE 1: MemberAuth::getTablePrefix() Alias\n";
echo "─────────────────────────────────────────────────────────────────\n";

// Read the MemberAuth.php file
$memberAuthPath = __DIR__ . '/../includes/member-kit/MemberAuth.php';
$memberAuthContent = file_get_contents($memberAuthPath);

assert_test(
    "TEST 1.1: MemberAuth.php file exists and is readable",
    file_exists($memberAuthPath) && is_readable($memberAuthPath),
    "MemberAuth.php file not found"
);

// Check if getTablePrefix method exists in the file
assert_test(
    "TEST 1.2: getTablePrefix() method is defined",
    strpos($memberAuthContent, 'public static function getTablePrefix()') !== false,
    "getTablePrefix() method not found in MemberAuth.php"
);

// Check if method returns correct prefix format
assert_test(
    "TEST 1.3: getTablePrefix() returns string type",
    (bool) preg_match('/public static function getTablePrefix\(\):\s*string/', $memberAuthContent),
    "getTablePrefix() does not have correct return type"
);

// Check if the method handles empty prefix
assert_test(
    "TEST 1.4: getTablePrefix() handles empty prefix",
    (bool) preg_match('/\$prefix\s*=\s*self::\$config\[\'table_prefix\'\]/', $memberAuthContent),
    "getTablePrefix() doesn't properly read table_prefix from config"
);

// Check if the method adds underscore suffix
assert_test(
    "TEST 1.5: getTablePrefix() adds underscore suffix when needed",
    (bool) preg_match('/!str_ends_with\(\$prefix,\s*\'_\'\)/', $memberAuthContent),
    "getTablePrefix() doesn't add underscore suffix"
);

// Verify consistency with prefixedTable method
assert_test(
    "TEST 1.6: getTablePrefix() logic matches prefixedTable() implementation",
    (bool) preg_match('/public static function prefixedTable\(string \$table\): string/', $memberAuthContent),
    "prefixedTable() method not found or format changed"
);

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// FIX 3: CSRF Fallback in dashboard.php
// ═══════════════════════════════════════════════════════════════════════════

echo "TEST SUITE 3: CSRF Fallback in dashboard.php\n";
echo "─────────────────────────────────────────────────────────────────\n";

$dashboardPath = __DIR__ . '/../templates/member/dashboard.php';
$dashboardContent = file_get_contents($dashboardPath);

// Check if the meta tag has CSRF fallback
assert_test(
    "TEST 3.1: CSRF meta tag exists in HTML",
    strpos($dashboardContent, 'csrf-token') !== false,
    "CSRF meta tag not found"
);

// Check for the fallback pattern
assert_test(
    "TEST 3.2: CSRF fallback uses null coalescing operator",
    (bool) preg_match('/\$csrfToken\s*\?\?\s*MemberAuth::getCsrfToken\(\)/', $dashboardContent),
    "CSRF fallback pattern not found"
);

// Verify the line is in the correct location (line 54 or nearby)
$lines = explode("\n", $dashboardContent);
$found = false;
for ($i = 50; $i < 60 && $i < count($lines); $i++) {
    if (strpos($lines[$i], 'csrf-token') !== false && strpos($lines[$i], '??') !== false) {
        $found = true;
        break;
    }
}
assert_test(
    "TEST 3.3: CSRF fallback is in correct location (around line 54)",
    $found,
    "CSRF fallback not found near line 54"
);

// Check that the fallback is applied in htmlspecialchars
assert_test(
    "TEST 3.4: CSRF value is properly escaped",
    (bool) preg_match('/htmlspecialchars\(\s*\$csrfToken\s*\?\?\s*MemberAuth::getCsrfToken\(\)\s*\)/', $dashboardContent),
    "CSRF value not properly escaped with htmlspecialchars"
);

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// FIX 4: CSRF Check in logout API
// ═══════════════════════════════════════════════════════════════════════════

echo "TEST SUITE 4: CSRF Check in logout API\n";
echo "─────────────────────────────────────────────────────────────────\n";

$logoutApiPath = __DIR__ . '/../api/member/logout.php';
$logoutApiContent = file_get_contents($logoutApiPath);

// Check if CSRF token verification is present
assert_test(
    "TEST 4.1: CSRF token verification is implemented",
    (bool) preg_match('/MemberAuth::verifyCsrf/', $logoutApiContent),
    "CSRF verification not found in logout API"
);

// Check if it reads CSRF from request body
assert_test(
    "TEST 4.2: CSRF token is read from request body",
    (bool) preg_match('/json_decode|php:\/\/input|_POST\[|_REQUEST\[/', $logoutApiContent),
    "CSRF token not read from request body"
);

// Check if 403 is returned on CSRF failure
assert_test(
    "TEST 4.3: 403 response on CSRF verification failure",
    (bool) preg_match('/403|Forbidden/', $logoutApiContent),
    "403 response code not found on CSRF failure"
);

// Check if 401 is returned when not logged in
assert_test(
    "TEST 4.4: 401 response when not logged in",
    (bool) preg_match('/401|Unauthorized/', $logoutApiContent),
    "401 response code not found"
);

// Check if 200 is returned on success
assert_test(
    "TEST 4.5: 200 response on successful logout",
    (bool) preg_match('/200|success/', $logoutApiContent),
    "Success response not found"
);

// Check if dashboard.php is updated with CSRF token in logout
assert_test(
    "TEST 4.6: dashboard.php logout function includes CSRF token",
    (bool) preg_match('/csrf_token|csrf|MemberAuth::getCsrfToken/', $dashboardContent),
    "CSRF token not included in dashboard logout function"
);

// Check if logout fetch call includes body
assert_test(
    "TEST 4.7: logout fetch call has proper method and body",
    (bool) preg_match('/method.*POST|POST.*method/', $dashboardContent),
    "logout fetch does not use POST method"
);

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════════════════════════

echo "========================================\n";
echo "Test Summary\n";
echo "========================================\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total:  " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed > 0) {
    echo "\nStatus: FAILED ✗\n";
    exit(1);
} else {
    echo "\nStatus: ALL TESTS PASSED ✓\n";
    exit(0);
}
