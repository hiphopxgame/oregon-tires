<?php
/**
 * Engine Kit — Convenience Helper Functions
 *
 * Simple functions that satellite sites call to render engine components.
 * All functions are safe to call — they return empty strings on failure.
 */

/**
 * Safe component call — loads a component, checks isEnabled(), calls $method.
 *
 * Centralizes the try/catch-component-call pattern shared by all helpers.
 * Returns $default (default '') on any failure or when the component is disabled.
 *
 * @param string $siteKey  Site key
 * @param string $slug     Component slug ('footer', 'contact-form', etc.)
 * @param string $method   Method to call on the component instance
 * @param array  $args     Arguments to pass to $method
 * @param mixed  $default  Return value on failure
 * @return mixed
 */
function _engineCall(string $siteKey, string $slug, string $method, array $args = [], mixed $default = ''): mixed
{
    $engine = getEngineKit($siteKey);
    if (!$engine) return $default;

    try {
        $comp = $engine->component($slug, ['site_key' => $siteKey]);
        if ($comp && $comp->isEnabled($siteKey)) {
            return $comp->$method(...$args);
        }
    } catch (\Throwable $e) {
        error_log("[engine-kit] {$slug}.{$method} error: {$e->getMessage()}");
    }
    return $default;
}

/**
 * Render <head> content: analytics script + SEO meta + social OG tags.
 *
 * @param string $siteKey Site key (e.g., 'oregontires')
 * @param array  $data    Optional extra data (page_title, page_url, etc.)
 * @return string HTML to include in <head>
 */
function engineHead(string $siteKey, array $data = []): string
{
    $engine = getEngineKit($siteKey);
    if (!$engine) return '';

    $html = '';

    // CSS variables from theme/branding
    try {
        $site = $engine->getSite($siteKey);
        if ($site) {
            $theme = \Engine\ThemeEngine::getInstance($engine->pdo, $site);
            $html .= $theme->getCSSVariables();
        }
    } catch (\Throwable $e) {
        error_log("[engine-kit] branding error: {$e->getMessage()}");
    }

    // Analytics (GA4/GTM)
    try {
        $html .= $engine->middleware->render('analytics', 'renderHead', [$siteKey, $data], '');
    } catch (\Throwable $e) {
        error_log("[engine-kit] analytics error: {$e->getMessage()}");
    }

    // SEO meta tags
    try {
        $html .= $engine->middleware->render('seo', 'renderHead', [$siteKey, $data], '');
    } catch (\Throwable $e) {
        error_log("[engine-kit] seo error: {$e->getMessage()}");
    }

    // Social/OG meta tags
    try {
        $html .= $engine->middleware->render('social-settings', 'renderHead', [$siteKey, $data], '');
    } catch (\Throwable $e) {
        error_log("[engine-kit] social error: {$e->getMessage()}");
    }

    return $html;
}

/**
 * Render the site footer.
 *
 * @param string $siteKey Site key
 * @return string Footer HTML
 */
function engineFooter(string $siteKey): string
{
    return (string) _engineCall($siteKey, 'footer', 'renderFooter', [$siteKey]);
}

/**
 * Render site navigation.
 *
 * Phase 3 stub — returns '' until a nav component is built.
 *
 * @param string $siteKey Site key
 * @param array  $data    Optional: active_page, mobile_menu, etc.
 * @return string Nav HTML, or '' if nav component is not enabled
 */
function engineNav(string $siteKey, array $data = []): string
{
    return (string) _engineCall($siteKey, 'nav', 'renderNav', [$siteKey, $data]);
}

/**
 * Render the contact form.
 *
 * @param string $siteKey Site key
 * @return string Contact form HTML
 */
function engineContactForm(string $siteKey): string
{
    return (string) _engineCall($siteKey, 'contact-form', 'renderForm', [$siteKey]);
}

/**
 * Get branding CSS variables as an associative array.
 *
 * @param string $siteKey Site key
 * @return array CSS variable name => value pairs (e.g. ['--site-primary' => '#0D3618'])
 */
function engineBranding(string $siteKey): array
{
    $engine = getEngineKit($siteKey);
    if (!$engine) return [];

    try {
        $site = $engine->getSite($siteKey);
        if (!$site) return [];

        $branding = !empty($site['branding']) ? json_decode($site['branding'], true) : [];
        if (!is_array($branding)) return [];

        $vars = [];
        $map = [
            'primary_color'   => '--site-primary',
            'secondary_color' => '--site-secondary',
            'accent_color'    => '--site-accent',
            'font'            => '--site-font',
            'bg_color'        => '--site-bg',
            'surface_color'   => '--site-surface',
            'text_primary'    => '--site-text',
            'text_secondary'  => '--site-text-muted',
        ];

        foreach ($map as $key => $cssVar) {
            if (!empty($branding[$key])) {
                $vars[$cssVar] = $branding[$key];
            }
        }

        return $vars;
    } catch (\Throwable $e) {
        error_log("[engine-kit] branding error: {$e->getMessage()}");
        return [];
    }
}

/**
 * Get member auth interface.
 *
 * Returns null until the member component is enabled for the given site.
 *
 * @param string $siteKey Site key
 * @return object|null Member component instance, or null if not enabled
 */
function engineMember(string $siteKey)
{
    $engine = getEngineKit($siteKey);
    if (!$engine) return null;

    try {
        $member = $engine->component('member', ['site_key' => $siteKey]);
        if ($member && $member->isEnabled($siteKey)) {
            return $member;
        }
    } catch (\Throwable $e) {
        error_log("[engine-kit] member error: {$e->getMessage()}");
    }

    return null;
}

/**
 * Send an email via the engine's email component.
 *
 * Returns false if the email component is not enabled for the given site.
 *
 * @param string $siteKey  Site key
 * @param string $to       Recipient email
 * @param string $subject  Subject line
 * @param string $body     HTML body
 * @param array  $opts     Optional: reply_to, from_name, etc.
 * @return bool
 */
function engineEmail(string $siteKey, string $to, string $subject, string $body, array $opts = []): bool
{
    $engine = getEngineKit($siteKey);
    if (!$engine) return false;

    try {
        $email = $engine->component('email', ['site_key' => $siteKey]);
        if ($email && $email->isEnabled($siteKey)) {
            $result = $email->sendEmail($siteKey, $to, $subject, $body, $opts);
            return $result;
        }
    } catch (\Throwable $e) {
        error_log("[engine-kit] email error: {$e->getMessage()}");
    }

    return false;
}
