<?php
declare(strict_types=1);

/**
 * Oregon Tires — Commerce Admin
 * Standalone admin page for managing orders.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

startSecureSession();
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/');
    exit;
}

$commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../---commerce-kit';
require_once $commerceKitPath . '/loader.php';

$siteKey = 'oregon.tires';
$apiBase = '/api/commerce';
$page = $_GET['page'] ?? 'orders';
$orderRef = $_GET['ref'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commerce — Oregon Tires Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="bg-[#0A0A0A] min-h-screen">
    <!-- Nav -->
    <div class="bg-[#111827] border-b border-gray-800 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="/admin/" class="text-gray-400 hover:text-white text-sm">&larr; Dashboard</a>
            <span class="text-gray-600">|</span>
            <h2 class="text-white font-semibold">Commerce</h2>
        </div>
    </div>

    <?php if ($page === 'order-detail' && $orderRef): ?>
        <?php include $commerceKitPath . '/templates/admin-order-detail.php'; ?>
    <?php else: ?>
        <?php include $commerceKitPath . '/templates/admin-orders.php'; ?>
    <?php endif; ?>
</body>
</html>
