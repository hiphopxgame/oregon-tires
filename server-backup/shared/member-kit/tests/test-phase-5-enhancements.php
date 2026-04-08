<?php
/**
 * Test Suite: Member Kit Phase 5 Enhancements
 *
 * Features:
 * 1. Device Nicknames
 * 2. Login History Dashboard
 * 3. Rate Limiting UI
 * 4. Loading States
 */

$tests = [];
$passed = 0;
$failed = 0;

function test($name, $fn) {
    global $tests, $passed, $failed;
    try {
        $fn();
        $tests[] = "✓ $name";
        $passed++;
    } catch (Exception $e) {
        $tests[] = "✗ $name: " . $e->getMessage();
        $failed++;
    }
}

function assert_contains($str, $needle) {
    if (strpos($str, $needle) === false) {
        throw new Exception("Expected: $needle");
    }
}

function assert_file_exists($path) {
    if (!file_exists($path)) {
        throw new Exception("File missing: $path");
    }
}

// ============================================================================
// PHASE 5.1: DEVICE NICKNAMES
// ============================================================================

test('DB: Device name column added to sessions table', function() {
    $mig = file_get_contents(__DIR__ . '/../migrations/002_session_tracking.php');
    assert_contains($mig, 'device_name', 'Device name column');
});

test('API: Get devices endpoint exists', function() {
    assert_file_exists(__DIR__ . '/../api/member/devices.php');
});

test('API: Rename device endpoint exists', function() {
    assert_file_exists(__DIR__ . '/../api/member/rename-device.php');
});

test('API: Revoke device endpoint exists', function() {
    assert_file_exists(__DIR__ . '/../api/member/revoke-device.php');
});

test('Template: Device management page exists', function() {
    assert_file_exists(__DIR__ . '/../templates/member/devices.php');
});

test('JS: Device renaming form handler', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'renameDevice', 'Rename device function');
});

test('CSS: Device card styling', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-device-card', 'Device card class');
});

// ============================================================================
// PHASE 5.2: LOGIN HISTORY DASHBOARD
// ============================================================================

test('Template: Login history page exists', function() {
    assert_file_exists(__DIR__ . '/../templates/member/login-history.php');
});

test('API: Login history with geo data endpoint', function() {
    $php = file_get_contents(__DIR__ . '/../api/member/login-activity.php');
    assert_contains($php, 'geo_location', 'Geo location field');
});

test('JS: Login history data loading', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'loadLoginHistory', 'Login history loader');
});

test('CSS: Activity timeline styling', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-activity-timeline', 'Activity timeline');
});

test('Template: Sign out all sessions option', function() {
    $tpl = file_get_contents(__DIR__ . '/../templates/member/login-history.php');
    assert_contains($tpl, 'sign-out-all', 'Sign out all button');
});

// ============================================================================
// PHASE 5.3: RATE LIMITING UI
// ============================================================================

test('API: Rate limit response includes countdown', function() {
    $php = file_get_contents(__DIR__ . '/../api/member/password-reset.php');
    assert_contains($php, 'retry_after', 'Retry-after field');
});

test('Template: Rate limit message element', function() {
    $tpl = file_get_contents(__DIR__ . '/../templates/member/login.php');
    assert_contains($tpl, 'rate-limit-message', 'Rate limit message');
});

test('JS: Countdown timer for rate limits', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'showRateLimitCountdown', 'Rate limit countdown');
});

test('CSS: Rate limit alert styling', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-alert--rate-limit', 'Rate limit alert');
});

// ============================================================================
// PHASE 5.4: LOADING STATES
// ============================================================================

test('CSS: Skeleton loader animation', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-skeleton', 'Skeleton loader');
});

test('CSS: Pulse animation for buttons', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '@keyframes.*pulse', 'Pulse animation');
});

test('JS: Show loading state function', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'showLoading', 'Loading state function');
});

test('Template: Loading placeholder elements', function() {
    $tpl = file_get_contents(__DIR__ . '/../templates/member/devices.php');
    assert_contains($tpl, 'member-skeleton', 'Skeleton placeholder');
});

// ============================================================================
// RUN TESTS
// ============================================================================

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║  Member Kit Phase 5 Enhancements — Test Suite                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

foreach ($tests as $result) {
    echo "$result\n";
}

echo "\n";
echo "Results: " . ($passed + $failed) . " tests — " .
     "\033[32m$passed passed\033[0m, " .
     ($failed > 0 ? "\033[31m$failed failed\033[0m" : "0 failed") . "\n";
echo "\n";

exit($failed > 0 ? 1 : 0);
