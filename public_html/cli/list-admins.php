<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
$envDir = dirname(__DIR__, 3);
$envFile = '.env.oregon-tires';
if (!file_exists($envDir . '/' . $envFile)) {
    $envDir = __DIR__ . '/..';
    $envFile = '.env';
}
$dotenv = Dotenv\Dotenv::createImmutable($envDir, $envFile);
$dotenv->load();
require_once __DIR__ . '/../includes/db.php';
$db = getDB();
$stmt = $db->query('SELECT id, email, display_name, role, is_active, password_hash IS NOT NULL as has_password, password_reset_token IS NOT NULL as has_token FROM oretir_admins ORDER BY id');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo json_encode($r) . "\n";
}
