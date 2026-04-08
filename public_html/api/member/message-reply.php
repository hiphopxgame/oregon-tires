<?php
/**
 * POST /api/member/message-reply.php
 *
 * Worker-side reply into an existing customer conversation.
 * Inserts an admin-typed message into oretir_conversation_messages.
 * Employee or admin role required.
 *
 * Body: { "conversation_id": 123, "body": "..." }
 * Returns: { "success": true, "data": { "message_id": 456 } }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

header('Content-Type: application/json');

try {
    requireMethod('POST');

    if (!MemberAuth::isMemberLoggedIn()) {
        jsonError('Authentication required.', 401);
    }

    $role = $_SESSION['dashboard_role'] ?? 'member';
    if (!in_array($role, ['employee', 'admin'], true)) {
        jsonError('Employee or admin access required.', 403);
    }

    $data = getJsonBody();
    $conversationId = (int) ($data['conversation_id'] ?? 0);
    $body = sanitize((string) ($data['body'] ?? ''), 5000);
    if ($conversationId <= 0 || $body === '') {
        jsonError('conversation_id and body are required.', 400);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM oretir_conversations WHERE id = ? LIMIT 1');
    $stmt->execute([$conversationId]);
    $conv = $stmt->fetch();
    if (!$conv) {
        jsonError('Conversation not found.', 404);
    }

    $email = $_SESSION['member_email'] ?? '';
    $senderName = 'Oregon Tires Team';
    if ($email) {
        $empStmt = $pdo->prepare('SELECT name FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
        $empStmt->execute([$email]);
        $row = $empStmt->fetch();
        if ($row && !empty($row['name'])) {
            $senderName = (string) $row['name'];
        }
    }

    $ins = $pdo->prepare(
        'INSERT INTO oretir_conversation_messages (conversation_id, sender_type, sender_name, body, is_read, source, created_at)
         VALUES (?, ?, ?, ?, 0, ?, NOW())'
    );
    $ins->execute([$conversationId, 'admin', $senderName, $body, 'web']);
    $messageId = (int) $pdo->lastInsertId();

    $pdo->prepare('UPDATE oretir_conversations SET last_message_at = NOW(), status = CASE WHEN status = "closed" THEN status ELSE "waiting_reply" END WHERE id = ?')
        ->execute([$conversationId]);

    $pdo->prepare('UPDATE oretir_conversation_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = ? AND is_read = 0')
        ->execute([$conversationId, 'customer']);

    jsonSuccess(['message_id' => $messageId]);

} catch (\Throwable $e) {
    error_log('message-reply.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error.']);
}
