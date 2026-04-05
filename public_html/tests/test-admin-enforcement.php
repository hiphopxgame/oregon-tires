#!/usr/bin/env php
<?php
/**
 * TDD: Test enforceAdminSession() — ALL admin emails always get admin rights.
 * Run: php tests/test-admin-enforcement.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/TestHelper.php';
TestHelper::initSession();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

$t = new TestHelper('Admin Session Enforcement');

$pdo = getDB();

// Get all active admin emails for testing
$admins = $pdo->query('SELECT id, email, role, display_name FROM oretir_admins WHERE is_active = 1 ORDER BY id')->fetchAll();

// ── Test 1: enforceAdminSession() exists ──
$t->test('enforceAdminSession() function exists', function () {
    TestHelper::assertTrue(function_exists('enforceAdminSession'), 'enforceAdminSession should be defined');
});

// ── Test 2: Owner email still works ──
$t->test('enforces admin for owner email (oregontirespdx@gmail.com)', function () use ($pdo) {
    $_SESSION = ['member_email' => OWNER_EMAIL];
    $result = enforceAdminSession($pdo);
    TestHelper::assertTrue($result, 'should return true for owner');
    TestHelper::assertTrue(!empty($_SESSION['admin_id']), 'admin_id should be set');
    TestHelper::assertEqual('admin', $_SESSION['dashboard_role']);
});

// ── Test 3: Every active admin gets admin rights ──
foreach ($admins as $admin) {
    $email = $admin['email'];
    $t->test("enforces admin for {$email}", function () use ($pdo, $email) {
        $_SESSION = ['member_email' => $email];
        $result = enforceAdminSession($pdo);
        TestHelper::assertTrue($result, "should return true for {$email}");
        TestHelper::assertTrue(!empty($_SESSION['admin_id']), "admin_id should be set for {$email}");
        TestHelper::assertEqual('admin', $_SESSION['dashboard_role']);
        TestHelper::assertTrue(!empty($_SESSION['admin_role']), "admin_role should be set for {$email}");
        TestHelper::assertTrue(!empty($_SESSION['login_time']), "login_time should be set for {$email}");
        TestHelper::assertTrue(!empty($_SESSION['csrf_token']), "csrf_token should be set for {$email}");
    });
}

// ── Test 4: Case-insensitive email matching ──
$t->test('case-insensitive: UPPERCASE email gets admin rights', function () use ($pdo, $admins) {
    if (empty($admins)) throw new \RuntimeException('No admins in DB');
    $email = strtoupper($admins[0]['email']);
    $_SESSION = ['member_email' => $email];
    $result = enforceAdminSession($pdo);
    TestHelper::assertTrue($result, "should match case-insensitively for {$email}");
    TestHelper::assertTrue(!empty($_SESSION['admin_id']), 'admin_id should be set');
});

$t->test('case-insensitive: MiXeD case email gets admin rights', function () use ($pdo, $admins) {
    if (empty($admins)) throw new \RuntimeException('No admins in DB');
    // Create mixed case version
    $original = $admins[0]['email'];
    $mixed = '';
    for ($i = 0; $i < strlen($original); $i++) {
        $mixed .= $i % 2 === 0 ? strtoupper($original[$i]) : strtolower($original[$i]);
    }
    $_SESSION = ['member_email' => $mixed];
    $result = enforceAdminSession($pdo);
    TestHelper::assertTrue($result, "should match mixed case {$mixed}");
});

// ── Test 5: admin_email session var also works ──
$t->test('detects admin via admin_email session var', function () use ($pdo, $admins) {
    if (empty($admins)) throw new \RuntimeException('No admins in DB');
    $_SESSION = ['admin_email' => $admins[0]['email']];
    $result = enforceAdminSession($pdo);
    TestHelper::assertTrue($result, 'should detect via admin_email');
    TestHelper::assertTrue(!empty($_SESSION['admin_id']), 'admin_id should be set');
});

// ── Test 6: Non-admin emails rejected ──
$t->test('returns false for non-admin email', function () use ($pdo) {
    $_SESSION = ['member_email' => 'random-nobody@example.com'];
    $result = enforceAdminSession($pdo);
    TestHelper::assertFalse($result, 'should return false for non-admin');
    TestHelper::assertTrue(empty($_SESSION['admin_id']), 'admin_id should NOT be set');
});

$t->test('returns false for empty session', function () use ($pdo) {
    $_SESSION = [];
    $result = enforceAdminSession($pdo);
    TestHelper::assertFalse($result, 'should return false for empty session');
});

// ── Test 7: Already-valid session is a no-op ──
$t->test('no-op when admin session is already valid', function () use ($pdo, $admins) {
    if (empty($admins)) throw new \RuntimeException('No admins in DB');
    $_SESSION = [
        'admin_id'    => $admins[0]['id'],
        'admin_email' => $admins[0]['email'],
        'admin_role'  => $admins[0]['role'],
        'login_time'  => time(),
        'csrf_token'  => 'existing-token',
    ];
    $result = enforceAdminSession($pdo);
    TestHelper::assertTrue($result, 'should return true for valid session');
    TestHelper::assertEqual('existing-token', $_SESSION['csrf_token'], 'should not overwrite existing csrf_token');
});

// ── Test 8: Owner auto-create still works ──
$t->test('isOwnerEmail() still works', function () {
    TestHelper::assertTrue(isOwnerEmail(OWNER_EMAIL));
    TestHelper::assertFalse(isOwnerEmail('nobody@example.com'));
});

// ── Test 9: Inactive admin is rejected ──
$t->test('inactive admin is rejected', function () use ($pdo) {
    // Find an inactive admin if any
    $stmt = $pdo->query('SELECT email FROM oretir_admins WHERE is_active = 0 LIMIT 1');
    $inactive = $stmt->fetchColumn();
    if (!$inactive) {
        // No inactive admins to test — pass vacuously
        return;
    }
    $_SESSION = ['member_email' => $inactive];
    $result = enforceAdminSession($pdo);
    TestHelper::assertFalse($result, "inactive admin {$inactive} should be rejected");
});

$t->done();
