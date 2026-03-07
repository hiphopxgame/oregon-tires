<?php
/**
 * Dynamic Sitemap Generator — Oregon Tires Auto Care
 * Generates valid sitemap XML with xhtml:link hreflang for bilingual (EN/ES) support.
 * Lightweight: no bootstrap dependency. DB connection optional (blog posts only).
 */

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$baseUrl = 'https://oregon.tires';

// ---------------------------------------------------------------------------
// Helper: generate a single <url> block with optional hreflang
// ---------------------------------------------------------------------------
function sitemapUrl(string $loc, float $priority, string $changefreq, string $lastmod, bool $bilingual = true): string {
    $xml  = "  <url>\n";
    $xml .= "    <loc>{$loc}</loc>\n";
    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
    $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
    $xml .= "    <priority>{$priority}</priority>\n";
    if ($bilingual) {
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$loc}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"es\" href=\"{$loc}?lang=es\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$loc}\" />\n";
    }
    $xml .= "  </url>\n";
    return $xml;
}

// ---------------------------------------------------------------------------
// Static pages
// ---------------------------------------------------------------------------
$staticPages = [
    ['path' => '/',                 'priority' => 1.0, 'freq' => 'weekly'],
    ['path' => '/book-appointment/','priority' => 0.9, 'freq' => 'weekly'],
    ['path' => '/contact',          'priority' => 0.8, 'freq' => 'monthly'],
    ['path' => '/why-us',           'priority' => 0.8, 'freq' => 'monthly'],
    ['path' => '/care-plan',        'priority' => 0.8, 'freq' => 'monthly'],
    ['path' => '/fleet-services',   'priority' => 0.7, 'freq' => 'monthly'],
    ['path' => '/guarantee',        'priority' => 0.6, 'freq' => 'monthly'],
    ['path' => '/blog',             'priority' => 0.8, 'freq' => 'weekly'],
    ['path' => '/faq',              'priority' => 0.7, 'freq' => 'monthly'],
    ['path' => '/reviews',          'priority' => 0.7, 'freq' => 'monthly'],
    ['path' => '/service-areas',    'priority' => 0.7, 'freq' => 'monthly'],
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
    ['slug' => 'tires-se-portland',   'priority' => 0.8],
    ['slug' => 'tires-clackamas',     'priority' => 0.7],
    ['slug' => 'tires-happy-valley',  'priority' => 0.7],
    ['slug' => 'tires-milwaukie',     'priority' => 0.7],
    ['slug' => 'tires-lents',         'priority' => 0.7],
    ['slug' => 'tires-woodstock',     'priority' => 0.7],
    ['slug' => 'tires-foster-powell', 'priority' => 0.7],
    ['slug' => 'tires-mt-scott',      'priority' => 0.7],
];

// ---------------------------------------------------------------------------
// Utility pages
// ---------------------------------------------------------------------------
$utilityPages = [
    ['path' => '/status',   'priority' => 0.5, 'freq' => 'monthly'],
    ['path' => '/feedback', 'priority' => 0.4, 'freq' => 'monthly'],
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
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

// Static pages
foreach ($staticPages as $page) {
    echo sitemapUrl($baseUrl . $page['path'], $page['priority'], $page['freq'], $today, true);
}

// Service detail pages
foreach ($services as $slug) {
    echo sitemapUrl($baseUrl . '/' . $slug, 0.8, 'monthly', $today, true);
}

// Service area pages
foreach ($serviceAreas as $area) {
    echo sitemapUrl($baseUrl . '/' . $area['slug'], $area['priority'], 'monthly', $today, true);
}

// Blog posts (from DB, no hreflang — content is single-language)
foreach ($blogPosts as $post) {
    $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : $today;
    echo sitemapUrl($baseUrl . '/blog/' . htmlspecialchars($post['slug']), 0.7, 'monthly', $lastmod, false);
}

// Utility pages
foreach ($utilityPages as $page) {
    echo sitemapUrl($baseUrl . $page['path'], $page['priority'], $page['freq'], $today, true);
}

echo '</urlset>' . "\n";
