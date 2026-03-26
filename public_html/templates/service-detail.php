<?php
/**
 * Oregon Tires — Service Detail Page Template
 * Variables expected: $serviceName, $serviceNameEs, $serviceSlug, $serviceIcon,
 * $serviceDescription, $serviceDescriptionEs,
 * $serviceBody, $serviceBodyEs, $faqItems, $relatedServices
 */
require_once __DIR__ . '/../includes/seo-lang.php';
$pageTitle = "$serviceName in Portland, OR | Oregon Tires Auto Care";
$pageTitleEs = "$serviceNameEs en Portland, OR | Oregon Tires Auto Care";
$canonicalUrl = "https://oregon.tires/$serviceSlug";
?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?></title>
  <meta name="description" content="<?= htmlspecialchars(seoMeta($serviceDescription, $serviceDescriptionEs)) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(seoMeta($serviceDescription, $serviceDescriptionEs)) ?>">
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
    details summary { cursor: pointer; list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    details summary::before { content: '+'; display: inline-block; width: 1.5rem; font-weight: bold; font-size: 1.25rem; transition: transform 0.2s; }
    details[open] summary::before { content: '\2212'; }
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

  <!-- JSON-LD: Service -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "<?= htmlspecialchars(seoMeta($serviceName, $serviceNameEs)) ?>",
    "description": "<?= htmlspecialchars(seoMeta($serviceDescription, $serviceDescriptionEs)) ?>",
    "provider": {
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
      "knowsLanguage": ["en", "es"],
      "priceRange": "$$"
    },
    "areaServed": {
      "@type": "City",
      "name": "Portland",
      "containedInPlace": {"@type": "State", "name": "Oregon"}
    },
    "url": "<?= $canonicalUrl ?>"
  }
  </script>

  <!-- JSON-LD: FAQPage -->
  <?php if (!empty($faqItems)): ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      <?php foreach ($faqItems as $i => $faq): ?>
      {
        "@type": "Question",
        "name": "<?= htmlspecialchars(seoMeta($faq['q'], $faq['qEs'])) ?>",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "<?= htmlspecialchars(seoMeta($faq['a'], $faq['aEs'])) ?>"
        }
      }<?= $i < count($faqItems) - 1 ? ',' : '' ?>
      <?php endforeach; ?>
    ]
  }
  </script>
  <?php endif; ?>

  <!-- JSON-LD: BreadcrumbList -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "<?= seoMeta('Home', 'Inicio') ?>", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "<?= seoMeta('Services', 'Servicios') ?>", "item": "https://oregon.tires/#services"},
      {"@type": "ListItem", "position": 3, "name": "<?= htmlspecialchars(seoMeta($serviceName, $serviceNameEs)) ?>"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php include __DIR__ . '/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-16 relative" role="img" aria-label="<?= htmlspecialchars($serviceName) ?> service">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="/#services" class="hover:text-amber-300" data-t="breadServices">Services</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="serviceName"><?= htmlspecialchars($serviceName) ?></li>
          </ol>
        </nav>
        <div class="flex items-center gap-3 mb-4">
          <span class="text-4xl" aria-hidden="true"><?= $serviceIcon ?></span>
          <h1 class="text-3xl md:text-5xl font-bold" data-t="heroTitle"><?= htmlspecialchars($serviceName) ?></h1>
        </div>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="heroSubtitle"><?= htmlspecialchars($serviceDescription) ?></p>
        <div class="mb-6"></div>
        <div class="flex flex-wrap gap-3">
          <a href="/book-appointment/?service=<?= htmlspecialchars($serviceSlug) ?>" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg" data-t="heroBook">Book Now</a>
          <a href="tel:5033679714" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition" data-t="heroCall">Call (503) 367-9714</a>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/90">
          <span class="flex items-center gap-1"><svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> <span data-t="heroStars">4.8 Stars &middot; 150+ Reviews</span></span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span data-t="heroSince">Since 2008</span>
          <span class="hidden sm:inline text-white/40">|</span>
          <span data-t="heroBilingual">Se Habla Espa&ntilde;ol</span>
        </div>
      </div>
    </section>

    <!-- Service Body Content -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6" data-t="aboutTitle">About Our <?= htmlspecialchars($serviceName) ?> Service</h2>
        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-4" data-t="serviceBody">
          <?= $serviceBody ?>
        </div>
        <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-4 hidden" data-t="serviceBodyEs">
          <?= $serviceBodyEs ?>
        </div>
      </div>
    </section>

    <!-- Process Steps -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="processTitle">How It Works</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="w-16 h-16 bg-brand text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3">1</div>
            <h3 class="font-bold text-brand dark:text-green-400 mb-1" data-t="step1Title">Schedule</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300" data-t="step1Desc">Book online or call us to set your appointment</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-brand text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3">2</div>
            <h3 class="font-bold text-brand dark:text-green-400 mb-1" data-t="step2Title">Drop Off</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300" data-t="step2Desc">Bring your vehicle to our shop on SE 82nd Ave</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-brand text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3">3</div>
            <h3 class="font-bold text-brand dark:text-green-400 mb-1" data-t="step3Title">Service</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300" data-t="step3Desc">Our expert technicians get to work right away</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-brand text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3">4</div>
            <h3 class="font-bold text-brand dark:text-green-400 mb-1" data-t="step4Title">Drive Away</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300" data-t="step4Desc">Pick up your vehicle and hit the road with confidence</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Grid -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="whyTitle">Why Choose Oregon Tires</h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400" data-t="benefit1Title">100% Bilingual</strong><p class="text-gray-600 dark:text-gray-300 text-sm" data-t="benefit1Desc">Full service in English and Spanish</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400" data-t="benefit2Title">Honest Pricing</strong><p class="text-gray-600 dark:text-gray-300 text-sm" data-t="benefit2Desc">No hidden fees or upselling</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400" data-t="benefit3Title">12-Month Warranty</strong><p class="text-gray-600 dark:text-gray-300 text-sm" data-t="benefit3Desc">12,000-mile warranty on all services</p></div>
          </div>
          <div class="flex items-start gap-3">
            <span class="text-brand dark:text-green-400 text-xl" aria-hidden="true">&#10003;</span>
            <div><strong class="text-brand dark:text-green-400" data-t="benefit4Title">Mobile Service</strong><p class="text-gray-600 dark:text-gray-300 text-sm" data-t="benefit4Desc">We come to your Portland location</p></div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Accordion -->
    <?php if (!empty($faqItems)): ?>
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="faqTitle">Frequently Asked Questions</h2>
        <div class="space-y-3">
          <?php foreach ($faqItems as $i => $faq): ?>
          <details class="bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600 group">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="faq<?= $i ?>Q"><?= htmlspecialchars($faq['q']) ?></span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="faq<?= $i ?>A"><?= htmlspecialchars($faq['a']) ?></p>
            </div>
          </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Related Services -->
    <?php if (!empty($relatedServices)): ?>
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="relatedTitle">Related Services</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($relatedServices as $i => $rs): ?>
          <a href="/<?= htmlspecialchars($rs['slug']) ?>" class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center border border-gray-200 dark:border-gray-700 hover:border-brand dark:hover:border-green-400 transition block">
            <div class="text-lg font-bold text-brand dark:text-green-400 mb-1" data-t="related<?= $i ?>Name"><?= htmlspecialchars($rs['name']) ?></div>
            <span class="inline-block mt-2 text-xs font-semibold px-3 py-1 rounded-full bg-brand text-white" data-t="relatedLearnMore">Learn More &rarr;</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Testimonial -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-2xl text-center">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6" data-t="reviewTitle">What Our Customers Say</h2>
        <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-md p-8">
          <div class="flex justify-center mb-3">
            <span class="text-yellow-400 text-xl" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
          </div>
          <p class="text-gray-600 dark:text-gray-300 text-lg italic mb-4" data-t="reviewText">"Excellent service! They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy."</p>
          <cite class="text-brand dark:text-green-400 font-semibold not-italic">&mdash; Maria Rodriguez</cite>
        </blockquote>
      </div>
    </section>

    <?php if (!empty($customSectionsBeforeCTA)) echo $customSectionsBeforeCTA; ?>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Ready for <?= htmlspecialchars($serviceName) ?>?</h2>
        <p class="mb-6" data-t="ctaSubtitle">Book online or call for same-day service. Free estimates, no obligation.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/?service=<?= htmlspecialchars($serviceSlug) ?>" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="ctaBook">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaCall">Call (503) 367-9714</a>
          <a href="sms:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaText">Text Us</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> <span data-t="mobileCall">Call Now</span>
      </a>
      <a href="/book-appointment/?service=<?= htmlspecialchars($serviceSlug) ?>" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> <span data-t="mobileBook">Book Now</span>
      </a>
    </div>
  </div>

  <!-- Bilingual Toggle Script -->
  <script>
  (function() {
    var t = {
      serviceName: '<?= addslashes($serviceNameEs) ?>',
      breadServices: 'Servicios',
      heroTitle: '<?= addslashes($serviceNameEs) ?>',
      heroSubtitle: '<?= addslashes($serviceDescriptionEs) ?>',
      aboutTitle: 'Sobre Nuestro Servicio de <?= addslashes($serviceNameEs) ?>',
      processTitle: 'Como Funciona',
      step1Title: 'Agendar', step1Desc: 'Reserve en linea o llamenos para programar su cita',
      step2Title: 'Entregar', step2Desc: 'Traiga su vehiculo a nuestro taller en la Ave SE 82nd',
      step3Title: 'Servicio', step3Desc: 'Nuestros tecnicos expertos comienzan a trabajar de inmediato',
      step4Title: 'Manejar', step4Desc: 'Recoja su vehiculo y maneje con confianza',
      whyTitle: 'Por Que Elegir Oregon Tires',
      benefit1Title: '100% Bilingue', benefit1Desc: 'Servicio completo en ingles y espanol',
      benefit2Title: 'Precios Honestos', benefit2Desc: 'Sin tarifas ocultas ni ventas agresivas',
      benefit3Title: 'Garantia de 12 Meses', benefit3Desc: 'Garantia de 12,000 millas en todos los servicios',
      benefit4Title: 'Servicio Movil', benefit4Desc: 'Vamos a su ubicacion en Portland',
      faqTitle: 'Preguntas Frecuentes',
      <?php foreach ($faqItems as $i => $faq): ?>
      'faq<?= $i ?>Q': '<?= addslashes($faq['qEs']) ?>',
      'faq<?= $i ?>A': '<?= addslashes($faq['aEs']) ?>',
      <?php endforeach; ?>
      relatedTitle: 'Servicios Relacionados',
      relatedLearnMore: 'Ver Mas \u2192',
      <?php foreach ($relatedServices as $i => $rs): ?>
      'related<?= $i ?>Name': '<?= addslashes($rs['nameEs'] ?? $rs['name']) ?>',
      <?php endforeach; ?>
      reviewTitle: 'Lo Que Dicen Nuestros Clientes',
      reviewText: '"Excelente servicio! Instalaron mis llantas nuevas rapidamente y el precio fue muy justo. El personal habla espanol, lo que facilito la comunicacion."',
      ctaTitle: 'Listo para <?= addslashes($serviceNameEs) ?>?',
      ctaSubtitle: 'Reserve en linea o llame para servicio el mismo dia. Estimados gratis, sin compromiso.',
      ctaBook: 'Estimado Gratis',
      ctaCall: 'Llamar (503) 367-9714',
      ctaText: 'Envienos un Texto',
      heroBook: 'Reserve Ahora',
      heroCall: 'Llamar (503) 367-9714',
      heroStars: '4.8 Estrellas \u00b7 150+ Rese\u00f1as',
      heroSince: 'Desde 2008',
      heroBilingual: 'Se Habla Espa\u00f1ol',
      mobileCall: 'Llamar',
      mobileBook: 'Reservar'<?php if (!empty($customTranslations)) echo ',' . $customTranslations; ?>

    };
    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang') || localStorage.getItem('oregontires_lang') || 'en';
    window.currentLang = lang;
    if (lang === 'es') {
      localStorage.setItem('oregontires_lang', 'es');
      document.documentElement.lang = 'es';
      document.title = '<?= addslashes($pageTitleEs) ?>';
      var meta = document.querySelector('meta[name="description"]');
      if (meta) meta.setAttribute('content', '<?= addslashes($serviceDescriptionEs) ?>');
      // Toggle body content
      var enBody = document.querySelector('[data-t="serviceBody"]');
      var esBody = document.querySelector('[data-t="serviceBodyEs"]');
      if (enBody) enBody.classList.add('hidden');
      if (esBody) esBody.classList.remove('hidden');
      // Translate all data-t elements
      document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (key === 'serviceBody' || key === 'serviceBodyEs') return;
        if (t[key]) el.textContent = t[key];
      });
    } else {
      localStorage.setItem('oregontires_lang', 'en');
    }
  })();
  </script>
  <?php if (!empty($customScripts)) echo $customScripts; ?>
</body>
</html>
