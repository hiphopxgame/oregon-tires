<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'PUT');
    $admin = requireAdmin();
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query('SELECT * FROM oretir_contact_messages ORDER BY created_at DESC LIMIT 500');
        jsonSuccess($stmt->fetchAll());
    }

    // PUT
    verifyCsrf();
    $body = getJsonBody();

    $id = (int) ($body['id'] ?? 0);
    $status = $body['status'] ?? '';

    if ($id < 1) {
        jsonError('Missing message id.', 400);
    }

    $validStatuses = ['new', 'priority', 'completed'];
    if (!in_array($status, $validStatuses, true)) {
        jsonError('Invalid status. Must be: new, priority, or completed.', 400);
    }

    $stmt = $db->prepare('UPDATE oretir_contact_messages SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);

    jsonSuccess(['updated' => $id]);

} catch (\Throwable $e) {
    error_log('messages.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
