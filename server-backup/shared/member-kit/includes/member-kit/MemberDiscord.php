<?php
declare(strict_types=1);

/**
 * MemberDiscord — Discord OAuth2 connect/disconnect for member accounts
 *
 * Follows the same pattern as MemberGoogle. Supports independent mode
 * (discord_id column on members table) and network mode (user_connections table).
 *
 * Discord OAuth2 docs: https://discord.com/developers/docs/topics/oauth2
 */
class MemberDiscord
{
    private const DISCORD_AUTH_URL     = 'https://discord.com/oauth2/authorize';
    private const DISCORD_TOKEN_URL   = 'https://discord.com/api/oauth2/token';
    private const DISCORD_USERINFO_URL = 'https://discord.com/api/users/@me';

    public static function isEnabled(): bool
    {
        return !empty($_ENV['DISCORD_CLIENT_ID']);
    }

    /**
     * Build the Discord OAuth authorize URL and store state in session.
     */
    public static function getAuthorizeUrl(?string $returnUrl = null, string $mode = 'login'): string
    {
        if (!self::isEnabled()) {
            throw new \RuntimeException('Discord OAuth is not configured');
        }

        $state = bin2hex(random_bytes(32));
        $_SESSION['discord_oauth_state'] = $state;
        $_SESSION['discord_oauth_mode'] = $mode;

        if ($returnUrl) {
            $_SESSION['discord_return_url'] = $returnUrl;
        }

        $redirectUri = $_ENV['DISCORD_MEMBER_REDIRECT_URI']
            ?? $_ENV['DISCORD_REDIRECT_URI']
            ?? ($_ENV['APP_URL'] ?? '') . '/api/member/discord-callback.php';
        $_SESSION['discord_redirect_uri'] = $redirectUri;

        $params = [
            'client_id'     => $_ENV['DISCORD_CLIENT_ID'],
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'identify email',
            'state'         => $state,
            'prompt'        => 'consent',
        ];

        return self::DISCORD_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for Discord profile data.
     */
    public static function exchangeCodeForProfile(string $code, string $state): array
    {
        $sessionState = $_SESSION['discord_oauth_state'] ?? '';
        if (empty($sessionState) || !hash_equals($sessionState, $state)) {
            throw new \RuntimeException('Invalid OAuth state');
        }

        $mode        = $_SESSION['discord_oauth_mode'] ?? 'login';
        $returnUrl   = $_SESSION['discord_return_url'] ?? null;
        $redirectUri = $_SESSION['discord_redirect_uri'] ?? '';

        unset(
            $_SESSION['discord_oauth_state'],
            $_SESSION['discord_oauth_mode'],
            $_SESSION['discord_return_url'],
            $_SESSION['discord_redirect_uri']
        );

        $tokenData = self::exchangeCode($code, $redirectUri);
        if (empty($tokenData['access_token'])) {
            $error = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Unknown error';
            throw new \RuntimeException('Token exchange failed: ' . $error);
        }

        $profile = self::fetchUserInfo($tokenData['access_token']);
        if (empty($profile['id'])) {
            throw new \RuntimeException('Failed to fetch valid user info from Discord');
        }

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
            $stmt->execute([$memberId, 'discord']);
            return $stmt->fetch() !== false;
        }

        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT discord_id FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            return $row !== false && !empty($row['discord_id']);
        } catch (\Throwable $_) {
            return false;
        }
    }

    public static function getLinkedInfo(int $memberId): ?array
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare('SELECT provider_id, provider_data FROM user_connections WHERE user_id = ? AND provider = ? LIMIT 1');
            $stmt->execute([$memberId, 'discord']);
            $row = $stmt->fetch();
            if (!$row) return null;
            $data = json_decode($row['provider_data'] ?? '{}', true);
            return ['discord_id' => $row['provider_id'], 'discord_username' => $data['username'] ?? null];
        }

        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT discord_id, discord_username FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['discord_id'])) return null;
            return ['discord_id' => $row['discord_id'], 'discord_username' => $row['discord_username'] ?? null];
        } catch (\Throwable $_) {
            return null;
        }
    }

    public static function linkAccount(int $memberId, string $discordId, ?string $username = null, ?string $avatarHash = null): bool
    {
        $pdo = MemberAuth::getPdo();

        if (self::usesConnectionsTable()) {
            $stmt = $pdo->prepare('SELECT user_id FROM user_connections WHERE provider = ? AND provider_id = ? LIMIT 1');
            $stmt->execute(['discord', $discordId]);
            $existing = $stmt->fetch();
            if ($existing && (int)$existing['user_id'] !== $memberId) {
                throw new \RuntimeException('This Discord account is already linked to another user');
            }
            if ($existing) return true;

            $providerData = ['username' => $username];
            if ($avatarHash) {
                $providerData['avatar_url'] = "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png";
            }
            $pdo->prepare('INSERT INTO user_connections (user_id, provider, provider_id, provider_data, is_primary, linked_at) VALUES (?, ?, ?, ?, 0, NOW())')
                ->execute([$memberId, 'discord', $discordId, json_encode($providerData)]);

            MemberProfile::logActivity($memberId, 'discord_linked');
            return true;
        }

        $table = MemberAuth::getMembersTable();
        try {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE discord_id = ? AND id != ? LIMIT 1");
            $stmt->execute([$discordId, $memberId]);
            if ($stmt->fetch()) {
                throw new \RuntimeException('This Discord account is already linked to another user');
            }
        } catch (\RuntimeException $e) { throw $e; } catch (\Throwable $_) {}

        try {
            $pdo->prepare("UPDATE {$table} SET discord_id = ?, discord_username = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$discordId, $username, $memberId]);
        } catch (\Throwable $_) {
            $pdo->prepare("UPDATE {$table} SET discord_id = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$discordId, $memberId]);
        }

        // Set avatar if empty
        if ($avatarHash) {
            $avatarUrl = "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png";
            try {
                $profile = MemberProfile::get($memberId);
                if ($profile && empty($profile['avatar_url'])) {
                    MemberProfile::update($memberId, ['avatar_url' => $avatarUrl]);
                }
            } catch (\Throwable $_) {}
        }

        MemberProfile::logActivity($memberId, 'discord_linked');
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
            throw new \RuntimeException('Set a password before disconnecting Discord');
        }

        if (self::usesConnectionsTable()) {
            $pdo->prepare('DELETE FROM user_connections WHERE user_id = ? AND provider = ?')
                ->execute([$memberId, 'discord']);
        } else {
            try {
                $pdo->prepare("UPDATE {$table} SET discord_id = NULL, discord_username = NULL, updated_at = NOW() WHERE id = ?")
                    ->execute([$memberId]);
            } catch (\Throwable $_) {
                $pdo->prepare("UPDATE {$table} SET discord_id = NULL, updated_at = NOW() WHERE id = ?")
                    ->execute([$memberId]);
            }
        }

        MemberProfile::logActivity($memberId, 'discord_unlinked');
        return true;
    }

    // ── Private Helpers ─────────────────────────────────────────

    private static function usesConnectionsTable(): bool
    {
        return MemberAuth::isSharedUsersMode();
    }

    private static function exchangeCode(string $code, string $redirectUri): array
    {
        $postData = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $_ENV['DISCORD_CLIENT_ID'],
            'client_secret' => $_ENV['DISCORD_CLIENT_SECRET'],
        ];

        try {
            $ch = curl_init(self::DISCORD_TOKEN_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($postData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) throw new \RuntimeException('cURL error: ' . $error);
            $data = json_decode($response, true);
            if (!is_array($data)) throw new \RuntimeException('Invalid JSON from Discord token endpoint');
            return $data;
        } catch (\Throwable $e) {
            error_log('MemberDiscord::exchangeCode failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to exchange code with Discord');
        }
    }

    private static function fetchUserInfo(string $accessToken): array
    {
        try {
            $ch = curl_init(self::DISCORD_USERINFO_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken, 'Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) throw new \RuntimeException('cURL error: ' . $error);
            if ($httpCode !== 200) throw new \RuntimeException('Discord API returned HTTP ' . $httpCode);
            $data = json_decode($response, true);
            if (!is_array($data)) throw new \RuntimeException('Invalid JSON from Discord user endpoint');
            return $data;
        } catch (\Throwable $e) {
            error_log('MemberDiscord::fetchUserInfo failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to fetch profile from Discord');
        }
    }
}
