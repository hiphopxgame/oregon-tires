<?php
/**
 * Test Suite: Member Kit Phase 4 Enhancements
 *
 * Features:
 * 1. Social Login Button Consistency
 * 2. Password Reset Flow
 * 3. Success/Error Animations
 * 4. Dark Mode Polish
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
    // Support regex patterns like '@keyframes.*toast'
    // Check if it looks like a regex pattern (contains .* but not as literal text)
    if (preg_match('/\.\*/', $needle)) {
        // Treat as regex pattern - escape everything first, then unescape .*
        $pattern = preg_quote($needle, '/');
        $pattern = str_replace('\.\*', '.*', $pattern);
        if (!preg_match('/' . $pattern . '/i', $str)) {
            throw new Exception("Expected pattern: $needle");
        }
    } else {
        // Literal string search
        if (strpos($str, $needle) === false) {
            throw new Exception("Expected: $needle");
        }
    }
}

function assert_file_exists($path) {
    if (!file_exists($path)) {
        throw new Exception("File missing: $path");
    }
}

// ============================================================================
// PHASE 4.1: SOCIAL LOGIN BUTTON CONSISTENCY
// ============================================================================

test('CSS: Unified social button class exists', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-social-btn', 'Unified social button class');
});

test('CSS: Social button hover state defined', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-social-btn:hover', 'Social button hover');
});

test('Template: Google button uses member-social-btn class', function() {
    $tpl = file_get_contents(__DIR__ . '/../templates/member/login.php');
    assert_contains($tpl, 'member-social-btn', 'Google button styling');
});

test('API: Social button endpoints registered', function() {
    assert_file_exists(__DIR__ . '/../api/member/sso.php');
});

// ============================================================================
// PHASE 4.2: PASSWORD RESET FLOW
// ============================================================================

test('API: Password reset endpoint exists', function() {
    assert_file_exists(__DIR__ . '/../api/member/password-reset.php');
});

test('API: Password reset has rate limiting', function() {
    $php = file_get_contents(__DIR__ . '/../api/member/password-reset.php');
    assert_contains($php, 'rate_limit', 'Rate limiting on password reset');
});

test('API: Password reset sends email', function() {
    $php = file_get_contents(__DIR__ . '/../api/member/password-reset.php');
    assert_contains($php, 'sendPasswordReset', 'Email sending method');
});

test('Template: Forgot password form exists', function() {
    assert_file_exists(__DIR__ . '/../templates/member/forgot-password.php');
});

test('Template: Reset password form exists', function() {
    assert_file_exists(__DIR__ . '/../templates/member/reset-password.php');
});

test('API: Reset endpoint validates token', function() {
    $php = file_get_contents(__DIR__ . '/../api/member/reset-password.php');
    assert_contains($php, 'verify', 'Token verification');
});

test('DB: Password reset tokens table created', function() {
    assert_file_exists(__DIR__ . '/../migrations/003_password_reset.php');
});

// ============================================================================
// PHASE 4.3: SUCCESS/ERROR ANIMATIONS
// ============================================================================

test('CSS: Toast notification styles defined', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '.member-toast', 'Toast CSS class');
});

test('CSS: Toast animation keyframes exist', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '@keyframes.*toast', 'Toast animation');
});

test('CSS: Field error shake animation', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '@keyframes.*shake', 'Shake animation');
});

test('JS: Toast notification function exists', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'showToast', 'Toast function');
});

test('JS: Error animation function exists', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'animateError', 'Error animation');
});

test('JS: Success callback triggers animation', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'onSuccess.*animate', 'Success animation trigger');
});

// ============================================================================
// PHASE 4.4: DARK MODE POLISH
// ============================================================================

test('Template: Color-scheme meta tag present', function() {
    $tpl = file_get_contents(__DIR__ . '/../templates/member/login.php');
    assert_contains($tpl, 'color-scheme', 'Color scheme meta');
});

test('JS: System theme detection script', function() {
    $js = file_get_contents(__DIR__ . '/../js/member.js');
    assert_contains($js, 'prefers-color-scheme', 'Theme detection');
});

test('CSS: Dark mode variables defined', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, '@media (prefers-color-scheme: dark)', 'Dark mode media query');
});

test('CSS: Dark mode button colors', function() {
    $css = file_get_contents(__DIR__ . '/../css/member.css');
    assert_contains($css, 'dark .member-btn', 'Dark mode button styles');
});

// ============================================================================
// RUN TESTS
// ============================================================================

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║  Member Kit Phase 4 Enhancements — Test Suite                         ║\n";
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
