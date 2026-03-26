<?php
/**
 * Oregon Tires — Reviews & Testimonials Page
 * Bilingual (EN/ES) reviews page with Google review CTA and customer testimonials.
 */
$pageTitle = 'Reviews & Testimonials | Oregon Tires Auto Care';
$pageTitleEs = 'Rese&ntilde;as y Testimonios | Oregon Tires Auto Care';
$pageDesc = 'Read what Portland drivers say about Oregon Tires Auto Care. 4.8-star rating with 150+ Google reviews. Bilingual English & Spanish service.';
$pageDescEs = 'Lee lo que dicen los conductores de Portland sobre Oregon Tires Auto Care. Calificaci&oacute;n de 4.8 estrellas con m&aacute;s de 150 rese&ntilde;as en Google.';
$canonicalUrl = 'https://oregon.tires/reviews';
require_once __DIR__ . '/includes/seo-lang.php';
?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= seoMeta($pageTitle, $pageTitleEs) ?></title>
  <meta name="description" content="<?= seoMeta($pageDesc, $pageDescEs) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= seoMeta($pageTitle, $pageTitleEs) ?>">
  <meta property="og:description" content="<?= seoMeta($pageDesc, $pageDescEs) ?>">
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

  <!-- AutomotiveBusiness JSON-LD with AggregateRating -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "AutomotiveBusiness",
    "name": "Oregon Tires Auto Care",
    "url": "https://oregon.tires",
    "telephone": "(503) 367-9714",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "8536 SE 82nd Ave",
      "addressLocality": "Portland",
      "addressRegion": "OR",
      "postalCode": "97266",
      "addressCountry": "US"
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.8",
      "reviewCount": "150",
      "bestRating": "5"
    }
  }
  </script>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Reviews"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-16 relative">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="home">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="breadcrumbCurrent">Reviews</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">What Our Customers Say</h1>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="heroSubtitle">Portland drivers trust Oregon Tires Auto Care for honest, bilingual service. See why we have a 4.8-star rating.</p>
        <!-- Star Rating Display -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
          <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
            <div class="flex" aria-label="4.8 out of 5 stars">
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <span class="text-2xl font-bold">4.8</span>
            <span class="text-white/80" data-t="heroReviewCount">150+ Google Reviews</span>
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <a href="https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg inline-flex items-center gap-2" data-t="leaveReview">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Leave Us a Review on Google
          </a>
          <a href="https://www.google.com/maps/place/?q=place_id:ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition inline-flex items-center gap-2" data-t="viewOnGoogle">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            View on Google Maps
          </a>
        </div>
      </div>
    </section>

    <!-- Rating Summary + Reviews -->
    <section class="py-10 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-6xl">

        <!-- Rating Summary Card -->
        <div id="rating-summary" class="bg-white dark:bg-gray-700 rounded-2xl shadow-md p-6 md:p-8 mb-8 border border-gray-200 dark:border-gray-600">
          <div class="flex flex-col md:flex-row md:items-center gap-6 md:gap-10">
            <!-- Big rating number -->
            <div class="text-center md:text-left flex-shrink-0">
              <div id="summary-rating" class="text-6xl font-extrabold text-brand dark:text-green-400 leading-none">4.8</div>
              <div class="flex justify-center md:justify-start mt-1 mb-1">
                <span class="text-yellow-400 text-2xl" id="summary-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400" id="summary-count" data-t="summaryCount">970+ reviews</div>
            </div>
            <!-- Rating distribution bars -->
            <div class="flex-1 min-w-0">
              <div id="rating-bars" class="space-y-1.5">
                <div class="flex items-center gap-2 text-sm" data-star="5">
                  <span class="w-8 text-right text-gray-600 dark:text-gray-300 font-medium">5</span>
                  <span class="text-yellow-400">&#9733;</span>
                  <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden cursor-pointer hover:opacity-80 transition" role="button" tabindex="0" aria-label="Filter 5 star reviews" data-t-aria="filterStarReviews5">
                    <div class="bg-yellow-400 h-full rounded-full transition-all duration-500" style="width:0%"></div>
                  </div>
                  <span class="w-8 text-right text-xs text-gray-500 dark:text-gray-400">0</span>
                </div>
                <div class="flex items-center gap-2 text-sm" data-star="4">
                  <span class="w-8 text-right text-gray-600 dark:text-gray-300 font-medium">4</span>
                  <span class="text-yellow-400">&#9733;</span>
                  <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden cursor-pointer hover:opacity-80 transition" role="button" tabindex="0" aria-label="Filter 4 star reviews" data-t-aria="filterStarReviews4">
                    <div class="bg-yellow-400 h-full rounded-full transition-all duration-500" style="width:0%"></div>
                  </div>
                  <span class="w-8 text-right text-xs text-gray-500 dark:text-gray-400">0</span>
                </div>
                <div class="flex items-center gap-2 text-sm" data-star="3">
                  <span class="w-8 text-right text-gray-600 dark:text-gray-300 font-medium">3</span>
                  <span class="text-yellow-400">&#9733;</span>
                  <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden cursor-pointer hover:opacity-80 transition" role="button" tabindex="0" aria-label="Filter 3 star reviews" data-t-aria="filterStarReviews3">
                    <div class="bg-yellow-400 h-full rounded-full transition-all duration-500" style="width:0%"></div>
                  </div>
                  <span class="w-8 text-right text-xs text-gray-500 dark:text-gray-400">0</span>
                </div>
                <div class="flex items-center gap-2 text-sm" data-star="2">
                  <span class="w-8 text-right text-gray-600 dark:text-gray-300 font-medium">2</span>
                  <span class="text-yellow-400">&#9733;</span>
                  <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden cursor-pointer hover:opacity-80 transition" role="button" tabindex="0" aria-label="Filter 2 star reviews" data-t-aria="filterStarReviews2">
                    <div class="bg-yellow-400 h-full rounded-full transition-all duration-500" style="width:0%"></div>
                  </div>
                  <span class="w-8 text-right text-xs text-gray-500 dark:text-gray-400">0</span>
                </div>
                <div class="flex items-center gap-2 text-sm" data-star="1">
                  <span class="w-8 text-right text-gray-600 dark:text-gray-300 font-medium">1</span>
                  <span class="text-yellow-400">&#9733;</span>
                  <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden cursor-pointer hover:opacity-80 transition" role="button" tabindex="0" aria-label="Filter 1 star reviews" data-t-aria="filterStarReviews1">
                    <div class="bg-yellow-400 h-full rounded-full transition-all duration-500" style="width:0%"></div>
                  </div>
                  <span class="w-8 text-right text-xs text-gray-500 dark:text-gray-400">0</span>
                </div>
              </div>
            </div>
            <!-- Write review CTA -->
            <div class="flex-shrink-0 text-center">
              <a href="https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow" data-t="leaveReviewBtn">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                Leave Us a Review
              </a>
            </div>
          </div>
        </div>

        <!-- Toolbar: filters + sort + count -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div class="flex flex-wrap items-center gap-2">
            <!-- Source filters -->
            <button data-filter="all" class="review-filter-btn px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 font-medium transition bg-brand text-white border-brand" data-t="filterAll">All</button>
            <button data-filter="google" class="review-filter-btn px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 font-medium transition hover:border-blue-400 dark:hover:border-blue-500" data-t="filterGoogle">
              <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg> Google</span>
            </button>
            <button data-filter="manual" class="review-filter-btn px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 font-medium transition hover:border-green-400 dark:hover:border-green-500" data-t="filterManual">Verified</button>
            <!-- Active star filter indicator (hidden by default) -->
            <button id="star-filter-badge" class="hidden px-3 py-1.5 text-sm rounded-full border border-amber-400 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium transition items-center gap-1">
              <span id="star-filter-label">5 &#9733;</span>
              <span class="ml-1 text-xs opacity-60 hover:opacity-100 cursor-pointer" id="clear-star-filter">&times;</span>
            </button>
          </div>
          <div class="flex items-center gap-3">
            <span id="reviews-showing" class="text-sm text-gray-500 dark:text-gray-400"></span>
            <select id="sort-select" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 cursor-pointer">
              <option value="newest" data-t="sortNewest">Newest</option>
              <option value="highest" data-t="sortHighest">Highest Rated</option>
              <option value="lowest" data-t="sortLowest">Lowest Rated</option>
            </select>
          </div>
        </div>

        <!-- Reviews list -->
        <div id="reviews-grid" class="grid md:grid-cols-2 gap-5" aria-live="polite">
          <div class="col-span-full text-center py-8 text-gray-400">Loading reviews...</div>
        </div>

        <!-- Empty state for filters -->
        <div id="reviews-empty" class="hidden text-center py-12">
          <div class="text-4xl mb-3">&#9734;</div>
          <p class="text-gray-500 dark:text-gray-400 text-lg" data-t="noMatchingReviews">No reviews match this filter</p>
          <button id="clear-all-filters" class="mt-3 text-brand dark:text-green-400 font-medium text-sm hover:underline" data-t="clearFilters">Clear all filters</button>
        </div>
      </div>
    </section>

    <!-- Why Customers Trust Us -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="trustTitle">Why Customers Trust Us</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">15+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statYears">Years in Portland</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">150+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statReviews">Google Reviews</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex justify-center gap-1 text-brand dark:text-green-400">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            </div>
            <div class="text-lg font-bold text-brand dark:text-green-400 mt-1">EN/ES</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statBilingual">Fully Bilingual</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">12mo</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statWarranty">12K Mile Warranty</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Ready to Experience the Difference?</h2>
        <p class="mb-6" data-t="ctaSubtitle">Join 150+ happy customers. Book online or call for same-day service.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="ctaBook">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaCall">Call (503) 367-9714</a>
          <a href="/contact" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaContact">Contact Us</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> <span data-t="callNow">Call Now</span>
      </a>
      <a href="/book-appointment" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> <span data-t="bookNow">Book Now</span>
      </a>
    </div>
  </div>

  <!-- Bilingual Translation + Dynamic Reviews Script -->
  <script>
  (function(){
    var t = {
      home:               { en: 'Home', es: 'Inicio' },
      breadcrumbCurrent:  { en: 'Reviews', es: 'Rese\u00f1as' },
      heroTitle:          { en: 'What Our Customers Say', es: 'Lo Que Dicen Nuestros Clientes' },
      heroSubtitle:       { en: 'Portland drivers trust Oregon Tires Auto Care for honest, bilingual service. See why we have a 4.8-star rating.', es: 'Los conductores de Portland conf\u00edan en Oregon Tires Auto Care por su servicio honesto y biling\u00fce. Descubre por qu\u00e9 tenemos una calificaci\u00f3n de 4.8 estrellas.' },
      heroReviewCount:    { en: '{{count}}+ Google Reviews', es: '{{count}}+ Rese\u00f1as en Google' },
      leaveReview:        { en: 'Leave Us a Review on Google', es: 'D\u00e9janos una Rese\u00f1a en Google' },
      viewOnGoogle:       { en: 'View on Google Maps', es: 'Ver en Google Maps' },
      testimonialsTitle:  { en: 'Customer Testimonials', es: 'Testimonios de Clientes' },
      sortNewest:         { en: 'Newest', es: 'M\u00e1s Recientes' },
      sortHighest:        { en: 'Highest Rated', es: 'Mejor Calificados' },
      leaveReviewBtn:     { en: 'Leave Us a Review on Google', es: 'D\u00e9janos una Rese\u00f1a en Google' },
      trustTitle:         { en: 'Why Customers Trust Us', es: 'Por Qu\u00e9 los Clientes Conf\u00edan en Nosotros' },
      statYears:          { en: 'Years in Portland', es: 'A\u00f1os en Portland' },
      statReviews:        { en: 'Google Reviews', es: 'Rese\u00f1as en Google' },
      statBilingual:      { en: 'Fully Bilingual', es: 'Completamente Biling\u00fce' },
      statWarranty:       { en: '12K Mile Warranty', es: '12K Millas de Garant\u00eda' },
      ctaTitle:           { en: 'Ready to Experience the Difference?', es: '\u00bfListo para Experimentar la Diferencia?' },
      ctaSubtitle:        { en: 'Join {{count}}+ happy customers. Book online or call for same-day service.', es: '\u00danete a m\u00e1s de {{count}} clientes satisfechos. Reserva en l\u00ednea o llama para servicio el mismo d\u00eda.' },
      ctaBook:            { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      ctaCall:            { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      ctaContact:         { en: 'Contact Us', es: 'Cont\u00e1ctenos' },
      callNow:            { en: 'Call Now', es: 'Llamar' },
      bookNow:            { en: 'Book Now', es: 'Reservar' },
      filterAll:          { en: 'All', es: 'Todas' },
      filterGoogle:       { en: 'Google', es: 'Google' },
      filterManual:       { en: 'Verified', es: 'Verificadas' },
      sortLowest:         { en: 'Lowest Rated', es: 'Menor Calificaci\u00f3n' },
      summaryCount:       { en: '970+ reviews', es: '970+ rese\u00f1as' },
      noMatchingReviews:  { en: 'No reviews match this filter', es: 'No hay rese\u00f1as con este filtro' },
      clearFilters:       { en: 'Clear all filters', es: 'Limpiar filtros' },
      googleBadge:        { en: 'Google', es: 'Google' },
      daysAgo:            { en: 'days ago', es: 'd\u00edas' },
      weeksAgo:           { en: 'weeks ago', es: 'semanas' },
      monthsAgo:          { en: 'months ago', es: 'meses' },
      filterStarReviews5: { en: 'Filter 5 star reviews', es: 'Filtrar rese\u00f1as de 5 estrellas' },
      filterStarReviews4: { en: 'Filter 4 star reviews', es: 'Filtrar rese\u00f1as de 4 estrellas' },
      filterStarReviews3: { en: 'Filter 3 star reviews', es: 'Filtrar rese\u00f1as de 3 estrellas' },
      filterStarReviews2: { en: 'Filter 2 star reviews', es: 'Filtrar rese\u00f1as de 2 estrellas' },
      filterStarReviews1: { en: 'Filter 1 star reviews', es: 'Filtrar rese\u00f1as de 1 estrella' }
    };
    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang');
    if (!lang) { lang = (navigator.language || '').startsWith('es') ? 'es' : 'en'; }
    window.currentLang = lang;
    if (lang === 'es') {
      document.documentElement.lang = 'es';
      document.title = '<?= $pageTitleEs ?>';
      var metaDesc = document.querySelector('meta[name="description"]');
      if (metaDesc) metaDesc.content = '<?= $pageDescEs ?>';
    }
    document.querySelectorAll('[data-t]').forEach(function(el){
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][lang]) el.textContent = t[key][lang];
    });
    document.querySelectorAll('[data-t-aria]').forEach(function(el){
      var key = el.getAttribute('data-t-aria');
      if (t[key] && t[key][lang]) el.setAttribute('aria-label', t[key][lang]);
    });

    // ─── Dynamic Reviews ──────────────────────────────────────
    var allReviews = [];
    var currentSort = 'newest';
    var currentSourceFilter = 'all';
    var currentStarFilter = 0;
    var TRUNCATE_LEN = 180;

    function formatRelativeDate(dateStr) {
      if (!dateStr) return '';
      var d = new Date(dateStr);
      var now = new Date();
      var diff = Math.floor((now - d) / (1000 * 60 * 60 * 24));
      if (diff < 1) return lang === 'es' ? 'Hoy' : 'Today';
      if (diff < 7) return (diff || 1) + ' ' + (t.daysAgo[lang] || 'days ago');
      if (diff < 30) return Math.floor(diff / 7) + ' ' + (t.weeksAgo[lang] || 'weeks ago');
      if (diff < 365) return Math.floor(diff / 30) + ' ' + (t.monthsAgo[lang] || 'months ago');
      return Math.floor(diff / 365) + (lang === 'es' ? ' a\u00f1o(s)' : ' year(s) ago');
    }

    function renderStars(rating) {
      return '\u2605'.repeat(rating) + '\u2606'.repeat(5 - rating);
    }

    // ─── Rating distribution bars ─────────────────────────────
    function updateRatingBars() {
      var counts = {5:0, 4:0, 3:0, 2:0, 1:0};
      var total = allReviews.length;
      allReviews.forEach(function(r) { counts[Number(r.rating) || 5]++; });
      for (var star = 5; star >= 1; star--) {
        var row = document.querySelector('[data-star="' + star + '"]');
        if (!row) continue;
        var fill = row.querySelector('.bg-yellow-400');
        var spans = row.querySelectorAll('span');
        var pct = total ? Math.round(counts[star] / total * 100) : 0;
        if (fill) fill.style.width = pct + '%';
        if (spans.length >= 4) spans[3].textContent = counts[star];
      }
    }

    // ─── Click rating bar to filter ───────────────────────────
    document.querySelectorAll('#rating-bars [data-star]').forEach(function(row) {
      var barEl = row.querySelector('[role="button"]');
      if (!barEl) return;
      barEl.addEventListener('click', function() {
        var star = Number(row.dataset.star);
        currentStarFilter = (currentStarFilter === star) ? 0 : star;
        updateStarFilterBadge();
        filterSortAndRender();
      });
    });

    function updateStarFilterBadge() {
      var badge = document.getElementById('star-filter-badge');
      if (!badge) return;
      if (currentStarFilter > 0) {
        badge.classList.remove('hidden');
        badge.classList.add('inline-flex');
        document.getElementById('star-filter-label').textContent = currentStarFilter + ' \u2605';
      } else {
        badge.classList.add('hidden');
        badge.classList.remove('inline-flex');
      }
    }

    document.getElementById('clear-star-filter').addEventListener('click', function(e) {
      e.stopPropagation();
      currentStarFilter = 0;
      updateStarFilterBadge();
      filterSortAndRender();
    });

    // ─── Source filter buttons ─────────────────────────────────
    document.querySelectorAll('.review-filter-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        currentSourceFilter = btn.dataset.filter;
        document.querySelectorAll('.review-filter-btn').forEach(function(b) {
          if (b.dataset.filter === currentSourceFilter) {
            b.className = 'review-filter-btn px-3 py-1.5 text-sm rounded-full border font-medium transition bg-brand text-white border-brand';
          } else {
            b.className = 'review-filter-btn px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 font-medium transition hover:border-gray-400';
          }
        });
        filterSortAndRender();
      });
    });

    // ─── Sort select ──────────────────────────────────────────
    document.getElementById('sort-select').addEventListener('change', function() {
      currentSort = this.value;
      filterSortAndRender();
    });

    // ─── Clear all filters ────────────────────────────────────
    document.getElementById('clear-all-filters').addEventListener('click', function() {
      currentSourceFilter = 'all';
      currentStarFilter = 0;
      updateStarFilterBadge();
      document.querySelectorAll('.review-filter-btn').forEach(function(b) {
        b.className = b.dataset.filter === 'all'
          ? 'review-filter-btn px-3 py-1.5 text-sm rounded-full border font-medium transition bg-brand text-white border-brand'
          : 'review-filter-btn px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 font-medium transition hover:border-gray-400';
      });
      filterSortAndRender();
    });

    // ─── Core filter + sort + render ──────────────────────────
    function getFilteredReviews() {
      return allReviews.filter(function(r) {
        if (currentSourceFilter === 'google' && r.source !== 'google') return false;
        if (currentSourceFilter === 'manual' && r.source !== 'manual') return false;
        if (currentStarFilter > 0 && Number(r.rating) !== currentStarFilter) return false;
        return true;
      });
    }

    function filterSortAndRender() {
      var filtered = getFilteredReviews();
      if (currentSort === 'highest') {
        filtered.sort(function(a, b) { return (Number(b.rating) || 5) - (Number(a.rating) || 5); });
      } else if (currentSort === 'lowest') {
        filtered.sort(function(a, b) { return (Number(a.rating) || 5) - (Number(b.rating) || 5); });
      } else {
        filtered.sort(function(a, b) {
          return new Date(b.google_published_at || b.created_at || 0) - new Date(a.google_published_at || a.created_at || 0);
        });
      }
      var grid = document.getElementById('reviews-grid');
      var emptyState = document.getElementById('reviews-empty');
      if (filtered.length === 0) {
        grid.classList.add('hidden');
        emptyState.classList.remove('hidden');
      } else {
        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');
      }
      var showingEl = document.getElementById('reviews-showing');
      if (showingEl) {
        var showLabel = lang === 'es' ? 'Mostrando' : 'Showing';
        var ofLabel = lang === 'es' ? 'de' : 'of';
        if (filtered.length === allReviews.length) {
          showingEl.textContent = filtered.length + ' ' + (lang === 'es' ? 'rese\u00f1as' : 'reviews');
        } else {
          showingEl.textContent = showLabel + ' ' + filtered.length + ' ' + ofLabel + ' ' + allReviews.length;
        }
      }
      renderReviewCards(filtered);
    }

    function renderReviewCards(reviews) {
      var grid = document.getElementById('reviews-grid');
      if (!grid) return;
      grid.textContent = '';
      reviews.forEach(function(r) {
        var reviewText = lang === 'es' ? (r.review_text_es || r.review_text_en) : r.review_text_en;
        var rating = Number(r.rating) || 5;
        var dateStr = r.google_published_at || r.created_at;
        var isLong = reviewText.length > TRUNCATE_LEN;

        var card = document.createElement('blockquote');
        card.className = 'bg-white dark:bg-gray-700 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-600 transition hover:shadow-md';

        // Top row: stars + source + date
        var topRow = document.createElement('div');
        topRow.className = 'flex items-center justify-between mb-3';
        var starsWrap = document.createElement('div');
        starsWrap.className = 'flex items-center gap-2';
        var starSpan = document.createElement('span');
        starSpan.className = 'text-yellow-400 text-lg';
        starSpan.setAttribute('aria-label', rating + ' out of 5 stars');
        starSpan.textContent = renderStars(rating);
        starsWrap.appendChild(starSpan);
        if (r.source === 'google') {
          var srcBadge = document.createElement('span');
          srcBadge.className = 'text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 font-medium';
          srcBadge.textContent = 'Google';
          starsWrap.appendChild(srcBadge);
        }
        topRow.appendChild(starsWrap);
        var dateEl = document.createElement('span');
        dateEl.className = 'text-xs text-gray-400 dark:text-gray-500 flex-shrink-0';
        dateEl.textContent = formatRelativeDate(dateStr);
        topRow.appendChild(dateEl);
        card.appendChild(topRow);

        // Review text with truncation
        var textEl = document.createElement('p');
        textEl.className = 'text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-3';
        if (isLong) {
          textEl.textContent = '\u201c' + reviewText.substring(0, TRUNCATE_LEN) + '...\u201d';
          var readMore = document.createElement('button');
          readMore.className = 'text-brand dark:text-green-400 text-xs font-medium ml-1 hover:underline';
          readMore.textContent = lang === 'es' ? 'Leer m\u00e1s' : 'Read more';
          readMore.addEventListener('click', (function(fullText, el, btn) {
            return function() {
              var expanded = el.dataset.expanded === '1';
              if (expanded) {
                el.textContent = '\u201c' + fullText.substring(0, TRUNCATE_LEN) + '...\u201d';
                el.appendChild(btn);
                btn.textContent = lang === 'es' ? 'Leer m\u00e1s' : 'Read more';
                el.dataset.expanded = '0';
              } else {
                el.textContent = '\u201c' + fullText + '\u201d';
                el.appendChild(btn);
                btn.textContent = lang === 'es' ? 'Leer menos' : 'Read less';
                el.dataset.expanded = '1';
              }
            };
          })(reviewText, textEl, readMore));
          textEl.appendChild(readMore);
        } else {
          textEl.textContent = '\u201c' + reviewText + '\u201d';
        }
        card.appendChild(textEl);

        // Author row
        var authorRow = document.createElement('div');
        authorRow.className = 'flex items-center gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-600';
        if (r.author_photo_url) {
          var avatar = document.createElement('img');
          avatar.src = r.author_photo_url;
          avatar.alt = '';
          avatar.className = 'w-9 h-9 rounded-full object-cover';
          avatar.loading = 'lazy';
          authorRow.appendChild(avatar);
        } else {
          var initials = document.createElement('span');
          initials.className = 'w-9 h-9 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-xs font-bold text-green-700 dark:text-green-300';
          initials.textContent = (r.customer_name || '?')[0].toUpperCase();
          authorRow.appendChild(initials);
        }
        var nameEl = document.createElement('cite');
        nameEl.className = 'text-gray-800 dark:text-gray-200 font-medium not-italic text-sm';
        nameEl.textContent = r.customer_name;
        authorRow.appendChild(nameEl);
        card.appendChild(authorRow);

        grid.appendChild(card);
      });
    }

    // ─── Load reviews from API ────────────────────────────────
    fetch('/api/testimonials.php?scope=all', { credentials: 'include' })
      .then(function(res) { return res.json(); })
      .then(function(json) {
        allReviews = (json.success && json.data) ? json.data : [];
        updateRatingBars();
        filterSortAndRender();
      })
      .catch(function(err) {
        console.error('Error loading reviews:', err);
      });

    // ─── Load stats and update hero + summary ─────────────────
    fetch('/api/testimonials.php?scope=stats', { credentials: 'include' })
      .then(function(res) { return res.json(); })
      .then(function(json) {
        if (!json.success || !json.data) return;
        var count = json.data.review_count || '150';
        var rating = json.data.rating_value || '4.8';
        // Hero
        var heroCount = document.querySelector('[data-t="heroReviewCount"]');
        if (heroCount) heroCount.textContent = (t.heroReviewCount[lang] || '{{count}}+ Google Reviews').replace('{{count}}', count);
        var heroRating = document.querySelector('.bg-white\\/10 .text-2xl');
        if (heroRating) heroRating.textContent = rating;
        // Summary card
        var summaryRating = document.getElementById('summary-rating');
        if (summaryRating) summaryRating.textContent = rating;
        var summaryCount = document.getElementById('summary-count');
        if (summaryCount) summaryCount.textContent = count + '+ ' + (lang === 'es' ? 'rese\u00f1as' : 'reviews');
        // Trust section
        document.querySelectorAll('.text-3xl.font-bold.text-brand').forEach(function(el) {
          if (el.textContent.includes('150')) el.textContent = count + '+';
        });
        // CTA
        var ctaSub = document.querySelector('[data-t="ctaSubtitle"]');
        if (ctaSub) ctaSub.textContent = (t.ctaSubtitle[lang] || '').replace('{{count}}', count);
      })
      .catch(function() {});
  })();
  </script>
</body>
</html>
