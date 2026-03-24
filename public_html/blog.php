<?php
/**
 * Oregon Tires — Blog Listing Page
 * Bilingual blog index with card grid and pagination.
 */
$pageTitle = 'Auto Care Blog | Oregon Tires';
$pageTitleEs = 'Blog de Servicio Automotriz | Oregon Tires';
$pageDesc = 'Expert tips on tires, brakes, oil changes, and car maintenance from Oregon Tires Auto Care in Portland, OR.';
$pageDescEs = 'Consejos expertos sobre llantas, frenos, cambios de aceite y mantenimiento de autos de Oregon Tires Auto Care en Portland, OR.';
$canonicalUrl = 'https://oregon.tires/blog';
require_once __DIR__ . '/includes/seo-lang.php';
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
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
  </style>
  <!-- GA4 -->
  <script>
  (function(){
    var id = 'G-CHYMTNB6LH';
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
      {"@type": "ListItem", "position": 2, "name": "Blog"}
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
            <li class="text-white font-medium" data-t="blog">Blog</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold mb-2" data-t="blogTitle">Auto Care Blog</h1>
        <p class="text-lg opacity-90 max-w-2xl" data-t="blogSubtitle">Expert tips on tires, brakes, maintenance, and more from the Oregon Tires team.</p>
      </div>
    </section>

    <!-- Blog Grid -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <div id="blog-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Cards rendered by JS -->
        </div>
        <div id="blog-loading" class="text-center py-12">
          <div class="inline-block w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full motion-safe:animate-spin"></div>
          <p class="mt-3 text-gray-500 dark:text-gray-400" data-t="loading">Loading articles...</p>
        </div>
        <div id="blog-empty" class="hidden text-center py-12">
          <p class="text-gray-500 dark:text-gray-400 text-lg" data-t="noPosts">No articles yet. Check back soon!</p>
        </div>
        <!-- Pagination -->
        <div id="blog-pagination" class="hidden flex justify-center items-center gap-4 mt-10">
          <button id="blog-prev" onclick="blogChangePage(-1)" class="px-5 py-2 rounded-lg bg-brand text-white font-medium hover:bg-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <span data-t="prev">&larr; Previous</span>
          </button>
          <span id="blog-page-info" class="text-sm text-gray-600 dark:text-gray-400"></span>
          <button id="blog-next" onclick="blogChangePage(1)" class="px-5 py-2 rounded-lg bg-brand text-white font-medium hover:bg-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <span data-t="next">Next &rarr;</span>
          </button>
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
      home:         { en: 'Home',          es: 'Inicio' },
      blog:         { en: 'Blog',          es: 'Blog' },
      blogTitle:    { en: 'Auto Care Blog', es: 'Blog de Servicio Automotriz' },
      blogSubtitle: { en: 'Expert tips on tires, brakes, maintenance, and more from the Oregon Tires team.', es: 'Consejos expertos sobre llantas, frenos, mantenimiento y mas del equipo de Oregon Tires.' },
      loading:      { en: 'Loading articles...', es: 'Cargando articulos...' },
      noPosts:      { en: 'No articles yet. Check back soon!', es: 'No hay articulos todavia. Vuelva pronto!' },
      prev:         { en: '\u2190 Previous', es: '\u2190 Anterior' },
      next:         { en: 'Next \u2192', es: 'Siguiente \u2192' },
      readMore:     { en: 'Read More', es: 'Leer Mas' },
      ctaTitle:     { en: 'Need Auto Service in Portland?', es: 'Necesita Servicio Automotriz en Portland?' },
      ctaDesc:      { en: 'Book online or call for same-day service. Free estimates, no obligation.', es: 'Reserve en linea o llame para servicio el mismo dia. Estimados gratis, sin compromiso.' },
      bookEstimate: { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      callUs:       { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      callNow:      { en: 'Call Now', es: 'Llamar' },
      bookNow:      { en: 'Book Now', es: 'Reservar' },
      pageOf:       { en: 'Page {page} of {pages}', es: 'Pagina {page} de {pages}' }
    };

    // Apply translations to static elements
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
    });

    if (currentLang === 'es') {
      document.getElementById('page-title').textContent = '<?= addslashes($pageTitleEs) ?>';
      document.getElementById('page-desc').setAttribute('content', '<?= addslashes($pageDescEs) ?>');
    }

    // ── Blog fetching ──
    var blogPage = 1;
    var blogPages = 1;

    function createBlogCard(post) {
      var title = currentLang === 'es' && post.title_es ? post.title_es : post.title_en;
      var excerpt = currentLang === 'es' && post.excerpt_es ? post.excerpt_es : (post.excerpt_en || '');
      var dateStr = '';
      if (post.published_at) {
        dateStr = new Date(post.published_at).toLocaleDateString(
          currentLang === 'es' ? 'es-US' : 'en-US',
          { year: 'numeric', month: 'long', day: 'numeric' }
        );
      }

      var card = document.createElement('a');
      card.href = '/blog/' + encodeURIComponent(post.slug);
      card.className = 'group bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow';

      // Image area
      var imgWrap = document.createElement('div');
      imgWrap.className = 'aspect-video overflow-hidden';
      if (post.featured_image) {
        imgWrap.className += ' bg-gray-200 dark:bg-gray-600';
        var img = document.createElement('img');
        img.src = post.featured_image;
        img.alt = title;
        img.className = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300';
        img.loading = 'lazy';
        imgWrap.appendChild(img);
      } else {
        imgWrap.className += ' bg-gradient-to-br from-green-700 to-green-900 flex items-center justify-center';
        var placeholder = document.createElement('div');
        placeholder.className = 'text-white/30 text-5xl';
        placeholder.textContent = '\u270D';
        imgWrap.appendChild(placeholder);
      }
      card.appendChild(imgWrap);

      // Content area
      var content = document.createElement('div');
      content.className = 'p-5';

      var meta = document.createElement('p');
      meta.className = 'text-xs text-gray-500 dark:text-gray-400 mb-2';
      meta.textContent = dateStr + (post.author ? ' \u00B7 ' + post.author : '');
      content.appendChild(meta);

      var h2 = document.createElement('h2');
      h2.className = 'text-lg font-bold text-brand dark:text-green-400 mb-2 group-hover:underline';
      h2.textContent = title;
      content.appendChild(h2);

      var p = document.createElement('p');
      p.className = 'text-sm text-gray-600 dark:text-gray-300 line-clamp-3';
      p.textContent = excerpt;
      content.appendChild(p);

      var readMore = document.createElement('span');
      readMore.className = 'inline-block mt-3 text-sm font-semibold text-brand dark:text-green-400';
      readMore.textContent = t.readMore[currentLang] + ' \u2192';
      content.appendChild(readMore);

      card.appendChild(content);
      return card;
    }

    function loadBlogPosts(page) {
      var grid = document.getElementById('blog-grid');
      var loading = document.getElementById('blog-loading');
      var empty = document.getElementById('blog-empty');
      var pagination = document.getElementById('blog-pagination');

      loading.classList.remove('hidden');
      grid.textContent = '';
      empty.classList.add('hidden');
      pagination.classList.add('hidden');

      fetch('/api/blog.php?page=' + page + '&limit=9')
        .then(function(r) { return r.json(); })
        .then(function(res) {
          loading.classList.add('hidden');

          if (!res.success || !res.data || !res.data.posts || res.data.posts.length === 0) {
            empty.classList.remove('hidden');
            return;
          }

          var posts = res.data.posts;
          blogPage = res.data.page;
          blogPages = res.data.pages;

          posts.forEach(function(post) {
            grid.appendChild(createBlogCard(post));
          });

          // Pagination
          if (blogPages > 1) {
            pagination.classList.remove('hidden');
            document.getElementById('blog-prev').disabled = (blogPage <= 1);
            document.getElementById('blog-next').disabled = (blogPage >= blogPages);
            document.getElementById('blog-page-info').textContent =
              t.pageOf[currentLang].replace('{page}', String(blogPage)).replace('{pages}', String(blogPages));
          }
        })
        .catch(function(err) {
          loading.classList.add('hidden');
          empty.classList.remove('hidden');
          console.error('Blog load error:', err);
        });
    }

    window.blogChangePage = function(delta) {
      var newPage = blogPage + delta;
      if (newPage >= 1 && newPage <= blogPages) {
        loadBlogPosts(newPage);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    // Initial load
    loadBlogPosts(1);
  })();
  </script>
</body>
</html>
