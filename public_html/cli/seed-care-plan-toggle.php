<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pdo = getDB();
$pdo->exec("INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES ('show_care_plan_page', '0', '0') ON DUPLICATE KEY UPDATE value_en = '0', value_es = '0'");
echo "Done: show_care_plan_page = 0 (hidden)\n";
