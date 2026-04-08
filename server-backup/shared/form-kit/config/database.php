<?php
declare(strict_types=1);

/**
 * Database Configuration & Session Management — Form Kit
 *
 * Standardized PDO singleton and session init.
 * Sites using form-kit via a wrapper will already have getDatabase()
 * defined — this file is only loaded as a fallback for standalone use.
 */

if (function_exists('getDatabase')) {
    return;
}

function getDatabase(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }

    // Load .env if available
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $val] = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
            }
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_NAME'] ?? '',
        $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? '', $_ENV['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
