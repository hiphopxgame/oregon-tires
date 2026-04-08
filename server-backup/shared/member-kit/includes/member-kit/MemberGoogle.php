<?php
declare(strict_types=1);

/**
 * MemberGoogle — Google OAuth2 connect/disconnect for member accounts
 *
 * Provides shared Google OAuth mechanics for all 1vsM network sites.
 * Sites keep their existing login callbacks but gain shared
 * connect/disconnect/linking support through this class.
 *
 * Schema differences handled internally:
 * - Independent mode (Oregon Tires, 1oh6): google_id column on members table
 * - Network/HW mode (1vsm.com): user_connections table with provider='google'
 */
class MemberGoogle
{
    private const GOOGLE_AUTH_URL    = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL   = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';

    /**
     * Check if Google OAuth is configured for this site.
     */
    public static function isEnabled(): bool
    {
        return !empty($_ENV['GOOGLE_CLIENT_ID']);
    }

    /**
     * Build the Google OAuth authorize URL and store state + PKCE verifier in session.
     *
     * @param string|null $returnUrl URL to redirect to after flow completes
     * @param string $mode 'login' or 'connect'
     * @return string Full authorize URL to redirect the user to
     */
    public static function getAuthorizeUrl(?string $returnUrl = null, string $mode = 'login'): string
    {
        if (!self::isEnabled()) {
            throw new \RuntimeException('Google OAuth is not configured');
        }

        // Generate CSRF state
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_mode'] = $mode;

        // PKCE: generate code_verifier and code_challenge (S256)
        $verifier = bin2hex(random_bytes(32));
        $_SESSION['google_code_verifier'] = $verifier;
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        if ($returnUrl) {
            $_SESSION['google_return_url'] = $returnUrl;
        }

        $redirectUri = $_ENV['GOOGLE_MEMBER_REDIRECT_URI']
            ?? $_ENV['GOOGLE_REDIRECT_URI']
            ?? ($_ENV['APP_URL'] ?? '') . '/api/auth/google-callback.php';
        $_SESSION['google_redirect_uri'] = $redirectUri;

        $params = [
            'client_id'             => $_ENV['GOOGLE_CLIENT_ID'],
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'openid email profile',
            'state'                 => $state,
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
            'access_type'           => 'online',
            'prompt'                => 'select_account',
        ];

        return self::GOOGLE_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for Google profile data.
     *
     * Verifies state, exchanges code for token, fetches userinfo.
     *
     * @return array{profile: array, mode: string, return_url: ?string}
     * @throws \RuntimeException on any failure
     */
    public static function exchangeCodeForProfile(string $code, string $state): array
    {
        // Verify state
        $sessionState = $_SESSION['google_oauth_state'] ?? '';
        if (empty($sessionState) || !hash_equals($sessionState, $state)) {
            throw new \RuntimeException('Invalid OAuth state');
        }

        $verifier    = $_SESSION['google_code_verifier'] ?? '';
        $mode        = $_SESSION['google_oauth_mode'] ?? 'login';
        $returnUrl   = $_SESSION['google_return_url'] ?? null;
        $redirectUri = $_SESSION['google_redirect_uri'] ?? '';

        // Clean up session OAuth data
        unset(
            $_SESSION['google_oauth_state'],
            $_SESSION['google_code_verifier'],
            $_SESSION['google_oauth_mode'],
            $_SESSION['google_return_url'],
            $_SESSION['google_redirect_uri']
        );

        // Exchange code for token
        $tokenData = self::exchangeCode($code, $verifier, $redirectUri);
        if (empty($tokenData['access_token'])) {
            $error = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Unknown error';
            throw new \RuntimeException('Token exchange failed: ' . $error);
        }

        // Fetch userinfo
        $profile = self::fetchUserInfo($tokenData['access_token']);
        if (empty($profile['sub']) || empty($profile['email'])) {
            throw new \RuntimeException('Failed to fetch valid user info from Google');
        }

        return [
            'profile'    => $profile,
            'mode'       => $mode,
            'return_url' => $returnUrl,
        ];
    }

    /**
     * Check if a member has a linked Google account.
     */
    public static function isLinked(int $memberId): bool
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare(
                'SELECT 1 FROM user_connections WHERE user_id = ? AND provider = ? LIMIT 1'
            );
            $stmt->execute([$memberId, 'google']);
            return $stmt->fetch() !== false;
        }

        // Independent mode: check google_id on members table
        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT google_id FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            return $row !== false && !empty($row['google_id']);
        } catch (\Throwable $_) {
            return false;
        }
    }

    /**
     * Get linked Google account info for a member.
     *
     * @return array{google_id: string, google_email: ?string}|null
     */
    public static function getLinkedInfo(int $memberId): ?array
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare(
                'SELECT provider_id, provider_data FROM user_connections
                 WHERE user_id = ? AND provider = ? LIMIT 1'
            );
            $stmt->execute([$memberId, 'google']);
            $row = $stmt->fetch();
            if (!$row) return null;

            $data = json_decode($row['provider_data'] ?? '{}', true);
            return [
                'google_id'    => $row['provider_id'],
                'google_email' => $data['email'] ?? null,
            ];
        }

        // Independent mode
        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT google_id, google_email FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['google_id'])) return null;

            return [
                'google_id'    => $row['google_id'],
                'google_email' => $row['google_email'] ?? null,
            ];
        } catch (\Throwable $_) {
            // google_email column may not exist yet
            try {
                $stmt = $pdo->prepare("SELECT google_id FROM {$table} WHERE id = ? LIMIT 1");
                $stmt->execute([$memberId]);
                $row = $stmt->fetch();
                if (!$row || empty($row['google_id'])) return null;
                return ['google_id' => $row['google_id'], 'google_email' => null];
            } catch (\Throwable $_) {
                return null;
            }
        }
    }

    /**
     * Link a Google account to a member.
     */
    public static function linkAccount(int $memberId, string $googleId, ?string $googleEmail = null, ?string $avatarUrl = null): bool
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            // Check if this Google ID is already linked to another user
            $stmt = $pdo->prepare(
                'SELECT user_id FROM user_connections WHERE provider = ? AND provider_id = ? LIMIT 1'
            );
            $stmt->execute(['google', $googleId]);
            $existing = $stmt->fetch();
            if ($existing && (int) $existing['user_id'] !== $memberId) {
                throw new \RuntimeException('This Google account is already linked to another user');
            }

            if ($existing) {
                // Still try to populate avatar if missing
                self::maybeSetAvatar($memberId, $avatarUrl);
                return true; // Already linked to this user
            }

            $providerData = ['email' => $googleEmail];
            if ($avatarUrl) {
                $providerData['avatar_url'] = $avatarUrl;
            }

            $pdo->prepare(
                'INSERT INTO user_connections (user_id, provider, provider_id, provider_data, is_primary, linked_at)
                 VALUES (?, ?, ?, ?, 0, NOW())'
            )->execute([$memberId, 'google', $googleId, json_encode($providerData)]);

            if (class_exists('ConnectionLedger')) {
                ConnectionLedger::logConnected($pdo, $memberId, 'google', $googleId, 'api');
            }

            self::maybeSetAvatar($memberId, $avatarUrl);
            MemberProfile::logActivity($memberId, 'google_linked');
            return true;
        }

        // Independent mode: set google_id on members table
        $table = MemberAuth::getMembersTable();

        // Check if this Google ID is already linked to another member
        try {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE google_id = ? AND id != ? LIMIT 1");
            $stmt->execute([$googleId, $memberId]);
            if ($stmt->fetch()) {
                throw new \RuntimeException('This Google account is already linked to another user');
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $_) {
            // google_id column may not exist — proceed anyway
        }

        // Update with google_id and optionally google_email
        try {
            $stmt = $pdo->prepare(
                "UPDATE {$table} SET google_id = ?, google_email = ?, updated_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$googleId, $googleEmail, $memberId]);
        } catch (\Throwable $_) {
            // google_email column may not exist — try without it
            $stmt = $pdo->prepare(
                "UPDATE {$table} SET google_id = ?, updated_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$googleId, $memberId]);
        }

        self::maybeSetAvatar($memberId, $avatarUrl);
        MemberProfile::logActivity($memberId, 'google_linked');
        return true;
    }

    /**
     * Set avatar_url on member profile if currently empty.
     * Non-critical — never breaks the link flow.
     */
    private static function maybeSetAvatar(int $memberId, ?string $avatarUrl): void
    {
        if (empty($avatarUrl)) {
            return;
        }

        try {
            $profile = MemberProfile::get($memberId);
            if ($profile && empty($profile['avatar_url'])) {
                MemberProfile::update($memberId, ['avatar_url' => $avatarUrl]);
            }
        } catch (\Throwable $_) {
            // Non-critical — avatar population is best-effort
        }
    }

    /**
     * Unlink a Google account from a member.
     * Requires the member to have a password set (same guard as SSO unlink).
     */
    public static function unlinkAccount(int $memberId): bool
    {
        $pdo = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        // Guard: must have a password set
        $stmt = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if ($member && empty($member['password_hash'])) {
            throw new \RuntimeException('Set a password before disconnecting Google (to keep account access)');
        }

        if (self::usesConnectionsTable()) {
            // Capture provider_id before deletion for ledger
            $linkedInfo = self::getLinkedInfo($memberId);
            $googleId = $linkedInfo['google_id'] ?? null;

            $pdo->prepare(
                'DELETE FROM user_connections WHERE user_id = ? AND provider = ?'
            )->execute([$memberId, 'google']);

            if (class_exists('ConnectionLedger')) {
                ConnectionLedger::logDisconnected($pdo, $memberId, 'google', $googleId, 'api');
            }
        } else {
            try {
                $pdo->prepare(
                    "UPDATE {$table} SET google_id = NULL, google_email = NULL, updated_at = NOW() WHERE id = ?"
                )->execute([$memberId]);
            } catch (\Throwable $_) {
                // google_email column may not exist
                $pdo->prepare(
                    "UPDATE {$table} SET google_id = NULL, updated_at = NOW() WHERE id = ?"
                )->execute([$memberId]);
            }
        }

        MemberProfile::logActivity($memberId, 'google_unlinked');
        return true;
    }

    // ── Private Helpers ─────────────────────────────────────────────────

    /**
     * Whether this site uses the user_connections table (network/HW mode)
     * vs direct google_id column on members table (independent mode).
     */
    private static function usesConnectionsTable(): bool
    {
        return MemberAuth::isSharedUsersMode();
    }

    /**
     * Exchange authorization code for access token.
     */
    private static function exchangeCode(string $code, string $verifier, string $redirectUri): array
    {
        $postData = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'code_verifier' => $verifier,
        ];

        try {
            $ch = curl_init(self::GOOGLE_TOKEN_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($postData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
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
                throw new \RuntimeException('Invalid JSON response from Google token endpoint');
            }

            return $data;
        } catch (\Throwable $e) {
            error_log('MemberGoogle::exchangeCode failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to exchange authorization code with Google');
        }
    }

    /**
     * Fetch user info from Google userinfo endpoint.
     */
    private static function fetchUserInfo(string $accessToken): array
    {
        try {
            $ch = curl_init(self::GOOGLE_USERINFO_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
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
                throw new \RuntimeException('Google UserInfo endpoint returned HTTP ' . $httpCode);
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                throw new \RuntimeException('Invalid JSON response from Google userinfo endpoint');
            }

            return $data;
        } catch (\Throwable $e) {
            error_log('MemberGoogle::fetchUserInfo failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to fetch user profile from Google');
        }
    }
}
