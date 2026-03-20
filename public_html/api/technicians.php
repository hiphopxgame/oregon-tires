<?php
/**
 * Oregon Tires — Public Technicians List
 * GET /api/technicians.php?service=tire-installation
 *
 * Returns active technician first names for optional customer preference.
 * No sensitive data exposed — only id and first name.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');
    $db = getDB();

    $service = sanitize((string) ($_GET['service'] ?? ''), 50);

    // Get active employees, optionally filtered by service skill
    if ($service !== '') {
        $stmt = $db->prepare(
            "SELECT DISTINCT e.id, e.name
             FROM oretir_employees e
             JOIN oretir_employee_skills es ON es.employee_id = e.id AND es.service_type = ?
             WHERE e.is_active = 1
             ORDER BY e.name ASC"
        );
        $stmt->execute([$service]);
    } else {
        $stmt = $db->query(
            "SELECT id, name FROM oretir_employees WHERE is_active = 1 ORDER BY name ASC"
        );
    }

    $techs = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        // Only expose first name for privacy
        $firstName = explode(' ', trim($row['name']))[0];
        $techs[] = ['id' => (int) $row['id'], 'name' => $firstName];
    }

    jsonSuccess($techs);

} catch (\Throwable $e) {
    error_log('technicians.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
