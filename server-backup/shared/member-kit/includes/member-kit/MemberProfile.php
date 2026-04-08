<?php
declare(strict_types=1);

/**
 * MemberProfile — Profile management for HW Member Kit
 *
 * Handles profile retrieval/updates, avatar uploads, email change requests,
 * per-site preferences, and activity logging.
 *
 * Depends on MemberAuth being initialized first:
 *   MemberAuth::getPdo(), MemberAuth::isHwMode(),
 *   MemberAuth::getMembersTable(), MemberAuth::getMemberIdColumn(),
 *   MemberAuth::prefixedTable()
 *
 * Table resolution:
 *   Independent mode — member_preferences, member_activity (column: member_id)
 *   HW mode          — {prefix}_member_preferences, {prefix}_member_activity (column: user_id)
 */
class MemberProfile
{
    // ── Profile Retrieval ────────────────────────────────────────────────

    /**
     * Get a member's profile by ID.
     *
     * Returns selected safe columns (never the password hash).
     */
    public static function get(int $memberId): ?array
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        if (MemberAuth::isHwMode()) {
            $stmt = $pdo->prepare(
                "SELECT id, email, username, display_name, bio, avatar_url,
                        created_at, updated_at
                 FROM {$table}
                 WHERE id = :id
                 LIMIT 1"
            );
        } else {
            $stmt = $pdo->prepare(
                "SELECT id, email, username, display_name, bio, avatar_url,
                        status, email_verified_at, last_login_at,
                        created_at, updated_at
                 FROM {$table}
                 WHERE id = :id
                 LIMIT 1"
            );
        }

        $stmt->execute([':id' => $memberId]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $member !== false ? $member : null;
    }

    // ── Profile Update ───────────────────────────────────────────────────

    /**
     * Update profile fields: display_name, username, bio, avatar_url.
     *
     * Email changes must go through requestEmailChange().
     * Username is validated for format and uniqueness.
     *
     * @param int   $memberId
     * @param array $data Allowed keys: display_name, username, bio, avatar_url
     * @return bool True on success
     * @throws \RuntimeException on validation failure
     */
    public static function update(int $memberId, array $data): bool
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        $allowedFields = ['display_name', 'username', 'bio', 'avatar_url'];
        $sets   = [];
        $params = [':id' => $memberId];

        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            // ── Username validation ──
            if ($field === 'username') {
                if ($value !== null && $value !== '') {
                    $value = trim((string) $value);

                    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $value)) {
                        throw new \RuntimeException(
                            'Username must be 3-50 characters: letters, numbers, underscore only'
                        );
                    }

                    // Uniqueness check (exclude current member)
                    $uniqueStmt = $pdo->prepare(
                        "SELECT id FROM {$table} WHERE username = :uname AND id != :uid LIMIT 1"
                    );
                    $uniqueStmt->execute([':uname' => $value, ':uid' => $memberId]);
                    if ($uniqueStmt->fetch()) {
                        throw new \RuntimeException('Username already taken');
                    }
                } else {
                    $value = null;
                }
            }

            // ── Display name sanitization ──
            if ($field === 'display_name' && $value !== null) {
                $value = trim((string) $value);
                if (mb_strlen($value) > 100) {
                    $value = mb_substr($value, 0, 100);
                }
                if ($value === '') {
                    $value = null;
                }
            }

            // ── Bio sanitization ──
            if ($field === 'bio' && $value !== null) {
                $value = trim((string) $value);
                if (mb_strlen($value) > 1000) {
                    $value = mb_substr($value, 0, 1000);
                }
                if ($value === '') {
                    $value = null;
                }
            }

            // ── Avatar URL sanitization ──
            if ($field === 'avatar_url' && $value !== null) {
                $value = trim((string) $value);
                if ($value === '') {
                    $value = null;
                }
            }

            $placeholder = ':' . $field;
            $sets[]      = "{$field} = {$placeholder}";
            $params[$placeholder] = $value;
        }

        if (empty($sets)) {
            return true; // nothing to update
        }

        $setClause = implode(', ', $sets);
        $sql = "UPDATE {$table} SET {$setClause}, updated_at = NOW() WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            self::logActivity($memberId, 'profile_updated', null, null, [
                'fields' => array_keys(array_intersect_key($data, array_flip($allowedFields))),
            ]);
        }

        return $result;
    }

    // ── Avatar Upload ────────────────────────────────────────────────────

    /**
     * Upload an avatar image for a member.
     *
     * Constraints:
     *   - Max 2 MB file size
     *   - Allowed MIME types: image/jpeg, image/png, image/webp
     *   - Resized to 256x256 using GD library
     *   - Stored at /uploads/avatars/{memberId}.{ext}
     *
     * @param int   $memberId
     * @param array $file     The $_FILES['avatar'] array
     * @return string Web-accessible path (e.g. /uploads/avatars/123.jpg)
     * @throws \RuntimeException on validation or processing failure
     */
    public static function uploadAvatar(int $memberId, array $file): string
    {
        // ── Check GD library ──
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Image processing is not available on this server (GD library missing)');
        }

        // ── Validate upload ──
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('No valid file uploaded');
        }

        // Check file size (2 MB max)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('File size exceeds 2 MB limit');
        }

        // Validate actual MIME type via finfo (not the user-supplied type)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedMimes[$mimeType])) {
            throw new \RuntimeException(
                'Invalid file type. Allowed: JPG, PNG, WEBP'
            );
        }

        $ext = $allowedMimes[$mimeType];

        // ── Load image with GD ──
        $srcImage = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default      => false,
        };

        if ($srcImage === false) {
            throw new \RuntimeException('Failed to process image');
        }

        // ── Resize to 256x256 (crop-center) ──
        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        $size = 256;

        // Determine crop region (center square)
        $cropSize = min($srcW, $srcH);
        $cropX    = (int) (($srcW - $cropSize) / 2);
        $cropY    = (int) (($srcH - $cropSize) / 2);

        $dstImage = imagecreatetruecolor($size, $size);
        if ($dstImage === false) {
            imagedestroy($srcImage);
            throw new \RuntimeException('Failed to create resized image');
        }

        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefill($dstImage, 0, 0, $transparent);
        }

        imagecopyresampled(
            $dstImage, $srcImage,
            0, 0,           // dst x, y
            $cropX, $cropY, // src x, y
            $size, $size,   // dst w, h
            $cropSize, $cropSize // src w, h
        );

        imagedestroy($srcImage);

        // ── Ensure upload directory exists ──
        $docRoot   = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $uploadDir = $docRoot . '/uploads/avatars';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                imagedestroy($dstImage);
                throw new \RuntimeException('Failed to create upload directory');
            }
        }

        // ── Remove previous avatar files for this member ──
        foreach ($allowedMimes as $oldExt) {
            $oldFile = $uploadDir . '/' . $memberId . '.' . $oldExt;
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        // ── Save resized image ──
        $filename = $memberId . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;

        $saved = match ($mimeType) {
            'image/jpeg' => imagejpeg($dstImage, $filepath, 85),
            'image/png'  => imagepng($dstImage, $filepath, 6),
            'image/webp' => imagewebp($dstImage, $filepath, 85),
            default      => false,
        };

        imagedestroy($dstImage);

        if (!$saved) {
            throw new \RuntimeException('Failed to save avatar image');
        }

        // ── Update member record with avatar URL ──
        $webPath = '/uploads/avatars/' . $filename;

        // Append cache-buster so browsers pick up the new avatar
        $webPathWithBuster = $webPath . '?v=' . time();

        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        $stmt = $pdo->prepare(
            "UPDATE {$table} SET avatar_url = :url, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':url' => $webPath, ':id' => $memberId]);

        self::logActivity($memberId, 'avatar_uploaded');

        return $webPathWithBuster;
    }

    // ── Email Change Request ─────────────────────────────────────────────

    /**
     * Request an email change.
     *
     * Does not update the email immediately — sends a verification link
     * to the new email address. The email_verifications table stores the
     * new_email and a hashed token. Verification is handled by
     * MemberAuth::verifyEmail().
     *
     * @param int    $memberId
     * @param string $newEmail
     * @return bool  True if the verification email was queued
     * @throws \RuntimeException on validation failure
     */
    public static function requestEmailChange(int $memberId, string $newEmail): bool
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::getMembersTable();

        $newEmail = strtolower(trim($newEmail));

        // Validate format
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid email address');
        }

        // Check it's actually different
        $stmt = $pdo->prepare("SELECT email FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $memberId]);
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$current) {
            throw new \RuntimeException('Member not found');
        }

        if ($current['email'] === $newEmail) {
            throw new \RuntimeException('New email is the same as current email');
        }

        // Check uniqueness across all members/users
        $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $newEmail]);
        if ($stmt->fetch()) {
            throw new \RuntimeException('This email address is already in use');
        }

        // Invalidate any existing email-change verifications for this member
        $stmt = $pdo->prepare(
            "UPDATE email_verifications
             SET verified_at = NOW()
             WHERE member_id = :mid AND verified_at IS NULL AND new_email IS NOT NULL"
        );
        $stmt->execute([':mid' => $memberId]);

        // Generate verification token
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiryMinutes = MemberAuth::getConfig('token_expiry_minutes') ?? 30;
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));

        $stmt = $pdo->prepare(
            "INSERT INTO email_verifications (member_id, token_hash, new_email, expires_at, created_at)
             VALUES (:mid, :hash, :new_email, :expires, NOW())"
        );
        $stmt->execute([
            ':mid'       => $memberId,
            ':hash'      => $tokenHash,
            ':new_email' => $newEmail,
            ':expires'   => $expiresAt,
        ]);

        // Send verification email to the new address
        $siteName = MemberAuth::getConfig('site_name') ?? 'Site';
        $siteUrl  = MemberAuth::getConfig('site_url') ?? '';

        MemberMail::sendVerification($newEmail, $token, $siteName, $siteUrl);

        self::logActivity($memberId, 'email_change_requested', null, null, [
            'new_email' => $newEmail,
        ]);

        return true;
    }

    // ── Preferences ──────────────────────────────────────────────────────

    /**
     * Get a single preference value for a member.
     *
     * @return string|null The preference value, or null if not set
     */
    public static function getPreference(int $memberId, string $key): ?string
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::prefixedTable('member_preferences');
        $idCol = MemberAuth::getMemberIdColumn();

        $stmt = $pdo->prepare(
            "SELECT pref_value FROM {$table}
             WHERE {$idCol} = :mid AND pref_key = :key
             LIMIT 1"
        );
        $stmt->execute([':mid' => $memberId, ':key' => $key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row !== false ? $row['pref_value'] : null;
    }

    /**
     * Set a single preference value for a member.
     *
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert behavior.
     */
    public static function setPreference(int $memberId, string $key, string $value): bool
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::prefixedTable('member_preferences');
        $idCol = MemberAuth::getMemberIdColumn();

        $stmt = $pdo->prepare(
            "INSERT INTO {$table} ({$idCol}, pref_key, pref_value, updated_at)
             VALUES (:mid, :key, :val, NOW())
             ON DUPLICATE KEY UPDATE pref_value = VALUES(pref_value), updated_at = NOW()"
        );

        return $stmt->execute([
            ':mid' => $memberId,
            ':key' => $key,
            ':val' => $value,
        ]);
    }

    /**
     * Get all preferences for a member as a key=>value array.
     */
    public static function getAllPreferences(int $memberId): array
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::prefixedTable('member_preferences');
        $idCol = MemberAuth::getMemberIdColumn();

        $stmt = $pdo->prepare(
            "SELECT pref_key, pref_value FROM {$table}
             WHERE {$idCol} = :mid
             ORDER BY pref_key"
        );
        $stmt->execute([':mid' => $memberId]);

        $prefs = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $prefs[$row['pref_key']] = $row['pref_value'];
        }

        return $prefs;
    }

    // ── Activity Logging ─────────────────────────────────────────────────

    /**
     * Log a member activity event.
     *
     * This method is intentionally fault-tolerant — it catches all throwables
     * so that a logging failure never disrupts application flow.
     *
     * @param int         $memberId
     * @param string      $action      e.g. 'login', 'profile_updated', 'email_verified'
     * @param string|null $entityType  Optional entity type (e.g. 'event', 'order')
     * @param int|null    $entityId    Optional entity ID
     * @param array|null  $details     Optional JSON-serializable details
     */
    public static function logActivity(
        int $memberId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $details = null
    ): void {
        try {
            $pdo   = MemberAuth::getPdo();
            $table = MemberAuth::prefixedTable('member_activity');
            $idCol = MemberAuth::getMemberIdColumn();

            $detailsJson = $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            $stmt = $pdo->prepare(
                "INSERT INTO {$table} ({$idCol}, action, entity_type, entity_id, details, ip_address, created_at)
                 VALUES (:mid, :action, :etype, :eid, :details, :ip, NOW())"
            );
            $stmt->execute([
                ':mid'     => $memberId,
                ':action'  => $action,
                ':etype'   => $entityType,
                ':eid'     => $entityId,
                ':details' => $detailsJson,
                ':ip'      => $ip,
            ]);
        } catch (\Throwable $e) {
            // Never let activity logging break the application.
            // Silently log to error_log so developers can diagnose issues.
            error_log('MemberProfile::logActivity failed: ' . $e->getMessage());
        }
    }

    /**
     * Get activity history for a member with pagination.
     *
     * @param int $memberId
     * @param int $limit  Max rows to return (default 50)
     * @param int $offset Rows to skip (default 0)
     * @return array List of activity records, newest first
     */
    public static function getActivity(int $memberId, int $limit = 50, int $offset = 0): array
    {
        $pdo   = MemberAuth::getPdo();
        $table = MemberAuth::prefixedTable('member_activity');
        $idCol = MemberAuth::getMemberIdColumn();

        // Clamp values
        $limit  = max(1, min($limit, 200));
        $offset = max(0, $offset);

        // Try full schema first, fall back to basic columns if table is outdated
        try {
            $stmt = $pdo->prepare(
                "SELECT id, action, entity_type, entity_id, details, ip_address, created_at
                 FROM {$table}
                 WHERE {$idCol} = :mid
                 ORDER BY created_at DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':mid', $memberId, \PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            // Fallback for tables missing newer columns
            $stmt = $pdo->prepare(
                "SELECT id, action, details, ip_address, created_at
                 FROM {$table}
                 WHERE {$idCol} = :mid
                 ORDER BY created_at DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':mid', $memberId, \PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
        }

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON details for convenience
        foreach ($rows as &$row) {
            if ($row['details'] !== null) {
                $decoded = json_decode($row['details'], true);
                $row['details'] = $decoded !== null ? $decoded : $row['details'];
            }
        }
        unset($row);

        return $rows;
    }
}
