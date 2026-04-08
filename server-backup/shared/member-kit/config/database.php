<?php
declare(strict_types=1);

/**
 * Database Configuration & Session Management — Member Kit Template
 *
 * Standardized PDO singleton and session init for any site.
 * Copy this to your site's config/ directory and adjust as needed.
 */

// Resolve project root: on server it's dirname(__DIR__) (document root),
// locally it's dirname(__DIR__, 2) (project root above public_html/).
$_projectRoot = file_exists(dirname(__DIR__) . '/vendor/autoload.php')
    ? dirname(__DIR__)
    : dirname(__DIR__, 2);

// Load Composer autoloader (for phpdotenv + PHPMailer)
if (file_exists($_projectRoot . '/vendor/autoload.php')) {
    require $_projectRoot . '/vendor/autoload.php';
}

// Load environment variables
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable($_projectRoot);
    $dotenv->safeLoad();
}

/** @var PDO|null Singleton PDO instance */
$_pdoInstance = null;

/**
 * Get PDO database connection (singleton)
 */
function getDatabase(): PDO
{
    global $_pdoInstance;

    if ($_pdoInstance !== null) {
        return $_pdoInstance;
    }

    $host     = $_ENV['DB_HOST']     ?? 'localhost';
    $port     = $_ENV['DB_PORT']     ?? '3306';
    $dbName   = $_ENV['DB_NAME']     ?? '';
    $user     = $_ENV['DB_USER']     ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    if ($dbName === '' || $user === '') {
        throw new \RuntimeException('Database configuration incomplete: DB_NAME and DB_USER are required');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $dbName
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $_pdoInstance = new PDO($dsn, $user, $password, $options);
    } catch (\Throwable $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new \RuntimeException('Database connection failed');
    }

    return $_pdoInstance;
}

/**
 * Initialize a secure session
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (($_SERVER['SERVER_PORT'] ?? 0) == 443)
    );

    $sessionName     = $_ENV['SESSION_NAME']     ?? 'member_session';
    $sessionLifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 86400);

    session_name($sessionName);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Session regeneration every 24 hours
    $now = time();
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = $now;
    } elseif ($now - $_SESSION['_created'] > 86400) {
        session_regenerate_id(true);
        $_SESSION['_created'] = $now;
    }

    // Activity timeout
    if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity'] > $sessionLifetime)) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_created'] = $now;
    }
    $_SESSION['_last_activity'] = $now;
}
