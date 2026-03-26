<?php
/**
 * Oregon Tires — Promotions Page (Server-Side Rendered for SEO)
 * URL: /promotions → listing, /promotions/{id} → detail
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/seo-lang.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$db = getDB();

// ── Single promotion detail ─────────────────────────────────────────────────
if ($id > 0) {
    $stmt = $db->prepare(
        'SELECT id, image_url, title_en, title_es, body_en, body_es, badge_text_en, badge_text_es,
                bg_color, text_color, cta_url, cta_text_en, cta_text_es, sort_order, created_at
         FROM oretir_promotions
         WHERE id = ? AND is_active = 1
           AND (starts_at IS NULL OR starts_at <= NOW())
           AND (ends_at IS NULL OR ends_at >= NOW())
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promo) {
        http_response_code(404);
        include __DIR__ . '/404.html';
        exit;
    }

    // Fetch other promotions for sidebar
    $otherStmt = $db->prepare(
        'SELECT id, image_url, title_en, title_es, body_en, body_es, badge_text_en, bg_color, text_color
         FROM oretir_promotions
         WHERE is_active = 1 AND id != ?
           AND (starts_at IS NULL OR starts_at <= NOW())
           AND (ends_at IS NULL OR ends_at >= NOW())
         ORDER BY sort_order ASC, id DESC
         LIMIT 4'
    );
    $otherStmt->execute([$id]);
    $otherPromos = $otherStmt->fetchAll(PDO::FETCH_ASSOC);

    $canonicalUrl = 'https://oregon.tires/promotions/' . $promo['id'];
    $titleEn = htmlspecialchars($promo['title_en'] ?? 'Promotion');
    $titleEs = htmlspecialchars($promo['title_es'] ?? $promo['title_en'] ?? 'Promocion');
    $descEn = htmlspecialchars($promo['body_en'] ?? '');
    $descEs = htmlspecialchars($promo['body_es'] ?? $promo['body_en'] ?? '');
    $ogImage = $promo['image_url'] ? htmlspecialchars($promo['image_url']) : 'https://oregon.tires/assets/og-image.jpg';

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title"><?= seoMeta($titleEn, $titleEs) ?> | Oregon Tires Promotions</title>
  <meta name="description" id="page-desc" content="<?= seoMeta($descEn, $descEs) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= seoMeta($titleEn, $titleEs) ?>">
  <meta property="og:description" content="<?= seoMeta($descEn, $descEs) ?>">
  <meta property="og:locale" content="<?= seoOgLocale() ?>">
  <meta property="og:url" content="<?= $canonicalUrl ?>">
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:type" content="article">
  <link rel="stylesheet" href="/assets/styles.css">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
  <meta name="theme-color" content="#15803d">
  <style>
    html { scroll-behavior: smooth; }
    :root { --brand-primary: #15803d; --brand-dark: #0D3618; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  </style>
  <!-- GA4 -->
  <script>
  (function(){
    var id = 'G-PCK6ZYFHQ0';
    try { var c = localStorage.getItem('oregontires_ga_id'); if (c) id = c; } catch(e){}
    var s = document.createElement('script'); s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);} window.gtag = gtag;
    gtag('js', new Date()); gtag('config', id);
  })();
  </script>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Promotions", "item": "https://oregon.tires/promotions"},
      {"@type": "ListItem", "position": 3, "name": <?= json_encode($promo['title_en'] ?? 'Promotion', JSON_UNESCAPED_UNICODE) ?>}
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
      <div class="container mx-auto px-4 relative z-10 max-w-4xl">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="home">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="/promotions" class="hover:text-amber-300" data-t="promotions">Promotions</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium truncate max-w-xs" id="breadcrumb-title"><?= $titleEn ?></li>
          </ol>
        </nav>
        <h1 class="text-2xl md:text-4xl font-bold" id="promo-title" data-title-es="<?= $titleEs ?>"><?= $titleEn ?></h1>
      </div>
    </section>

    <!-- Promotion Detail -->
    <section class="py-10 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Main content -->
          <div class="lg:col-span-2">
            <?php if ($promo['image_url']): ?>
            <div class="mb-6 rounded-xl overflow-hidden shadow-lg">
              <img src="<?= htmlspecialchars($promo['image_url']) ?>" alt="<?= $titleEn ?>" class="w-full h-auto" loading="eager">
            </div>
            <?php endif; ?>

            <?php if ($promo['badge_text_en']): ?>
            <div class="mb-4">
              <span class="inline-block px-3 py-1 rounded-full text-sm font-bold" style="background-color: <?= htmlspecialchars($promo['bg_color'] ?? '#f59e0b') ?>; color: <?= htmlspecialchars($promo['text_color'] ?? '#000') ?>" id="promo-badge" data-badge-es="<?= htmlspecialchars($promo['badge_text_es'] ?? '') ?>"><?= htmlspecialchars($promo['badge_text_en']) ?></span>
            </div>
            <?php endif; ?>

            <div class="prose dark:prose-invert max-w-none">
              <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed" id="promo-desc-en"><?= nl2br($descEn) ?></p>
              <?php if ($promo['body_es']): ?>
              <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed hidden" id="promo-desc-es"><?= nl2br($descEs) ?></p>
              <?php endif; ?>
            </div>

            <?php if ($promo['cta_url']): ?>
            <div class="mt-6">
              <a href="<?= htmlspecialchars($promo['cta_url']) ?>" class="inline-block px-6 py-3 rounded-lg font-semibold text-lg transition shadow-lg hover:shadow-xl" style="background-color: <?= htmlspecialchars($promo['bg_color'] ?? '#15803d') ?>; color: <?= htmlspecialchars($promo['text_color'] ?? '#fff') ?>" id="promo-cta" data-cta-es="<?= htmlspecialchars($promo['cta_text_es'] ?? '') ?>"><?= htmlspecialchars($promo['cta_text_en'] ?? 'Book Now') ?></a>
            </div>
            <?php endif; ?>

            <!-- Share + Back -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between">
              <a href="/promotions" class="text-brand dark:text-green-400 font-medium hover:underline" data-t="backToPromotions">&larr; Back to All Promotions</a>
              <button onclick="copyPromoUrl()" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm" id="share-btn" data-t="sharePromotion">Share This Deal</button>
            </div>
          </div>

          <!-- Sidebar: Other Promotions -->
          <?php if (!empty($otherPromos)): ?>
          <aside class="lg:col-span-1">
            <h2 class="text-lg font-bold text-brand dark:text-green-400 mb-4" data-t="otherPromotions">Other Promotions</h2>
            <div class="space-y-4">
              <?php foreach ($otherPromos as $op): ?>
              <a href="/promotions/<?= (int) $op['id'] ?>" class="block bg-gray-50 dark:bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-md transition-shadow group">
                <?php if ($op['image_url']): ?>
                <div class="h-32 overflow-hidden">
                  <img src="<?= htmlspecialchars($op['image_url']) ?>" alt="<?= htmlspecialchars($op['title_en'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="p-3">
                  <h3 class="font-semibold text-brand dark:text-green-400 text-sm group-hover:underline" data-sidebar-es="<?= htmlspecialchars($op['title_es'] ?? '') ?>"><?= htmlspecialchars($op['title_en'] ?? '') ?></h3>
                </div>
              </a>
              <?php endforeach; ?>
            </div>
          </aside>
          <?php endif; ?>
        </div>
      </div>
    </section>

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
      home:             { en: 'Home',                   es: 'Inicio' },
      promotions:       { en: 'Promotions',             es: 'Promociones' },
      backToPromotions: { en: '\u2190 Back to All Promotions', es: '\u2190 Volver a Todas las Promociones' },
      sharePromotion:   { en: 'Share This Deal',        es: 'Compartir Esta Oferta' },
      otherPromotions:  { en: 'Other Promotions',       es: 'Otras Promociones' },
      copied:           { en: 'Link copied!',           es: 'Enlace copiado!' },
      ctaTitle:         { en: 'Need Auto Service in Portland?', es: '\u00bfNecesita Servicio Automotriz en Portland?' },
      ctaDesc:          { en: 'Book online or call for same-day service. Free estimates, no obligation.', es: 'Reserve en l\u00ednea o llame para servicio el mismo d\u00eda. Estimados gratis, sin compromiso.' },
      bookEstimate:     { en: 'Book Free Estimate',     es: 'Reserve Estimado Gratis' },
      callUs:           { en: 'Call (503) 367-9714',    es: 'Llame (503) 367-9714' },
      callNow:          { en: 'Call Now',               es: 'Llamar' },
      bookNow:          { en: 'Book Now',               es: 'Reservar' }
    };

    // Apply translations
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
    });

    // Switch to Spanish content
    if (currentLang === 'es') {
      var titleEl = document.getElementById('promo-title');
      if (titleEl && titleEl.dataset.titleEs) {
        titleEl.textContent = titleEl.dataset.titleEs;
        document.getElementById('breadcrumb-title').textContent = titleEl.dataset.titleEs;
        document.title = titleEl.dataset.titleEs + ' | Oregon Tires Promociones';
      }
      var descEn = document.getElementById('promo-desc-en');
      var descEs = document.getElementById('promo-desc-es');
      if (descEn && descEs) {
        descEn.classList.add('hidden');
        descEs.classList.remove('hidden');
      }
      document.querySelectorAll('[data-sidebar-es]').forEach(function(el) {
        if (el.dataset.sidebarEs) el.textContent = el.dataset.sidebarEs;
      });
      var badgeEl = document.getElementById('promo-badge');
      if (badgeEl && badgeEl.dataset.badgeEs) badgeEl.textContent = badgeEl.dataset.badgeEs;
      var ctaEl = document.getElementById('promo-cta');
      if (ctaEl && ctaEl.dataset.ctaEs) ctaEl.textContent = ctaEl.dataset.ctaEs;
      var pageTitleEl = document.getElementById('page-title');
      if (pageTitleEl) pageTitleEl.textContent = '<?= addslashes($titleEs) ?> | Oregon Tires Promociones';
      var pageDescEl = document.getElementById('page-desc');
      if (pageDescEl) pageDescEl.setAttribute('content', '<?= addslashes($descEs) ?>');
    }

    // Share button
    window.copyPromoUrl = function() {
      var url = window.location.href.split('?')[0];
      navigator.clipboard.writeText(url).then(function() {
        var btn = document.getElementById('share-btn');
        var original = btn.textContent;
        btn.textContent = t.copied[currentLang];
        setTimeout(function() { btn.textContent = original; }, 2000);
      });
    };
  })();
  </script>
</body>
</html>
<?php
    exit;
}

// ── Promotions listing ──────────────────────────────────────────────────────
$stmt = $db->query(
    'SELECT id, image_url, title_en, title_es, body_en, body_es, badge_text_en, badge_text_es,
            bg_color, text_color, cta_url, cta_text_en, cta_text_es, sort_order, created_at
     FROM oretir_promotions
     WHERE is_active = 1
       AND (starts_at IS NULL OR starts_at <= NOW())
       AND (ends_at IS NULL OR ends_at >= NOW())
     ORDER BY sort_order ASC, id DESC'
);
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Promotions & Deals | Oregon Tires Auto Care';
$pageTitleEs = 'Promociones y Ofertas | Oregon Tires Auto Care';
$pageDesc = 'Current promotions, deals, and special offers from Oregon Tires Auto Care in Portland, OR.';
$pageDescEs = 'Promociones actuales, ofertas y ofertas especiales de Oregon Tires Auto Care en Portland, OR.';
$canonicalUrl = 'https://oregon.tires/promotions';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title"><?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?></title>
  <meta name="description" id="page-desc" content="<?= htmlspecialchars(seoMeta($pageDesc, $pageDescEs)) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(seoMeta($pageDesc, $pageDescEs)) ?>">
  <meta property="og:locale" content="<?= seoOgLocale() ?>">
  <meta property="og:url" content="<?= $canonicalUrl ?>">
  <meta property="og:image" content="https://oregon.tires/assets/og-image.jpg">
  <meta property="og:type" content="website">
  <link rel="stylesheet" href="/assets/styles.css">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
  <meta name="theme-color" content="#15803d">
  <style>
    html { scroll-behavior: smooth; }
    :root { --brand-primary: #15803d; --brand-dark: #0D3618; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  </style>
  <!-- GA4 -->
  <script>
  (function(){
    var id = 'G-PCK6ZYFHQ0';
    try { var c = localStorage.getItem('oregontires_ga_id'); if (c) id = c; } catch(e){}
    var s = document.createElement('script'); s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);} window.gtag = gtag;
    gtag('js', new Date()); gtag('config', id);
  })();
  </script>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Promotions"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-12 relative">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="home">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="promotions">Promotions</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold mb-2" data-t="promoTitle">Promotions & Deals</h1>
        <p class="text-lg opacity-90 max-w-2xl" data-t="promoSubtitle">Check out our latest deals and special offers on tires, brakes, oil changes, and more.</p>
      </div>
    </section>

    <!-- Promotions Grid -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <?php if (empty($promotions)): ?>
        <div class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400 text-lg" data-t="noPromos">No promotions available right now. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($promotions as $promo): ?>
          <a href="/promotions/<?= (int) $promo['id'] ?>" class="group bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <?php if ($promo['image_url']): ?>
            <div class="h-56 overflow-hidden bg-gray-200 dark:bg-gray-600">
              <img src="<?= htmlspecialchars($promo['image_url']) ?>" alt="<?= htmlspecialchars($promo['title_en'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
            </div>
            <?php else: ?>
            <div class="h-56 flex items-center justify-center" style="background-color: <?= htmlspecialchars($promo['bg_color'] ?? '#15803d') ?>">
              <span style="color: <?= htmlspecialchars($promo['text_color'] ?? '#fff') ?>; opacity: 0.4" class="text-5xl">&#127873;</span>
            </div>
            <?php endif; ?>
            <div class="p-5">
              <?php if ($promo['badge_text_en']): ?>
              <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold mb-2" style="background-color: <?= htmlspecialchars($promo['bg_color'] ?? '#f59e0b') ?>; color: <?= htmlspecialchars($promo['text_color'] ?? '#000') ?>"><?= htmlspecialchars($promo['badge_text_en']) ?></span>
              <?php endif; ?>
              <h2 class="text-lg font-bold text-brand dark:text-green-400 mb-2 group-hover:underline" data-card-es="<?= htmlspecialchars($promo['title_es'] ?? '') ?>"><?= htmlspecialchars($promo['title_en'] ?? '') ?></h2>
              <?php if ($promo['body_en']): ?>
              <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2" data-card-desc-es="<?= htmlspecialchars($promo['body_es'] ?? '') ?>"><?= htmlspecialchars($promo['body_en']) ?></p>
              <?php endif; ?>
              <span class="inline-block mt-3 text-sm font-semibold text-brand dark:text-green-400" data-t="viewDetails">View Details &rarr;</span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

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
      home:          { en: 'Home',              es: 'Inicio' },
      promotions:    { en: 'Promotions',        es: 'Promociones' },
      promoTitle:    { en: 'Promotions & Deals', es: 'Promociones y Ofertas' },
      promoSubtitle: { en: 'Check out our latest deals and special offers on tires, brakes, oil changes, and more.', es: 'Descubra nuestras ultimas ofertas y promociones especiales en llantas, frenos, cambios de aceite y mas.' },
      noPromos:      { en: 'No promotions available right now. Check back soon!', es: 'No hay promociones disponibles ahora. Vuelva pronto!' },
      viewDetails:   { en: 'View Details \u2192', es: 'Ver Detalles \u2192' },
      ctaTitle:      { en: 'Need Auto Service in Portland?', es: '\u00bfNecesita Servicio Automotriz en Portland?' },
      ctaDesc:       { en: 'Book online or call for same-day service. Free estimates, no obligation.', es: 'Reserve en l\u00ednea o llame para servicio el mismo d\u00eda. Estimados gratis, sin compromiso.' },
      bookEstimate:  { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      callUs:        { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      callNow:       { en: 'Call Now',           es: 'Llamar' },
      bookNow:       { en: 'Book Now',           es: 'Reservar' }
    };

    // Apply translations to static elements
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
    });

    if (currentLang === 'es') {
      document.getElementById('page-title').textContent = '<?= addslashes($pageTitleEs) ?>';
      document.getElementById('page-desc').setAttribute('content', '<?= addslashes($pageDescEs) ?>');
      // Swap card titles and descriptions
      document.querySelectorAll('[data-card-es]').forEach(function(el) {
        if (el.dataset.cardEs) el.textContent = el.dataset.cardEs;
      });
      document.querySelectorAll('[data-card-desc-es]').forEach(function(el) {
        if (el.dataset.cardDescEs) el.textContent = el.dataset.cardDescEs;
      });
    }
  })();
  </script>
</body>
</html>
