<?php
/**
 * Admin Conversations API
 *
 * GET (no id)     — List all conversations with customer info
 * GET ?id=N       — Conversation detail with all messages
 * POST ?id=N      — Admin reply (sender_type='admin')
 * PUT ?id=N       — Update conversation status
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';

try {
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    // ─── GET: List or Detail ─────────────────────────────────────────────
    if ($method === 'GET') {
        if ($id > 0) {
            // Conversation detail
            $convStmt = $db->prepare(
                'SELECT c.*,
                        CONCAT(cust.first_name, " ", cust.last_name) as customer_name,
                        cust.email as customer_email
                 FROM oretir_conversations c
                 JOIN oretir_customers cust ON c.customer_id = cust.id
                 WHERE c.id = ?'
            );
            $convStmt->execute([$id]);
            $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

            if (!$conversation) {
                jsonError('Conversation not found.', 404);
            }

            // Fetch messages
            $msgStmt = $db->prepare(
                'SELECT id, sender_type, sender_name, body, is_read, created_at
                 FROM oretir_conversation_messages
                 WHERE conversation_id = ?
                 ORDER BY created_at ASC'
            );
            $msgStmt->execute([$id]);
            $conversation['messages'] = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

            // Mark customer messages as read
            $db->prepare(
                'UPDATE oretir_conversation_messages
                 SET is_read = 1
                 WHERE conversation_id = ? AND sender_type = ? AND is_read = 0'
            )->execute([$id, 'customer']);

            jsonSuccess($conversation);
        }

        // List all conversations
        $where = [];
        $params = [];

        // Status filter
        if (!empty($_GET['status'])) {
            $validStatuses = ['open', 'waiting_reply', 'resolved', 'closed'];
            if (in_array($_GET['status'], $validStatuses, true)) {
                $where[] = 'c.status = ?';
                $params[] = $_GET['status'];
            }
        }

        // Unread filter
        if (!empty($_GET['unread']) && $_GET['unread'] === '1') {
            $where[] = '(SELECT COUNT(*) FROM oretir_conversation_messages m2 WHERE m2.conversation_id = c.id AND m2.is_read = 0 AND m2.sender_type = "customer") > 0';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare(
            "SELECT c.*,
                    CONCAT(cust.first_name, ' ', cust.last_name) as customer_name,
                    cust.email as customer_email,
                    (SELECT COUNT(*) FROM oretir_conversation_messages m
                     WHERE m.conversation_id = c.id AND m.is_read = 0 AND m.sender_type = 'customer') as unread_count
             FROM oretir_conversations c
             JOIN oretir_customers cust ON c.customer_id = cust.id
             {$whereSQL}
             ORDER BY
                CASE WHEN c.status IN ('open', 'waiting_reply') THEN 0 ELSE 1 END,
                c.last_message_at DESC"
        );
        $stmt->execute($params);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess(['conversations' => $conversations]);
    }

    // Require CSRF for write operations
    verifyCsrf();

    // ─── POST: Admin reply ───────────────────────────────────────────────
    if ($method === 'POST') {
        if ($id < 1) {
            jsonError('Conversation ID required.', 400);
        }

        $input = getJsonBody();
        $body = trim($input['body'] ?? '');

        if ($body === '' || mb_strlen($body) > 5000) {
            jsonError('Message body is required (max 5000 characters).', 400);
        }

        // Verify conversation exists
        $convStmt = $db->prepare(
            'SELECT c.id, c.customer_id, c.subject,
                    cust.email as customer_email,
                    CONCAT(cust.first_name, " ", cust.last_name) as customer_name,
                    cust.language as customer_language
             FROM oretir_conversations c
             JOIN oretir_customers cust ON c.customer_id = cust.id
             WHERE c.id = ?'
        );
        $convStmt->execute([$id]);
        $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

        if (!$conversation) {
            jsonError('Conversation not found.', 404);
        }

        $senderName = $admin['name'] ?? 'Oregon Tires';

        // Insert admin message
        $db->prepare(
            'INSERT INTO oretir_conversation_messages (conversation_id, sender_type, sender_name, body, is_read, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())'
        )->execute([$id, 'admin', $senderName, $body]);

        // Mark all customer messages as read
        $db->prepare(
            'UPDATE oretir_conversation_messages
             SET is_read = 1
             WHERE conversation_id = ? AND sender_type = ? AND is_read = 0'
        )->execute([$id, 'customer']);

        // Update conversation status
        $db->prepare(
            'UPDATE oretir_conversations SET last_message_at = NOW(), status = ? WHERE id = ?'
        )->execute(['waiting_reply', $id]);

        // Send email notification to customer
        if (!empty($conversation['customer_email'])) {
            try {
                $custLang = ($conversation['customer_language'] ?? 'english') === 'spanish' ? 'es' : 'en';
                sendConversationReplyEmail(
                    $conversation['customer_email'],
                    $conversation['customer_name'] ?: 'Customer',
                    $conversation['subject'],
                    $body,
                    $custLang === 'es' ? 'es' : 'en'
                );
            } catch (\Throwable $e) {
                error_log("conversations.php (admin): email notification error: " . $e->getMessage());
            }
        }

        jsonSuccess(['message' => 'Reply sent.']);
    }

    // ─── PUT: Update status ──────────────────────────────────────────────
    if ($method === 'PUT') {
        if ($id < 1) {
            jsonError('Conversation ID required.', 400);
        }

        $input = getJsonBody();
        $status = trim($input['status'] ?? '');

        $validStatuses = ['open', 'waiting_reply', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses, true)) {
            jsonError('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 400);
        }

        // Verify conversation exists
        $checkStmt = $db->prepare('SELECT id FROM oretir_conversations WHERE id = ?');
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            jsonError('Conversation not found.', 404);
        }

        $db->prepare('UPDATE oretir_conversations SET status = ? WHERE id = ?')
           ->execute([$status, $id]);

        jsonSuccess(['message' => 'Status updated.', 'status' => $status]);
    }

} catch (\Throwable $e) {
    error_log('conversations.php (admin) error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
