<?php
declare(strict_types=1);

/**
 * Smart Account Creation — Oregon Tires
 * Auto-creates member accounts for guest customers.
 */

/**
 * Find or create a member account for a customer email.
 * If the member already exists, returns their ID.
 * If not, creates an unverified account and sends a claim email.
 *
 * @return int|null Member ID or null on failure
 */
function findOrCreateMemberAccount(string $email, string $firstName, string $lastName, PDO $pdo, string $language = 'english'): ?int
{
    $email = strtolower(trim($email));
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    try {
        // Check if member already exists
        $stmt = $pdo->prepare('SELECT id FROM members WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            return (int) $existing['id'];
        }

        // Generate unique username
        $username = generateUniqueUsername($firstName, $email, $pdo);

        // Create unverified account (no password)
        $displayName = trim($firstName . ' ' . $lastName);
        $stmt = $pdo->prepare(
            'INSERT INTO members (email, username, display_name, status, registered_site_key, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$email, $username, $displayName, 'unverified', 'oregon_tires']);

        $memberId = (int) $pdo->lastInsertId();

        // Generate email verification token (7-day TTL — longer since user didn't request account)
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $pdo->prepare(
            'INSERT INTO email_verifications (member_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$memberId, $tokenHash, $expiresAt]);

        // Send claim email (fire-and-forget — don't break if email fails)
        try {
            require_once __DIR__ . '/mail.php';
            sendAccountClaimEmail($email, $firstName, $token, $language);
        } catch (\Throwable $mailErr) {
            error_log("Smart account: claim email failed for {$email}: " . $mailErr->getMessage());
        }

        // Record activity if MemberProfile is available
        if (class_exists('MemberProfile')) {
            try {
                MemberProfile::logActivity($memberId, 'smart_account_created', null, null, [
                    'source' => 'oregon_tires',
                ]);
            } catch (\Throwable $actErr) {
                error_log("Smart account: activity log failed for member #{$memberId}: " . $actErr->getMessage());
            }
        }

        return $memberId;

    } catch (\Throwable $e) {
        error_log("Smart account creation failed for {$email}: " . $e->getMessage());
        return null;
    }
}

/**
 * Generate a unique username from first name or email prefix.
 */
function generateUniqueUsername(string $firstName, string $email, PDO $pdo): string
{
    $base = preg_replace('/[^a-zA-Z0-9_]/', '', $firstName);
    if (empty($base) || strlen($base) < 3) {
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
    }
    if (empty($base) || strlen($base) < 3) {
        $base = 'customer';
    }
    $base = strtolower(substr($base, 0, 30));

    $username = $base;
    $counter = 0;
    while ($counter < 100) {
        $stmt = $pdo->prepare('SELECT id FROM members WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $counter++;
        $username = $base . $counter;
    }

    // Fallback: append random suffix
    return $base . '_' . substr(bin2hex(random_bytes(4)), 0, 6);
}
