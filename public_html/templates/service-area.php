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
            ['name' => 'Tire Installation', 'price' => '$20+', 'slug' => 'tire-installation', 'setting' => 'price_tire_install'],
            ['name' => 'Tire Repair', 'price' => '$15+', 'slug' => 'tire-repair', 'setting' => 'price_tire_repair'],
            ['name' => 'Oil Change', 'price' => '$35+', 'slug' => 'oil-change', 'setting' => 'price_oil_change'],
            ['name' => 'Brake Service', 'price' => '$100+', 'slug' => 'brake-service', 'setting' => 'price_brake_service'],
            ['name' => 'Wheel Alignment', 'price' => '$75+', 'slug' => 'wheel-alignment', 'setting' => 'price_alignment'],
            ['name' => 'Tuneup', 'price' => '$80+', 'slug' => 'tuneup', 'setting' => 'price_tuneup'],
            ['name' => 'Inspection', 'price' => '$50+', 'slug' => 'inspection', 'setting' => 'price_inspection'],
            ['name' => 'Mobile Service', 'price' => 'Call', 'slug' => 'mobile-service', 'setting' => 'price_roadside'],
          ];
          $servicePages = ['tire-installation', 'tire-repair', 'oil-change', 'brake-service', 'wheel-alignment', 'engine-diagnostics', 'suspension-repair'];
          foreach ($services as $i => $svc): ?>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 text-center border border-gray-200 dark:border-gray-700 hover:border-brand dark:hover:border-green-400 transition" data-reveal data-reveal-delay="<?= $i * 100 ?>">
            <?php if (in_array($svc['slug'], $servicePages)): ?>
            <a href="/<?= $svc['slug'] ?>" class="text-sm text-gray-500 dark:text-gray-400 mb-1 hover:text-brand dark:hover:text-green-400 transition block"><?= $svc['name'] ?></a>
            <?php else: ?>
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1"><?= $svc['name'] ?></div>
            <?php endif; ?>
            <div class="text-2xl font-bold text-brand dark:text-green-400" data-setting="<?= htmlspecialchars($svc['setting']) ?>"><?= $svc['price'] ?></div>
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
        <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-md p-8" data-reveal="fade">
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
          <div class="flex items-start gap-3" data-reveal data-reveal-delay="0">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">100% Bilingual</strong><p class="text-gray-600 dark:text-gray-300 text-sm">Full service in English and Spanish</p></div>
          </div>
          <div class="flex items-start gap-3" data-reveal data-reveal-delay="100">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">Honest Pricing</strong><p class="text-gray-600 dark:text-gray-300 text-sm">No hidden fees or upselling</p></div>
          </div>
          <div class="flex items-start gap-3" data-reveal data-reveal-delay="200">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400">12-Month Warranty</strong><p class="text-gray-600 dark:text-gray-300 text-sm">12,000-mile warranty on all services</p></div>
          </div>
          <div class="flex items-start gap-3" data-reveal data-reveal-delay="300">
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
<script src="/assets/js/scroll-reveal.js" defer></script>
<!-- Bilingual Toggle Script -->
<script>
(function() {
  var areaNameEs = '<?= addslashes($areaNameEs) ?>';
  var areaDescriptionEs = '<?= addslashes($areaDescriptionEs) ?>';
  var pageTitleEs = '<?= addslashes($pageTitleEs) ?>';

  var landmarksEs = [
    <?php foreach ($landmarks as $i => $lm): ?>
    { name: '<?= addslashes($lm['nameEs'] ?? $lm['name']) ?>', distance: '<?= addslashes($lm['distance']) ?>' }<?= $i < count($landmarks) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ];

  var testimonialTextEs = '<?= addslashes($testimonial['textEs'] ?? $testimonial['text']) ?>';
  var testimonialDetailEs = '<?= addslashes($testimonial['detailEs'] ?? $testimonial['detail'] ?? '') ?>';

  var serviceNamesEs = {
    'Tire Installation': 'Instalaci\u00f3n de Llantas',
    'Tire Repair': 'Reparaci\u00f3n de Llantas',
    'Oil Change': 'Cambio de Aceite',
    'Brake Service': 'Servicio de Frenos',
    'Wheel Alignment': 'Alineaci\u00f3n de Ruedas',
    'Tuneup': 'Afinaci\u00f3n',
    'Inspection': 'Inspecci\u00f3n',
    'Mobile Service': 'Servicio M\u00f3vil'
  };

  var params = new URLSearchParams(window.location.search);
  var lang = params.get('lang') || localStorage.getItem('oregontires_lang') || 'en';

  if (lang === 'es') {
    localStorage.setItem('oregontires_lang', 'es');
    document.documentElement.lang = 'es';

    document.title = pageTitleEs;
    var meta = document.querySelector('meta[name="description"]');
    if (meta) meta.setAttribute('content', areaDescriptionEs);

    // data-t elements
    var dt = document.querySelector('[data-t="heroTitle"]');
    if (dt) dt.textContent = 'Llantas y Servicio Automotriz en ' + areaNameEs;
    dt = document.querySelector('[data-t="heroSubtitle"]');
    if (dt) dt.textContent = areaDescriptionEs;
    dt = document.querySelector('[data-t="areaName"]');
    if (dt) dt.textContent = areaNameEs;
    dt = document.querySelector('[data-t="howToFind"]');
    if (dt) dt.textContent = 'Convenientemente Ubicados Cerca de ' + areaNameEs;

    // Breadcrumb
    var breadNav = document.querySelector('nav[aria-label="Breadcrumb"]');
    if (breadNav) {
      breadNav.setAttribute('aria-label', 'Navegaci\u00f3n');
      var links = breadNav.querySelectorAll('a');
      links.forEach(function(a) {
        if (a.textContent.trim() === 'Home') a.textContent = 'Inicio';
        if (a.textContent.trim() === 'Services') a.textContent = 'Servicios';
      });
    }

    // Hero CTA buttons
    document.querySelectorAll('a.bg-amber-500').forEach(function(el) {
      if (el.textContent.trim() === 'Get Your Free Estimate') el.textContent = 'Obtenga Su Estimado Gratis';
      if (el.textContent.trim() === 'Book Free Estimate') el.textContent = 'Reserve Su Estimado Gratis';
    });

    // Hero section aria-label
    var heroSection = document.querySelector('section[role="img"]');
    if (heroSection) heroSection.setAttribute('aria-label', areaNameEs + ' \u00e1rea de servicio');

    // Trust signals
    document.querySelectorAll('.text-white\\/90 span, .text-white\\/90 .flex').forEach(function(el) {
      var txt = el.textContent.trim();
      if (txt === 'Since 2008') el.textContent = 'Desde 2008';
    });
    // Stars text (contains SVG so use childNodes)
    document.querySelectorAll('.text-white\\/90 .flex.items-center').forEach(function(el) {
      el.childNodes.forEach(function(node) {
        if (node.nodeType === 3 && node.textContent.indexOf('Stars') !== -1) {
          node.textContent = node.textContent.replace('4.8 Stars \u00b7 150+ Reviews', '4.8 Estrellas \u00b7 150+ Rese\u00f1as');
        }
      });
    });

    // "Call (503) 367-9714" buttons — change "Call" to "Llamar"
    document.querySelectorAll('a[href="tel:5033679714"]').forEach(function(el) {
      if (el.textContent.trim() === 'Call (503) 367-9714') el.textContent = 'Llamar (503) 367-9714';
    });

    // Location paragraph — rebuild with textContent
    var locParas = document.querySelectorAll('.bg-gray-50 > .container p.text-gray-600, .dark\\:bg-gray-800 > .container p.text-gray-600');
    locParas.forEach(function(p) {
      if (p.textContent.indexOf('Oregon Tires Auto Care is located at') !== -1) {
        while (p.firstChild) p.removeChild(p.firstChild);
        p.appendChild(document.createTextNode('Oregon Tires Auto Care est\u00e1 ubicado en '));
        var strong = document.createElement('strong');
        strong.textContent = '8536 SE 82nd Ave, Portland, OR 97266';
        p.appendChild(strong);
        p.appendChild(document.createTextNode(', a solo minutos de ' + areaNameEs + '.'));
      }
    });

    // Nearby Landmarks heading
    document.querySelectorAll('h3').forEach(function(h3) {
      if (h3.textContent.trim() === 'Nearby Landmarks') h3.textContent = 'Puntos de Referencia Cercanos';
    });

    // Landmark list items
    var landmarkLis = document.querySelectorAll('.space-y-2 li');
    landmarkLis.forEach(function(li, i) {
      if (landmarksEs[i]) {
        var span = li.querySelector('span:last-child');
        if (span) span.textContent = landmarksEs[i].distance + ' de ' + landmarksEs[i].name;
      }
    });

    // Section headings
    document.querySelectorAll('section h2').forEach(function(h2) {
      var txt = h2.textContent.trim();
      if (txt.indexOf('Auto Services for') !== -1) h2.textContent = 'Servicios Automotrices para Conductores de ' + areaNameEs;
      if (txt.indexOf('Customers Say') !== -1) h2.textContent = 'Lo Que Dicen los Clientes de ' + areaNameEs;
      if (txt.indexOf('Why') !== -1 && txt.indexOf('Choose') !== -1) h2.textContent = 'Por Qu\u00e9 los Conductores de ' + areaNameEs + ' Eligen Oregon Tires';
      if (txt.indexOf('Ready for Service') !== -1) h2.textContent = '\u00bfListo para Servicio en ' + areaNameEs + '?';
      if (txt === 'Also Serving Nearby Areas') h2.textContent = 'Tambi\u00e9n Servimos \u00c1reas Cercanas';
    });

    // Service card names and "Call" price
    document.querySelectorAll('.rounded-xl .text-sm.text-gray-500, .rounded-xl .text-sm.text-gray-400').forEach(function(el) {
      var name = el.textContent.trim();
      if (serviceNamesEs[name]) el.textContent = serviceNamesEs[name];
    });
    document.querySelectorAll('.text-2xl.font-bold.text-brand').forEach(function(el) {
      if (el.textContent.trim() === 'Call') el.textContent = 'Llamar';
    });

    // "Book Now" links
    document.querySelectorAll('.rounded-full.bg-brand.text-white').forEach(function(el) {
      if (el.textContent.trim().indexOf('Book Now') !== -1) el.textContent = 'Reservar \u2192';
    });

    // Price disclaimer
    document.querySelectorAll('p.text-center.text-sm.text-gray-500').forEach(function(el) {
      if (el.textContent.indexOf('Prices vary') !== -1) el.textContent = 'Los precios var\u00edan seg\u00fan el veh\u00edculo. Llame para una cotizaci\u00f3n exacta.';
    });

    // Testimonial quote and star aria-label
    var quoteP = document.querySelector('blockquote p');
    if (quoteP) quoteP.textContent = '\u201c' + testimonialTextEs + '\u201d';
    var starSpan = document.querySelector('blockquote .text-yellow-400');
    if (starSpan) starSpan.setAttribute('aria-label', '5 de 5 estrellas');
    // Testimonial cite detail
    if (testimonialDetailEs) {
      var cite = document.querySelector('blockquote cite');
      if (cite) {
        var citeName = '<?= addslashes($testimonial['name']) ?>';
        cite.textContent = '\u2014 ' + citeName;
        if (testimonialDetailEs) {
          cite.textContent += ', ' + testimonialDetailEs;
        }
      }
    }

    // Why Choose Us benefits
    var benefitTitles = ['100% Biling\u00fce', 'Precios Honestos', 'Garant\u00eda de 12 Meses', 'Servicio M\u00f3vil'];
    var benefitDescs = [
      'Servicio completo en ingl\u00e9s y espa\u00f1ol',
      'Sin tarifas ocultas ni ventas agresivas',
      'Garant\u00eda de 12,000 millas en todos los servicios',
      'Vamos a su ubicaci\u00f3n en ' + areaNameEs
    ];
    document.querySelectorAll('.grid.md\\:grid-cols-2 strong').forEach(function(el, i) {
      if (benefitTitles[i]) el.textContent = benefitTitles[i];
    });
    document.querySelectorAll('.grid.md\\:grid-cols-2 p.text-sm').forEach(function(el, i) {
      if (benefitDescs[i]) el.textContent = benefitDescs[i];
    });

    // CTA paragraph
    document.querySelectorAll('.bg-amber-500.text-black p').forEach(function(el) {
      if (el.textContent.indexOf('Book online or call') !== -1) el.textContent = 'Reserve en l\u00ednea o llame para servicio el mismo d\u00eda. Estimados gratis, sin compromiso.';
    });

    // Text Us button
    document.querySelectorAll('.bg-amber-500 a').forEach(function(el) {
      if (el.textContent.trim() === 'Text Us') el.textContent = 'Env\u00ede Texto';
    });

    // Sticky mobile CTA — text is inline alongside emoji spans
    var mobileCtas = document.querySelectorAll('.fixed.bottom-0 a');
    mobileCtas.forEach(function(el) {
      el.childNodes.forEach(function(node) {
        if (node.nodeType === 3) {
          if (node.textContent.indexOf('Call Now') !== -1) node.textContent = ' Llamar ';
          if (node.textContent.indexOf('Book Now') !== -1) node.textContent = ' Reservar ';
        }
      });
    });
    // Sticky CTA aria-label
    var stickyCta = document.querySelector('.fixed.bottom-0[role="complementary"]');
    if (stickyCta) stickyCta.setAttribute('aria-label', 'Acciones r\u00e1pidas');

    // Skip to main content
    var skipLink = document.querySelector('a[href="#main-content"]');
    if (skipLink) skipLink.textContent = 'Saltar al contenido principal';

    // Map iframe title
    var iframe = document.querySelector('iframe');
    if (iframe) iframe.setAttribute('title', 'Oregon Tires Auto Care - Direcciones desde ' + areaNameEs);
  } else {
    localStorage.setItem('oregontires_lang', 'en');
  }

  // Load dynamic prices from DB
  fetch('/api/settings.php').then(function(r){return r.json()}).then(function(json){
    var data = json.data || [];
    for (var i = 0; i < data.length; i++) {
      var row = data[i];
      document.querySelectorAll('[data-setting="' + row.setting_key + '"]').forEach(function(el) {
        var val = lang === 'es' ? (row.value_es || row.value_en) : row.value_en;
        if (val) el.textContent = val;
      });
    }
  }).catch(function(){});
})();
</script>
</body>
</html>
