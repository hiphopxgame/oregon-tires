<?php
declare(strict_types=1);

/**
 * FormRenderer — Template rendering for Form Kit
 *
 * Provides helper methods for rendering form templates with
 * proper context variables, CSRF protection, and honeypot fields.
 * Sites can override templates via FormManager::resolveTemplate().
 */
class FormRenderer
{
    /**
     * Render a form template with the given context variables.
     *
     * Resolves the template path (site-local override first, then shared),
     * extracts context variables into scope, and includes the template.
     * Output is captured and returned as a string.
     *
     * @param string $template Relative template path (e.g., 'form/contact.php')
     * @param array  $context  Variables to extract into template scope
     * @param string $siteDir  Site's local templates directory for overrides
     * @return string Rendered HTML
     */
    public static function render(string $template, array $context = [], string $siteDir = ''): string
    {
        $templatePath = FormManager::resolveTemplate($template, $siteDir);

        // Add default context variables
        $context = array_merge([
            'config'         => FormManager::getConfig(),
            'honeypot_field' => FormManager::getConfig('honeypot_field') ?? '_hp_email',
            'form_type'      => FormManager::getConfig('form_type') ?? 'contact',
            'site_key'       => FormManager::getConfig('site_key') ?? '',
            'version'        => defined('FORM_KIT_VERSION') ? FORM_KIT_VERSION : '1.0.0',
        ], $context);

        // Extract context variables into local scope for the template
        extract($context, EXTR_SKIP);

        ob_start();
        include $templatePath;
        return (string) ob_get_clean();
    }

    /**
     * Generate a honeypot field HTML snippet.
     *
     * Creates an invisible input field that bots tend to fill out.
     * Legitimate users won't see or interact with it. The field is
     * hidden via CSS (not type="hidden") so bots treat it as real.
     *
     * @param string|null $fieldName Override the honeypot field name
     * @return string HTML for the honeypot field
     */
    public static function honeypotField(?string $fieldName = null): string
    {
        $name = htmlspecialchars(
            $fieldName ?? FormManager::getConfig('honeypot_field') ?? '_hp_email',
            ENT_QUOTES,
            'UTF-8'
        );

        return '<div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">'
             . '<label for="' . $name . '">Leave this empty</label>'
             . '<input type="text" name="' . $name . '" id="' . $name . '" value="" tabindex="-1" autocomplete="off">'
             . '</div>';
    }

    /**
     * Generate a CSRF token and hidden input field.
     *
     * Creates a random token, stores it in the session, and returns
     * an HTML hidden input. The token should be validated on submission.
     *
     * @return string HTML hidden input with CSRF token
     */
    public static function csrfField(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '<!-- CSRF: no active session -->';
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['form_kit_csrf'] = $token;
        $_SESSION['form_kit_csrf_time'] = time();

        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate a CSRF token from the submitted form data.
     *
     * Checks that the token matches the session-stored value and
     * has not expired (default 1 hour lifetime).
     *
     * @param string $token     The submitted CSRF token
     * @param int    $maxAge    Maximum token age in seconds (default 3600)
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrf(string $token, int $maxAge = 3600): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $storedToken = $_SESSION['form_kit_csrf'] ?? '';
        $storedTime = (int) ($_SESSION['form_kit_csrf_time'] ?? 0);

        if ($storedToken === '' || $token === '') {
            return false;
        }

        // Timing-safe comparison
        if (!hash_equals($storedToken, $token)) {
            return false;
        }

        // Check expiration
        if ((time() - $storedTime) > $maxAge) {
            return false;
        }

        // Clear used token (one-time use)
        unset($_SESSION['form_kit_csrf'], $_SESSION['form_kit_csrf_time']);

        return true;
    }
}
