<?php
/**
 * Oregon Tires — Admin Promotions CRUD
 * GET    /api/admin/promotions.php       — list all promotions
 * POST   /api/admin/promotions.php       — create promotion (multipart/form-data, supports image)
 * PUT    /api/admin/promotions.php       — update promotion (POST with _method=PUT)
 * DELETE /api/admin/promotions.php       — delete promotion (query param ?id=N)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'DELETE');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // Support _method override for PUT via POST (multipart/form-data)
    if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
        $method = 'PUT';
    }

    // ─── GET: List all promotions ─────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT * FROM oretir_promotions ORDER BY sort_order ASC, id DESC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ───────────────────────────
    verifyCsrf();

    // ─── Helper: handle image upload ──────────────────────────────
    function handlePromoImage(): ?string {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
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
        $filename = 'promotions/' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/promotions/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = __DIR__ . '/../../uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonError('Failed to save uploaded file.', 500);
        }

        return '/uploads/' . $filename;
    }

    // ─── Helper: delete promo image file safely ───────────────────
    function deletePromoImageFile(?string $imageUrl): void {
        if (!$imageUrl || strpos($imageUrl, '/uploads/') !== 0) return;
        $filePath = __DIR__ . '/../../' . ltrim($imageUrl, '/');
        $realBase = realpath(__DIR__ . '/../../uploads');
        $realFile = realpath($filePath);
        if ($realFile !== false && $realBase !== false && strpos($realFile, $realBase) === 0 && is_file($realFile)) {
            unlink($realFile);
        }
    }

    // ─── POST: Create new promotion ───────────────────────────────
    if ($method === 'POST') {
        $imageUrl = handlePromoImage();

        $stmt = $db->prepare(
            'INSERT INTO oretir_promotions
                (title_en, title_es, body_en, body_es, cta_text_en, cta_text_es,
                 cta_url, bg_color, text_color, badge_text, image_url, is_active,
                 starts_at, ends_at, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            sanitize((string)($_POST['title_en'] ?? ''), 255),
            sanitize((string)($_POST['title_es'] ?? ''), 255),
            $_POST['body_en'] ?? '',
            $_POST['body_es'] ?? '',
            sanitize((string)($_POST['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($_POST['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($_POST['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($_POST['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($_POST['text_color'] ?? '#000000'), 20),
            sanitize((string)($_POST['badge_text'] ?? ''), 50) ?: null,
            $imageUrl,
            (int)($_POST['is_active'] ?? 1),
            $_POST['starts_at'] ?? null,
            $_POST['ends_at'] ?? null,
            (int)($_POST['sort_order'] ?? 0),
        ]);

        jsonSuccess(['id' => (int)$db->lastInsertId()]);
    }

    // ─── PUT: Update existing promotion ───────────────────────────
    if ($method === 'PUT') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing promotion id', 400);
        }

        // Fetch existing for image cleanup
        $stmt = $db->prepare('SELECT image_url FROM oretir_promotions WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$existing) {
            jsonError('Promotion not found', 404);
        }

        // Handle image: new upload, remove, or keep existing
        $imageUrl = $existing['image_url'];
        $newImage = handlePromoImage();
        if ($newImage) {
            deletePromoImageFile($existing['image_url']);
            $imageUrl = $newImage;
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image']) {
            deletePromoImageFile($existing['image_url']);
            $imageUrl = null;
        }

        $stmt = $db->prepare(
            'UPDATE oretir_promotions SET
                title_en = ?, title_es = ?, body_en = ?, body_es = ?,
                cta_text_en = ?, cta_text_es = ?, cta_url = ?,
                bg_color = ?, text_color = ?, badge_text = ?, image_url = ?,
                is_active = ?, starts_at = ?, ends_at = ?, sort_order = ?,
                updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            sanitize((string)($_POST['title_en'] ?? ''), 255),
            sanitize((string)($_POST['title_es'] ?? ''), 255),
            $_POST['body_en'] ?? '',
            $_POST['body_es'] ?? '',
            sanitize((string)($_POST['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($_POST['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($_POST['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($_POST['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($_POST['text_color'] ?? '#000000'), 20),
            sanitize((string)($_POST['badge_text'] ?? ''), 50) ?: null,
            $imageUrl,
            (int)($_POST['is_active'] ?? 1),
            $_POST['starts_at'] ?? null,
            $_POST['ends_at'] ?? null,
            (int)($_POST['sort_order'] ?? 0),
            $id,
        ]);

        jsonSuccess(['updated' => $stmt->rowCount()]);
    }

    // ─── DELETE: Remove promotion ─────────────────────────────────
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            jsonError('Missing promotion id', 400);
        }

        // Delete image file if exists
        $stmt = $db->prepare('SELECT image_url FROM oretir_promotions WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($existing) {
            deletePromoImageFile($existing['image_url']);
        }

        $db->prepare('DELETE FROM oretir_promotions WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/promotions.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
