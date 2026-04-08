<?php
/**
 * Form Kit — CLI Helpers
 *
 * Shared utilities for CLI scripts across all sites.
 * Usage: require_once __DIR__ . '/helpers.php';  (from Form Kit cli/)
 *   or:  require_once '/path/to/form-kit/cli/helpers.php';  (from site cli/)
 */

declare(strict_types=1);

/**
 * Guard: exit with 403 if not running from CLI.
 * Call at the top of any script that must not be web-accessible.
 */
function requireCli(): void
{
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        exit('CLI only.');
    }
}

/**
 * Parse a .env file into an associative array.
 * Handles comments, quoted values, and empty lines.
 * Does NOT pollute $_ENV or putenv — returns a clean array.
 */
function parseEnvFile(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;

        $key = trim(substr($line, 0, $eqPos));
        $val = trim(substr($line, $eqPos + 1));

        // Strip surrounding quotes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"'))
            || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }

        $env[$key] = $val;
    }

    return $env;
}

/**
 * Create a PDO connection from a parsed .env array.
 */
function connectFromEnv(array $env): PDO
{
    $host    = $env['DB_HOST'] ?? 'localhost';
    $dbname  = $env['DB_NAME'] ?? '';
    $user    = $env['DB_USER'] ?? '';
    $pass    = $env['DB_PASSWORD'] ?? '';
    $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

    return new PDO(
        "mysql:host={$host};dbname={$dbname};charset={$charset}",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}
