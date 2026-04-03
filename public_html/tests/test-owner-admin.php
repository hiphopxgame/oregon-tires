#!/usr/bin/env php
<?php
/**
 * TDD: Test enforceOwnerAdmin() — owner email always gets admin rights.
 * Run: php tests/test-owner-admin.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/TestHelper.php';
TestHelper::initSession();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

$t = new TestHelper('Owner Admin Enforcement');

$pdo = getDB();

// ── Test 1: Constants defined ──
$t->test('OWNER_EMAIL constant is defined', function () {
    TestHelper::assertTrue(defined('OWNER_EMAIL'), 'OWNER_EMAIL should be defined');
    TestHelper::assertEqual('oregontirespdx@gmail.com', OWNER_EMAIL);
});

$t->test('OWNER_EMAIL is in PROTECTED_SUPERADMINS', function () {
    TestHelper::assertTrue(in_array(OWNER_EMAIL, PROTECTED_SUPERADMINS, true));
});

// ── Test 2: Function exists ──
$t->test('enforceOwnerAdmin() function exists', function () {
    TestHelper::assertTrue(function_exists('enforceOwnerAdmin'), 'enforceOwnerAdmin should be defined');
});

// ── Test 3: Returns true for owner email in admin_email ──
$t->test('restores admin session when admin_email matches owner', function () use ($pdo) {
    // Clear session
    $_SESSION = [];
    $_SESSION['admin_email'] = OWNER_EMAIL;
    // No admin_id set — simulates a wiped session

    $result = enforceOwnerAdmin($pdo);
    TestHelper::assertTrue($result, 'should return true for owner email');
    TestHelper::assertTrue(!empty($_SESSION['admin_id']), 'admin_id should be set');
    TestHelper::assertEqual(OWNER_EMAIL, $_SESSION['admin_email']);
    TestHelper::assertTrue(in_array($_SESSION['admin_role'], ['admin', 'superadmin', 'super_admin', 'owner'], true), 'role should be admin-level');
    TestHelper::assertTrue(!empty($_SESSION['login_time']), 'login_time should be set');
    TestHelper::assertTrue(!empty($_SESSION['csrf_token']), 'csrf_token should be set');
    TestHelper::assertEqual('admin', $_SESSION['dashboard_role']);
});

// ── Test 4: Returns true for owner email in member_email ──
$t->test('restores admin session when member_email matches owner', function () use ($pdo) {
    $_SESSION = [];
    $_SESSION['member_email'] = OWNER_EMAIL;
    $_SESSION['member_id'] = 999;

    $result = enforceOwnerAdmin($pdo);
    TestHelper::assertTrue($result, 'should return true for owner member_email');
    TestHelper::assertTrue(!empty($_SESSION['admin_id']), 'admin_id should be set');
    TestHelper::assertEqual('admin', $_SESSION['dashboard_role']);
});

// ── Test 5: Returns false for non-owner email ──
$t->test('returns false for non-owner email', function () use ($pdo) {
    $_SESSION = [];
    $_SESSION['admin_email'] = 'random@example.com';

    $result = enforceOwnerAdmin($pdo);
    TestHelper::assertFalse($result, 'should return false for non-owner');
    TestHelper::assertTrue(empty($_SESSION['admin_id']), 'admin_id should NOT be set');
});

// ── Test 6: Returns false for empty session ──
$t->test('returns false for empty session', function () use ($pdo) {
    $_SESSION = [];

    $result = enforceOwnerAdmin($pdo);
    TestHelper::assertFalse($result, 'should return false for empty session');
});

// ── Test 7: Owner admin row exists in DB ──
$t->test('owner has active admin row in oretir_admins', function () use ($pdo) {
    $stmt = $pdo->prepare('SELECT id, role, is_active FROM oretir_admins WHERE email = ? LIMIT 1');
    $stmt->execute([OWNER_EMAIL]);
    $admin = $stmt->fetch();
    TestHelper::assertNotNull($admin, 'owner should have admin row');
    TestHelper::assertTrue((bool) $admin['is_active'], 'owner admin should be active');
});

// ── Test 8: Owner cannot be demoted via DB ──
$t->test('isOwnerEmail() helper works', function () {
    TestHelper::assertTrue(isOwnerEmail(OWNER_EMAIL));
    TestHelper::assertTrue(isOwnerEmail(strtoupper(OWNER_EMAIL)));
    TestHelper::assertFalse(isOwnerEmail('other@gmail.com'));
    TestHelper::assertFalse(isOwnerEmail(''));
});

$t->done();
