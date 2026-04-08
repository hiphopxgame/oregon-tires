<?php
/**
 * IP Helpers — Cloudflare-aware client IP detection for the 1vsM network.
 *
 * Modeled on Oregon Tires' proven implementation, extended with Cloudflare
 * CF-Connecting-IP header support for all proxied sites.
 *
 * Usage:
 *   require_once __DIR__ . '/ip-helpers.php';
 *   $ip = getClientIp();
 */

/**
 * Get the real client IP address behind Cloudflare / reverse proxies.
 *
 * Priority: CF-Connecting-IP > X-Forwarded-For (first) > X-Real-IP > REMOTE_ADDR
 *
 * Only trusts proxy headers when REMOTE_ADDR is a private/reserved IP (i.e., the
 * direct connection is from a known proxy like Cloudflare, LiteSpeed, or localhost).
 *
 * @return string Valid IPv4 or IPv6 address, or '0.0.0.0' if unresolvable
 */
function getClientIp(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Only trust forwarded headers if direct connection is from a trusted proxy
    // (private range = behind load balancer / Cloudflare / localhost)
    $isTrustedProxy = filter_var(
        $remote,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;

    if ($isTrustedProxy) {
        // Cloudflare sets this to the true client IP — most reliable when proxied
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $remote;

        // X-Forwarded-For may contain multiple IPs (client, proxy1, proxy2)
        // The first entry is the original client
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
    } else {
        // Direct connection from a public IP — trust REMOTE_ADDR
        $ip = $remote;
    }

    // Validate IP format (prevents spoofed garbage)
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '0.0.0.0';
    }

    return $ip;
}
