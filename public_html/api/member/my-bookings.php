<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('GET');
    requireCustomerAuth();

    $memberId = (int) $_SESSION['member_id'];
    $status = sanitize((string) ($_GET['status'] ?? ''), 20);

    $sql = 'SELECT id, reference_number, service, preferred_date, preferred_time,
                   vehicle_year, vehicle_make, vehicle_model, status, language,
                   created_at
            FROM oretir_appointments
            WHERE member_id = ?';
    $params = [$memberId];

    if ($status && in_array($status, ['new', 'confirmed', 'completed', 'cancelled'], true)) {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY preferred_date DESC, preferred_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    jsonSuccess(['bookings' => $bookings, 'total' => count($bookings)]);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-bookings error: " . $e->getMessage());
    jsonError('Server error', 500);
}
