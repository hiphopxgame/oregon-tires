<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../includes/bootstrap.php';
$pdo = getDB();

try {
    $stmt = $pdo->prepare("UPDATE oretir_site_settings SET value_en = REPLACE(value_en, ?, ?), value_es = REPLACE(value_es, ?, ?) WHERE setting_key = ?");
    $stmt->execute([
        'call or text you shortly', 'contact you shortly',
        'Le llamaremos o enviaremos un mensaje de texto pronto', 'Nos comunicaremos con usted pronto',
        'email_tpl_booking_body'
    ]);
    echo "Rows updated: " . $stmt->rowCount() . PHP_EOL;
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
