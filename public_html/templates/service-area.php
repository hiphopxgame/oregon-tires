<?php
/**
 * Oregon Tires — Service Area Landing Page Template
 * Variables expected: $areaName, $areaNameEs, $areaSlug, $areaSlugEs, $areaZip, $areaDescription, $areaDescriptionEs,
 * $landmarks (array of {name, distance}), $landmarksEs, $testimonial, $testimonialEs, $mapQuery, $nearbyAreas
 */
$pageTitle = "Tires & Auto Care in $areaName | Oregon Tires";
$pageTitleEs = "Llantas y Servicio Automotriz en $areaNameEs | Oregon Tires";
$canonicalUrl = "https://oregon.tires/$areaSlug";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($areaDescription) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($areaDescription) ?>">
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

  <!-- LocalBusiness + Service Area JSON-LD -->
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
    "aggregateRating": {"@type": "AggregateRating", "ratingValue": "4.8", "reviewCount": "150", "bestRating": "5"},
    "openingHours": ["Mo-Sa 07:00-19:00"],
    "areaServed": {
      "@type": "Place",
      "name": "<?= htmlspecialchars($areaName) ?>"
    },
    "knowsLanguage": ["en", "es"],
    "priceRange": "$$"
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Service Areas", "item": "https://oregon.tires/#services"},
      {"@type": "ListItem", "position": 3, "name": "<?= htmlspecialchars($areaName) ?>"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php include __DIR__ . '/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-16 relative" role="img" aria-label="<?= htmlspecialchars($areaName) ?> service area">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="/#services" class="hover:text-amber-300">Services</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="areaName"><?= htmlspecialchars($areaName) ?></li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">Tires & Auto Care in <?= htmlspecialchars($areaName) ?></h1>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="heroSubtitle"><?= htmlspecialchars($areaDescription) ?></p>
        <div class="flex flex-wrap gap-3">
          <a href="/book-appointment/" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg">Get Your Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Call (503) 367-9714</a>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/90">
          <span class="flex items-center gap-1"><svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> 4.8 Stars · 150+ Reviews</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span>Since 2008</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span>Se Habla Espa&ntilde;ol</span>
        </div>
      </div>
    </section>

    <!-- How to Find Us -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="howToFind">Conveniently Located Near <?= htmlspecialchars($areaName) ?></h2>
        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Oregon Tires Auto Care is located at <strong>8536 SE 82nd Ave, Portland, OR 97266</strong>, just minutes from <?= htmlspecialchars($areaName) ?>.</p>
            <h3 class="font-bold text-brand dark:text-green-400 mb-3">Nearby Landmarks</h3>
            <ul class="space-y-2">
              <?php foreach ($landmarks as $lm): ?>
              <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
                <span class="text-brand dark:text-green-400 mt-1" aria-hidden="true">&#x1F4CD;</span>
                <span><?= htmlspecialchars($lm['distance']) ?> from <?= htmlspecialchars($lm['name']) ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden h-64 md:h-auto">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2796.8567891234567!2d-122.57895!3d45.46123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x5495a0b91234567%3A0x1234567890abcdef!2s8536%20SE%2082nd%20Ave%2C%20Portland%2C%20OR%2097266!5e0!3m2!1sen!2sus!4v1234567890123"
              width="100%" height="100%" style="border:0; min-height: 256px;" allowfullscreen
              loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              title="Oregon Tires Auto Care - Directions from <?= htmlspecialchars($areaName) ?>"></iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center">Auto Services for <?= htmlspecialchars($areaName) ?> Drivers</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <?php
          $services = [
            ['name' => 'Tire Installation', 'price' => '$20+', 'slug' => 'tire-installation'],
            ['name' => 'Tire Repair', 'price' => '$15+', 'slug' => 'tire-repair'],
            ['name' => 'Oil Change', 'price' => '$35+', 'slug' => 'oil-change'],
            ['name' => 'Brake Service', 'price' => '$100+', 'slug' => 'brake-service'],
            ['name' => 'Wheel Alignment', 'price' => '$75+', 'slug' => 'wheel-alignment'],
            ['name' => 'Tuneup', 'price' => '$80+', 'slug' => 'tuneup'],
            ['name' => 'Inspection', 'price' => '$50+', 'slug' => 'inspection'],
            ['name' => 'Mobile Service', 'price' => 'Call', 'slug' => 'mobile-service'],
          ];
          foreach ($services as $svc): ?>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 text-center border border-gray-200 dark:border-gray-700 hover:border-brand dark:hover:border-green-400 transition">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1"><?= $svc['name'] ?></div>
            <div class="text-2xl font-bold text-brand dark:text-green-400"><?= $svc['price'] ?></div>
            <a href="/book-appointment/?service=<?= $svc['slug'] ?>" class="inline-block mt-2 text-xs font-semibold px-3 py-1 rounded-full bg-brand text-white hover:bg-green-700 transition">Book Now &rarr;</a>
          </div>
          <?php endforeach; ?>
        </div>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">Prices vary by vehicle. Call for an exact quote.</p>
      </div>
    </section>

    <!-- Testimonial -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-2xl text-center">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6">What <?= htmlspecialchars($areaName) ?> Customers Say</h2>
        <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-md p-8">
          <div class="flex justify-center mb-3">
            <span class="text-yellow-400 text-xl" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
          </div>
          <p class="text-gray-600 dark:text-gray-300 text-lg italic mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
          <cite class="text-brand dark:text-green-400 font-semibold not-italic">&mdash; <?= htmlspecialchars($testimonial['name']) ?></cite>
        </blockquote>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center">Why <?= htmlspecialchars($areaName) ?> Drivers Choose Oregon Tires</h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">100% Bilingual</strong><p class="text-gray-600 dark:text-gray-300 text-sm">Full service in English and Spanish</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">Honest Pricing</strong><p class="text-gray-600 dark:text-gray-300 text-sm">No hidden fees or upselling</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">12-Month Warranty</strong><p class="text-gray-600 dark:text-gray-300 text-sm">12,000-mile warranty on all services</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">Mobile Service</strong><p class="text-gray-600 dark:text-gray-300 text-sm">We come to your <?= htmlspecialchars($areaName) ?> location</p></div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3">Ready for Service in <?= htmlspecialchars($areaName) ?>?</h2>
        <p class="mb-6">Book online or call for same-day service. Free estimates, no obligation.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Call (503) 367-9714</a>
          <a href="sms:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Text Us</a>
        </div>
      </div>
    </section>

    <!-- Nearby Service Areas -->
    <?php if (!empty($nearbyAreas)): ?>
    <section class="py-10 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl text-center">
        <h2 class="text-xl font-bold text-brand dark:text-green-400 mb-4">Also Serving Nearby Areas</h2>
        <div class="flex flex-wrap justify-center gap-3">
          <?php foreach ($nearbyAreas as $area): ?>
          <a href="/<?= $area['slug'] ?>" class="bg-white dark:bg-gray-700 px-4 py-2 rounded-full text-sm font-medium text-brand dark:text-green-400 border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-green-400 transition"><?= htmlspecialchars($area['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>

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
</body>
</html>
