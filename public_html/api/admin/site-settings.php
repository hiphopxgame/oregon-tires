<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST');
    $admin = requireAdmin();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: Return all site settings ──────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT setting_key, value_en, value_es, updated_at
               FROM oretir_site_settings
              ORDER BY id ASC'
        );
        jsonSuccess($stmt->fetchAll());
    }

    // ─── POST: Save site settings ───────────────────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    if (!isset($body['settings']) || !is_array($body['settings'])) {
        jsonError('Missing settings array.', 400);
    }

    $validKeys = [
        'phone', 'email', 'address',
        'hours_weekday', 'hours_sunday',
        'rating_value', 'review_count',
        // Email templates — welcome
        'email_tpl_welcome_subject', 'email_tpl_welcome_greeting',
        'email_tpl_welcome_body', 'email_tpl_welcome_button', 'email_tpl_welcome_footer',
        // Email templates — password reset
        'email_tpl_reset_subject', 'email_tpl_reset_greeting',
        'email_tpl_reset_body', 'email_tpl_reset_button', 'email_tpl_reset_footer',
        // Email templates — contact notification
        'email_tpl_contact_subject', 'email_tpl_contact_greeting',
        'email_tpl_contact_body', 'email_tpl_contact_button', 'email_tpl_contact_footer',
    ];

    $stmt = $db->prepare(
        'INSERT INTO oretir_site_settings (setting_key, value_en, value_es, updated_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE value_en = VALUES(value_en),
                                 value_es = VALUES(value_es),
                                 updated_at = NOW()'
    );

    $updated = 0;

    foreach ($body['settings'] as $setting) {
        if (!isset($setting['setting_key'])) {
            continue;
        }

        $key = $setting['setting_key'];
        if (!in_array($key, $validKeys, true)) {
            jsonError("Invalid setting key: {$key}", 400);
        }

        // Email templates allow basic HTML (strong, em, br, a) — skip strip_tags
        $isTemplate = str_starts_with($key, 'email_tpl_');
        $maxLen = $isTemplate ? 5000 : 1000;

        if ($isTemplate) {
            // Allow safe HTML for email templates, trim + length limit only
            $allowedTags = '<strong><em><br><a><b><i><u><span>';
            $valueEn = mb_substr(trim(strip_tags((string) ($setting['value_en'] ?? ''), $allowedTags)), 0, $maxLen, 'UTF-8');
            $valueEs = mb_substr(trim(strip_tags((string) ($setting['value_es'] ?? ''), $allowedTags)), 0, $maxLen, 'UTF-8');
        } else {
            $valueEn = sanitize((string) ($setting['value_en'] ?? ''), $maxLen);
            $valueEs = sanitize((string) ($setting['value_es'] ?? ''), $maxLen);
        }

        $stmt->execute([$key, $valueEn, $valueEs]);
        $updated++;
    }

    jsonSuccess(['updated' => $updated]);

} catch (\Throwable $e) {
    error_log('site-settings.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
