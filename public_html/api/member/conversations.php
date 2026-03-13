<?php
/**
 * Customer Conversations API
 *
 * GET (no id)     — List conversations for logged-in member
 * GET ?id=N       — Conversation detail with all messages
 * POST (no id)    — New conversation (subject + body)
 * POST ?id=N      — Reply to existing conversation
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/member-translations.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('GET', 'POST');

    // Auth check
    if (!MemberAuth::isMemberLoggedIn()) {
        jsonError('Authentication required.', 401);
    }

    $memberId = (int) $_SESSION['member_id'];
    $member = MemberAuth::getCurrentMember();
    $memberEmail = $member['email'] ?? '';

    // Find customer record
    $custStmt = $pdo->prepare('SELECT id FROM oretir_customers WHERE member_id = ? OR email = ? LIMIT 1');
    $custStmt->execute([$memberId, $memberEmail]);
    $customerId = (int) $custStmt->fetchColumn();

    if (!$customerId) {
        jsonError('No customer record found for your account.', 404);
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    // ─── GET: List or Detail ─────────────────────────────────────────────
    if ($method === 'GET') {
        if ($id > 0) {
            // Conversation detail — verify ownership
            $convStmt = $pdo->prepare(
                'SELECT id, customer_id, subject, status, last_message_at, created_at
                 FROM oretir_conversations
                 WHERE id = ? AND customer_id = ?'
            );
            $convStmt->execute([$id, $customerId]);
            $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

            if (!$conversation) {
                jsonError('Conversation not found.', 404);
            }

            // Fetch messages
            $msgStmt = $pdo->prepare(
                'SELECT id, sender_type, sender_name, body, is_read, created_at
                 FROM oretir_conversation_messages
                 WHERE conversation_id = ?
                 ORDER BY created_at ASC'
            );
            $msgStmt->execute([$id]);
            $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

            // Mark admin/system messages as read
            $pdo->prepare(
                'UPDATE oretir_conversation_messages
                 SET is_read = 1
                 WHERE conversation_id = ? AND sender_type != ? AND is_read = 0'
            )->execute([$id, 'customer']);

            $conversation['messages'] = $messages;
            jsonSuccess($conversation);
        }

        // List conversations
        $stmt = $pdo->prepare(
            'SELECT c.id, c.subject, c.status, c.last_message_at, c.created_at,
                    (SELECT COUNT(*) FROM oretir_conversation_messages m
                     WHERE m.conversation_id = c.id AND m.is_read = 0 AND m.sender_type != "customer") as unread_count
             FROM oretir_conversations c
             WHERE c.customer_id = ?
             ORDER BY c.last_message_at DESC'
        );
        $stmt->execute([$customerId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess(['conversations' => $conversations]);
    }

    // ─── POST: New conversation or Reply ─────────────────────────────────
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            jsonError('Invalid request body.', 400);
        }

        $customerName = '';
        $nameStmt = $pdo->prepare('SELECT first_name, last_name FROM oretir_customers WHERE id = ?');
        $nameStmt->execute([$customerId]);
        $custRow = $nameStmt->fetch(PDO::FETCH_ASSOC);
        if ($custRow) {
            $customerName = trim(($custRow['first_name'] ?? '') . ' ' . ($custRow['last_name'] ?? ''));
        }

        if ($id > 0) {
            // Reply to existing conversation — verify ownership
            $convStmt = $pdo->prepare(
                'SELECT id, customer_id, status FROM oretir_conversations WHERE id = ? AND customer_id = ?'
            );
            $convStmt->execute([$id, $customerId]);
            $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

            if (!$conversation) {
                jsonError('Conversation not found.', 404);
            }

            $body = trim($input['body'] ?? '');
            if ($body === '' || mb_strlen($body) > 5000) {
                jsonError('Message body is required (max 5000 characters).', 400);
            }

            // Insert message
            $pdo->prepare(
                'INSERT INTO oretir_conversation_messages (conversation_id, sender_type, sender_name, body, is_read, created_at)
                 VALUES (?, ?, ?, ?, 0, NOW())'
            )->execute([$id, 'customer', $customerName ?: 'Customer', $body]);

            // Update conversation
            $pdo->prepare(
                'UPDATE oretir_conversations SET last_message_at = NOW(), status = ? WHERE id = ?'
            )->execute(['open', $id]);

            jsonSuccess(['message' => 'Reply sent.']);
        }

        // New conversation
        $subject = trim($input['subject'] ?? '');
        $body = trim($input['body'] ?? '');

        if ($subject === '' || mb_strlen($subject) > 255) {
            jsonError('Subject is required (max 255 characters).', 400);
        }
        if ($body === '' || mb_strlen($body) > 5000) {
            jsonError('Message body is required (max 5000 characters).', 400);
        }

        // Create conversation
        $pdo->prepare(
            'INSERT INTO oretir_conversations (customer_id, subject, status, last_message_at, created_at)
             VALUES (?, ?, ?, NOW(), NOW())'
        )->execute([$customerId, $subject, 'open']);
        $conversationId = (int) $pdo->lastInsertId();

        // Create first message
        $pdo->prepare(
            'INSERT INTO oretir_conversation_messages (conversation_id, sender_type, sender_name, body, is_read, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())'
        )->execute([$conversationId, 'customer', $customerName ?: 'Customer', $body]);

        jsonSuccess(['conversation_id' => $conversationId, 'message' => 'Conversation created.']);
    }

} catch (\Throwable $e) {
    error_log('conversations.php (member) error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
