<?php
/**
 * Oregon Tires — Database Connection (PDO)
 */

declare(strict_types=1);

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host    = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname  = $_ENV['DB_NAME'] ?? '';
    $user    = $_ENV['DB_USER'] ?? '';
    $pass    = $_ENV['DB_PASSWORD'] ?? '';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
