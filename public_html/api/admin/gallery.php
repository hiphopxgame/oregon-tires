<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'DELETE');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all gallery images ───────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->prepare(
            'SELECT id, image_url, title, description, is_active, display_order, created_at
             FROM oretir_gallery_images
             ORDER BY display_order ASC, id DESC'
        );
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess($images);
    }

    // ─── POST: Upload new gallery image (multipart/form-data) ───────
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

        // Extract extension from MIME type (more secure than filename)
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

        // Form fields
        $title       = isset($_POST['title']) ? sanitize($_POST['title']) : null;
        $description = isset($_POST['description']) ? sanitize($_POST['description']) : null;

        // Generate unique filename
        $filename  = 'gallery/' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/gallery/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = __DIR__ . '/../../uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonError('Failed to save uploaded file.', 500);
        }

        $imageUrl = '/uploads/' . $filename;

        // Get next display_order
        $stmt = $db->prepare(
            'SELECT COALESCE(MAX(display_order), 0) + 1 AS next_order FROM oretir_gallery_images'
        );
        $stmt->execute();
        $nextOrder = (int) $stmt->fetchColumn();

        // Insert record
        $stmt = $db->prepare(
            'INSERT INTO oretir_gallery_images (image_url, title, description, display_order)
             VALUES (:image_url, :title, :description, :display_order)'
        );
        $stmt->execute([
            ':image_url'     => $imageUrl,
            ':title'         => $title,
            ':description'   => $description,
            ':display_order' => $nextOrder,
        ]);

        $newId = (int) $db->lastInsertId();

        // Fetch the inserted record
        $stmt = $db->prepare(
            'SELECT id, image_url, title, description, is_active, display_order, created_at
             FROM oretir_gallery_images WHERE id = :id'
        );
        $stmt->execute([':id' => $newId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonSuccess($image);
    }

    // ─── DELETE: Remove a gallery image ─────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonError('Valid image ID is required.', 400);
        }

        // Fetch image record
        $stmt = $db->prepare(
            'SELECT id, image_url FROM oretir_gallery_images WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            jsonError('Image not found.', 404);
        }

        // Delete physical file (safety: must be under /uploads/)
        $imageUrl = $image['image_url'];
        if (strpos($imageUrl, '/uploads/') === 0) {
            $filePath = __DIR__ . '/../../' . ltrim($imageUrl, '/');
            $realBase = realpath(__DIR__ . '/../../uploads');
            $realFile = realpath($filePath);

            // Extra safety: resolved path must be inside uploads/
            if ($realFile !== false && $realBase !== false && strpos($realFile, $realBase) === 0) {
                if (is_file($realFile)) {
                    unlink($realFile);
                }
            }
        }

        // Delete DB record
        $stmt = $db->prepare('DELETE FROM oretir_gallery_images WHERE id = :id');
        $stmt->execute([':id' => $id]);

        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log('gallery.php error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
