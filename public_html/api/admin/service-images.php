<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

const ALLOWED_SERVICE_KEYS = [
    'hero-background',
    'expert-technicians',
    'fast-cars',
    'quality-car-parts',
    'bilingual-support',
    'tire-shop',
    'auto-repair',
    'specialized-tools',
];

try {
    requireMethod('GET', 'POST', 'PUT');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all current service images ───────────────────────
    if ($method === 'GET') {
        $stmt = $db->prepare(
            'SELECT id, service_key, image_url, position_x, position_y, scale, is_current, created_at
             FROM oretir_service_images
             WHERE is_current = 1
             ORDER BY service_key ASC'
        );
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess($images);
    }

    // ─── POST: Upload new service image (multipart/form-data) ───────
    if ($method === 'POST') {
        verifyCsrf();

        // Validate uploaded file
        if (!isset($_FILES['image'])) {
            jsonError('No image file provided.', 400);
        }

        $file = $_FILES['image'];
        $validation = validateImageUpload($file, 5);
        if ($validation !== true) {
            jsonError($validation, 400);
        }

        // Validate service_key
        $serviceKey = isset($_POST['service_key']) ? sanitize($_POST['service_key']) : '';
        if (!in_array($serviceKey, ALLOWED_SERVICE_KEYS, true)) {
            jsonError('Invalid service key. Allowed: ' . implode(', ', ALLOWED_SERVICE_KEYS), 400);
        }

        // Extract extension from MIME type
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!isset($mimeToExt[$mime])) {
            jsonError('Unsupported image type.', 400);
        }

        $ext = $mimeToExt[$mime];

        // Generate filename
        $filename  = 'service-images/' . $serviceKey . '-' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/service-images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = __DIR__ . '/../../uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonError('Failed to save uploaded file.', 500);
        }

        $imageUrl = '/uploads/' . $filename;

        // Mark old images for this service_key as not current
        $stmt = $db->prepare(
            'UPDATE oretir_service_images SET is_current = 0 WHERE service_key = :service_key'
        );
        $stmt->execute([':service_key' => $serviceKey]);

        // Insert new record
        $stmt = $db->prepare(
            'INSERT INTO oretir_service_images (service_key, image_url, is_current)
             VALUES (:service_key, :image_url, 1)'
        );
        $stmt->execute([
            ':service_key' => $serviceKey,
            ':image_url'   => $imageUrl,
        ]);

        $newId = (int) $db->lastInsertId();

        // Fetch the inserted record
        $stmt = $db->prepare(
            'SELECT id, service_key, image_url, position_x, position_y, scale, is_current, created_at
             FROM oretir_service_images WHERE id = :id'
        );
        $stmt->execute([':id' => $newId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonSuccess(['image' => $image]);
    }

    // ─── PUT: Update image position/scale (JSON body) ───────────────
    if ($method === 'PUT') {
        verifyCsrf();

        $body = getJsonBody();

        $id        = isset($body['id']) ? (int) $body['id'] : 0;
        $positionX = $body['position_x'] ?? null;
        $positionY = $body['position_y'] ?? null;
        $scale     = $body['scale'] ?? null;

        if ($id <= 0) {
            jsonError('Valid image ID is required.', 400);
        }

        // Validate position_x: integer 0-100
        if ($positionX === null || !is_numeric($positionX)) {
            jsonError('position_x is required and must be numeric.', 400);
        }
        $positionX = (int) $positionX;
        if ($positionX < 0 || $positionX > 100) {
            jsonError('position_x must be between 0 and 100.', 400);
        }

        // Validate position_y: integer 0-100
        if ($positionY === null || !is_numeric($positionY)) {
            jsonError('position_y is required and must be numeric.', 400);
        }
        $positionY = (int) $positionY;
        if ($positionY < 0 || $positionY > 100) {
            jsonError('position_y must be between 0 and 100.', 400);
        }

        // Validate scale: 0.5 to 3.0
        if ($scale === null || !is_numeric($scale)) {
            jsonError('scale is required and must be numeric.', 400);
        }
        $scale = (float) $scale;
        if ($scale < 0.5 || $scale > 3.0) {
            jsonError('scale must be between 0.5 and 3.0.', 400);
        }

        // Verify record exists
        $stmt = $db->prepare('SELECT id FROM oretir_service_images WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            jsonError('Image not found.', 404);
        }

        // Update position and scale
        $stmt = $db->prepare(
            'UPDATE oretir_service_images
             SET position_x = :position_x, position_y = :position_y, scale = :scale
             WHERE id = :id'
        );
        $stmt->execute([
            ':position_x' => $positionX,
            ':position_y' => $positionY,
            ':scale'      => $scale,
            ':id'         => $id,
        ]);

        // Fetch updated record
        $stmt = $db->prepare(
            'SELECT id, service_key, image_url, position_x, position_y, scale, is_current, created_at
             FROM oretir_service_images WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonSuccess(['image' => $image]);
    }

} catch (\Throwable $e) {
    error_log('service-images.php error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
