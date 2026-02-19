<?php
/**
 * Oregon Tires — Admin Data Export Endpoint
 * GET /api/admin/export.php?type=appointments&format=csv
 * GET /api/admin/export.php?type=messages&format=csv
 *
 * Optional filters: date_from, date_to, status
 * Returns CSV download with proper headers.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');
    $admin = requireAdmin();
    $db = getDB();

    // ─── Validate type param ──────────────────────────────────────────────
    $type = $_GET['type'] ?? '';
    if (!in_array($type, ['appointments', 'messages'], true)) {
        jsonError('Invalid export type. Must be: appointments or messages.', 400);
    }

    $format = $_GET['format'] ?? 'csv';
    if ($format !== 'csv') {
        jsonError('Invalid format. Currently only csv is supported.', 400);
    }

    // ─── Build WHERE clauses ──────────────────────────────────────────────
    $where  = [];
    $params = [];

    if ($type === 'appointments') {
        $validStatuses = ['new', 'pending', 'confirmed', 'completed', 'cancelled'];

        if (!empty($_GET['status']) && in_array($_GET['status'], $validStatuses, true)) {
            $where[]  = 'a.status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['date_from'])) {
            $where[]  = 'a.preferred_date >= ?';
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where[]  = 'a.preferred_date <= ?';
            $params[] = $_GET['date_to'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT a.id, a.service, a.preferred_date, a.preferred_time, a.status,
                       a.first_name, a.last_name, a.phone, a.email,
                       a.vehicle_year, a.vehicle_make, a.vehicle_model,
                       e.name AS employee_name, a.notes, a.admin_notes, a.created_at
                FROM oretir_appointments a
                LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id
                {$whereSQL}
                ORDER BY a.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $csvHeaders = [
            'ID', 'Service', 'Date', 'Time', 'Status',
            'First Name', 'Last Name', 'Phone', 'Email',
            'Vehicle Year', 'Vehicle Make', 'Vehicle Model',
            'Assigned Employee', 'Notes', 'Admin Notes', 'Created At',
        ];

        $csvKeys = [
            'id', 'service', 'preferred_date', 'preferred_time', 'status',
            'first_name', 'last_name', 'phone', 'email',
            'vehicle_year', 'vehicle_make', 'vehicle_model',
            'employee_name', 'notes', 'admin_notes', 'created_at',
        ];

        $filename = 'appointments-export-' . date('Y-m-d') . '.csv';

    } else {
        // messages
        $validStatuses = ['new', 'priority', 'completed'];

        if (!empty($_GET['status']) && in_array($_GET['status'], $validStatuses, true)) {
            $where[]  = 'status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['date_from'])) {
            $where[]  = 'created_at >= ?';
            $params[] = $_GET['date_from'] . ' 00:00:00';
        }
        if (!empty($_GET['date_to'])) {
            $where[]  = 'created_at <= ?';
            $params[] = $_GET['date_to'] . ' 23:59:59';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT id, first_name, last_name, email, phone, message,
                       status, language, created_at
                FROM oretir_contact_messages
                {$whereSQL}
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $csvHeaders = [
            'ID', 'First Name', 'Last Name', 'Email', 'Phone',
            'Message', 'Status', 'Language', 'Created At',
        ];

        $csvKeys = [
            'id', 'first_name', 'last_name', 'email', 'phone',
            'message', 'status', 'language', 'created_at',
        ];

        $filename = 'messages-export-' . date('Y-m-d') . '.csv';
    }

    // ─── Output CSV ───────────────────────────────────────────────────────
    // Override the default JSON content-type set by bootstrap
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    $output = fopen('php://output', 'w');

    // BOM for Excel UTF-8 compatibility
    fwrite($output, "\xEF\xBB\xBF");

    // Header row
    fputcsv($output, $csvHeaders);

    // Data rows
    foreach ($rows as $row) {
        $line = [];
        foreach ($csvKeys as $key) {
            $line[] = $row[$key] ?? '';
        }
        fputcsv($output, $line);
    }

    fclose($output);
    exit;

} catch (\Throwable $e) {
    error_log('export.php error: ' . $e->getMessage());
    // If headers already sent (mid-CSV), we can't send JSON error
    if (!headers_sent()) {
        jsonError('Server error.', 500);
    }
    exit(1);
}
