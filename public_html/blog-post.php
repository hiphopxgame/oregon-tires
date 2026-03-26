<?php
/**
 * Oregon Tires — Blog Post Page (Server-Side Rendered for SEO)
 * URL: /blog/{slug} -> blog-post.php?slug={slug}
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/seo-lang.php';

$slug = sanitize((string) ($_GET['slug'] ?? ''), 200);
if (!$slug) {
    http_response_code(404);
    include __DIR__ . '/404.html';
    exit;
}

$db = getDB();
$stmt = $db->prepare(
    'SELECT * FROM oretir_blog_posts WHERE slug = ? AND status = ? LIMIT 1'
);
$stmt->execute([$slug, 'published']);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/404.html';
    exit;
}

// Fetch categories
$catStmt = $db->prepare(
    'SELECT c.slug, c.name_en, c.name_es
     FROM oretir_blog_categories c
     JOIN oretir_blog_post_categories pc ON pc.category_id = c.id
     WHERE pc.post_id = ?'
);
$catStmt->execute([$post['id']]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent posts for sidebar / related
$recentStmt = $db->prepare(
    'SELECT slug, title_en, title_es, published_at
     FROM oretir_blog_posts
     WHERE status = ? AND slug != ?
     ORDER BY published_at DESC
     LIMIT 4'
);
$recentStmt->execute(['published', $slug]);
$recentPosts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// SEO data
$canonicalUrl = 'https://oregon.tires/blog/' . htmlspecialchars($post['slug']);
$publishedIso = $post['published_at'] ? date('c', strtotime($post['published_at'])) : '';
$updatedIso = $post['updated_at'] ? date('c', strtotime($post['updated_at'])) : $publishedIso;

// Stop sending JSON content-type (bootstrap sets it)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title"><?= htmlspecialchars(seoMeta($post['title_en'], $post['title_es'] ?? $post['title_en'])) ?> | Oregon Tires Blog</title>
  <meta name="description" id="page-desc" content="<?= htmlspecialchars(seoMeta($post['excerpt_en'] ?? '', $post['excerpt_es'] ?? $post['excerpt_en'] ?? '')) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars(seoMeta($post['title_en'], $post['title_es'] ?? $post['title_en'])) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(seoMeta($post['excerpt_en'] ?? '', $post['excerpt_es'] ?? $post['excerpt_en'] ?? '')) ?>">
  <meta property="og:locale" content="<?= seoOgLocale() ?>">
  <meta property="og:url" content="<?= $canonicalUrl ?>">
  <meta property="og:image" content="<?= $post['featured_image'] ? htmlspecialchars($post['featured_image']) : 'https://oregon.tires/assets/og-image.jpg' ?>">
  <meta property="og:type" content="article">
  <meta property="article:published_time" content="<?= $publishedIso ?>">
  <meta property="article:author" content="<?= htmlspecialchars($post['author'] ?? 'Oregon Tires') ?>">
  <link rel="stylesheet" href="/assets/styles.css">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
  <meta name="theme-color" content="#15803d">
  <style>
    html { scroll-behavior: smooth; }
    :root { --brand-primary: #15803d; --brand-dark: #0D3618; }
    .blog-body h2 { font-size: 1.5rem; font-weight: 700; margin: 1.5rem 0 0.75rem; }
    .blog-body h3 { font-size: 1.25rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
    .blog-body p { margin-bottom: 1rem; line-height: 1.75; }
    .blog-body ul, .blog-body ol { margin-bottom: 1rem; padding-left: 1.5rem; }
    .blog-body ul { list-style: disc; }
    .blog-body ol { list-style: decimal; }
    .blog-body li { margin-bottom: 0.5rem; line-height: 1.6; }
    .blog-body strong { font-weight: 600; }
    .dark .blog-body h2, .dark .blog-body h3 { color: #4ade80; }
  </style>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- Article JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": <?= json_encode($post['title_en'], JSON_UNESCAPED_UNICODE) ?>,
    "description": <?= json_encode($post['excerpt_en'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    "author": {
      "@type": "Organization",
      "name": <?= json_encode($post['author'] ?? 'Oregon Tires', JSON_UNESCAPED_UNICODE) ?>,
      "url": "https://oregon.tires"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Oregon Tires Auto Care",
      "url": "https://oregon.tires",
      "logo": {
        "@type": "ImageObject",
        "url": "https://oregon.tires/assets/logo.png"
      }
    },
    "datePublished": "<?= $publishedIso ?>",
    "dateModified": "<?= $updatedIso ?>",
    "mainEntityOfPage": "<?= $canonicalUrl ?>",
    "image": "<?= $post['featured_image'] ? htmlspecialchars($post['featured_image']) : 'https://oregon.tires/assets/og-image.jpg' ?>"
  }
  </script>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Blog", "item": "https://oregon.tires/blog"},
      {"@type": "ListItem", "position": 3, "name": <?= json_encode($post['title_en'], JSON_UNESCAPED_UNICODE) ?>}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-10 relative">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10 max-w-3xl">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="home">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="/blog" class="hover:text-amber-300" data-t="blog">Blog</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium truncate max-w-xs" id="breadcrumb-title"><?= htmlspecialchars($post['title_en']) ?></li>
          </ol>
        </nav>
        <h1 class="text-2xl md:text-4xl font-bold mb-3" id="post-title-en" data-title-es="<?= htmlspecialchars($post['title_es']) ?>"><?= htmlspecialchars($post['title_en']) ?></h1>
        <div class="flex flex-wrap items-center gap-3 text-sm text-white/80">
          <?php if ($post['published_at']): ?>
          <time datetime="<?= $publishedIso ?>" id="post-date">
            <?= date('F j, Y', strtotime($post['published_at'])) ?>
          </time>
          <?php endif; ?>
          <?php if ($post['author']): ?>
          <span class="text-white/50">|</span>
          <span><?= htmlspecialchars($post['author']) ?></span>
          <?php endif; ?>
          <?php if (!empty($categories)): ?>
          <span class="text-white/50">|</span>
          <?php foreach ($categories as $cat): ?>
          <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs" data-cat-es="<?= htmlspecialchars($cat['name_es']) ?>"><?= htmlspecialchars($cat['name_en']) ?></span>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Article Body -->
    <section class="py-10 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl">
        <?php if ($post['featured_image']): ?>
        <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
          <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title_en']) ?>" class="w-full h-auto" loading="eager">
        </div>
        <?php endif; ?>

        <!-- Language toggle -->
        <?php if (!empty($post['body_es'])): ?>
        <div class="flex gap-2 mb-6">
          <button onclick="setBlogLang('en')" id="lang-en-btn" class="px-4 py-1.5 rounded-full text-sm font-medium bg-brand text-white transition">English</button>
          <button onclick="setBlogLang('es')" id="lang-es-btn" class="px-4 py-1.5 rounded-full text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Espa&ntilde;ol</button>
        </div>
        <?php endif; ?>

        <article class="blog-body text-gray-700 dark:text-gray-300" id="blog-body-en">
          <?= $post['body_en'] ?>
        </article>

        <?php if (!empty($post['body_es'])): ?>
        <article class="blog-body text-gray-700 dark:text-gray-300 hidden" id="blog-body-es">
          <?= $post['body_es'] ?>
        </article>
        <?php endif; ?>

        <!-- Back to blog + CTA -->
        <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between">
          <a href="/blog" class="text-brand dark:text-green-400 font-medium hover:underline" data-t="backToBlog">&larr; Back to Blog</a>
          <a href="/book-appointment/" class="bg-amber-500 text-black px-6 py-2.5 rounded-lg font-semibold hover:bg-amber-600 transition shadow" data-t="bookService">Book a Service</a>
        </div>
      </div>
    </section>

    <!-- Recent Posts -->
    <?php if (!empty($recentPosts)): ?>
    <section class="py-10 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-xl font-bold text-brand dark:text-green-400 mb-6" data-t="moreArticles">More Articles</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <?php foreach ($recentPosts as $rp): ?>
          <a href="/blog/<?= htmlspecialchars($rp['slug']) ?>" class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow hover:shadow-md transition-shadow group">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              <?= $rp['published_at'] ? date('M j, Y', strtotime($rp['published_at'])) : '' ?>
            </p>
            <h3 class="font-semibold text-brand dark:text-green-400 group-hover:underline" data-recent-es="<?= htmlspecialchars($rp['title_es']) ?>"><?= htmlspecialchars($rp['title_en']) ?></h3>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Need Auto Service in Portland?</h2>
        <p class="mb-6" data-t="ctaDesc">Book online or call for same-day service. Free estimates, no obligation.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="bookEstimate">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="callUs">Call (503) 367-9714</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        &#x1F4DE; <span data-t="callNow">Call Now</span>
      </a>
      <a href="/book-appointment" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        &#x1F4C5; <span data-t="bookNow">Book Now</span>
      </a>
    </div>
  </div>

  <script>
  (function() {
    // ── Language ──
    var currentLang = 'en';
    try {
      var params = new URLSearchParams(window.location.search);
      var langParam = params.get('lang');
      if (langParam === 'es') currentLang = 'es';
      else {
        var saved = localStorage.getItem('oregontires_lang');
        if (saved === 'es') currentLang = 'es';
      }
    } catch(e) {}

    var t = {
      home:         { en: 'Home',            es: 'Inicio' },
      blog:         { en: 'Blog',            es: 'Blog' },
      backToBlog:   { en: '\u2190 Back to Blog', es: '\u2190 Volver al Blog' },
      bookService:  { en: 'Book a Service',  es: 'Reservar Servicio' },
      moreArticles: { en: 'More Articles',   es: 'M\u00e1s Art\u00edculos' },
      ctaTitle:     { en: 'Need Auto Service in Portland?', es: '\u00bfNecesita Servicio Automotriz en Portland?' },
      ctaDesc:      { en: 'Book online or call for same-day service. Free estimates, no obligation.', es: 'Reserve en l\u00ednea o llame para servicio el mismo d\u00eda. Estimados gratis, sin compromiso.' },
      bookEstimate: { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      callUs:       { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      callNow:      { en: 'Call Now',        es: 'Llamar' },
      bookNow:      { en: 'Book Now',        es: 'Reservar' }
    };

    // Apply translations
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
    });

    // Language toggle for blog body
    window.setBlogLang = function(lang) {
      var enBody = document.getElementById('blog-body-en');
      var esBody = document.getElementById('blog-body-es');
      var enBtn = document.getElementById('lang-en-btn');
      var esBtn = document.getElementById('lang-es-btn');
      var titleEl = document.getElementById('post-title-en');

      if (lang === 'es' && esBody) {
        enBody.classList.add('hidden');
        esBody.classList.remove('hidden');
        enBtn.className = 'px-4 py-1.5 rounded-full text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition';
        esBtn.className = 'px-4 py-1.5 rounded-full text-sm font-medium bg-brand text-white transition';
        if (titleEl && titleEl.dataset.titleEs) {
          titleEl.textContent = titleEl.dataset.titleEs;
          document.getElementById('breadcrumb-title').textContent = titleEl.dataset.titleEs;
        }
        // Swap category labels
        document.querySelectorAll('[data-cat-es]').forEach(function(el) {
          if (!el.dataset.catEn) el.dataset.catEn = el.textContent;
          el.textContent = el.dataset.catEs;
        });
        // Swap recent post titles
        document.querySelectorAll('[data-recent-es]').forEach(function(el) {
          if (!el.dataset.recentEn) el.dataset.recentEn = el.textContent;
          el.textContent = el.dataset.recentEs;
        });
        // Update page title
        var esTitleMeta = titleEl ? titleEl.dataset.titleEs : '';
        if (esTitleMeta) document.title = esTitleMeta + ' | Oregon Tires Blog';
      } else {
        if (esBody) esBody.classList.add('hidden');
        enBody.classList.remove('hidden');
        if (enBtn) enBtn.className = 'px-4 py-1.5 rounded-full text-sm font-medium bg-brand text-white transition';
        if (esBtn) esBtn.className = 'px-4 py-1.5 rounded-full text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition';
        if (titleEl) {
          titleEl.textContent = '<?= addslashes($post['title_en']) ?>';
          document.getElementById('breadcrumb-title').textContent = '<?= addslashes($post['title_en']) ?>';
        }
        document.querySelectorAll('[data-cat-es]').forEach(function(el) {
          if (el.dataset.catEn) el.textContent = el.dataset.catEn;
        });
        document.querySelectorAll('[data-recent-es]').forEach(function(el) {
          if (el.dataset.recentEn) el.textContent = el.dataset.recentEn;
        });
        document.title = '<?= addslashes($post['title_en']) ?> | Oregon Tires Blog';
      }
      currentLang = lang;
      // Re-apply static translations
      document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
      });
    };

    // Auto-set language on load
    if (currentLang === 'es') {
      setBlogLang('es');
    }

    // Format date for current language
    var dateEl = document.getElementById('post-date');
    if (dateEl && dateEl.getAttribute('datetime')) {
      var d = new Date(dateEl.getAttribute('datetime'));
      dateEl.textContent = d.toLocaleDateString(
        currentLang === 'es' ? 'es-US' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
      );
    }
  })();
  </script>
</body>
</html>
