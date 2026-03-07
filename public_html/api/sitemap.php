<?php
/**
 * Dynamic Sitemap Generator — Oregon Tires Auto Care
 * Generates valid sitemap XML with xhtml:link hreflang for bilingual (EN/ES) support.
 * Lightweight: no bootstrap dependency. DB connection optional (blog posts only).
 */

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

$baseUrl = 'https://oregon.tires';
$docRoot = dirname(__DIR__);

// ---------------------------------------------------------------------------
// Helper: generate a single <url> block with optional hreflang
// ---------------------------------------------------------------------------
function sitemapUrl(string $loc, string $lastmod, bool $bilingual = true): string {
    $xml  = "  <url>\n";
    $xml .= "    <loc>{$loc}</loc>\n";
    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
    if ($bilingual) {
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$loc}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"es\" href=\"{$loc}?lang=es\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$loc}\" />\n";
    }
    $xml .= "  </url>\n";
    return $xml;
}

// Get real file modification date, fallback to deploy date
function fileLastmod(string $docRoot, string $path): string {
    // Map URL path to PHP file
    $file = $path === '/' ? '/index.php' : '/' . ltrim($path, '/') . '.php';
    $fullPath = $docRoot . $file;
    if (file_exists($fullPath)) {
        return date('Y-m-d', filemtime($fullPath));
    }
    // Check for directory index (e.g., /book-appointment/ → book-appointment/index.html)
    $dirIndex = $docRoot . '/' . trim($path, '/') . '/index.html';
    if (file_exists($dirIndex)) {
        return date('Y-m-d', filemtime($dirIndex));
    }
    return date('Y-m-d');
}

// ---------------------------------------------------------------------------
// Static pages
// ---------------------------------------------------------------------------
$staticPages = [
    '/',
    '/book-appointment/',
    '/contact',
    '/why-us',
    '/care-plan',
    '/fleet-services',
    '/guarantee',
    '/blog',
    '/faq',
    '/reviews',
    '/service-areas',
];

// ---------------------------------------------------------------------------
// Service detail pages
// ---------------------------------------------------------------------------
$services = [
    'tire-installation',
    'tire-repair',
    'wheel-alignment',
    'brake-service',
    'oil-change',
    'engine-diagnostics',
    'suspension-repair',
];

// ---------------------------------------------------------------------------
// Service area pages
// ---------------------------------------------------------------------------
$serviceAreas = [
    'tires-se-portland',
    'tires-clackamas',
    'tires-happy-valley',
    'tires-milwaukie',
    'tires-lents',
    'tires-woodstock',
    'tires-foster-powell',
    'tires-mt-scott',
];

// ---------------------------------------------------------------------------
// Try to load .env for DB connection (blog posts)
// ---------------------------------------------------------------------------
$dbConfig = null;
$envPaths = [
    [dirname(__DIR__, 3), '.env.oregon-tires'],
    [dirname(__DIR__), '.env'],
];

foreach ($envPaths as [$dir, $file]) {
    $envFile = $dir . '/' . $file;
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim(trim($value), '"\'');
        }
        if (!empty($env['DB_HOST']) && !empty($env['DB_NAME'])) {
            $dbConfig = $env;
            break;
        }
    }
}

// ---------------------------------------------------------------------------
// Fetch blog posts from DB (graceful failure)
// ---------------------------------------------------------------------------
$blogPosts = [];
if ($dbConfig) {
    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $dbConfig['DB_HOST'] ?? 'localhost',
            $dbConfig['DB_NAME'],
            $dbConfig['DB_CHARSET'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $dbConfig['DB_USER'] ?? '', $dbConfig['DB_PASSWORD'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $stmt = $pdo->query(
            "SELECT slug, updated_at FROM oretir_blog_posts WHERE status = 'published' ORDER BY updated_at DESC"
        );
        $blogPosts = $stmt->fetchAll();
    } catch (\Throwable $e) {
        // DB unavailable — skip blog posts silently
        $blogPosts = [];
    }
}

// ---------------------------------------------------------------------------
// Build XML output
// ---------------------------------------------------------------------------
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

// Static pages
foreach ($staticPages as $path) {
    echo sitemapUrl($baseUrl . $path, fileLastmod($docRoot, $path));
}

// Service detail pages
foreach ($services as $slug) {
    echo sitemapUrl($baseUrl . '/' . $slug, fileLastmod($docRoot, '/' . $slug));
}

// Service area pages
foreach ($serviceAreas as $slug) {
    echo sitemapUrl($baseUrl . '/' . $slug, fileLastmod($docRoot, '/' . $slug));
}

// Blog posts from DB (no hreflang — content is single-language per post)
foreach ($blogPosts as $post) {
    $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d');
    echo sitemapUrl($baseUrl . '/blog/' . htmlspecialchars($post['slug']), $lastmod, false);
}

echo '</urlset>' . "\n";
