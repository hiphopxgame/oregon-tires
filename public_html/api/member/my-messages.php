<?php
/**
 * GET /api/member/my-messages.php
 *
 * Returns contact messages for the logged-in customer.
 * For dashboard tab content (HTML rendering).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('GET');

    // Check if member is logged in
    if (!MemberAuth::isMemberLoggedIn()) {
        http_response_code(401);
        echo '<div class="member-alert member-alert--error">Please sign in to view messages.</div>';
        exit;
    }

    $member = MemberAuth::getCurrentMember();
    $memberEmail = $member['email'] ?? null;

    if (!$memberEmail) {
        http_response_code(400);
        echo '<div class="member-alert member-alert--error">Unable to load messages.</div>';
        exit;
    }

    // Get pagination params
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Query messages by email
    $stmt = $pdo->prepare(
        'SELECT id, name, email, subject, message, created_at, status
         FROM oretir_contact_messages
         WHERE email = ?
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$memberEmail, $limit, $offset]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) as total FROM oretir_contact_messages WHERE email = ?'
    );
    $countStmt->execute([$memberEmail]);
    $total = (int) ($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1>Messages</h1>
                <p>Your contact submissions</p>
            </div>

            <?php if (empty($messages)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    No messages yet. Your contact form submissions will appear here.
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($messages as $msg): ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h3 style="margin: 0; font-size: 0.95rem;"><?= htmlspecialchars($msg['subject'] ?? 'No Subject') ?></h3>
                                <span style="font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($msg['created_at'] ?? 'now'))) ?>
                                </span>
                            </div>
                            <p style="margin: 0.5rem 0 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                <?= htmlspecialchars(substr($msg['message'] ?? '', 0, 150)) ?>
                                <?= strlen($msg['message'] ?? '') > 150 ? '...' : '' ?>
                            </p>
                            <?php if (!empty($msg['status'])): ?>
                                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--member-success);">
                                    ✓ <?= htmlspecialchars(ucfirst($msg['status'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total > $limit): ?>
                    <div style="margin-top: 1.5rem; text-align: center; padding: 1rem 0; border-top: 1px solid var(--member-border);">
                        <p style="color: var(--member-text-muted); font-size: 0.875rem; margin: 0;">
                            Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total) ?> of <?= $total ?> messages
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-messages error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">Error loading messages. Please try again.</div>';
}
