<?php
/**
 * Oregon Tires — Blog Posts Endpoint
 * GET /api/blog.php          — paginated list of published posts
 * GET /api/blog.php?slug=X   — single published post by slug
 *
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();

    $slug = sanitize((string) ($_GET['slug'] ?? ''), 200);

    if ($slug !== '') {
        // Single post by slug
        $stmt = $db->prepare(
            'SELECT id, slug, title_en, title_es, excerpt_en, excerpt_es, body_en, body_es,
                    featured_image, author, status, published_at, created_at
             FROM oretir_blog_posts
             WHERE slug = ? AND status = ?
             LIMIT 1'
        );
        $stmt->execute([$slug, 'published']);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            jsonError('Post not found', 404);
        }

        // Fetch categories for this post
        $catStmt = $db->prepare(
            'SELECT c.slug, c.name_en, c.name_es
             FROM oretir_blog_categories c
             JOIN oretir_blog_post_categories pc ON pc.category_id = c.id
             WHERE pc.post_id = ?'
        );
        $catStmt->execute([$post['id']]);
        $post['categories'] = $catStmt->fetchAll(\PDO::FETCH_ASSOC);

        jsonSuccess($post);
    }

    // Paginated list
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(20, max(1, (int) ($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $countStmt = $db->query("SELECT COUNT(*) FROM oretir_blog_posts WHERE status = 'published'");
    $total = (int) $countStmt->fetchColumn();

    $stmt = $db->prepare(
        'SELECT id, slug, title_en, title_es, excerpt_en, excerpt_es,
                featured_image, author, published_at
         FROM oretir_blog_posts
         WHERE status = ?
         ORDER BY published_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute(['published', $limit, $offset]);
    $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    jsonSuccess([
        'posts' => $posts,
        'total' => $total,
        'page'  => $page,
        'pages' => (int) ceil($total / max(1, $limit)),
    ]);

} catch (\Throwable $e) {
    error_log('blog.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
