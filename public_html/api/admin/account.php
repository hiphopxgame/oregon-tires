<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'PUT');
    $admin = requireAdmin();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: Current admin account info ────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->prepare(
            'SELECT display_name, email, notification_email, language
               FROM oretir_admins
              WHERE id = ?'
        );
        $stmt->execute([$admin['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            jsonError('Admin not found.', 404);
        }

        jsonSuccess($row);
    }

    // ─── PUT: Update account settings ───────────────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    $action = $body['action'] ?? '';

    switch ($action) {
        // ── Update display name ─────────────────────────────────────
        case 'update_name':
            if (empty($body['name']) || !is_string($body['name'])) {
                jsonError('Name is required.', 400);
            }

            $name = sanitize($body['name'], 100);

            $stmt = $db->prepare(
                'UPDATE oretir_admins SET display_name = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$name, $admin['id']]);

            $_SESSION['admin_name'] = $name;

            jsonSuccess(['display_name' => $name]);
            break;

        // ── Update email ────────────────────────────────────────────
        case 'update_email':
            if (empty($body['email']) || !is_string($body['email'])) {
                jsonError('Email is required.', 400);
            }

            $email = sanitize($body['email'], 255);

            if (!isValidEmail($email)) {
                jsonError('Invalid email address.', 400);
            }

            // Check email not taken by another admin
            $check = $db->prepare(
                'SELECT id FROM oretir_admins WHERE email = ? AND id != ? LIMIT 1'
            );
            $check->execute([$email, $admin['id']]);
            if ($check->fetch()) {
                jsonError('That email is already in use by another admin.', 409);
            }

            $stmt = $db->prepare(
                'UPDATE oretir_admins SET email = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$email, $admin['id']]);

            $_SESSION['admin_email'] = $email;

            jsonSuccess(['email' => $email]);
            break;

        // ── Update password ─────────────────────────────────────────
        case 'update_password':
            if (empty($body['current_password']) || empty($body['new_password'])) {
                jsonError('Current password and new password are required.', 400);
            }

            $currentPassword = $body['current_password'];
            $newPassword     = $body['new_password'];

            if (strlen($newPassword) < 8) {
                jsonError('New password must be at least 8 characters.', 400);
            }

            // Verify current password
            $stmt = $db->prepare('SELECT password_hash FROM oretir_admins WHERE id = ?');
            $stmt->execute([$admin['id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                jsonError('Current password is incorrect.', 403);
            }

            $newHash = hashPassword($newPassword);

            $stmt = $db->prepare(
                'UPDATE oretir_admins SET password_hash = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$newHash, $admin['id']]);

            jsonSuccess(['message' => 'Password updated successfully.']);
            break;

        // ── Update notification email ───────────────────────────────
        case 'update_notification_email':
            if (!isset($body['notification_email']) || !is_string($body['notification_email'])) {
                jsonError('Notification email is required.', 400);
            }

            $notifEmail = sanitize($body['notification_email'], 255);

            if ($notifEmail !== '' && !isValidEmail($notifEmail)) {
                jsonError('Invalid notification email address.', 400);
            }

            // Allow empty string to clear notification email
            $value = $notifEmail !== '' ? $notifEmail : null;

            $stmt = $db->prepare(
                'UPDATE oretir_admins SET notification_email = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$value, $admin['id']]);

            jsonSuccess(['notification_email' => $value]);
            break;

        // ── Update language preference ─────────────────────────────────
        case 'update_language':
            $lang = sanitize($body['language'] ?? '', 5);

            if (!in_array($lang, ['en', 'es', 'both'], true)) {
                jsonError('Invalid language. Must be: en, es, or both.', 400);
            }

            $stmt = $db->prepare(
                'UPDATE oretir_admins SET language = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$lang, $admin['id']]);

            // Sync session so subsequent requests reflect the change
            startSecureSession();
            $_SESSION['admin_language'] = $lang;

            jsonSuccess(['language' => $lang]);
            break;

        default:
            jsonError('Invalid action. Must be: update_name, update_email, update_password, update_notification_email, or update_language.', 400);
    }

} catch (\Throwable $e) {
    error_log('account.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
