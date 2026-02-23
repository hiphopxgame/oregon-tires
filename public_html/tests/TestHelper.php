<?php
/**
 * Minimal test harness for standalone PHP tests.
 * Provides assert helpers and colored pass/fail output.
 *
 * IMPORTANT: This class handles output buffering and session initialization
 * to prevent "headers already sent" warnings in test environments.
 *
 * Usage:
 *   require_once __DIR__ . '/TestHelper.php';
 *   TestHelper::initSession();  // Call this once per test file
 *   $t = new TestHelper('Suite Name');
 *   $t->test('description', function () { ... assert ... });
 *   $t->done();
 */

// Start output buffering at the very top to prevent header conflicts
if (ob_get_level() === 0) {
    ob_start();
}

class TestHelper
{
    private string $suite;
    private int $passed = 0;
    private int $failed = 0;
    /** @var array{string, string}[] */
    private array $failures = [];

    public function __construct(string $suite)
    {
        $this->suite = $suite;
        echo "\n========================================\n";
        echo "  TEST SUITE: {$suite}\n";
        echo "========================================\n\n";
    }

    /**
     * Initialize session for testing.
     *
     * Call this once per test file, AFTER including TestHelper but BEFORE
     * manipulating $_SESSION. This ensures:
     * - Output buffering is active (prevents header conflicts)
     * - Session is started safely without "headers already sent" warnings
     * - CSRF token is generated
     * - Session can be restarted cleanly between test runs
     *
     * Safe to call multiple times (idempotent).
     *
     * @return void
     */
    public static function initSession(): void
    {
        // If session is already active, nothing to do
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Ensure output buffering is active before starting session
        if (ob_get_level() === 0) {
            ob_start();
        }

        // Configure session for testing
        $lifetime = 2592000; // 30 days
        $sessionName = 'test_session_' . getmypid();

        ini_set('session.gc_maxlifetime', (string) $lifetime);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'httponly' => true,
            'secure' => false, // Allow non-HTTPS in test environment
            'samesite' => 'Lax',
        ]);

        session_name($sessionName);
        session_start();

        // Generate CSRF token if not set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Run a single test case.
     */
    public function test(string $description, callable $fn): void
    {
        try {
            $fn();
            $this->passed++;
            echo "  [PASS] {$description}\n";
        } catch (\Throwable $e) {
            $this->failed++;
            $msg = $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
            $this->failures[] = [$description, $msg];
            echo "  [FAIL] {$description}\n";
            echo "         -> {$msg}\n";
        }
    }

    /**
     * Print summary and exit with appropriate code.
     */
    public function done(): void
    {
        $total = $this->passed + $this->failed;
        echo "\n----------------------------------------\n";
        echo "  Results: {$this->passed}/{$total} passed";
        if ($this->failed > 0) {
            echo " ({$this->failed} FAILED)";
        }
        echo "\n----------------------------------------\n";

        if ($this->failures) {
            echo "\n  Failures:\n";
            foreach ($this->failures as [$desc, $msg]) {
                echo "    - {$desc}: {$msg}\n";
            }
        }

        echo "\n";
        exit($this->failed > 0 ? 1 : 0);
    }

    // ---- Assertion helpers ----

    public static function assertEqual(mixed $expected, mixed $actual, string $msg = ''): void
    {
        if ($expected !== $actual) {
            $e = var_export($expected, true);
            $a = var_export($actual, true);
            $label = $msg ? "{$msg}: " : '';
            throw new \RuntimeException("{$label}Expected {$e}, got {$a}");
        }
    }

    public static function assertTrue(mixed $value, string $msg = ''): void
    {
        if ($value !== true) {
            $label = $msg ?: 'Expected true';
            throw new \RuntimeException("{$label}, got " . var_export($value, true));
        }
    }

    public static function assertFalse(mixed $value, string $msg = ''): void
    {
        if ($value !== false) {
            $label = $msg ?: 'Expected false';
            throw new \RuntimeException("{$label}, got " . var_export($value, true));
        }
    }

    public static function assertContains(string $needle, string $haystack, string $msg = ''): void
    {
        if (!str_contains($haystack, $needle)) {
            $label = $msg ?: "Expected string to contain '{$needle}'";
            throw new \RuntimeException($label);
        }
    }

    public static function assertNull(mixed $value, string $msg = ''): void
    {
        if ($value !== null) {
            $label = $msg ?: 'Expected null';
            throw new \RuntimeException("{$label}, got " . var_export($value, true));
        }
    }

    public static function assertNotNull(mixed $value, string $msg = ''): void
    {
        if ($value === null) {
            $label = $msg ?: 'Expected non-null value';
            throw new \RuntimeException($label);
        }
    }

    public static function assertGreaterThan(int|float $expected, int|float $actual, string $msg = ''): void
    {
        if ($actual <= $expected) {
            $label = $msg ?: "Expected value > {$expected}";
            throw new \RuntimeException("{$label}, got {$actual}");
        }
    }
}
