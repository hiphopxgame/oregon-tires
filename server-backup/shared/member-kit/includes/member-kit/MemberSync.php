<?php
declare(strict_types=1);

/**
 * MemberSync — Cross-site activity reporting to HW hub
 *
 * Fire-and-forget: failures are logged but never break the site.
 * Uses a site-level API key (SYNC_API_KEY) for authentication.
 */
class MemberSync
{
    private const DEFAULT_ACTIVITY_URL     = 'https://hiphop.world/api/network/activity.php';
    private const DEFAULT_PROFILE_SYNC_URL = 'https://hiphop.world/api/network/profile-sync.php';

    private static function getActivityUrl(): string
    {
        return $_ENV['SYNC_ACTIVITY_URL'] ?? self::DEFAULT_ACTIVITY_URL;
    }

    private static function getProfileSyncUrl(): string
    {
        return $_ENV['SYNC_PROFILE_URL'] ?? self::DEFAULT_PROFILE_SYNC_URL;
    }

    /**
     * Check if cross-site sync is enabled.
     */
    public static function isEnabled(): bool
    {
        return !empty($_ENV['SYNC_API_KEY']);
    }

    /**
     * Report an activity to the HW hub.
     *
     * @param int         $hwUserId   The hiphop.world user ID
     * @param string      $siteDomain The site domain (e.g., "1oh6.events")
     * @param string      $action     Action name (e.g., "login", "profile_updated")
     * @param array|null  $details    Optional details
     * @return bool True on success (2xx response)
     */
    public static function reportActivity(
        int $hwUserId,
        string $siteDomain,
        string $action,
        ?array $details = null
    ): bool {
        if (!self::isEnabled()) {
            return false;
        }

        $payload = [
            'user_id'    => $hwUserId,
            'site_domain' => $siteDomain ?: ($_ENV['SITE_URL'] ?? ''),
            'action'     => $action,
            'details'    => $details,
            'timestamp'  => date('c'),
        ];

        return self::postJson(self::getActivityUrl(), $payload);
    }

    /**
     * Push profile updates to the HW hub.
     *
     * @param int   $hwUserId The hiphop.world user ID
     * @param array $profile  Profile fields: display_name, avatar_url, bio
     * @return bool True on success
     */
    public static function syncProfile(int $hwUserId, array $profile): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $payload = array_merge(['user_id' => $hwUserId], $profile);

        return self::postJson(self::getProfileSyncUrl(), $payload);
    }

    /**
     * POST JSON to an endpoint with Bearer token auth.
     * Uses a short timeout to avoid blocking the main request.
     */
    private static function postJson(string $url, array $payload): bool
    {
        try {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $apiKey = $_ENV['SYNC_API_KEY'];

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $json,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                error_log('MemberSync POST to ' . $url . ' failed: ' . $error);
                return false;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                error_log('MemberSync POST to ' . $url . ' returned HTTP ' . $httpCode . ': ' . $response);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            error_log('MemberSync error: ' . $e->getMessage());
            return false;
        }
    }
}
