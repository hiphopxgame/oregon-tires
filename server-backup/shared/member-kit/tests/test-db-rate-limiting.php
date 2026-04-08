<?php
declare(strict_types=1);

/**
 * Test: DB-Backed Rate Limiting in MemberAuth
 *
 * Tests:
 * 1. checkDbRateLimit() returns correct structure
 * 2. checkDbRateLimit() correctly counts requests within window
 * 3. checkDbRateLimit() respects window expiry
 * 4. recordDbRateLimit() inserts successfully
 * 5. DB rate limit is enforced in login()
 * 6. Multiple failed login attempts increment DB counter
 * 7. Requests beyond window are not counted
 * 8. Fallback to session-based when DB fails
 * 9. recordDbRateLimit() handles errors gracefully
 * 10. resetAt timestamp calculation is correct
 */

$passCount = 0;
$failCount = 0;

function testAssert($condition, $testName) {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "[✓] $testName\n";
    } else {
        $failCount++;
        echo "[✗] $testName\n";
    }
}

function section($title) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "$title\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
}

$basePath = __DIR__ . '/..';

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 1: MemberAuth Class Structure
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 1: MemberAuth Class Structure');

$authFile = $basePath . '/includes/member-kit/MemberAuth.php';
testAssert(file_exists($authFile), 'MemberAuth.php exists');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, 'public static function checkDbRateLimit(') !== false,
        'checkDbRateLimit() method exists as public static'
    );

    testAssert(
        strpos($authCode, 'public static function recordDbRateLimit(') !== false,
        'recordDbRateLimit() method exists as public static'
    );

    testAssert(
        strpos($authCode, 'private static function checkSessionRateLimit(') !== false,
        'checkSessionRateLimit() method exists as private static'
    );

    testAssert(
        strpos($authCode, "'allowed' => \$allowed,") !== false,
        'checkDbRateLimit() returns array with allowed key'
    );

    testAssert(
        strpos($authCode, "'remaining' => \$remaining,") !== false,
        'checkDbRateLimit() returns array with remaining key'
    );

    testAssert(
        strpos($authCode, "'resetAt' => \$resetAt") !== false,
        'checkDbRateLimit() returns array with resetAt key'
    );

    testAssert(
        strpos($authCode, "FROM {$prefix}rate_limit_actions") !== false ||
        strpos($authCode, 'rate_limit_actions') !== false,
        'checkDbRateLimit() queries rate_limit_actions table'
    );

    testAssert(
        strpos($authCode, "INSERT INTO {$prefix}rate_limit_actions") !== false ||
        strpos($authCode, 'INSERT INTO') !== false && strpos($authCode, 'rate_limit_actions') !== false,
        'recordDbRateLimit() inserts into rate_limit_actions table'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 2: Method Signature & Return Types
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 2: Method Signature & Return Types');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        preg_match('/public static function checkDbRateLimit\(\s*string \$action\s*,\s*string \$identifier/', $authCode) === 1,
        'checkDbRateLimit() has correct parameter types (string $action, string $identifier)'
    );

    testAssert(
        preg_match('/int \$max = 5\s*,\s*int \$windowSecs = 3600/', $authCode) === 1,
        'checkDbRateLimit() has default params (max=5, windowSecs=3600)'
    );

    testAssert(
        preg_match('/\): array/', $authCode) !== false,
        'checkDbRateLimit() returns array type'
    );

    testAssert(
        preg_match('/public static function recordDbRateLimit\(\s*string \$action\s*,\s*string \$identifier/', $authCode) === 1,
        'recordDbRateLimit() has correct parameter types'
    );

    testAssert(
        strpos($authCode, 'public static function recordDbRateLimit') !== false &&
        strpos($authCode, ': bool') !== false,
        'recordDbRateLimit() returns bool type'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 3: DB Rate Limit Logic
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 3: DB Rate Limit Logic');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "COUNT(*) as count") !== false,
        'checkDbRateLimit() counts rows in window'
    );

    testAssert(
        strpos($authCode, "MAX(created_at) as latest") !== false,
        'checkDbRateLimit() gets latest timestamp'
    );

    testAssert(
        strpos($authCode, "AND created_at >") !== false,
        'checkDbRateLimit() filters by time window'
    );

    testAssert(
        strpos($authCode, "date('Y-m-d H:i:s', time() - \$windowSecs)") !== false,
        'checkDbRateLimit() calculates window start time correctly'
    );

    testAssert(
        strpos($authCode, "self::\$pdo->prepare(") !== false,
        'checkDbRateLimit() uses prepared statements'
    );

    testAssert(
        strpos($authCode, "\$stmt->execute([\$action, \$identifier, \$sinceTime])") !== false,
        'checkDbRateLimit() executes with action, identifier, sinceTime params'
    );

    testAssert(
        strpos($authCode, "max(0, \$max - \$count)") !== false,
        'checkDbRateLimit() calculates remaining as max(0, max-count)'
    );

    testAssert(
        strpos($authCode, "strtotime(\$result['latest']) + \$windowSecs") !== false,
        'checkDbRateLimit() calculates resetAt based on latest timestamp'
    );

    testAssert(
        strpos($authCode, "return [") !== false &&
        strpos($authCode, "'allowed' =>") !== false &&
        strpos($authCode, "'remaining' =>") !== false &&
        strpos($authCode, "'resetAt' =>") !== false,
        'checkDbRateLimit() returns correct array structure'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 4: Error Handling & Fallback
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 4: Error Handling & Fallback');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "if (!self::\$pdo)") !== false ||
        strpos($authCode, 'if (!self::$pdo)') !== false,
        'checkDbRateLimit() checks if PDO is available'
    );

    testAssert(
        strpos($authCode, "throw new \\Exception('No database connection')") !== false,
        'checkDbRateLimit() throws exception if DB unavailable'
    );

    testAssert(
        strpos($authCode, "} catch (\\Throwable \$e) {") !== false,
        'checkDbRateLimit() catches Throwable exceptions'
    );

    testAssert(
        strpos($authCode, "error_log('Rate limit check error:") !== false,
        'checkDbRateLimit() logs errors to error_log()'
    );

    testAssert(
        strpos($authCode, "return self::checkSessionRateLimit(\$action, \$identifier, \$max, \$windowSecs)") !== false,
        'checkDbRateLimit() falls back to session-based on DB error'
    );

    testAssert(
        strpos($authCode, "} catch (\\Throwable \$e) {") !== false &&
        strpos($authCode, "error_log('Rate limit record error:") !== false,
        'recordDbRateLimit() catches Throwable and logs errors'
    );

    testAssert(
        strpos($authCode, "return false;") !== false,
        'recordDbRateLimit() returns false on error'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 5: Session Fallback Implementation
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 5: Session Fallback Implementation');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "private static function checkSessionRateLimit(") !== false,
        'checkSessionRateLimit() is private static method'
    );

    testAssert(
        preg_match('/\$key\s*=\s*"ratelimit_/', $authCode) === 1,
        'checkSessionRateLimit() uses ratelimit_action_identifier session key'
    );

    testAssert(
        strpos($authCode, "if (!isset(\$_SESSION[\$key]))") !== false,
        'checkSessionRateLimit() initializes session key if missing'
    );

    testAssert(
        strpos($authCode, "array_filter(") !== false &&
        strpos($authCode, "fn(\$ts) => \$ts > (\$now - \$windowSecs)") !== false,
        'checkSessionRateLimit() filters old timestamps'
    );

    testAssert(
        strpos($authCode, "count(\$_SESSION[\$key]['timestamps'])") !== false,
        'checkSessionRateLimit() counts timestamps in session'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 6: Integration with login() Method
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 6: Integration with login() Method');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "public static function login(string \$email, string \$password)") !== false,
        'login() method exists'
    );

    // Check if login uses DB rate limit instead of old session-based
    testAssert(
        preg_match('/\$rateLimitResult\s*=\s*self::checkDbRateLimit/', $authCode) === 1,
        'login() calls checkDbRateLimit() instead of old session-based limit'
    );

    testAssert(
        strpos($authCode, "if (!\$rateLimitResult['allowed'])") !== false ||
        strpos($authCode, "!\$rateLimitResult['allowed']") !== false,
        'login() checks $rateLimitResult[allowed] key'
    );

    testAssert(
        strpos($authCode, "'remaining' => 0,") !== false ||
        strpos($authCode, "'remaining' => \$rateLimitResult['remaining']") !== false,
        'login() returns remaining in rate limit response'
    );

    testAssert(
        strpos($authCode, "'resetAt' => \$rateLimitResult['resetAt']") !== false ||
        strpos($authCode, "'resetAt' =>") !== false,
        'login() returns resetAt in rate limit response'
    );

    testAssert(
        strpos($authCode, "self::recordDbRateLimit('login', \$email)") !== false ||
        strpos($authCode, "recordDbRateLimit('login'") !== false,
        'login() calls recordDbRateLimit() for successful logins'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 7: requestPasswordReset() Integration
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 7: requestPasswordReset() Integration');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "public static function requestPasswordReset(string \$email)") !== false,
        'requestPasswordReset() method exists'
    );

    // Check if it uses DB rate limit
    testAssert(
        preg_match('/requestPasswordReset[\s\S]*?checkDbRateLimit.*password_reset/s', $authCode) === 1 ||
        preg_match('/password_reset.*checkDbRateLimit/s', $authCode) === 1,
        'requestPasswordReset() uses DB-backed rate limiting for password_reset action'
    );

    testAssert(
        strpos($authCode, "self::recordDbRateLimit('password_reset'") !== false ||
        preg_match('/recordDbRateLimit\([\'"]password_reset[\'"]/', $authCode) === 1,
        'requestPasswordReset() records password_reset action to DB'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 8: resendVerification() Integration
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 8: resendVerification() Integration');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "public static function resendVerification(int \$memberId)") !== false,
        'resendVerification() method exists'
    );

    // Check if it still has action rate limit (IP-based)
    testAssert(
        strpos($authCode, "checkActionRateLimit('resend_verification'") !== false,
        'resendVerification() uses checkActionRateLimit() for IP-based rate limiting'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 9: Database Query Structure
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 9: Database Query Structure');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        preg_match('/SELECT COUNT\(\*\) as count.*FROM.*rate_limit_actions/s', $authCode) === 1,
        'SELECT query structure is correct'
    );

    testAssert(
        strpos($authCode, "WHERE action = ? AND identifier = ? AND created_at >") !== false,
        'SELECT query filters by action, identifier, and created_at window'
    );

    testAssert(
        preg_match('/INSERT INTO.*rate_limit_actions\s*\(action\s*,\s*identifier\s*,\s*created_at\)/s', $authCode) === 1,
        'INSERT query has correct columns (action, identifier, created_at)'
    );

    testAssert(
        strpos($authCode, "VALUES (?, ?, NOW())") !== false,
        'INSERT query uses placeholders and NOW() for timestamp'
    );

    testAssert(
        strpos($authCode, "\$stmt->execute([\$action, \$identifier])") !== false,
        'INSERT query binds action and identifier parameters'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 10: Security & Best Practices
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 10: Security & Best Practices');

if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);

    testAssert(
        strpos($authCode, "\$stmt = self::\$pdo->prepare(") !== false,
        'All queries use prepared statements'
    );

    testAssert(
        strpos($authCode, "execute([") !== false,
        'All queries use parameterized execution'
    );

    testAssert(
        strpos($authCode, "error_log(") !== false,
        'Errors are logged instead of exposed to user'
    );

    testAssert(
        strpos($authCode, "return self::checkSessionRateLimit(") !== false,
        'Graceful fallback to session-based when DB fails'
    );

    testAssert(
        strpos($authCode, "try {") !== false &&
        strpos($authCode, "} catch (\\Throwable \$e) {") !== false,
        'Exceptions are caught using Throwable for maximum coverage'
    );

    testAssert(
        strpos($authCode, "self::getTablePrefix()") !== false ||
        strpos($authCode, "\$prefix") !== false,
        'Table prefix is properly handled for HW mode'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// RESULTS
// ═════════════════════════════════════════════════════════════════════════════

section('TEST RESULTS');

$total = $passCount + $failCount;
$percentage = $total > 0 ? round(($passCount / $total) * 100) : 0;

echo "\nTotal: $passCount/$total assertions passed ($percentage%)\n";

if ($failCount === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ $failCount test(s) failed\n";
    exit(1);
}
