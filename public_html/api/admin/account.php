<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();

try {
    // Allow both admins and employees to manage their own account
    $staff = requireStaff();
    requireMethod('GET', 'PUT');
    $db = getDB();

    // Init member-kit (needed for employee password changes)
    initMemberKit($db);
    $method = $_SERVER['REQUEST_METHOD'];
    $isAdmin = $staff['type'] === 'admin';

    // ─── GET: Current account info ────────────────────────────────────
    if ($method === 'GET') {
        if ($isAdmin) {
            // Check if google_id column exists
            $hasGoogleId = false;
            try {
                $db->query('SELECT google_id FROM oretir_admins LIMIT 0');
                $hasGoogleId = true;
            } catch (\Throwable $_) {}

            $cols = 'display_name, email, notification_email, language';
            if ($hasGoogleId) {
                $cols .= ', google_id';
            }

            $stmt = $db->prepare("SELECT {$cols} FROM oretir_admins WHERE id = ?");
            $stmt->execute([$staff['id']]);
            $row = $stmt->fetch();

            if (!$row) {
                jsonError('Admin not found.', 404);
            }

            $data = [
                'display_name'       => $row['display_name'],
                'email'              => $row['email'],
                'notification_email' => $row['notification_email'],
                'language'           => $row['language'],
                'google_linked'      => $hasGoogleId && !empty($row['google_id']),
                'staff_type'         => 'admin',
            ];
        } else {
            // Employee: pull from oretir_employees + members
            $empId = $staff['employee_id'];
            $stmt = $db->prepare('SELECT name, email FROM oretir_employees WHERE id = ? LIMIT 1');
            $stmt->execute([$empId]);
            $emp = $stmt->fetch();

            if (!$emp) {
                jsonError('Employee not found.', 404);
            }

            // Check Google link via members table
            $googleLinked = false;
            $memberId = $_SESSION['member_id'] ?? null;
            $language = 'both';
            if ($memberId) {
                try {
                    $mStmt = $db->prepare('SELECT google_id, language FROM members WHERE id = ? LIMIT 1');
                    $mStmt->execute([$memberId]);
                    $mRow = $mStmt->fetch();
                    if ($mRow) {
                        $googleLinked = !empty($mRow['google_id']);
                        $language = $mRow['language'] ?? 'both';
                    }
                } catch (\Throwable $_) {}
            }

            $data = [
                'display_name'       => $emp['name'],
                'email'              => $emp['email'],
                'notification_email' => null,
                'language'           => $language,
                'google_linked'      => $googleLinked,
                'staff_type'         => 'employee',
            ];
        }

        jsonSuccess($data);
    }

    // ─── PUT: Update account settings ─────────────────────────────────
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

            if ($isAdmin) {
                $db->prepare('UPDATE oretir_admins SET display_name = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$name, $staff['id']]);
                $_SESSION['admin_name'] = $name;
            } else {
                $db->prepare('UPDATE oretir_employees SET name = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$name, $staff['employee_id']]);
                $_SESSION['employee_name'] = $name;
            }

            // Also sync to members table
            $memberId = $_SESSION['member_id'] ?? null;
            if ($memberId) {
                try {
                    $db->prepare('UPDATE members SET display_name = ? WHERE id = ?')
                       ->execute([$name, $memberId]);
                } catch (\Throwable $_) {}
            }

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

            if ($isAdmin) {
                // Check email not taken by another admin
                $check = $db->prepare('SELECT id FROM oretir_admins WHERE email = ? AND id != ? LIMIT 1');
                $check->execute([$email, $staff['id']]);
                if ($check->fetch()) {
                    jsonError('That email is already in use by another admin.', 409);
                }

                $db->prepare('UPDATE oretir_admins SET email = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$email, $staff['id']]);
                $_SESSION['admin_email'] = $email;
            } else {
                // Check email not taken by another employee
                $check = $db->prepare('SELECT id FROM oretir_employees WHERE email = ? AND id != ? LIMIT 1');
                $check->execute([$email, $staff['employee_id']]);
                if ($check->fetch()) {
                    jsonError('That email is already in use by another employee.', 409);
                }

                $db->prepare('UPDATE oretir_employees SET email = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$email, $staff['employee_id']]);
                $_SESSION['employee_email'] = $email;
            }

            // Also sync to members table
            $memberId = $_SESSION['member_id'] ?? null;
            if ($memberId) {
                try {
                    $check = $db->prepare('SELECT id FROM members WHERE email = ? AND id != ? LIMIT 1');
                    $check->execute([$email, $memberId]);
                    if (!$check->fetch()) {
                        $db->prepare('UPDATE members SET email = ? WHERE id = ?')
                           ->execute([$email, $memberId]);
                        $_SESSION['member_email'] = $email;
                    }
                } catch (\Throwable $_) {}
            }

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

            if ($isAdmin) {
                // Verify current password against oretir_admins
                $stmt = $db->prepare('SELECT password_hash FROM oretir_admins WHERE id = ?');
                $stmt->execute([$staff['id']]);
                $row = $stmt->fetch();

                if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                    jsonError('Current password is incorrect.', 403);
                }

                $newHash = hashPassword($newPassword);
                $db->prepare('UPDATE oretir_admins SET password_hash = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$newHash, $staff['id']]);
            } else {
                // Employee: use member-kit password change
                $memberId = $_SESSION['member_id'] ?? null;
                if (!$memberId) {
                    jsonError('Member session not found.', 400);
                }

                $changed = MemberAuth::changePassword($memberId, $currentPassword, $newPassword);
                if (!$changed) {
                    jsonError('Current password is incorrect.', 403);
                }
            }

            jsonSuccess(['message' => 'Password updated successfully.']);
            break;

        // ── Update notification email (admin only) ────────────────
        case 'update_notification_email':
            if (!$isAdmin) {
                jsonError('Notification email is an admin-only setting.', 403);
            }

            if (!isset($body['notification_email']) || !is_string($body['notification_email'])) {
                jsonError('Notification email is required.', 400);
            }

            $notifEmail = sanitize($body['notification_email'], 255);

            if ($notifEmail !== '' && !isValidEmail($notifEmail)) {
                jsonError('Invalid notification email address.', 400);
            }

            // Allow empty string to clear notification email
            $value = $notifEmail !== '' ? $notifEmail : null;

            $db->prepare('UPDATE oretir_admins SET notification_email = ?, updated_at = NOW() WHERE id = ?')
               ->execute([$value, $staff['id']]);

            jsonSuccess(['notification_email' => $value]);
            break;

        // ── Update language preference ───────────────────────────────
        case 'update_language':
            $lang = sanitize($body['language'] ?? '', 5);

            if (!in_array($lang, ['en', 'es', 'both'], true)) {
                jsonError('Invalid language. Must be: en, es, or both.', 400);
            }

            if ($isAdmin) {
                $db->prepare('UPDATE oretir_admins SET language = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$lang, $staff['id']]);
            } else {
                // Store in members table for employees
                $memberId = $_SESSION['member_id'] ?? null;
                if ($memberId) {
                    try {
                        $db->prepare('UPDATE members SET language = ? WHERE id = ?')
                           ->execute([$lang, $memberId]);
                    } catch (\Throwable $_) {}
                }
            }

            startSecureSession();
            $_SESSION['admin_language'] = $lang;

            jsonSuccess(['language' => $lang]);
            break;

        // ── Unlink Google account ───────────────────────────────────
        case 'unlink_google':
            if ($isAdmin) {
                try {
                    $db->query('SELECT google_id FROM oretir_admins LIMIT 0');
                } catch (\Throwable $_) {
                    jsonError('Google OAuth not configured.', 400);
                }

                $db->prepare('UPDATE oretir_admins SET google_id = NULL, updated_at = NOW() WHERE id = ?')
                   ->execute([$staff['id']]);
            } else {
                // Employee: unlink via members table
                $memberId = $_SESSION['member_id'] ?? null;
                if (!$memberId) {
                    jsonError('Member session not found.', 400);
                }
                try {
                    $db->prepare('UPDATE members SET google_id = NULL, google_email = NULL WHERE id = ?')
                       ->execute([$memberId]);
                } catch (\Throwable $_) {
                    jsonError('Google OAuth not configured.', 400);
                }
            }

            jsonSuccess(['google_linked' => false]);
            break;

        default:
            jsonError('Invalid action. Must be: update_name, update_email, update_password, update_notification_email, update_language, or unlink_google.', 400);
    }

} catch (\Throwable $e) {
    error_log('account.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
