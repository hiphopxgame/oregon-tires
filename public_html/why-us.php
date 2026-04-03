<?php
/**
 * Oregon Tires — Why Choose Us Page
 * Bilingual (EN/ES) trust-building page with comparison table, testimonials, certifications.
 */
$pageTitle = "Why Choose Oregon Tires Auto Care | Portland OR";
$pageTitleEs = "Por Qu&eacute; Elegir Oregon Tires Auto Care | Portland OR";
$pageDesc = "15+ years serving Portland with honest, bilingual auto care. 4.8-star rating, 150+ reviews, 12-month warranty. See why SE Portland drivers trust Oregon Tires.";
$pageDescEs = "M&aacute;s de 15 a&ntilde;os sirviendo a Portland con servicio automotriz honesto y biling&uuml;e. 4.8 estrellas, 150+ rese&ntilde;as, garant&iacute;a de 12 meses.";
$canonicalUrl = "https://oregon.tires/why-us";
require_once __DIR__ . '/includes/seo-lang.php';
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/seo-config.php';
$_rating = getAggregateRating();
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
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- LocalBusiness JSON-LD -->
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
    "geo": {"@type": "GeoCoordinates", "latitude": 45.46123, "longitude": -122.57895},
    "aggregateRating": {"@type": "AggregateRating", "ratingValue": "<?= $_rating['ratingValue'] ?>", "reviewCount": "<?= $_rating['reviewCount'] ?>", "bestRating": "5"},
    "openingHours": ["Mo-Sa 07:00-19:00"],
    "knowsLanguage": ["en", "es"],
    "priceRange": "$$",
    "foundingDate": "2008",
    "description": "Portland's trusted bilingual auto care shop. Tires, brakes, oil changes, and full auto repair with honest pricing and a 12-month warranty."
  }
  </script>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Why Us"}
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
            <li><a href="/" class="hover:text-amber-300">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="breadcrumbCurrent">Why Us</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">Why Choose Oregon Tires Auto Care</h1>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="heroSubtitle">Portland's trusted bilingual auto care since 2008. Honest pricing, expert service, and a team that treats you like family.</p>
        <div class="flex flex-wrap gap-3">
          <a href="/book-appointment/" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg">Get Your Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Call (503) 367-9714</a>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/90">
          <span class="flex items-center gap-1"><svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> 4.8 Stars &middot; 150+ Reviews</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span>Since 2008</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span>Se Habla Espa&ntilde;ol</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span>12-Month Warranty</span>
        </div>
      </div>
    </section>

    <!-- Trust Metrics Grid -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="text-3xl font-bold text-brand dark:text-green-400">15+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="metricYears">Years Experience</div>
          </div>
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="text-3xl font-bold text-brand dark:text-green-400">4.8/5</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="metricRating">Star Rating</div>
          </div>
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="text-3xl font-bold text-brand dark:text-green-400">150+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="metricReviews">Five-Star Reviews</div>
          </div>
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="text-3xl font-bold text-brand dark:text-green-400">12mo</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="metricWarranty">12K Mile Warranty</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Story -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="storyTitle">Our Story</h2>
        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
          <p data-t="storyText">Founded in 2008 on SE 82nd Avenue in Portland, Oregon Tires Auto Care was built on a simple promise: honest, affordable auto care for every driver, in the language they're most comfortable with. From day one, we've been a fully bilingual shop &mdash; serving our community in both English and Spanish. Over 15 years later, that commitment hasn't changed. We know your name, we know your car, and we stand behind every job with a 12-month, 12,000-mile warranty. That's the Oregon Tires difference.</p>
        </div>
      </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="compareTitle">Oregon Tires vs. Chain Stores</h2>
        <div class="overflow-x-auto">
          <table class="w-full bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-600">
            <thead>
              <tr class="bg-brand text-white">
                <th class="text-left px-5 py-3 font-semibold" data-t="compareFeature">Feature</th>
                <th class="text-center px-5 py-3 font-semibold">Oregon Tires</th>
                <th class="text-center px-5 py-3 font-semibold" data-t="compareChain">Chain Store</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-gray-100 dark:border-gray-600">
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowBilingual">Bilingual Service</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valBilingualUs">Full EN/ES</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg> <span data-t="valBilingualChain">Limited</span></span></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-750">
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowPricing">Honest Pricing</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valPricingUs">No hidden fees</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-amber-500 dark:text-amber-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> <span data-t="valPricingChain">Upselling common</span></span></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-600">
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowWarranty">Warranty</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valWarrantyUs">12mo / 12K miles</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-amber-500 dark:text-amber-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> <span data-t="valWarrantyChain">Varies</span></span></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-750">
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowWait">Wait Time</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valWaitUs">Same-day service</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg> <span data-t="valWaitChain">Days to weeks</span></span></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-600">
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowPersonal">Personal Attention</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valPersonalUs">Know your name</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg> <span data-t="valPersonalChain">Just a number</span></span></td>
              </tr>
              <tr>
                <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-200" data-t="rowMobile">Mobile Service</td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <span data-t="valMobileUs">We come to you</span></span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg> <span data-t="valMobileChain">Drop-off only</span></span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="reviewsTitle">What Our Customers Say</h2>
        <div class="grid md:grid-cols-3 gap-6">
          <!-- Maria G. -->
          <blockquote class="bg-gray-50 dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review1Text">"I needed four new tires and they had me in and out in under an hour. The price was way better than what the chain store quoted me, and the staff explained everything clearly. I'm a customer for life!"</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Maria G.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review1Service">Tire Installation</p>
          </blockquote>
          <!-- James T. -->
          <blockquote class="bg-gray-50 dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review2Text">"My brakes were squealing badly and I was worried it'd cost a fortune. Oregon Tires diagnosed the problem quickly, gave me an honest estimate, and had my car ready the same day. No surprises on the bill."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; James T.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review2Service">Brake Service</p>
          </blockquote>
          <!-- Carlos M. -->
          <blockquote class="bg-gray-50 dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review3Text">"Por fin encontr&eacute; un taller donde puedo hablar espa&ntilde;ol y me entienden perfectamente. The team is professional, fast, and truly cares about their customers. I've recommended them to my whole family."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Carlos M.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review3Service">Full Service &mdash; Bilingual</p>
          </blockquote>
        </div>
      </div>
    </section>

    <!-- Certifications -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="certTitle">Trusted &amp; Certified</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-brand/10 dark:bg-green-400/10 flex items-center justify-center mb-3">
              <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm" data-t="certASE">ASE Certified</span>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-brand/10 dark:bg-green-400/10 flex items-center justify-center mb-3">
              <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm" data-t="certBBB">BBB Accredited</span>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-brand/10 dark:bg-green-400/10 flex items-center justify-center mb-3">
              <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            </div>
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm" data-t="certBilingual">100% Bilingual</span>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-brand/10 dark:bg-green-400/10 flex items-center justify-center mb-3">
              <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm" data-t="certLocal">Locally Owned</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Ready to Experience the Difference?</h2>
        <p class="mb-6" data-t="ctaSubtitle">Book online or call for same-day service. Free estimates, no obligation.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Call (503) 367-9714</a>
          <a href="sms:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Text Us</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> Call Now
      </a>
      <a href="/book-appointment" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> Book Now
      </a>
    </div>
  </div>

  <!-- Bilingual Translation Script -->
  <script>
  (function(){
    var t = {
      breadcrumbCurrent: { en: 'Why Us', es: 'Por Qu\u00e9 Nosotros' },
      heroTitle: { en: 'Why Choose Oregon Tires Auto Care', es: 'Por Qu\u00e9 Elegir Oregon Tires Auto Care' },
      heroSubtitle: { en: "Portland\u2019s trusted bilingual auto care since 2008. Honest pricing, expert service, and a team that treats you like family.", es: "El servicio automotriz biling\u00fce de confianza en Portland desde 2008. Precios honestos, servicio experto y un equipo que te trata como familia." },
      metricYears: { en: 'Years Experience', es: 'A\u00f1os de Experiencia' },
      metricRating: { en: 'Star Rating', es: 'Calificaci\u00f3n' },
      metricReviews: { en: 'Five-Star Reviews', es: 'Rese\u00f1as de Cinco Estrellas' },
      metricWarranty: { en: '12K Mile Warranty', es: '12K Millas de Garant\u00eda' },
      storyTitle: { en: 'Our Story', es: 'Nuestra Historia' },
      storyText: { en: "Founded in 2008 on SE 82nd Avenue in Portland, Oregon Tires Auto Care was built on a simple promise: honest, affordable auto care for every driver, in the language they\u2019re most comfortable with. From day one, we\u2019ve been a fully bilingual shop \u2014 serving our community in both English and Spanish. Over 15 years later, that commitment hasn\u2019t changed. We know your name, we know your car, and we stand behind every job with a 12-month, 12,000-mile warranty. That\u2019s the Oregon Tires difference.", es: "Fundado en 2008 en la Avenida SE 82nd en Portland, Oregon Tires Auto Care se construy\u00f3 sobre una promesa simple: servicio automotriz honesto y accesible para cada conductor, en el idioma en el que se sienta m\u00e1s c\u00f3modo. Desde el primer d\u00eda, hemos sido un taller completamente biling\u00fce \u2014 sirviendo a nuestra comunidad en ingl\u00e9s y espa\u00f1ol. M\u00e1s de 15 a\u00f1os despu\u00e9s, ese compromiso no ha cambiado. Conocemos tu nombre, conocemos tu carro, y respaldamos cada trabajo con una garant\u00eda de 12 meses y 12,000 millas. Esa es la diferencia de Oregon Tires." },
      compareTitle: { en: 'Oregon Tires vs. Chain Stores', es: 'Oregon Tires vs. Cadenas Comerciales' },
      compareFeature: { en: 'Feature', es: 'Caracter\u00edstica' },
      compareChain: { en: 'Chain Store', es: 'Cadena Comercial' },
      rowBilingual: { en: 'Bilingual Service', es: 'Servicio Biling\u00fce' },
      rowPricing: { en: 'Honest Pricing', es: 'Precios Honestos' },
      rowWarranty: { en: 'Warranty', es: 'Garant\u00eda' },
      rowWait: { en: 'Wait Time', es: 'Tiempo de Espera' },
      rowPersonal: { en: 'Personal Attention', es: 'Atenci\u00f3n Personal' },
      rowMobile: { en: 'Mobile Service', es: 'Servicio M\u00f3vil' },
      valBilingualUs: { en: 'Full EN/ES', es: 'EN/ES Completo' },
      valBilingualChain: { en: 'Limited', es: 'Limitado' },
      valPricingUs: { en: 'No hidden fees', es: 'Sin cargos ocultos' },
      valPricingChain: { en: 'Upselling common', es: 'Ventas adicionales frecuentes' },
      valWarrantyUs: { en: '12mo / 12K miles', es: '12 meses / 12K millas' },
      valWarrantyChain: { en: 'Varies', es: 'Var\u00eda' },
      valWaitUs: { en: 'Same-day service', es: 'Servicio el mismo d\u00eda' },
      valWaitChain: { en: 'Days to weeks', es: 'D\u00edas a semanas' },
      valPersonalUs: { en: 'Know your name', es: 'Conocen tu nombre' },
      valPersonalChain: { en: 'Just a number', es: 'Solo un n\u00famero' },
      valMobileUs: { en: 'We come to you', es: 'Vamos a ti' },
      valMobileChain: { en: 'Drop-off only', es: 'Solo en taller' },
      reviewsTitle: { en: 'What Our Customers Say', es: 'Lo Que Dicen Nuestros Clientes' },
      review1Text: { en: "\u201cI needed four new tires and they had me in and out in under an hour. The price was way better than what the chain store quoted me, and the staff explained everything clearly. I\u2019m a customer for life!\u201d", es: "\u201cNecesitaba cuatro llantas nuevas y me atendieron en menos de una hora. El precio fue mucho mejor que lo que me cotiz\u00f3 la cadena, y el personal me explic\u00f3 todo claramente. \u00a1Soy cliente de por vida!\u201d" },
      review1Service: { en: 'Tire Installation', es: 'Instalaci\u00f3n de Llantas' },
      review2Text: { en: "\u201cMy brakes were squealing badly and I was worried it\u2019d cost a fortune. Oregon Tires diagnosed the problem quickly, gave me an honest estimate, and had my car ready the same day. No surprises on the bill.\u201d", es: "\u201cMis frenos rechinaban mucho y me preocupaba que costara una fortuna. Oregon Tires diagnostic\u00f3 el problema r\u00e1pidamente, me dieron un presupuesto honesto y tuvieron mi carro listo el mismo d\u00eda. Sin sorpresas en la cuenta.\u201d" },
      review2Service: { en: 'Brake Service', es: 'Servicio de Frenos' },
      review3Text: { en: "\u201cI finally found a shop where I can speak Spanish and they understand me perfectly. The team is professional, fast, and truly cares about their customers. I\u2019ve recommended them to my whole family.\u201d", es: "\u201cPor fin encontr\u00e9 un taller donde puedo hablar espa\u00f1ol y me entienden perfectamente. El equipo es profesional, r\u00e1pido y realmente se preocupa por sus clientes. Los he recomendado a toda mi familia.\u201d" },
      review3Service: { en: 'Full Service \u2014 Bilingual', es: 'Servicio Completo \u2014 Biling\u00fce' },
      certTitle: { en: 'Trusted & Certified', es: 'Confiable y Certificado' },
      certASE: { en: 'ASE Certified', es: 'Certificado ASE' },
      certBBB: { en: 'BBB Accredited', es: 'Acreditado BBB' },
      certBilingual: { en: '100% Bilingual', es: '100% Biling\u00fce' },
      certLocal: { en: 'Locally Owned', es: 'Negocio Local' },
      ctaTitle: { en: 'Ready to Experience the Difference?', es: '\u00bfListo para Experimentar la Diferencia?' },
      ctaSubtitle: { en: 'Book online or call for same-day service. Free estimates, no obligation.', es: 'Reserva en l\u00ednea o llama para servicio el mismo d\u00eda. Presupuestos gratis, sin compromiso.' }
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
  })();
  </script>
</body>
</html>
