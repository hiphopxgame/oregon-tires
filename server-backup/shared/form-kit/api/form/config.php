<?php
declare(strict_types=1);

/**
 * GET /api/form/config.php?site_key=oregon.tires
 * Fetch form configuration for a site.
 *
 * Returns the site's form config from the form_configs table,
 * merged with FormManager defaults. If no config row exists,
 * returns sensible defaults.
 */

// Bootstrap guard — skip if site wrapper already loaded
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('FORM_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Init if not already initialized by site wrapper
    if (!FormManager::getConfig('site_key')) {
        FormManager::init(getDatabase());
    }

    $siteKey = $_GET['site_key'] ?? FormManager::getConfig('site_key') ?? '';

    if (empty($siteKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'site_key is required']);
        exit;
    }

    $pdo = FormManager::getPdo();
    $table = FormManager::configsTable();

    $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE site_key = ? LIMIT 1");
    $stmt->execute([$siteKey]);
    $row = $stmt->fetch();

    // Default config
    $defaults = [
        'form_type'       => 'contact',
        'recipient_email' => null,
        'subject_prefix'  => '[Contact]',
        'auto_reply'      => false,
        'auto_reply_subject' => null,
        'auto_reply_body' => null,
        'success_message' => 'Thank you for your message. We will get back to you soon.',
        'rate_limit_max'  => 5,
        'rate_limit_window' => 3600,
        'actions'         => null,
        'custom_fields'   => null,
        'template'        => 'default',
    ];

    if ($row) {
        // Merge row over defaults, decode JSON fields
        $config = array_merge($defaults, [
            'form_type'       => $row['form_type'] ?? $defaults['form_type'],
            'recipient_email' => $row['recipient_email'] ?? $defaults['recipient_email'],
            'subject_prefix'  => $row['subject_prefix'] ?? $defaults['subject_prefix'],
            'auto_reply'      => (bool) ($row['auto_reply'] ?? $defaults['auto_reply']),
            'auto_reply_subject' => $row['auto_reply_subject'] ?? $defaults['auto_reply_subject'],
            'auto_reply_body' => $row['auto_reply_body'] ?? $defaults['auto_reply_body'],
            'success_message' => $row['success_message'] ?? $defaults['success_message'],
            'rate_limit_max'  => (int) ($row['rate_limit_max'] ?? $defaults['rate_limit_max']),
            'rate_limit_window' => (int) ($row['rate_limit_window'] ?? $defaults['rate_limit_window']),
            'actions'         => $row['actions'] ? json_decode($row['actions'], true) : $defaults['actions'],
            'custom_fields'   => $row['custom_fields'] ? json_decode($row['custom_fields'], true) : $defaults['custom_fields'],
            'template'        => $row['template'] ?? $defaults['template'],
        ]);
    } else {
        $config = $defaults;
    }

    echo json_encode([
        'success' => true,
        'config'  => $config,
    ]);
} catch (\Throwable $e) {
    error_log('Form config error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
