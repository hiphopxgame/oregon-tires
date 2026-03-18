<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/business-hours.php';

try {
    requireAdmin();
    requireMethod('GET', 'PUT', 'POST', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET: Return all business hours + holidays
    if ($method === 'GET') {
        $hours = getAllBusinessHours($db);
        $year = (int) ($_GET['year'] ?? date('Y'));
        $holidays = getHolidays($db, $year);

        jsonSuccess([
            'hours' => $hours,
            'holidays' => $holidays,
        ]);
    }

    verifyCsrf();
    $data = getJsonBody();

    // PUT: Update a business hours row
    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) jsonError('Missing business hours id.', 400);

        $fields = [];
        $params = [];

        if (isset($data['open_time'])) {
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['open_time'])) {
                jsonError('Invalid open_time format. Use HH:MM.', 400);
            }
            $fields[] = 'open_time = ?';
            $params[] = $data['open_time'];
        }
        if (isset($data['close_time'])) {
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['close_time'])) {
                jsonError('Invalid close_time format. Use HH:MM.', 400);
            }
            $fields[] = 'close_time = ?';
            $params[] = $data['close_time'];
        }
        if (isset($data['is_open'])) {
            $fields[] = 'is_open = ?';
            $params[] = (int) $data['is_open'];
        }
        if (isset($data['max_concurrent'])) {
            $val = max(1, min(20, (int) $data['max_concurrent']));
            $fields[] = 'max_concurrent = ?';
            $params[] = $val;
        }

        if (empty($fields)) jsonError('No fields to update.', 400);

        $params[] = $id;
        $db->prepare('UPDATE oretir_business_hours SET ' . implode(', ', $fields) . ' WHERE id = ?')
           ->execute($params);

        jsonSuccess(['updated' => $id]);
    }

    // POST: Add a holiday
    if ($method === 'POST') {
        $action = $data['action'] ?? 'add_holiday';
        if ($action !== 'add_holiday') jsonError('Invalid action.', 400);

        $holidayDate = sanitize((string) ($data['holiday_date'] ?? ''), 10);
        $nameEn = sanitize((string) ($data['name_en'] ?? ''), 100);
        $nameEs = sanitize((string) ($data['name_es'] ?? ''), 100);
        $isRecurring = !empty($data['is_recurring']) ? 1 : 0;

        if (!$holidayDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $holidayDate)) {
            jsonError('Valid holiday_date (YYYY-MM-DD) is required.', 400);
        }
        if (!$nameEn) jsonError('Holiday name (English) is required.', 400);

        $stmt = $db->prepare(
            'INSERT INTO oretir_holidays (holiday_date, name_en, name_es, is_recurring)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), name_es = VALUES(name_es), is_recurring = VALUES(is_recurring)'
        );
        $stmt->execute([$holidayDate, $nameEn, $nameEs ?: null, $isRecurring]);

        $id = (int) $db->lastInsertId();
        jsonSuccess(['id' => $id, 'holiday_date' => $holidayDate, 'name_en' => $nameEn]);
    }

    // DELETE: Remove a holiday
    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id < 1) jsonError('Valid holiday id is required.', 400);

        $db->prepare('DELETE FROM oretir_holidays WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log('business-hours.php admin error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
