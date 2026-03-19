<?php
declare(strict_types=1);

/**
 * Oregon Tires — Project Value Report
 * Admin-only page that renders the branded project valuation document.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

startSecureSession();
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/');
    exit;
}

require __DIR__ . '/../templates/admin/doc-project-value.php';
