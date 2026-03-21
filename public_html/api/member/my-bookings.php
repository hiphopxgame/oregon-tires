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

    $sql = 'SELECT a.id, a.reference_number, a.service, a.preferred_date, a.preferred_time,
                   a.vehicle_year, a.vehicle_make, a.vehicle_model, a.status, a.language,
                   a.created_at, ro.status as ro_status, ro.ro_number
            FROM oretir_appointments a
            LEFT JOIN oretir_repair_orders ro ON ro.appointment_id = a.id
            WHERE a.member_id = ?';
    $params = [$memberId];

    if ($status && in_array($status, ['new', 'confirmed', 'completed', 'cancelled'], true)) {
        $sql .= ' AND a.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY a.preferred_date DESC, a.preferred_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    jsonSuccess(['bookings' => $bookings, 'total' => count($bookings)]);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-bookings error: " . $e->getMessage());
    jsonError('Server error', 500);
}
