<?php
declare(strict_types=1);
return function (PDO $pdo): void {
    try {
        $pdo->exec("ALTER TABLE members ADD COLUMN IF NOT EXISTS `2fa_suggested_at` TIMESTAMP NULL DEFAULT NULL");
    } catch (\Throwable $e) {}
    $mode = $_ENV['MEMBER_MODE'] ?? 'independent';
    if ($mode === 'hw') {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS `2fa_suggested_at` TIMESTAMP NULL DEFAULT NULL");
        } catch (\Throwable $e) {}
    }
};
