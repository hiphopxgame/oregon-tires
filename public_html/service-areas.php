<?php
/**
 * Oregon Tires — Service Areas Overview Page
 * Bilingual overview of all neighborhoods served with links to detail pages.
 */
$pageTitle = 'Service Areas | Oregon Tires Auto Care Portland OR';
$pageTitleEs = 'Áreas de Servicio | Oregon Tires Auto Care Portland OR';
$pageDesc = 'Oregon Tires Auto Care serves SE Portland, Clackamas, Happy Valley, Milwaukie, Lents, Woodstock, Foster-Powell, and Mt. Scott. Professional bilingual auto care near you.';
$pageDescEs = 'Oregon Tires Auto Care sirve a SE Portland, Clackamas, Happy Valley, Milwaukie, Lents, Woodstock, Foster-Powell y Mt. Scott. Servicio automotriz bilingüe profesional cerca de usted.';
$canonicalUrl = 'https://oregon.tires/service-areas';
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
  </style>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- AutomotiveBusiness + areaServed JSON-LD -->
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
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 45.46123,
      "longitude": -122.57895
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "<?= $_rating['ratingValue'] ?>",
      "reviewCount": "<?= $_rating['reviewCount'] ?>",
      "bestRating": "5"
    },
    "areaServed": [
      {"@type": "Place", "name": "SE Portland, OR"},
      {"@type": "Place", "name": "Clackamas, OR"},
      {"@type": "Place", "name": "Happy Valley, OR"},
      {"@type": "Place", "name": "Milwaukie, OR"},
      {"@type": "Place", "name": "Lents, Portland, OR"},
      {"@type": "Place", "name": "Woodstock, Portland, OR"},
      {"@type": "Place", "name": "Foster-Powell, Portland, OR"},
      {"@type": "Place", "name": "Mt. Scott, Portland, OR"}
    ],
    "knowsLanguage": ["en", "es"],
    "priceRange": "$$"
  }
  </script>

  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Service Areas"}
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
            <li class="text-white font-medium" data-t="serviceAreas">Service Areas</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold mb-3" data-t="heroTitle">Areas We Serve</h1>
        <p class="text-lg opacity-90 max-w-2xl" data-t="heroSubtitle">Oregon Tires Auto Care proudly serves the greater SE Portland area with professional, bilingual auto care. We're your neighborhood tire and auto shop.</p>
      </div>
    </section>

    <!-- Map Embed -->
    <section class="py-10 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl font-bold text-center mb-6 text-brand dark:text-green-400" data-t="findUs">Find Us</h2>
        <div class="rounded-xl overflow-hidden shadow-lg">
          <iframe
            src="https://www.google.com/maps?q=Oregon+Tires+Auto+Care,+8536+SE+82nd+Ave,+Portland,+OR+97266&output=embed"
            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Oregon Tires Auto Care location"></iframe>
        </div>
        <p class="text-center mt-3 text-sm text-gray-500 dark:text-gray-400">
          <a href="https://maps.google.com/?q=Oregon+Tires+Auto+Care,+8536+SE+82nd+Ave,+Portland,+OR+97266" target="_blank" rel="noopener" class="text-brand dark:text-green-400 hover:underline" data-t="openMaps">Open in Google Maps &rarr;</a>
        </p>
      </div>
    </section>

    <!-- Area Cards Grid -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl font-bold text-center mb-2 text-brand dark:text-green-400" data-t="neighborhoodsTitle">Neighborhoods We Serve</h2>
        <p class="text-center text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto" data-t="neighborhoodsDesc">From SE Portland to Clackamas County, we provide expert tire and auto services to drivers across these communities.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

          <!-- SE Portland -->
          <a href="/tires-se-portland" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaSEPortland">SE Portland</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaSEPortlandDesc">Our home base on 82nd Ave, serving the heart of Southeast.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Clackamas -->
          <a href="/tires-clackamas" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaClackamas">Clackamas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaClackamasDesc">Just minutes from Clackamas Town Center and surrounding areas.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Happy Valley -->
          <a href="/tires-happy-valley" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaHappyValley">Happy Valley</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaHappyValleyDesc">Convenient tire service for Happy Valley families and commuters.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Milwaukie -->
          <a href="/tires-milwaukie" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaMilwaukie">Milwaukie</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaMilwaukieDesc">Trusted auto care for Milwaukie residents, right down 82nd.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Lents -->
          <a href="/tires-lents" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaLents">Lents</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaLentsDesc">Your local tire shop in the Lents neighborhood.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Woodstock -->
          <a href="/tires-woodstock" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaWoodstock">Woodstock</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaWoodstockDesc">Quick drive from Woodstock for all your tire and brake needs.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Foster-Powell -->
          <a href="/tires-foster-powell" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaFosterPowell">Foster-Powell</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaFosterPowellDesc">Serving the Foster-Powell community with honest, affordable auto care.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

          <!-- Mt. Scott -->
          <a href="/tires-mt-scott" class="group block bg-gray-50 dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-green-400 transition-colors" data-t="areaMtScott">Mt. Scott</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="areaMtScottDesc">Professional tire service for the Mt. Scott-Arleta neighborhood.</p>
              </div>
              <span class="text-brand dark:text-green-400 text-xl group-hover:translate-x-1 transition-transform" aria-hidden="true">&rarr;</span>
            </div>
          </a>

        </div>
      </div>
    </section>

    <!-- Services Available -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl font-bold text-center mb-2 text-brand dark:text-green-400" data-t="servicesTitle">Services Available in All Areas</h2>
        <p class="text-center text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto" data-t="servicesDesc">No matter which neighborhood you're in, we offer the full range of tire and auto services.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <a href="/tire-installation" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F6DE;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcTireInstall">Tire Installation</span>
          </a>
          <a href="/tire-repair" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F527;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcTireRepair">Tire Repair</span>
          </a>
          <a href="/wheel-alignment" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x2699;&#xFE0F;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcAlignment">Wheel Alignment</span>
          </a>
          <a href="/brake-service" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F6D1;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcBrakes">Brake Service</span>
          </a>
          <a href="/oil-change" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F6E2;&#xFE0F;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcOilChange">Oil Change</span>
          </a>
          <a href="/engine-diagnostics" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F50D;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcDiagnostics">Engine Diagnostics</span>
          </a>
          <a href="/suspension-repair" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F3CE;&#xFE0F;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcSuspension">Suspension Repair</span>
          </a>
          <a href="/fleet-services" class="flex items-center gap-3 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-600">
            <span class="text-2xl" aria-hidden="true">&#x1F69A;</span>
            <span class="font-semibold text-gray-900 dark:text-white" data-t="svcFleet">Fleet Services</span>
          </a>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Need Tire Service Near You?</h2>
        <p class="mb-6 max-w-xl mx-auto" data-t="ctaDesc">Book an appointment online or give us a call. Free estimates, bilingual service, and same-day availability.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="bookAppointment">Book Appointment</a>
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
      home:                { en: 'Home', es: 'Inicio' },
      serviceAreas:        { en: 'Service Areas', es: '\u00c1reas de Servicio' },
      heroTitle:           { en: 'Areas We Serve', es: '\u00c1reas que Servimos' },
      heroSubtitle:        { en: 'Oregon Tires Auto Care proudly serves the greater SE Portland area with professional, bilingual auto care. We\u2019re your neighborhood tire and auto shop.', es: 'Oregon Tires Auto Care sirve con orgullo al \u00e1rea de SE Portland con servicio automotriz profesional y biling\u00fce. Somos su taller de llantas y autos del vecindario.' },
      findUs:              { en: 'Find Us', es: 'Enc\u00faentrenos' },
      openMaps:            { en: 'Open in Google Maps \u2192', es: 'Abrir en Google Maps \u2192' },
      neighborhoodsTitle:  { en: 'Neighborhoods We Serve', es: 'Vecindarios que Servimos' },
      neighborhoodsDesc:   { en: 'From SE Portland to Clackamas County, we provide expert tire and auto services to drivers across these communities.', es: 'Desde SE Portland hasta el condado de Clackamas, brindamos servicios expertos de llantas y autos a conductores en estas comunidades.' },
      areaSEPortland:      { en: 'SE Portland', es: 'SE Portland' },
      areaSEPortlandDesc:  { en: 'Our home base on 82nd Ave, serving the heart of Southeast.', es: 'Nuestra base en la 82nd Ave, sirviendo el coraz\u00f3n del sureste.' },
      areaClackamas:       { en: 'Clackamas', es: 'Clackamas' },
      areaClackamasDesc:   { en: 'Just minutes from Clackamas Town Center and surrounding areas.', es: 'A solo minutos del Clackamas Town Center y \u00e1reas cercanas.' },
      areaHappyValley:     { en: 'Happy Valley', es: 'Happy Valley' },
      areaHappyValleyDesc: { en: 'Convenient tire service for Happy Valley families and commuters.', es: 'Servicio de llantas conveniente para familias y viajeros de Happy Valley.' },
      areaMilwaukie:       { en: 'Milwaukie', es: 'Milwaukie' },
      areaMilwaukieDesc:   { en: 'Trusted auto care for Milwaukie residents, right down 82nd.', es: 'Servicio automotriz confiable para residentes de Milwaukie, por la 82nd.' },
      areaLents:           { en: 'Lents', es: 'Lents' },
      areaLentsDesc:       { en: 'Your local tire shop in the Lents neighborhood.', es: 'Su taller de llantas local en el vecindario de Lents.' },
      areaWoodstock:       { en: 'Woodstock', es: 'Woodstock' },
      areaWoodstockDesc:   { en: 'Quick drive from Woodstock for all your tire and brake needs.', es: 'A un corto viaje desde Woodstock para todas sus necesidades de llantas y frenos.' },
      areaFosterPowell:    { en: 'Foster-Powell', es: 'Foster-Powell' },
      areaFosterPowellDesc:{ en: 'Serving the Foster-Powell community with honest, affordable auto care.', es: 'Sirviendo a la comunidad de Foster-Powell con servicio automotriz honesto y accesible.' },
      areaMtScott:         { en: 'Mt. Scott', es: 'Mt. Scott' },
      areaMtScottDesc:     { en: 'Professional tire service for the Mt. Scott-Arleta neighborhood.', es: 'Servicio profesional de llantas para el vecindario de Mt. Scott-Arleta.' },
      servicesTitle:       { en: 'Services Available in All Areas', es: 'Servicios Disponibles en Todas las \u00c1reas' },
      servicesDesc:        { en: 'No matter which neighborhood you\u2019re in, we offer the full range of tire and auto services.', es: 'Sin importar en qu\u00e9 vecindario se encuentre, ofrecemos la gama completa de servicios de llantas y autos.' },
      svcTireInstall:      { en: 'Tire Installation', es: 'Instalaci\u00f3n de Llantas' },
      svcTireRepair:       { en: 'Tire Repair', es: 'Reparaci\u00f3n de Llantas' },
      svcAlignment:        { en: 'Wheel Alignment', es: 'Alineaci\u00f3n de Ruedas' },
      svcBrakes:           { en: 'Brake Service', es: 'Servicio de Frenos' },
      svcOilChange:        { en: 'Oil Change', es: 'Cambio de Aceite' },
      svcDiagnostics:      { en: 'Engine Diagnostics', es: 'Diagn\u00f3stico de Motor' },
      svcSuspension:       { en: 'Suspension Repair', es: 'Reparaci\u00f3n de Suspensi\u00f3n' },
      svcFleet:            { en: 'Fleet Services', es: 'Servicios de Flotilla' },
      ctaTitle:            { en: 'Need Tire Service Near You?', es: '\u00bfNecesita Servicio de Llantas Cerca de Usted?' },
      ctaDesc:             { en: 'Book an appointment online or give us a call. Free estimates, bilingual service, and same-day availability.', es: 'Reserve una cita en l\u00ednea o ll\u00e1menos. Estimados gratis, servicio biling\u00fce y disponibilidad el mismo d\u00eda.' },
      bookAppointment:     { en: 'Book Appointment', es: 'Reservar Cita' },
      callUs:              { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      callNow:             { en: 'Call Now', es: 'Llamar' },
      bookNow:             { en: 'Book Now', es: 'Reservar' }
    };

    // Apply translations
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][currentLang]) el.textContent = t[key][currentLang];
    });

    if (currentLang === 'es') {
      document.getElementById('page-title').textContent = '<?= addslashes($pageTitleEs) ?>';
      document.getElementById('page-desc').setAttribute('content', '<?= addslashes($pageDescEs) ?>');
    }
  })();
  </script>
</body>
</html>
