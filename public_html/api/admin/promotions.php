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
    requireAdmin();
    requireMethod('GET', 'POST', 'DELETE');

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

    // ─── Helper: validate activation requirements ─────────────────
    function validateActivationRequirements(string $placement, int $isActive, array $data): void {
        if ($isActive !== 1) return;

        $titleEn = trim((string)($data['title_en'] ?? ''));
        if ($titleEn === '') {
            jsonError('Title (EN) is required to activate a promotion', 400);
        }

        if ($placement === 'exit_intent') {
            $subtitleEn = trim((string)($data['subtitle_en'] ?? ''));
            if ($subtitleEn === '') {
                jsonError('Subtitle (EN) is required to activate an exit-intent promotion', 400);
            }
        } else {
            $bodyEn = trim((string)($data['body_en'] ?? ''));
            if ($bodyEn === '') {
                jsonError('Body (EN) is required to activate a banner/sidebar/inline promotion', 400);
            }
        }
    }

    // ─── POST: Create new promotion ───────────────────────────────
    if ($method === 'POST') {
        $imageUrl = handlePromoImage();

        $validPlacements = ['banner', 'exit_intent', 'sidebar', 'inline'];
        $placement = in_array($_POST['placement'] ?? 'banner', $validPlacements, true)
            ? $_POST['placement'] : 'banner';
        $isActive = (int)($_POST['is_active'] ?? 0);

        validateActivationRequirements($placement, $isActive, $_POST);

        $stmt = $db->prepare(
            'INSERT INTO oretir_promotions
                (placement, title_en, title_es, body_en, body_es,
                 subtitle_en, subtitle_es,
                 cta_text_en, cta_text_es, cta_url,
                 placeholder_en, placeholder_es,
                 success_msg_en, success_msg_es,
                 error_msg_en, error_msg_es,
                 nospam_en, nospam_es, popup_icon,
                 bg_color, text_color, badge_text_en, badge_text_es, image_url, is_active,
                 starts_at, ends_at, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $placement,
            sanitize((string)($_POST['title_en'] ?? ''), 255),
            sanitize((string)($_POST['title_es'] ?? ''), 255),
            $_POST['body_en'] ?? '',
            $_POST['body_es'] ?? '',
            sanitize((string)($_POST['subtitle_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['subtitle_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($_POST['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($_POST['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($_POST['placeholder_en'] ?? ''), 100) ?: null,
            sanitize((string)($_POST['placeholder_es'] ?? ''), 100) ?: null,
            sanitize((string)($_POST['success_msg_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['success_msg_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['error_msg_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['error_msg_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['nospam_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['nospam_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['popup_icon'] ?? ''), 20) ?: null,
            sanitize((string)($_POST['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($_POST['text_color'] ?? '#000000'), 20),
            sanitize((string)($_POST['badge_text_en'] ?? ''), 50) ?: null,
            sanitize((string)($_POST['badge_text_es'] ?? ''), 50) ?: null,
            $imageUrl,
            $isActive,
            $_POST['starts_at'] ?? null,
            $_POST['ends_at'] ?? null,
            (int)($_POST['sort_order'] ?? 0),
        ]);

        $newId = (int)$db->lastInsertId();

        // Enforce single active exit-intent: deactivate others
        if ($placement === 'exit_intent' && $isActive === 1) {
            $db->prepare('UPDATE oretir_promotions SET is_active = 0 WHERE placement = ? AND is_active = 1 AND id != ?')
               ->execute(['exit_intent', $newId]);
        }

        jsonSuccess(['id' => $newId]);
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

        $validPlacements = ['banner', 'exit_intent', 'sidebar', 'inline'];
        $placement = in_array($_POST['placement'] ?? 'banner', $validPlacements, true)
            ? $_POST['placement'] : 'banner';
        $isActive = (int)($_POST['is_active'] ?? 0);

        validateActivationRequirements($placement, $isActive, $_POST);

        $stmt = $db->prepare(
            'UPDATE oretir_promotions SET
                placement = ?, title_en = ?, title_es = ?, body_en = ?, body_es = ?,
                subtitle_en = ?, subtitle_es = ?,
                cta_text_en = ?, cta_text_es = ?, cta_url = ?,
                placeholder_en = ?, placeholder_es = ?,
                success_msg_en = ?, success_msg_es = ?,
                error_msg_en = ?, error_msg_es = ?,
                nospam_en = ?, nospam_es = ?, popup_icon = ?,
                bg_color = ?, text_color = ?, badge_text_en = ?, badge_text_es = ?, image_url = ?,
                is_active = ?, starts_at = ?, ends_at = ?, sort_order = ?,
                updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $placement,
            sanitize((string)($_POST['title_en'] ?? ''), 255),
            sanitize((string)($_POST['title_es'] ?? ''), 255),
            $_POST['body_en'] ?? '',
            $_POST['body_es'] ?? '',
            sanitize((string)($_POST['subtitle_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['subtitle_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($_POST['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($_POST['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($_POST['placeholder_en'] ?? ''), 100) ?: null,
            sanitize((string)($_POST['placeholder_es'] ?? ''), 100) ?: null,
            sanitize((string)($_POST['success_msg_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['success_msg_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['error_msg_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['error_msg_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['nospam_en'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['nospam_es'] ?? ''), 255) ?: null,
            sanitize((string)($_POST['popup_icon'] ?? ''), 20) ?: null,
            sanitize((string)($_POST['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($_POST['text_color'] ?? '#000000'), 20),
            sanitize((string)($_POST['badge_text_en'] ?? ''), 50) ?: null,
            sanitize((string)($_POST['badge_text_es'] ?? ''), 50) ?: null,
            $imageUrl,
            $isActive,
            $_POST['starts_at'] ?? null,
            $_POST['ends_at'] ?? null,
            (int)($_POST['sort_order'] ?? 0),
            $id,
        ]);

        // Enforce single active exit-intent: deactivate others
        if ($placement === 'exit_intent' && $isActive === 1) {
            $db->prepare('UPDATE oretir_promotions SET is_active = 0 WHERE placement = ? AND is_active = 1 AND id != ?')
               ->execute(['exit_intent', $id]);
        }

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
