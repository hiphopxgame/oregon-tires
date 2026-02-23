<?php
/**
 * Oregon Tires — Admin Inspection Photo Upload
 * POST   /api/admin/inspection-photos.php   — Upload photo for inspection item
 * DELETE /api/admin/inspection-photos.php?id=N — Delete photo
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    requireAdmin();
    requireMethod('POST', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── POST: Upload photo ──────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();

        $inspectionItemId = (int) ($_POST['inspection_item_id'] ?? 0);
        if ($inspectionItemId <= 0) jsonError('inspection_item_id is required.');

        // Verify item exists and get RO number for folder
        $stmt = $db->prepare(
            'SELECT ii.id, i.repair_order_id, r.ro_number
             FROM oretir_inspection_items ii
             JOIN oretir_inspections i ON i.id = ii.inspection_id
             JOIN oretir_repair_orders r ON r.id = i.repair_order_id
             WHERE ii.id = ?'
        );
        $stmt->execute([$inspectionItemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) jsonError('Inspection item not found.', 404);

        // Validate file
        if (!isset($_FILES['photo'])) {
            jsonError('No photo file provided.', 400);
        }

        $file = $_FILES['photo'];
        $validation = validateImageUpload($file, 10);
        if ($validation !== true) {
            jsonError($validation, 400);
        }

        // Detect MIME type
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!isset($mimeToExt[$mime])) {
            jsonError('Unsupported image type. Use JPEG, PNG, or WebP.', 400);
        }

        $ext = $mimeToExt[$mime];

        // Build upload path: /uploads/inspections/{ro_number}/
        $roNumber = $item['ro_number'];
        $uploadDir = __DIR__ . '/../../uploads/inspections/' . $roNumber . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonError('Failed to save uploaded file.', 500);
        }

        $imageUrl = '/uploads/inspections/' . $roNumber . '/' . $filename;
        $caption = isset($_POST['caption']) ? sanitize($_POST['caption'], 200) : null;

        $db->prepare(
            'INSERT INTO oretir_inspection_photos (inspection_item_id, image_url, caption, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([$inspectionItemId, $imageUrl, $caption]);

        jsonSuccess([
            'id'        => (int) $db->lastInsertId(),
            'image_url' => $imageUrl,
            'caption'   => $caption,
            'message'   => 'Photo uploaded.',
        ]);
    }

    // ─── DELETE: Remove photo ────────────────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) jsonError('Photo ID is required.');

        $stmt = $db->prepare('SELECT id, image_url FROM oretir_inspection_photos WHERE id = ?');
        $stmt->execute([$id]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$photo) jsonError('Photo not found.', 404);

        // Delete physical file (safety check)
        $imageUrl = $photo['image_url'];
        if (strpos($imageUrl, '/uploads/inspections/') === 0) {
            $filePath = __DIR__ . '/../../' . ltrim($imageUrl, '/');
            $realBase = realpath(__DIR__ . '/../../uploads');
            $realFile = realpath($filePath);

            if ($realFile !== false && $realBase !== false && strpos($realFile, $realBase) === 0) {
                if (is_file($realFile)) {
                    unlink($realFile);
                }
            }
        }

        $db->prepare('DELETE FROM oretir_inspection_photos WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/inspection-photos.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
