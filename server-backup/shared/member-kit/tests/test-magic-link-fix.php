<?php
declare(strict_types=1);

/**
 * Test: Magic Link Feature — Migration & API Fixes
 *
 * Tests:
 * 1. Migration file exists with correct table definitions
 * 2. magic-link.php uses magic_link_tokens table
 * 3. magic-link-verify.php uses magic_link_tokens table
 * 4. API code uses token_hash column correctly
 * 5. API code uses used_at column for tracking
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
// TEST SUITE 1: Migration File Structure
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 1: Migration File Structure');

$migrationFile = $basePath . '/migrations/006_magic_link.php';
testAssert(file_exists($migrationFile), 'Migration file 006_magic_link.php exists');

if (file_exists($migrationFile)) {
    $migrationCode = file_get_contents($migrationFile);

    testAssert(
        strpos($migrationCode, 'CREATE TABLE IF NOT EXISTS {$tablePrefix}rate_limit_actions') !== false,
        'Migration creates rate_limit_actions table'
    );

    testAssert(
        strpos($migrationCode, 'CREATE TABLE IF NOT EXISTS {$tablePrefix}magic_link_tokens') !== false,
        'Migration creates magic_link_tokens table'
    );

    testAssert(
        strpos($migrationCode, 'action VARCHAR(100)') !== false,
        'rate_limit_actions has action column'
    );

    testAssert(
        strpos($migrationCode, 'identifier VARCHAR(255)') !== false,
        'rate_limit_actions has identifier column'
    );

    testAssert(
        strpos($migrationCode, 'email VARCHAR(255)') !== false,
        'magic_link_tokens has email column'
    );

    testAssert(
        strpos($migrationCode, 'token_hash VARCHAR(255)') !== false,
        'magic_link_tokens has token_hash column'
    );

    testAssert(
        strpos($migrationCode, 'expires_at TIMESTAMP') !== false,
        'magic_link_tokens has expires_at column'
    );

    testAssert(
        strpos($migrationCode, 'used_at TIMESTAMP NULL DEFAULT NULL') !== false,
        'magic_link_tokens has used_at column with NULL default'
    );

    testAssert(
        strpos($migrationCode, 'INDEX idx_action_identifier') !== false,
        'rate_limit_actions has idx_action_identifier index'
    );

    testAssert(
        strpos($migrationCode, 'INDEX idx_token') !== false,
        'magic_link_tokens has idx_token index'
    );

    testAssert(
        strpos($migrationCode, 'INDEX idx_email') !== false,
        'magic_link_tokens has idx_email index'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 2: Magic Link API File Updates
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 2: Magic Link API File Updates');

$magicLinkFile = $basePath . '/api/member/magic-link.php';
testAssert(file_exists($magicLinkFile), 'magic-link.php file exists');

if (file_exists($magicLinkFile)) {
    $magicLinkCode = file_get_contents($magicLinkFile);

    // Check that it uses magic_link_tokens table, not email_verifications
    testAssert(
        strpos($magicLinkCode, 'INSERT INTO {$prefix}magic_link_tokens') !== false,
        'magic-link.php inserts into magic_link_tokens table'
    );

    testAssert(
        strpos($magicLinkCode, '(email, token_hash, expires_at, created_at)') !== false,
        'magic-link.php uses correct column list (email, token_hash, expires_at, created_at)'
    );

    testAssert(
        strpos($magicLinkCode, "VALUES (?, ?, ?, NOW())") !== false,
        'magic-link.php uses prepared statement with 4 parameters'
    );

    // Check rate limit insertion
    testAssert(
        strpos($magicLinkCode, 'INSERT INTO {$prefix}rate_limit_actions') !== false,
        'magic-link.php inserts into rate_limit_actions table'
    );

    testAssert(
        strpos($magicLinkCode, "(action, identifier, created_at)") !== false,
        'magic-link.php rate limit uses correct columns'
    );

    // Check token hash generation
    testAssert(
        strpos($magicLinkCode, "hash('sha256', \$token)") !== false,
        'magic-link.php hashes token with SHA256'
    );

    // Should NOT use email_verifications table
    testAssert(
        strpos($magicLinkCode, 'email_verifications') === false,
        'magic-link.php does not reference old email_verifications table'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 3: Magic Link Verify API File Updates
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 3: Magic Link Verify API File Updates');

$magicLinkVerifyFile = $basePath . '/api/member/magic-link-verify.php';
testAssert(file_exists($magicLinkVerifyFile), 'magic-link-verify.php file exists');

if (file_exists($magicLinkVerifyFile)) {
    $verifyCode = file_get_contents($magicLinkVerifyFile);

    // Check that it queries magic_link_tokens table
    testAssert(
        strpos($verifyCode, 'SELECT id, created_at FROM {$prefix}magic_link_tokens') !== false,
        'magic-link-verify.php queries magic_link_tokens table'
    );

    // Check WHERE clause
    testAssert(
        strpos($verifyCode, "WHERE email = ?") !== false &&
        strpos($verifyCode, "AND token_hash = ?") !== false &&
        strpos($verifyCode, "AND expires_at > NOW()") !== false,
        'magic-link-verify.php uses correct WHERE conditions'
    );

    // Check that it updates used_at instead of deleting
    testAssert(
        strpos($verifyCode, 'UPDATE {$prefix}magic_link_tokens') !== false,
        'magic-link-verify.php updates magic_link_tokens table'
    );

    testAssert(
        strpos($verifyCode, 'SET used_at = NOW()') !== false,
        'magic-link-verify.php sets used_at timestamp'
    );

    // Should NOT delete the record
    testAssert(
        strpos($verifyCode, 'DELETE FROM {$prefix}email_verifications') === false,
        'magic-link-verify.php does not delete from old email_verifications table'
    );

    // Should NOT reference type = 'magic_link'
    testAssert(
        strpos($verifyCode, "type = 'magic_link'") === false,
        'magic-link-verify.php does not reference type column'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 4: API Code Syntax Validation
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 4: API Code Syntax Validation');

// Check both APIs have valid PHP syntax
if (file_exists($magicLinkFile)) {
    $output = [];
    $return = null;
    exec("php -l " . escapeshellarg($magicLinkFile), $output, $return);
    testAssert($return === 0, 'magic-link.php has valid PHP syntax');
}

if (file_exists($magicLinkVerifyFile)) {
    $output = [];
    $return = null;
    exec("php -l " . escapeshellarg($magicLinkVerifyFile), $output, $return);
    testAssert($return === 0, 'magic-link-verify.php has valid PHP syntax');
}

if (file_exists($migrationFile)) {
    $output = [];
    $return = null;
    exec("php -l " . escapeshellarg($migrationFile), $output, $return);
    testAssert($return === 0, '006_magic_link.php migration has valid PHP syntax');
}

// ═════════════════════════════════════════════════════════════════════════════
// TEST SUITE 5: Code Quality Checks
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUITE 5: Code Quality Checks');

if (file_exists($magicLinkFile)) {
    $magicLinkCode = file_get_contents($magicLinkFile);

    testAssert(
        strpos($magicLinkCode, 'try {') !== false && strpos($magicLinkCode, '} catch') !== false,
        'magic-link.php uses try-catch error handling'
    );

    testAssert(
        strpos($magicLinkCode, '\\Throwable') !== false,
        'magic-link.php catches Throwable exceptions'
    );

    testAssert(
        strpos($magicLinkCode, '$stmt->execute(') !== false,
        'magic-link.php uses prepared statement execution'
    );
}

if (file_exists($magicLinkVerifyFile)) {
    $verifyCode = file_get_contents($magicLinkVerifyFile);

    testAssert(
        strpos($verifyCode, 'try {') !== false && strpos($verifyCode, '} catch') !== false,
        'magic-link-verify.php uses try-catch error handling'
    );

    testAssert(
        strpos($verifyCode, '\\Throwable') !== false,
        'magic-link-verify.php catches Throwable exceptions'
    );

    testAssert(
        strpos($verifyCode, '$stmt->execute(') !== false,
        'magic-link-verify.php uses prepared statement execution'
    );
}

// ═════════════════════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ═════════════════════════════════════════════════════════════════════════════

section('TEST SUMMARY');
$totalTests = $passCount + $failCount;
echo "Passed: {$passCount}/{$totalTests}\n";
echo "Failed: {$failCount}/{$totalTests}\n";

if ($failCount > 0) {
    echo "\n✗ Some tests failed\n";
    exit(1);
} else {
    echo "\n✓ All tests passed!\n";
    exit(0);
}
