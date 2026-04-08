<?php
declare(strict_types=1);

/**
 * FormManager — Core form management for Form Kit
 *
 * Static manager class that holds PDO connection, configuration,
 * and registered action handlers. All other Form Kit classes
 * depend on this for database access and config.
 *
 * Supports two modes via FORM_KIT_MODE env:
 *   "independent" — site has its own form tables
 *   "hw"          — site uses hiphop.world's shared tables
 */
class FormManager extends KitBase
{
    private static ?\PDO $pdo = null;
    private static array $config = [];
    private static bool $initialized = false;

    /** @var array<string, callable> Registered action handlers */
    private static array $actions = [];

    // ── KitBase abstract method implementations ──────────────────────────

    protected static function &staticPdo(): ?\PDO        { return self::$pdo; }
    protected static function &staticConfig(): array     { return self::$config; }
    protected static function &staticInitialized(): bool { return self::$initialized; }
    protected static function kitName(): string          { return 'FormManager'; }

    protected static function defaultConfig(): array
    {
        return [
            'mode'               => $_ENV['FORM_KIT_MODE'] ?? 'independent',
            'table_prefix'       => $_ENV['FORM_KIT_TABLE_PREFIX'] ?? '',
            'site_key'           => '',
            'form_type'          => 'contact',
            'recipient_email'    => '',
            'subject_prefix'     => '[Contact]',
            'auto_reply'         => false,
            'auto_reply_subject' => '',
            'auto_reply_body'    => '',
            'success_message'    => 'Thank you for your message. We will get back to you soon.',
            'rate_limit_max'     => 5,
            'rate_limit_window'  => 3600,
            'honeypot_field'     => '_hp_email',
            'actions'            => [],
            'mail_helper_path'   => null,
            'mail_from'          => '',
            'mail_from_name'     => '',
            'smtp_config'        => null,
        ];
    }

    // ── Initialization ───────────────────────────────────────────────────

    /**
     * Initialize FormManager with a PDO connection and optional config.
     *
     * @param \PDO  $pdo    Database connection
     * @param array $config Config overrides (merged with env defaults)
     */
    public static function init(\PDO $pdo, array $config = []): void
    {
        if (self::$initialized) {
            return;
        }
        parent::baseInit($pdo, $config);
    }

    // ── Table Names ──────────────────────────────────────────────────────

    /**
     * Get the form_submissions table name (with prefix if configured).
     */
    public static function submissionsTable(): string
    {
        return self::prefixedTable('form_submissions');
    }

    /**
     * Get the form_configs table name (with prefix if configured).
     */
    public static function configsTable(): string
    {
        return self::prefixedTable('form_configs');
    }

    // ── Action Registry ──────────────────────────────────────────────────

    /**
     * Register an action handler.
     *
     * Actions are executed after a successful form submission. Built-in
     * actions (like 'notify') are registered internally; sites can add
     * custom actions for booking, payment, etc.
     *
     * @param string   $name    Unique action identifier
     * @param callable $handler Receives (array $submission, array $config) and returns array
     */
    public static function registerAction(string $name, callable $handler): void
    {
        self::$actions[$name] = $handler;
    }

    /**
     * Get a registered action handler by name.
     */
    public static function getAction(string $name): ?callable
    {
        return self::$actions[$name] ?? null;
    }

    /**
     * Get all registered action handlers.
     *
     * @return array<string, callable>
     */
    public static function getActions(): array
    {
        return self::$actions;
    }

    // ── Template Resolution ──────────────────────────────────────────────

    /**
     * Resolve a form template path.
     *
     * Checks site-local override first, then falls back to the shared
     * form-kit templates directory. This allows any site to override
     * specific templates while sharing the rest.
     *
     * @param string $template Relative path like 'form/contact.php'
     * @param string $siteDir  The site's local templates directory
     * @return string Absolute path to the template file
     */
    public static function resolveTemplate(string $template, string $siteDir = ''): string
    {
        // Site-specific override takes priority
        if ($siteDir !== '') {
            $localPath = rtrim($siteDir, '/') . '/' . $template;
            if (file_exists($localPath)) {
                return $localPath;
            }
        }

        // Fall back to shared form-kit templates
        if (defined('FORM_KIT_TEMPLATES')) {
            $sharedPath = FORM_KIT_TEMPLATES . '/' . $template;
            if (file_exists($sharedPath)) {
                return $sharedPath;
            }
        }

        // Return local path even if missing (will trigger a standard PHP error)
        if ($siteDir !== '') {
            return rtrim($siteDir, '/') . '/' . $template;
        }
        return (defined('FORM_KIT_TEMPLATES') ? FORM_KIT_TEMPLATES : __DIR__ . '/../templates') . '/' . $template;
    }

    // ── Reset (testing) ──────────────────────────────────────────────────

    /**
     * Reset initialization state (for testing purposes).
     */
    public static function reset(): void
    {
        parent::baseReset();
        self::$actions = [];
    }
}
