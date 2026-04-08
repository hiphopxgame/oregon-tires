<?php
declare(strict_types=1);

/**
 * MemberLinkedIn — LinkedIn OAuth2 (OpenID Connect) for member accounts
 *
 * LinkedIn uses OpenID Connect with standard userinfo endpoint.
 * Follows the same pattern as MemberGoogle.
 */
class MemberLinkedIn
{
    private const AUTH_URL     = 'https://www.linkedin.com/oauth/v2/authorization';
    private const TOKEN_URL   = 'https://www.linkedin.com/oauth/v2/accessToken';
    private const USERINFO_URL = 'https://api.linkedin.com/v2/userinfo';

    public static function isEnabled(): bool
    {
        return !empty($_ENV['LINKEDIN_CLIENT_ID']);
    }

    public static function getAuthorizeUrl(?string $returnUrl = null, string $mode = 'login'): string
    {
        if (!self::isEnabled()) {
            throw new \RuntimeException('LinkedIn OAuth is not configured');
        }

        $state = bin2hex(random_bytes(32));
        $_SESSION['linkedin_oauth_state'] = $state;
        $_SESSION['linkedin_oauth_mode'] = $mode;
        if ($returnUrl) $_SESSION['linkedin_return_url'] = $returnUrl;

        $redirectUri = $_ENV['LINKEDIN_REDIRECT_URI']
            ?? ($_ENV['APP_URL'] ?? '') . '/api/member/linkedin-callback.php';
        $_SESSION['linkedin_redirect_uri'] = $redirectUri;

        $params = [
            'response_type' => 'code',
            'client_id'     => $_ENV['LINKEDIN_CLIENT_ID'],
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
            'scope'         => 'openid profile email',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    public static function exchangeCodeForProfile(string $code, string $state): array
    {
        $sessionState = $_SESSION['linkedin_oauth_state'] ?? '';
        if (empty($sessionState) || !hash_equals($sessionState, $state)) {
            throw new \RuntimeException('Invalid OAuth state');
        }

        $mode        = $_SESSION['linkedin_oauth_mode'] ?? 'login';
        $returnUrl   = $_SESSION['linkedin_return_url'] ?? null;
        $redirectUri = $_SESSION['linkedin_redirect_uri'] ?? '';

        unset(
            $_SESSION['linkedin_oauth_state'],
            $_SESSION['linkedin_oauth_mode'],
            $_SESSION['linkedin_return_url'],
            $_SESSION['linkedin_redirect_uri']
        );

        $tokenData = self::exchangeCode($code, $redirectUri);
        if (empty($tokenData['access_token'])) {
            $error = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Unknown error';
            throw new \RuntimeException('Token exchange failed: ' . $error);
        }

        $profile = self::fetchUserInfo($tokenData['access_token']);
        if (empty($profile['sub'])) {
            throw new \RuntimeException('Failed to fetch valid user info from LinkedIn');
        }

        // Store access token in session for profile import
        $_SESSION['linkedin_access_token'] = $tokenData['access_token'];

        return [
            'profile'    => $profile,
            'mode'       => $mode,
            'return_url' => $returnUrl,
        ];
    }

    public static function isLinked(int $memberId): bool
    {
        $pdo = MemberAuth::getPdo();
        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare('SELECT 1 FROM user_connections WHERE user_id = ? AND provider = ? LIMIT 1');
            $stmt->execute([$memberId, 'linkedin']);
            return $stmt->fetch() !== false;
        }
        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT linkedin_id FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            return $row !== false && !empty($row['linkedin_id']);
        } catch (\Throwable $_) { return false; }
    }

    public static function getLinkedInfo(int $memberId): ?array
    {
        $pdo = MemberAuth::getPdo();
        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare('SELECT provider_id, provider_data FROM user_connections WHERE user_id = ? AND provider = ? LIMIT 1');
            $stmt->execute([$memberId, 'linkedin']);
            $row = $stmt->fetch();
            if (!$row) return null;
            $data = json_decode($row['provider_data'] ?? '{}', true);
            return ['linkedin_id' => $row['provider_id'], 'linkedin_name' => $data['name'] ?? null, 'linkedin_email' => $data['email'] ?? null];
        }
        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT linkedin_id, linkedin_name, linkedin_email FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['linkedin_id'])) return null;
            return $row;
        } catch (\Throwable $_) { return null; }
    }

    public static function linkAccount(int $memberId, string $linkedinId, ?string $name = null, ?string $email = null, ?string $avatar = null): bool
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare('SELECT user_id FROM user_connections WHERE provider = ? AND provider_id = ? LIMIT 1');
            $stmt->execute(['linkedin', $linkedinId]);
            $existing = $stmt->fetch();
            if ($existing && (int)$existing['user_id'] !== $memberId) {
                throw new \RuntimeException('This LinkedIn account is already linked to another user');
            }
            if ($existing) return true;

            $pdo->prepare('INSERT INTO user_connections (user_id, provider, provider_id, provider_data, is_primary, linked_at) VALUES (?, ?, ?, ?, 0, NOW())')
                ->execute([$memberId, 'linkedin', $linkedinId, json_encode(['name' => $name, 'email' => $email, 'avatar' => $avatar])]);
            MemberProfile::logActivity($memberId, 'linkedin_linked');
            return true;
        }

        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE linkedin_id = ? AND id != ? LIMIT 1");
            $stmt->execute([$linkedinId, $memberId]);
            if ($stmt->fetch()) throw new \RuntimeException('This LinkedIn account is already linked to another user');
        } catch (\RuntimeException $e) { throw $e; } catch (\Throwable $_) {}

        try {
            $pdo->prepare("UPDATE {$table} SET linkedin_id = ?, linkedin_name = ?, linkedin_email = ?, linkedin_avatar = ?, linkedin_connected_at = NOW(), updated_at = NOW() WHERE id = ?")
                ->execute([$linkedinId, $name, $email, $avatar, $memberId]);
        } catch (\Throwable $_) {
            $pdo->prepare("UPDATE {$table} SET linkedin_id = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$linkedinId, $memberId]);
        }

        if ($avatar) {
            try {
                $profile = MemberProfile::get($memberId);
                if ($profile && empty($profile['avatar_url'])) {
                    MemberProfile::update($memberId, ['avatar_url' => $avatar]);
                }
            } catch (\Throwable $_) {}
        }

        MemberProfile::logActivity($memberId, 'linkedin_linked');
        return true;
    }

    public static function unlinkAccount(int $memberId): bool
    {
        $pdo = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        $stmt = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();
        if ($member && empty($member['password_hash'])) {
            throw new \RuntimeException('Set a password before disconnecting LinkedIn');
        }

        if (self::usesConnectionsTable()) {
            $pdo->prepare('DELETE FROM user_connections WHERE user_id = ? AND provider = ?')->execute([$memberId, 'linkedin']);
        } else {
            try {
                $pdo->prepare("UPDATE {$table} SET linkedin_id = NULL, linkedin_name = NULL, linkedin_email = NULL, linkedin_avatar = NULL, linkedin_connected_at = NULL, updated_at = NOW() WHERE id = ?")->execute([$memberId]);
            } catch (\Throwable $_) {
                $pdo->prepare("UPDATE {$table} SET linkedin_id = NULL, updated_at = NOW() WHERE id = ?")->execute([$memberId]);
            }
        }
        MemberProfile::logActivity($memberId, 'linkedin_unlinked');
        return true;
    }

    // ── Private ─────────────────────────────────────────────

    private static function usesConnectionsTable(): bool
    {
        return MemberAuth::isSharedUsersMode();
    }

    private static function exchangeCode(string $code, string $redirectUri): array
    {
        $postData = http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $_ENV['LINKEDIN_CLIENT_ID'],
            'client_secret' => $_ENV['LINKEDIN_CLIENT_SECRET'],
        ]);

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) throw new \RuntimeException('cURL error: ' . $error);
        $data = json_decode($response, true);
        if (!is_array($data)) throw new \RuntimeException('Invalid JSON from LinkedIn token endpoint');
        return $data;
    }

    private static function fetchUserInfo(string $accessToken): array
    {
        $ch = curl_init(self::USERINFO_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) throw new \RuntimeException('cURL error: ' . $error);
        if ($httpCode !== 200) throw new \RuntimeException('LinkedIn API returned HTTP ' . $httpCode);
        $data = json_decode($response, true);
        if (!is_array($data)) throw new \RuntimeException('Invalid JSON from LinkedIn userinfo');
        return $data;
    }
}
