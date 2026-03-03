<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'PUT', 'DELETE', 'PATCH');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // Support _method override for PUT via POST (multipart/form-data)
    if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
        $method = 'PUT';
    }

    // ─── GET: List all gallery images ───────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->prepare(
            'SELECT id, image_url, title_en, title_es, description_en, description_es, is_active, display_order, created_at
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

        // Form fields (bilingual)
        $titleEn       = isset($_POST['title_en']) ? sanitize($_POST['title_en']) : null;
        $titleEs       = isset($_POST['title_es']) ? sanitize($_POST['title_es']) : null;
        $descriptionEn = isset($_POST['description_en']) ? sanitize($_POST['description_en']) : null;
        $descriptionEs = isset($_POST['description_es']) ? sanitize($_POST['description_es']) : null;

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
            'INSERT INTO oretir_gallery_images (image_url, title_en, title_es, description_en, description_es, display_order)
             VALUES (:image_url, :title_en, :title_es, :description_en, :description_es, :display_order)'
        );
        $stmt->execute([
            ':image_url'       => $imageUrl,
            ':title_en'        => $titleEn,
            ':title_es'        => $titleEs,
            ':description_en'  => $descriptionEn,
            ':description_es'  => $descriptionEs,
            ':display_order'   => $nextOrder,
        ]);

        $newId = (int) $db->lastInsertId();

        // Fetch the inserted record
        $stmt = $db->prepare(
            'SELECT id, image_url, title_en, title_es, description_en, description_es, is_active, display_order, created_at
             FROM oretir_gallery_images WHERE id = :id'
        );
        $stmt->execute([':id' => $newId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonSuccess($image);
    }

    // ─── PUT: Update a gallery image (multipart/form-data) ──────────
    if ($method === 'PUT') {
        verifyCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            jsonError('Valid image ID is required.', 400);
        }

        // Fetch existing record
        $stmt = $db->prepare('SELECT * FROM oretir_gallery_images WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            jsonError('Image not found.', 404);
        }

        $imageUrl = $existing['image_url'];

        // Handle optional image replacement
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $validation = validateImageUpload($file, 5);
            if ($validation !== true) {
                jsonError($validation, 400);
            }

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
            $filename  = 'gallery/' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../uploads/gallery/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destPath = __DIR__ . '/../../uploads/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                jsonError('Failed to save uploaded file.', 500);
            }

            // Delete old file safely
            $oldUrl = $existing['image_url'];
            if ($oldUrl && strpos($oldUrl, '/uploads/') === 0) {
                $oldPath = __DIR__ . '/../../' . ltrim($oldUrl, '/');
                $realBase = realpath(__DIR__ . '/../../uploads');
                $realFile = realpath($oldPath);
                if ($realFile !== false && $realBase !== false && strpos($realFile, $realBase) === 0 && is_file($realFile)) {
                    unlink($realFile);
                }
            }

            $imageUrl = '/uploads/' . $filename;
        }

        // Update record
        $stmt = $db->prepare(
            'UPDATE oretir_gallery_images SET
                image_url = :image_url,
                title_en = :title_en, title_es = :title_es,
                description_en = :description_en, description_es = :description_es
             WHERE id = :id'
        );
        $stmt->execute([
            ':image_url'       => $imageUrl,
            ':title_en'        => isset($_POST['title_en']) ? sanitize($_POST['title_en']) : $existing['title_en'],
            ':title_es'        => isset($_POST['title_es']) ? sanitize($_POST['title_es']) : $existing['title_es'],
            ':description_en'  => isset($_POST['description_en']) ? sanitize($_POST['description_en']) : $existing['description_en'],
            ':description_es'  => isset($_POST['description_es']) ? sanitize($_POST['description_es']) : $existing['description_es'],
            ':id'              => $id,
        ]);

        // Fetch updated record
        $stmt = $db->prepare(
            'SELECT id, image_url, title_en, title_es, description_en, description_es, is_active, display_order, created_at
             FROM oretir_gallery_images WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        jsonSuccess($stmt->fetch(PDO::FETCH_ASSOC));
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

    // ─── PATCH: Reorder gallery images ─────────────────────────────
    if ($method === 'PATCH') {
        verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? [];

        if (!is_array($order) || empty($order)) {
            jsonError('Order array is required.', 400);
        }

        $stmt = $db->prepare('UPDATE oretir_gallery_images SET display_order = :order WHERE id = :id');
        foreach ($order as $item) {
            if (!isset($item['id'], $item['display_order'])) continue;
            $stmt->execute([':order' => (int) $item['display_order'], ':id' => (int) $item['id']]);
        }

        jsonSuccess(['reordered' => count($order)]);
    }

} catch (\Throwable $e) {
    error_log('gallery.php error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
