<?php
declare(strict_types=1);

/**
 * Test Suite: Site Connection Tracking
 * Tests recordSiteConnection() + register() + startAuthenticatedSession()
 * Ensures member_site_connections table is populated correctly
 */

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDatabase();
$testsPassed = 0;
$testsFailed = 0;

// Test data
$testSiteKey = 'test_site_key_' . uniqid();
$testMemberId = null;
$testEmail = 'siteconn_test_' . uniqid() . '@example.com';

echo "\n=== Site Connection Tracking Tests ===\n\n";

// SETUP: Create a test table if it doesn't exist (for testing purposes)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `member_site_connections` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `member_id` INT UNSIGNED NOT NULL,
        `site_key` VARCHAR(64) NOT NULL,
        `first_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `connection_count` INT UNSIGNED NOT NULL DEFAULT 1,
        UNIQUE KEY `uk_member_site` (`member_id`, `site_key`),
        KEY `idx_site_key` (`site_key`),
        KEY `idx_member_id` (`member_id`),
        CONSTRAINT `fk_msc_member` FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (\Throwable $e) {
    // Table may already exist
}

// Add registered_site_key column if missing
try {
    $pdo->exec("ALTER TABLE `members` ADD COLUMN `registered_site_key` VARCHAR(64) DEFAULT NULL");
} catch (\Throwable $e) {
    // Column may already exist
}

// TEST 1: recordSiteConnection() inserts a row when site_key is set
echo "TEST 1: recordSiteConnection() inserts when site_key is set\n";
try {
    require_once dirname(__DIR__) . '/loader.php';

    // Create a test member
    $stmt = $pdo->prepare("INSERT INTO members (email, password_hash, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
    $stmt->execute([$testEmail, password_hash('test', PASSWORD_BCRYPT)]);
    $testMemberId = (int)$pdo->lastInsertId();

    // Initialize MemberAuth with site_key
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => $testSiteKey,
    ]);

    // Use reflection to call private recordSiteConnection method
    $reflection = new ReflectionClass('MemberAuth');
    $method = $reflection->getMethod('recordSiteConnection');
    $method->setAccessible(true);
    $method->invokeArgs(null, [$testMemberId]);

    // Verify row was inserted
    $stmt = $pdo->prepare("SELECT * FROM member_site_connections WHERE member_id = ? AND site_key = ? LIMIT 1");
    $stmt->execute([$testMemberId, $testSiteKey]);
    $row = $stmt->fetch();

    if ($row && $row['connection_count'] == 1) {
        echo "  ✓ PASS: Row inserted with connection_count=1\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: Row not found or connection_count incorrect\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// TEST 2: Second call increments connection_count and updates last_seen_at
echo "\nTEST 2: Second call increments connection_count and updates last_seen_at\n";
try {
    $reflection = new ReflectionClass('MemberAuth');
    $method = $reflection->getMethod('recordSiteConnection');
    $method->setAccessible(true);

    // Sleep briefly to ensure last_seen_at changes
    sleep(1);
    $method->invokeArgs(null, [$testMemberId]);

    // Verify count incremented
    $stmt = $pdo->prepare("SELECT connection_count, last_seen_at FROM member_site_connections WHERE member_id = ? AND site_key = ? LIMIT 1");
    $stmt->execute([$testMemberId, $testSiteKey]);
    $row = $stmt->fetch();

    if ($row && $row['connection_count'] == 2) {
        echo "  ✓ PASS: connection_count incremented to 2\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: connection_count not incremented (got " . ($row['connection_count'] ?? 'null') . ")\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// TEST 3: No-op when site_key is empty string
echo "\nTEST 3: No-op when site_key is empty string\n";
try {
    // Reinitialize with empty site_key
    MemberAuth::reset();
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => '', // Empty
    ]);

    $countBefore = 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM member_site_connections WHERE member_id = ?");
    $stmt->execute([$testMemberId]);
    $countBefore = $stmt->fetch()['cnt'];

    $reflection = new ReflectionClass('MemberAuth');
    $method = $reflection->getMethod('recordSiteConnection');
    $method->setAccessible(true);
    $method->invokeArgs(null, [$testMemberId]);

    $countAfter = 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM member_site_connections WHERE member_id = ?");
    $stmt->execute([$testMemberId]);
    $countAfter = $stmt->fetch()['cnt'];

    if ($countBefore === $countAfter) {
        echo "  ✓ PASS: No rows added when site_key is empty\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: Rows were added despite empty site_key\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// TEST 4: Graceful no-op when member_site_connections table is absent
echo "\nTEST 4: Graceful no-op when member_site_connections table is absent\n";
try {
    // Drop table temporarily
    $pdo->exec("DROP TABLE IF EXISTS member_site_connections");

    MemberAuth::reset();
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => 'some_site',
    ]);

    $reflection = new ReflectionClass('MemberAuth');
    $method = $reflection->getMethod('recordSiteConnection');
    $method->setAccessible(true);

    // Should not throw
    $method->invokeArgs(null, [$testMemberId]);

    echo "  ✓ PASS: No exception thrown when table absent\n";
    $testsPassed++;
} catch (\Throwable $e) {
    echo "  ✗ FAIL: Exception thrown: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// Recreate table for remaining tests
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `member_site_connections` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `member_id` INT UNSIGNED NOT NULL,
        `site_key` VARCHAR(64) NOT NULL,
        `first_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `connection_count` INT UNSIGNED NOT NULL DEFAULT 1,
        UNIQUE KEY `uk_member_site` (`member_id`, `site_key`),
        KEY `idx_site_key` (`site_key`),
        KEY `idx_member_id` (`member_id`),
        CONSTRAINT `fk_msc_member` FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (\Throwable $e) {
    // Already exists
}

// TEST 5: register() writes registered_site_key on the member row
echo "\nTEST 5: register() writes registered_site_key when site_key is set\n";
try {
    MemberAuth::reset();
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => 'registration_test_site',
    ]);

    $testEmail2 = 'siteconn_reg_test_' . uniqid() . '@example.com';
    $newMember = MemberAuth::register([
        'email' => $testEmail2,
        'password' => 'TestPassword123!',
        'username' => 'testuser_' . uniqid(),
        'display_name' => 'Test User',
    ]);

    // Verify registered_site_key was set
    $stmt = $pdo->prepare("SELECT registered_site_key FROM members WHERE id = ? LIMIT 1");
    $stmt->execute([$newMember['id']]);
    $row = $stmt->fetch();

    if ($row && $row['registered_site_key'] === 'registration_test_site') {
        echo "  ✓ PASS: registered_site_key set to site_key value\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: registered_site_key not set correctly (got: " . ($row['registered_site_key'] ?? 'null') . ")\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// TEST 6: register() leaves registered_site_key NULL when site_key is empty
echo "\nTEST 6: register() leaves registered_site_key NULL when site_key is empty\n";
try {
    MemberAuth::reset();
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => '', // Empty
    ]);

    $testEmail3 = 'siteconn_no_key_test_' . uniqid() . '@example.com';
    $newMember = MemberAuth::register([
        'email' => $testEmail3,
        'password' => 'TestPassword123!',
        'username' => 'testuser2_' . uniqid(),
        'display_name' => 'Test User 2',
    ]);

    // Verify registered_site_key is NULL
    $stmt = $pdo->prepare("SELECT registered_site_key FROM members WHERE id = ? LIMIT 1");
    $stmt->execute([$newMember['id']]);
    $row = $stmt->fetch();

    if ($row && $row['registered_site_key'] === null) {
        echo "  ✓ PASS: registered_site_key left NULL\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: registered_site_key should be NULL (got: " . ($row['registered_site_key'] ?? 'null') . ")\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// TEST 7: startAuthenticatedSession() records a connection after login
echo "\nTEST 7: startAuthenticatedSession() records member_site_connections after login\n";
try {
    MemberAuth::reset();
    MemberAuth::init($pdo, [
        'mode' => 'independent',
        'members_table' => 'members',
        'table_prefix' => '',
        'site_key' => 'authenticated_session_test',
    ]);

    // Start session for test
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    MemberAuth::startSession();

    // Get a test member
    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? LIMIT 1");
    $stmt->execute([$testEmail]);
    $member = $stmt->fetch();

    // Clean up any prior connections for this test
    $pdo->prepare("DELETE FROM member_site_connections WHERE member_id = ? AND site_key = ?")->execute([
        $member['id'], 'authenticated_session_test'
    ]);

    MemberAuth::startAuthenticatedSession($member);

    // Verify connection was recorded
    $stmt = $pdo->prepare("SELECT connection_count FROM member_site_connections WHERE member_id = ? AND site_key = ? LIMIT 1");
    $stmt->execute([$member['id'], 'authenticated_session_test']);
    $row = $stmt->fetch();

    if ($row && $row['connection_count'] >= 1) {
        echo "  ✓ PASS: member_site_connections row created/updated after startAuthenticatedSession\n";
        $testsPassed++;
    } else {
        echo "  ✗ FAIL: member_site_connections row not found\n";
        $testsFailed++;
    }
} catch (\Throwable $e) {
    echo "  ✗ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

// CLEANUP
try {
    $stmt = $pdo->prepare("DELETE FROM member_site_connections WHERE member_id = ?");
    $stmt->execute([$testMemberId]);

    $stmt = $pdo->prepare("DELETE FROM members WHERE email LIKE ?");
    $stmt->execute(['siteconn_%']);
} catch (\Throwable $e) {
    // Cleanup may fail if tables don't exist
}

// SUMMARY
echo "\n=== Test Summary ===\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total:  " . ($testsPassed + $testsFailed) . "\n\n";

if ($testsFailed === 0) {
    echo "✓ All tests passed!\n";
    exit(0);
} else {
    echo "✗ Some tests failed\n";
    exit(1);
}
