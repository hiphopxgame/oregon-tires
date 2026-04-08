<?php
declare(strict_types=1);

/**
 * MemberSSO — OAuth2 consumer for SSO authentication
 *
 * Implements Authorization Code flow with PKCE (S256).
 * Works in both independent and HW modes.
 */
class MemberSSO
{
    private const DEFAULT_AUTHORIZE_URL = 'https://hiphop.world/oauth/authorize';
    private const DEFAULT_TOKEN_URL     = 'https://hiphop.world/oauth/token';
    private const DEFAULT_USERINFO_URL  = 'https://hiphop.world/oauth/userinfo';

    private static function getAuthorizeEndpoint(): string
    {
        return $_ENV['SSO_AUTH_URL'] ?? self::DEFAULT_AUTHORIZE_URL;
    }

    private static function getTokenEndpoint(): string
    {
        return $_ENV['SSO_TOKEN_URL'] ?? self::DEFAULT_TOKEN_URL;
    }

    private static function getUserinfoEndpoint(): string
    {
        return $_ENV['SSO_USERINFO_URL'] ?? self::DEFAULT_USERINFO_URL;
    }

    /**
     * Check if SSO is configured.
     */
    public static function isEnabled(): bool
    {
        return !empty($_ENV['SSO_CLIENT_ID']);
    }

    /**
     * Build the OAuth authorize URL and store state + PKCE verifier in session.
     *
     * @param string|null $returnUrl URL to redirect to after SSO completes
     * @return string Full authorize URL to redirect the user to
     */
    public static function getAuthorizeUrl(?string $returnUrl = null, ?string $redirectUri = null): string
    {
        if (!self::isEnabled()) {
            throw new \RuntimeException('SSO is not configured');
        }

        // Generate state
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;

        // PKCE: generate code_verifier and code_challenge
        $verifier = bin2hex(random_bytes(32));
        $_SESSION['oauth_verifier'] = $verifier;

        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        // Store return URL
        if ($returnUrl) {
            $_SESSION['oauth_return'] = $returnUrl;
        }

        // Store the redirect_uri so handleCallback/exchangeCode uses the same value
        $resolvedRedirectUri = $redirectUri ?? $_ENV['SSO_REDIRECT_URI'];
        $_SESSION['oauth_redirect_uri'] = $resolvedRedirectUri;

        $params = [
            'client_id'             => $_ENV['SSO_CLIENT_ID'],
            'redirect_uri'          => $resolvedRedirectUri,
            'response_type'         => 'code',
            'scope'                 => 'openid profile email',
            'state'                 => $state,
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
        ];

        return self::getAuthorizeEndpoint() . '?' . http_build_query($params);
    }

    /**
     * Handle the OAuth callback after user authorizes via SSO.
     *
     * 1. Verify state
     * 2. Exchange code for access_token (with PKCE verifier)
     * 3. Fetch userinfo
     * 4. Find or create local member
     * 5. Return member array
     *
     * @return array Member/user array
     * @throws \RuntimeException on any failure
     */
    public static function handleCallback(string $code, string $state): array
    {
        // Verify PKCE session data exists (may be lost if session expired between authorize and callback)
        if (empty($_SESSION['oauth_state']) || empty($_SESSION['oauth_verifier'])) {
            throw new \RuntimeException('SSO session expired. Please try logging in again.');
        }

        // Verify state matches
        $sessionState = $_SESSION['oauth_state'];
        if (!hash_equals($sessionState, $state)) {
            throw new \RuntimeException('Invalid OAuth state — possible CSRF. Please try logging in again.');
        }

        $verifier = $_SESSION['oauth_verifier'];
        $returnUrl = $_SESSION['oauth_return'] ?? null;
        $redirectUri = $_SESSION['oauth_redirect_uri'] ?? $_ENV['SSO_REDIRECT_URI'];

        // Clean up session OAuth data
        unset($_SESSION['oauth_state'], $_SESSION['oauth_verifier'], $_SESSION['oauth_return'], $_SESSION['oauth_redirect_uri']);

        // Exchange code for token (must use same redirect_uri as authorize request)
        $tokenData = self::exchangeCode($code, $verifier, $redirectUri);
        if (empty($tokenData['access_token'])) {
            $error = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Unknown error';
            throw new \RuntimeException('Token exchange failed: ' . $error);
        }

        // Fetch userinfo
        $userInfo = self::fetchUserInfo($tokenData['access_token']);
        if (empty($userInfo['sub'])) {
            throw new \RuntimeException('Failed to fetch user info from SSO provider');
        }

        // Find or create local member
        $member = self::findOrCreateMember($userInfo);

        // Store return URL for the caller to use
        if ($returnUrl) {
            $_SESSION['oauth_return'] = $returnUrl;
        }

        return $member;
    }

    /**
     * Remove the hw_user_id link from a member (independent mode only).
     */
    public static function unlinkAccount(int $memberId): bool
    {
        if (MemberAuth::isHwMode()) {
            throw new \RuntimeException('Cannot unlink accounts in HW mode');
        }

        $pdo = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();
        $stmt = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        // Don't allow unlinking if the member has no password (SSO-only account)
        if ($member && empty($member['password_hash'])) {
            throw new \RuntimeException('Cannot unlink SSO account without setting a password first');
        }

        $stmt = $pdo->prepare("UPDATE {$table} SET hw_user_id = NULL, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$memberId]);

        MemberProfile::logActivity($memberId, 'sso_unlinked');

        return true;
    }

    // ── Private Helpers ─────────────────────────────────────────────────

    /**
     * Exchange authorization code for access token via POST to token endpoint.
     */
    private static function exchangeCode(string $code, string $verifier, string $redirectUri): array
    {
        $postData = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $_ENV['SSO_CLIENT_ID'],
            'client_secret' => $_ENV['SSO_CLIENT_SECRET'],
            'code_verifier' => $verifier,
        ];

        try {
            $ch = curl_init(self::getTokenEndpoint());
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($postData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \RuntimeException('cURL error: ' . $error);
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                throw new \RuntimeException('Invalid JSON response from token endpoint');
            }

            return $data;
        } catch (\Throwable $e) {
            error_log('MemberSSO::exchangeCode failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to exchange authorization code');
        }
    }

    /**
     * Fetch user info from SSO userinfo endpoint with Bearer token.
     */
    private static function fetchUserInfo(string $accessToken): array
    {
        try {
            $ch = curl_init(self::getUserinfoEndpoint());
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $accessToken,
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \RuntimeException('cURL error: ' . $error);
            }

            if ($httpCode !== 200) {
                throw new \RuntimeException('UserInfo endpoint returned HTTP ' . $httpCode);
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                throw new \RuntimeException('Invalid JSON response from userinfo endpoint');
            }

            return $data;
        } catch (\Throwable $e) {
            error_log('MemberSSO::fetchUserInfo failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to fetch user profile from SSO provider');
        }
    }

    /**
     * Find or create a local member from SSO userinfo data.
     *
     * UserInfo shape: {sub, preferred_username, name, picture, email, hip_hop_id}
     */
    private static function findOrCreateMember(array $userInfo): array
    {
        $pdo = MemberAuth::getPdo();
        $hwUserId = (int) $userInfo['sub'];
        $email = strtolower(trim($userInfo['email'] ?? ''));
        $username = $userInfo['preferred_username'] ?? null;
        $displayName = $userInfo['name'] ?? null;
        $avatarUrl = $userInfo['picture'] ?? null;

        if (MemberAuth::isHwMode()) {
            // HW mode: user already exists in shared users table
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$hwUserId]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new \RuntimeException('HW user not found for sub: ' . $hwUserId);
            }

            return $user;
        }

        // Independent mode: find by hw_user_id first, then by email
        $table = MemberAuth::getMembersTable();

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE hw_user_id = ? LIMIT 1");
        $stmt->execute([$hwUserId]);
        $member = $stmt->fetch();

        if ($member) {
            // Update profile from SSO data
            $stmt = $pdo->prepare(
                "UPDATE {$table} SET display_name = COALESCE(?, display_name),
                 avatar_url = COALESCE(?, avatar_url),
                 last_login_at = NOW(), updated_at = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([$displayName, $avatarUrl, $member['id']]);

            // Refresh
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$member['id']]);
            return $stmt->fetch();
        }

        // Try by email
        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $member = $stmt->fetch();

            if ($member) {
                // Link the existing account
                $stmt = $pdo->prepare(
                    "UPDATE {$table} SET hw_user_id = ?, display_name = COALESCE(?, display_name),
                     avatar_url = COALESCE(?, avatar_url),
                     last_login_at = NOW(), updated_at = NOW()
                     WHERE id = ?"
                );
                $stmt->execute([$hwUserId, $displayName, $avatarUrl, $member['id']]);

                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
                $stmt->execute([$member['id']]);
                return $stmt->fetch();
            }
        }

        // Create new member (SSO-only, no password)
        $stmt = $pdo->prepare(
            "INSERT INTO {$table} (hw_user_id, email, username, display_name, avatar_url, status, last_login_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW(), NOW())"
        );
        $stmt->execute([$hwUserId, $email, $username, $displayName, $avatarUrl]);

        $newId = (int) $pdo->lastInsertId();

        MemberProfile::logActivity($newId, 'sso_register', null, null, [
            'hw_user_id' => $hwUserId,
        ]);

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$newId]);
        return $stmt->fetch();
    }
}
