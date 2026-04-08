<?php
/**
 * Admin Conversations API
 *
 * GET (no id)     — List all conversations with customer info
 * GET ?id=N       — Conversation detail with all messages
 * POST ?id=N      — Admin reply (sender_type='admin', email-aware)
 * PUT ?id=N       — Update conversation status
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';

try {
    $admin = requirePermission('messaging');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    // ─── GET: List or Detail ─────────────────────────────────────────────
    if ($method === 'GET') {
        if ($id > 0) {
            // Conversation detail — include source + email_thread_id
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

            // Fetch messages — include source + attachments_json
            $msgStmt = $db->prepare(
                'SELECT id, sender_type, sender_name, body, is_read, source, attachments_json, created_at
                 FROM oretir_conversation_messages
                 WHERE conversation_id = ?
                 ORDER BY created_at ASC'
            );
            $msgStmt->execute([$id]);
            $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

            // Parse attachments_json for each message
            foreach ($messages as &$msg) {
                if (!empty($msg['attachments_json'])) {
                    $msg['attachments'] = json_decode($msg['attachments_json'], true) ?: [];
                } else {
                    $msg['attachments'] = [];
                }
                unset($msg['attachments_json']);
            }
            unset($msg);

            $conversation['messages'] = $messages;

            // Mark customer messages as read
            $db->prepare(
                'UPDATE oretir_conversation_messages
                 SET is_read = 1
                 WHERE conversation_id = ? AND sender_type = ? AND is_read = 0'
            )->execute([$id, 'customer']);

            jsonSuccess($conversation);
        }

        // List all conversations — include source column
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

        // Source filter (web, email, contact_form)
        if (!empty($_GET['source'])) {
            $validSources = ['web', 'email', 'contact_form'];
            if (in_array($_GET['source'], $validSources, true)) {
                $where[] = 'c.source = ?';
                $params[] = $_GET['source'];
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
        $input = getJsonBody();
        $action = trim($input['action'] ?? '');

        // Bulk mark all conversations read (no id needed)
        if ($action === 'mark_read_all') {
            $db->exec(
                "UPDATE oretir_conversation_messages SET is_read = 1 WHERE sender_type = 'customer' AND is_read = 0"
            );
            jsonSuccess(['message' => 'All conversations marked as read.']);
        }

        // Mark single conversation read
        if ($action === 'mark_read') {
            if ($id < 1) {
                jsonError('Conversation ID required.', 400);
            }
            $db->prepare(
                'UPDATE oretir_conversation_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = ? AND is_read = 0'
            )->execute([$id, 'customer']);
            jsonSuccess(['message' => 'Conversation marked as read.']);
        }

        if ($id < 1) {
            jsonError('Conversation ID required.', 400);
        }

        $body = trim($input['body'] ?? '');

        if ($body === '' || mb_strlen($body) > 5000) {
            jsonError('Message body is required (max 5000 characters).', 400);
        }

        // Verify conversation exists — include source for email-aware reply
        $convStmt = $db->prepare(
            'SELECT c.id, c.customer_id, c.subject, c.source,
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
        $isEmailConv = ($conversation['source'] ?? '') === 'email';

        // Insert admin message
        $db->prepare(
            'INSERT INTO oretir_conversation_messages (conversation_id, sender_type, sender_name, body, is_read, source, created_at)
             VALUES (?, ?, ?, ?, 0, ?, NOW())'
        )->execute([$id, 'admin', $senderName, $body, $isEmailConv ? 'email' : 'web']);

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

                if ($isEmailConv) {
                    // Email-sourced conversation: send threaded email reply
                    $inReplyTo = null;
                    $replyStmt = $db->prepare(
                        'SELECT message_id_header FROM oretir_email_message_ids
                         WHERE conversation_id = ? AND direction = ?
                         ORDER BY created_at DESC LIMIT 1'
                    );
                    $replyStmt->execute([$id, 'inbound']);
                    $lastInbound = $replyStmt->fetch(PDO::FETCH_ASSOC);
                    if ($lastInbound) {
                        $inReplyTo = $lastInbound['message_id_header'];
                    }

                    sendEmailReply(
                        $conversation['customer_email'],
                        $conversation['customer_name'] ?: 'Customer',
                        $conversation['subject'],
                        $body,
                        $id,
                        $inReplyTo,
                        $custLang
                    );
                } else {
                    // Web-sourced conversation: send standard notification
                    sendConversationReplyEmail(
                        $conversation['customer_email'],
                        $conversation['customer_name'] ?: 'Customer',
                        $conversation['subject'],
                        $body,
                        $custLang === 'es' ? 'es' : 'en'
                    );
                }
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

    // ─── DELETE: Remove conversation(s) ─────────────────────────────────
    if ($method === 'DELETE') {
        $data = getJsonBody();
        $action = $data['action'] ?? '';

        // ── Bulk delete ──
        if ($action === 'bulk_delete') {
            requireSuperAdmin();
            $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn(int $v) => $v > 0);
            if (empty($ids)) jsonError('No valid IDs.', 400);
            if (count($ids) > 100) jsonError('Maximum 100 items per batch.', 400);

            $db->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM oretir_conversation_messages WHERE conversation_id IN ($placeholders)")->execute($ids);
            $db->prepare("DELETE FROM oretir_conversations WHERE id IN ($placeholders)")->execute($ids);
            $db->commit();
            jsonSuccess(['deleted' => count($ids)]);
        }

        // ── Single delete ──
        requireAdmin();
        $delId = (int) ($data['id'] ?? 0);
        if ($delId <= 0) jsonError('Conversation ID is required.', 400);

        $db->beginTransaction();
        $db->prepare('DELETE FROM oretir_conversation_messages WHERE conversation_id = ?')->execute([$delId]);
        $db->prepare('DELETE FROM oretir_conversations WHERE id = ?')->execute([$delId]);
        $db->commit();
        jsonSuccess(['deleted' => 1]);
    }

} catch (\Throwable $e) {
    error_log('conversations.php (admin) error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
