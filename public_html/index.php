<?php
/**
 * Oregon Tires — Home Page (PHP Entry Point)
 * Loads bootstrap, initializes Engine Kit for GA and network integration.
 */

declare(strict_types=1);

// Load bootstrap and environment
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/engine-kit-init.php';
require_once __DIR__ . '/includes/seo-lang.php';
require_once __DIR__ . '/includes/seo-config.php';
$_rating = getAggregateRating();

$pageTitleEn = 'Oregon Tires Auto Care - Tire & Auto Services Portland, OR';
$pageTitleEs = 'Oregon Tires Auto Care - Llantas y Servicio Automotriz Portland, OR';
$pageDescEn = 'Professional tire sales, installation, brakes & auto care in Portland, OR. Bilingual English & Spanish service. Call (503) 367-9714';
$pageDescEs = 'Venta e instalación profesional de llantas, frenos y servicio automotriz en Portland, OR. Servicio bilingüe inglés y español. Llame al (503) 367-9714';

// Override bootstrap's JSON Content-Type — this is an HTML page, not an API endpoint
header('Content-Type: text/html; charset=utf-8');
header_remove('X-API-Version');

initEngineKit();

// Allow outputting HTML
if (ob_get_level() === 0) {
    ob_start();
}
?><!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(seoMeta($pageTitleEn, $pageTitleEs)) ?></title>
  <meta name="description" content="<?= htmlspecialchars(seoMeta($pageDescEn, $pageDescEs)) ?>">
  <meta name="author" content="Oregon Tires Auto Care">
  <meta name="keywords" content="tires, auto care, brake service, oil change, Portland Oregon, bilingual service, Spanish English speaking">
  <link rel="icon" href="assets/favicon.ico" sizes="any">
  <link rel="icon" href="assets/favicon.png" type="image/png" sizes="32x32">
  <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#15803d">
  <meta name="msapplication-TileColor" content="#15803d">

  <!-- SEO Meta Tags -->
  <link rel="canonical" href="https://oregon.tires/">
  <meta name="robots" content="index, follow">

  <!-- Hreflang Tags -->
  <link rel="alternate" hreflang="en" href="https://oregon.tires/">
  <link rel="alternate" hreflang="es" href="https://oregon.tires/?lang=es">
  <link rel="alternate" hreflang="x-default" href="https://oregon.tires/">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars(seoMeta('Oregon Tires Auto Care', 'Oregon Tires Auto Care - Servicio Automotriz')) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(seoMeta('Oregon Tires is serving Portland with honest, reliable automotive services since 2008.', 'Oregon Tires sirve a Portland con servicios automotrices honestos y confiables desde 2008.')) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="<?= seoOgLocale() ?>">
  <meta property="og:url" content="https://oregon.tires/">
  <meta property="og:image" content="https://oregon.tires/assets/og-image.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:alt" content="Oregon Tires Auto Care - Spanish & English Speaking">
  <meta property="og:site_name" content="Oregon Tires Auto Care">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars(seoMeta($pageTitleEn, $pageTitleEs)) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars(seoMeta($pageDescEn, $pageDescEs)) ?>">
  <meta name="twitter:image" content="https://oregon.tires/assets/og-image.jpg">

  <!-- Structured Data (abbreviated for brevity) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "AutomotiveBusiness",
    "name": "Oregon Tires Auto Care",
    "description": "Professional tire sales, installation, brake services, and auto care in Portland, Oregon",
    "url": "https://oregon.tires",
    "image": "https://oregon.tires/assets/og-image.jpg",
    "telephone": "(503) 367-9714",
    "email": "oregontirespdx@gmail.com",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "8536 SE 82nd Ave",
      "addressLocality": "Portland",
      "addressRegion": "OR",
      "postalCode": "97266",
      "addressCountry": "US"
    },
    "knowsLanguage": ["en", "es"],
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "<?= $_rating['ratingValue'] ?>",
      "reviewCount": "<?= $_rating['reviewCount'] ?>",
      "bestRating": "5"
    },
    "priceRange": "$$",
    "openingHoursSpecification": [
      { "@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "08:00", "closes": "18:00" },
      { "@type": "OpeningHoursSpecification", "dayOfWeek": "Saturday", "opens": "08:00", "closes": "17:00" }
    ],
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 45.4626,
      "longitude": -122.5801
    },
    "sameAs": ["https://www.facebook.com/OregonTiresPDX", "https://www.instagram.com/oregontirespdx"]
  }
  </script>

  <!-- Tailwind CSS (built) -->
  <link rel="stylesheet" href="assets/styles.css">

  <style>
    html { scroll-behavior: smooth; }
    :root { --brand-primary: #15803d; --brand-dark: #0D3618; }
    a, h1, h2, h3 { caret-color: #15803d; }
    .star { color: #facc15; }
    .fade-in { animation: fadeIn 0.6s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .faq-item summary { list-style: none; }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary .faq-chevron { transition: transform 0.2s ease; }
    .faq-item[open] summary .faq-chevron { transform: rotate(180deg); }
  </style>

  <?php require_once __DIR__ . '/includes/gtag.php'; ?>
  <?php if (function_exists('engineHead')): ?>
    <?php engineHead('oregontires', ['page_title' => 'Oregon Tires Auto Care']); ?>
  <?php endif; ?>

  <!-- Basic error tracking via GA4 -->
  <script>
    window.addEventListener('error', function(e) {
      if (typeof gtag === 'function') gtag('event', 'exception', {
        description: e.message + ' at ' + (e.filename || 'unknown') + ':' + (e.lineno || 0),
        fatal: false
      });
    });
  </script>
  <!-- Dark mode init (prevent FOUC) -->
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

</head>
<body>
  <!-- Include the rest of the HTML from index.html -->
  <?php
    // Read the original index.html and extract body content
    $indexHtml = file_get_contents(__DIR__ . '/index.html');
    if ($indexHtml) {
        $bodyStart = strpos($indexHtml, '<body');
        if ($bodyStart !== false) {
            $bodyContent = substr($indexHtml, strpos($indexHtml, '>', $bodyStart) + 1);
            $bodyEnd = strrpos($bodyContent, '</body>');
            if ($bodyEnd !== false) {
                echo substr($bodyContent, 0, $bodyEnd);
            }
        }
    }
  ?>
</body>
</html>
<?php
if (ob_get_level() > 0) {
    ob_end_flush();
}
