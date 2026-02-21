<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';

try {
    requireMethod('GET', 'POST', 'DELETE');
    $admin = requireAdmin();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── All routes require superadmin ──────────────────────────────────
    if ($admin['role'] !== 'superadmin') {
        jsonError('Superadmin access required.', 403);
    }

    // ─── GET: List active admins ────────────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT id, email, display_name, role, language, created_at,
                    setup_completed_at, last_login_at,
                    CASE
                        WHEN last_login_at IS NOT NULL OR setup_completed_at IS NOT NULL THEN "active"
                        WHEN password_reset_token IS NOT NULL AND password_reset_expires > NOW() THEN "invited"
                        WHEN password_reset_token IS NOT NULL THEN "invite_expired"
                        ELSE "no_invite"
                    END AS invite_status
               FROM oretir_admins
              WHERE is_active = 1
              ORDER BY created_at ASC'
        );
        jsonSuccess($stmt->fetchAll());
    }

    verifyCsrf();

    // ─── POST: Resend invite or Create new admin ────────────────────────
    if ($method === 'POST') {
        $body = getJsonBody();

        // ── Resend invite action ──────────────────────────────────────
        if (($body['action'] ?? '') === 'resend_invite') {
            $targetId = (int) ($body['id'] ?? 0);
            if ($targetId < 1) {
                jsonError('Missing admin id.', 400);
            }

            $stmt = $db->prepare(
                'SELECT id, email, display_name, role, language FROM oretir_admins WHERE id = ? AND is_active = 1 LIMIT 1'
            );
            $stmt->execute([$targetId]);
            $target = $stmt->fetch();

            if (!$target) {
                jsonError('Admin not found.', 404);
            }

            // Generate fresh token
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

            $db->prepare(
                'UPDATE oretir_admins SET password_reset_token = ?, password_reset_expires = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$token, $expires, $targetId]);

            $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
            $setupUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;
            $lang     = $target['language'] ?? 'both';
            $roleLabel = ($target['role'] ?? 'admin') === 'superadmin' ? 'Super Admin' : 'Admin';

            $mailResult = sendBrandedSetupEmail($target['email'], $target['display_name'], $setupUrl, $lang, $roleLabel);

            jsonSuccess([
                'email_sent'  => $mailResult['success'],
                'email_error' => $mailResult['error'],
            ]);
        }

        // ── Set password action (manual) ────────────────────────────
        if (($body['action'] ?? '') === 'set_password') {
            $targetId = (int) ($body['id'] ?? 0);
            $password = (string) ($body['password'] ?? '');

            if ($targetId < 1) {
                jsonError('Missing admin id.', 400);
            }
            if (mb_strlen($password) < 8) {
                jsonError('Password must be at least 8 characters.', 400);
            }
            if (mb_strlen($password) > 72) {
                jsonError('Password must not exceed 72 characters.', 400);
            }
            if (!preg_match('/[A-Z]/', $password)) {
                jsonError('Password must contain at least one uppercase letter.', 400);
            }
            if (!preg_match('/[a-z]/', $password)) {
                jsonError('Password must contain at least one lowercase letter.', 400);
            }
            if (!preg_match('/[0-9]/', $password)) {
                jsonError('Password must contain at least one number.', 400);
            }

            $stmt = $db->prepare('SELECT id, email, display_name FROM oretir_admins WHERE id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$targetId]);
            $target = $stmt->fetch();

            if (!$target) {
                jsonError('Admin not found.', 404);
            }

            $hash = hashPassword($password);
            $db->prepare(
                'UPDATE oretir_admins SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL, login_attempts = 0, locked_until = NULL, setup_completed_at = COALESCE(setup_completed_at, NOW()), updated_at = NOW() WHERE id = ?'
            )->execute([$hash, $targetId]);

            logEmail('password_set_manual', "Password manually set by superadmin for: {$target['email']}");

            jsonSuccess(['message' => 'Password set for ' . $target['display_name']]);
        }

        // ── Create new admin ──────────────────────────────────────────
        $missing = requireFields($body, ['email', 'display_name']);
        if (!empty($missing)) {
            jsonError('Missing required fields: ' . implode(', ', $missing), 400);
        }

        $email       = sanitize($body['email'], 255);
        $displayName = sanitize($body['display_name'], 100);
        $role        = $body['role'] ?? 'admin';
        $language    = $body['language'] ?? 'both';

        if (!isValidEmail($email)) {
            jsonError('Invalid email address.', 400);
        }

        if (!in_array($role, ['admin', 'superadmin'], true)) {
            jsonError('Invalid role. Must be admin or superadmin.', 400);
        }

        if (!in_array($language, ['en', 'es', 'both'], true)) {
            jsonError('Invalid language. Must be en, es, or both.', 400);
        }

        // Check for duplicate email
        $check = $db->prepare('SELECT id FROM oretir_admins WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            jsonError('An admin with that email already exists.', 409);
        }

        // Generate setup token & unusable password
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        $hash    = hashPassword(bin2hex(random_bytes(32))); // unusable random hash

        $stmt = $db->prepare(
            'INSERT INTO oretir_admins
                (email, password_hash, display_name, role, language, is_active,
                 password_reset_token, password_reset_expires, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$email, $hash, $displayName, $role, $language, $token, $expires]);

        $newId = (int) $db->lastInsertId();

        // Auto-send branded bilingual setup email with language preference
        $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $setupUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;
        $roleLabel = $role === 'superadmin' ? 'Super Admin' : 'Admin';
        $mailResult = sendBrandedSetupEmail($email, $displayName, $setupUrl, $language, $roleLabel);

        jsonSuccess([
            'id'         => $newId,
            'email_sent' => $mailResult['success'],
            'email_error' => $mailResult['error'],
        ], 201);
    }

    // ─── DELETE: Deactivate admin ───────────────────────────────────────
    $id = (int) ($_GET['id'] ?? 0);
    if ($id < 1) {
        jsonError('Missing admin id.', 400);
    }

    if ($id === (int) $admin['id']) {
        jsonError('You cannot deactivate your own account.', 400);
    }

    $stmt = $db->prepare('UPDATE oretir_admins SET is_active = 0, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonError('Admin not found.', 404);
    }

    jsonSuccess(['deactivated' => $id]);

} catch (\Throwable $e) {
    error_log('admins.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
