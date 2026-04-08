<?php
declare(strict_types=1);

/**
 * ConnectionLedger — Immutable audit trail for user_connections changes.
 *
 * All methods are fire-and-forget: they catch \Throwable and log to error_log().
 * A ledger failure must NEVER block the primary action (login, disconnect, etc.).
 *
 * Table: connection_ledger (created by migration 230)
 */
class ConnectionLedger
{
    /**
     * Core: INSERT a single ledger entry.
     *
     * @param PDO   $pdo   Database connection
     * @param array $entry Associative array with keys:
     *   - user_id        (int, required)
     *   - provider        (string, required)
     *   - event           (string, required)
     *   - connection_id   (int|null)
     *   - provider_id     (string|null)
     *   - trigger_source  (string, default 'api')
     *   - actor_user_id   (int|null)
     *   - ip_address      (string|null, auto-detected if omitted)
     *   - user_agent      (string|null, auto-detected if omitted)
     *   - metadata        (array|null)
     */
    public static function log(PDO $pdo, array $entry): void
    {
        try {
            $userId = (int) ($entry['user_id'] ?? 0);
            $provider = $entry['provider'] ?? '';
            $event = $entry['event'] ?? '';

            if ($userId <= 0 || $provider === '' || $event === '') {
                error_log('[ConnectionLedger] Invalid entry: missing user_id, provider, or event');
                return;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO connection_ledger
                    (user_id, connection_id, provider, provider_id, event, trigger_source,
                     actor_user_id, ip_address, user_agent, metadata)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $userId,
                $entry['connection_id'] ?? null,
                $provider,
                $entry['provider_id'] ?? null,
                $event,
                $entry['trigger_source'] ?? 'api',
                $entry['actor_user_id'] ?? null,
                $entry['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
                $entry['user_agent'] ?? (isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null),
                isset($entry['metadata']) ? json_encode($entry['metadata']) : null,
            ]);
        } catch (\Throwable $e) {
            error_log('[ConnectionLedger] Failed to log: ' . $e->getMessage());
        }
    }

    /**
     * Convenience: log a 'connected' event.
     */
    public static function logConnected(
        PDO $pdo,
        int $userId,
        string $provider,
        ?string $providerId,
        string $triggerSource = 'api',
        ?int $actorUserId = null,
        ?array $metadata = null
    ): void {
        self::log($pdo, [
            'user_id'        => $userId,
            'provider'       => $provider,
            'provider_id'    => $providerId,
            'event'          => 'connected',
            'trigger_source' => $triggerSource,
            'actor_user_id'  => $actorUserId,
            'metadata'       => $metadata,
        ]);
    }

    /**
     * Convenience: log a 'disconnected' event.
     */
    public static function logDisconnected(
        PDO $pdo,
        int $userId,
        string $provider,
        ?string $providerId,
        string $triggerSource = 'api',
        ?int $actorUserId = null,
        ?array $metadata = null
    ): void {
        self::log($pdo, [
            'user_id'        => $userId,
            'provider'       => $provider,
            'provider_id'    => $providerId,
            'event'          => 'disconnected',
            'trigger_source' => $triggerSource,
            'actor_user_id'  => $actorUserId,
            'metadata'       => $metadata,
        ]);
    }

    /**
     * Log a 'transferred' event — creates 2 entries with shared transfer_ref.
     *
     * Entry 1: disconnected from fromUserId
     * Entry 2: connected+transferred to toUserId
     */
    public static function logTransferred(
        PDO $pdo,
        int $fromUserId,
        int $toUserId,
        string $provider,
        ?string $providerId,
        ?int $actorUserId = null,
        ?array $metadata = null
    ): void {
        $transferRef = bin2hex(random_bytes(8));
        $meta = array_merge($metadata ?? [], ['transfer_ref' => $transferRef]);

        self::log($pdo, [
            'user_id'        => $fromUserId,
            'provider'       => $provider,
            'provider_id'    => $providerId,
            'event'          => 'disconnected',
            'trigger_source' => 'admin',
            'actor_user_id'  => $actorUserId,
            'metadata'       => array_merge($meta, ['transferred_to' => $toUserId]),
        ]);

        self::log($pdo, [
            'user_id'        => $toUserId,
            'provider'       => $provider,
            'provider_id'    => $providerId,
            'event'          => 'transferred',
            'trigger_source' => 'admin',
            'actor_user_id'  => $actorUserId,
            'metadata'       => array_merge($meta, ['transferred_from' => $fromUserId]),
        ]);
    }

    /**
     * Get ledger entries for a user, newest first.
     *
     * @return array Array of ledger rows
     */
    public static function getForUser(PDO $pdo, int $userId, int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM connection_ledger
                 WHERE user_id = ?
                 ORDER BY created_at DESC
                 LIMIT ? OFFSET ?"
            );
            $stmt->execute([$userId, $limit, $offset]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                if (isset($row['metadata'])) {
                    $row['metadata'] = json_decode($row['metadata'], true);
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            error_log('[ConnectionLedger] getForUser failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count ledger entries for a user (for pagination).
     */
    public static function countForUser(PDO $pdo, int $userId): int
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM connection_ledger WHERE user_id = ?");
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[ConnectionLedger] countForUser failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get ledger entries for a specific provider identity.
     * Answers: "Who has ever had this Google/Discord/etc. ID?"
     *
     * @return array Array of ledger rows
     */
    public static function getForProvider(PDO $pdo, string $provider, string $providerId, int $limit = 50): array
    {
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM connection_ledger
                 WHERE provider = ? AND provider_id = ?
                 ORDER BY created_at DESC
                 LIMIT ?"
            );
            $stmt->execute([$provider, $providerId, $limit]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                if (isset($row['metadata'])) {
                    $row['metadata'] = json_decode($row['metadata'], true);
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            error_log('[ConnectionLedger] getForProvider failed: ' . $e->getMessage());
            return [];
        }
    }
}
