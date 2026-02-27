<?php
/**
 * Oregon Tires — Admin Blog Management Endpoint
 * GET    /api/admin/blog.php         — paginated list (all statuses)
 * GET    /api/admin/blog.php?id=N    — single post by ID
 * POST   /api/admin/blog.php         — create new post
 * PUT    /api/admin/blog.php         — update post (by id in body)
 * DELETE /api/admin/blog.php?id=N    — delete post
 *
 * Requires admin session + CSRF for mutations.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all posts or single by ID ──────────────────────────
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $stmt = $db->prepare(
                'SELECT id, slug, title_en, title_es, excerpt_en, excerpt_es, body_en, body_es,
                        featured_image, author, status, published_at, created_at, updated_at
                 FROM oretir_blog_posts WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                jsonError('Post not found.', 404);
            }

            // Fetch categories
            $catStmt = $db->prepare(
                'SELECT c.id, c.slug, c.name_en, c.name_es
                 FROM oretir_blog_categories c
                 JOIN oretir_blog_post_categories pc ON pc.category_id = c.id
                 WHERE pc.post_id = ?'
            );
            $catStmt->execute([$post['id']]);
            $post['categories'] = $catStmt->fetchAll(PDO::FETCH_ASSOC);

            jsonSuccess($post);
        }

        // Paginated list
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = sanitize((string) ($_GET['status'] ?? ''), 20);
        $search = sanitize((string) ($_GET['search'] ?? ''), 200);

        $where = [];
        $params = [];

        if ($status !== '' && in_array($status, ['draft', 'published'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        if ($search !== '') {
            $where[] = '(title_en LIKE ? OR title_es LIKE ? OR slug LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_blog_posts {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $params[] = $limit;
        $params[] = $offset;
        $stmt = $db->prepare(
            "SELECT id, slug, title_en, title_es, excerpt_en, excerpt_es,
                    featured_image, author, status, published_at, created_at, updated_at
             FROM oretir_blog_posts {$whereClause}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all categories for the UI
        $allCats = $db->query('SELECT id, slug, name_en, name_es FROM oretir_blog_categories ORDER BY name_en')
            ->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess([
            'posts'      => $posts,
            'total'      => $total,
            'page'       => $page,
            'pages'      => (int) ceil($total / max(1, $limit)),
            'categories' => $allCats,
        ]);
    }

    // ─── POST: Create new post ────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();

        $data = getJsonBody();
        $missing = requireFields($data, ['title_en', 'body_en']);
        if (!empty($missing)) {
            jsonError('Missing required fields: ' . implode(', ', $missing), 400);
        }

        $titleEn  = sanitize($data['title_en'], 255);
        $titleEs  = sanitize($data['title_es'] ?? '', 255);
        $slug     = !empty($data['slug']) ? sanitize($data['slug'], 200) : generateSlug($titleEn);
        $excerptEn = sanitize($data['excerpt_en'] ?? '', 1000);
        $excerptEs = sanitize($data['excerpt_es'] ?? '', 1000);
        $bodyEn   = $data['body_en'];   // Allow HTML
        $bodyEs   = $data['body_es'] ?? '';
        $image    = sanitize($data['featured_image'] ?? '', 500);
        $author   = sanitize($data['author'] ?? 'Oregon Tires', 100);
        $status   = in_array($data['status'] ?? '', ['draft', 'published'], true) ? $data['status'] : 'draft';

        // Ensure slug is unique
        $slug = ensureUniqueSlug($db, $slug);

        $publishedAt = null;
        if ($status === 'published') {
            $publishedAt = $data['published_at'] ?? date('Y-m-d H:i:s');
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_blog_posts
                (slug, title_en, title_es, excerpt_en, excerpt_es, body_en, body_es,
                 featured_image, author, status, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $slug, $titleEn, $titleEs, $excerptEn, $excerptEs,
            $bodyEn, $bodyEs, $image ?: null, $author, $status, $publishedAt,
        ]);

        $newId = (int) $db->lastInsertId();

        // Handle categories
        if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
            saveCategoryLinks($db, $newId, $data['category_ids']);
        }

        jsonSuccess(['id' => $newId, 'slug' => $slug], 201);
    }

    // ─── PUT: Update existing post ────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();

        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Valid post ID is required.', 400);
        }

        // Verify post exists
        $checkStmt = $db->prepare('SELECT id, slug, status FROM oretir_blog_posts WHERE id = ?');
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            jsonError('Post not found.', 404);
        }

        // Build dynamic UPDATE
        $updates = [];
        $params  = [];

        if (isset($data['title_en'])) {
            $updates[] = 'title_en = ?';
            $params[]  = sanitize($data['title_en'], 255);
        }
        if (isset($data['title_es'])) {
            $updates[] = 'title_es = ?';
            $params[]  = sanitize($data['title_es'], 255);
        }
        if (isset($data['slug'])) {
            $newSlug = sanitize($data['slug'], 200);
            if ($newSlug !== $existing['slug']) {
                $newSlug = ensureUniqueSlug($db, $newSlug, $id);
            }
            $updates[] = 'slug = ?';
            $params[]  = $newSlug;
        }
        if (isset($data['excerpt_en'])) {
            $updates[] = 'excerpt_en = ?';
            $params[]  = sanitize($data['excerpt_en'], 1000);
        }
        if (isset($data['excerpt_es'])) {
            $updates[] = 'excerpt_es = ?';
            $params[]  = sanitize($data['excerpt_es'], 1000);
        }
        if (isset($data['body_en'])) {
            $updates[] = 'body_en = ?';
            $params[]  = $data['body_en']; // Allow HTML
        }
        if (isset($data['body_es'])) {
            $updates[] = 'body_es = ?';
            $params[]  = $data['body_es'];
        }
        if (isset($data['featured_image'])) {
            $updates[] = 'featured_image = ?';
            $params[]  = sanitize($data['featured_image'], 500) ?: null;
        }
        if (isset($data['author'])) {
            $updates[] = 'author = ?';
            $params[]  = sanitize($data['author'], 100);
        }
        if (isset($data['status']) && in_array($data['status'], ['draft', 'published'], true)) {
            $updates[] = 'status = ?';
            $params[]  = $data['status'];

            // Set published_at when first published
            if ($data['status'] === 'published' && $existing['status'] === 'draft') {
                $updates[] = 'published_at = COALESCE(published_at, NOW())';
            }
        }
        if (isset($data['published_at'])) {
            $updates[] = 'published_at = ?';
            $params[]  = $data['published_at'];
        }

        if (empty($updates)) {
            jsonError('No fields to update.', 400);
        }

        $params[] = $id;
        $db->prepare('UPDATE oretir_blog_posts SET ' . implode(', ', $updates) . ' WHERE id = ?')
           ->execute($params);

        // Handle categories
        if (isset($data['category_ids']) && is_array($data['category_ids'])) {
            // Delete existing links, re-insert
            $db->prepare('DELETE FROM oretir_blog_post_categories WHERE post_id = ?')->execute([$id]);
            saveCategoryLinks($db, $id, $data['category_ids']);
        }

        jsonSuccess(['updated' => $id]);
    }

    // ─── DELETE: Remove a post ────────────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonError('Valid post ID is required.', 400);
        }

        $checkStmt = $db->prepare('SELECT id FROM oretir_blog_posts WHERE id = ?');
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            jsonError('Post not found.', 404);
        }

        // Category links cascade-deleted via FK
        $db->prepare('DELETE FROM oretir_blog_posts WHERE id = ?')->execute([$id]);

        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log('admin/blog.php error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}

// ─── Helper: Generate URL-safe slug from title ────────────────────────
function generateSlug(string $title): string
{
    $slug = mb_strtolower($title, 'UTF-8');
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'untitled-' . time();
}

// ─── Helper: Ensure slug is unique ─────────────────────────────────────
function ensureUniqueSlug(PDO $db, string $slug, int $excludeId = 0): string
{
    $base = $slug;
    $counter = 1;
    while (true) {
        $stmt = $db->prepare('SELECT id FROM oretir_blog_posts WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $base . '-' . (++$counter);
    }
    return $slug;
}

// ─── Helper: Save category links ───────────────────────────────────────
function saveCategoryLinks(PDO $db, int $postId, array $categoryIds): void
{
    $stmt = $db->prepare(
        'INSERT IGNORE INTO oretir_blog_post_categories (post_id, category_id) VALUES (?, ?)'
    );
    foreach ($categoryIds as $catId) {
        $catId = (int) $catId;
        if ($catId > 0) {
            $stmt->execute([$postId, $catId]);
        }
    }
}
