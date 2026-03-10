<?php
/**
 * Oregon Tires — FAQ Page
 * Bilingual (EN/ES) frequently asked questions with accordion layout.
 * Driven by oretir_faq database table.
 */

// ─── Bootstrap & fetch FAQs ─────────────────────────────────────────────────
require_once __DIR__ . '/includes/bootstrap.php';

$faqs = [];
$dbError = false;
try {
    $db = getDB();
    $stmt = $db->query(
        'SELECT id, question_en, question_es, answer_en, answer_es
         FROM oretir_faq
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    );
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    error_log('faq.php DB error: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle = 'Frequently Asked Questions | Oregon Tires Auto Care';
$pageTitleEs = 'Preguntas Frecuentes | Oregon Tires Auto Care';
$pageDesc = 'Common questions about tire installation, brake service, oil changes, and auto repair at Oregon Tires Auto Care in Portland, OR.';
$pageDescEs = 'Preguntas comunes sobre instalación de llantas, servicio de frenos, cambios de aceite y reparación automotriz en Oregon Tires Auto Care en Portland, OR.';
$canonicalUrl = 'https://oregon.tires/faq';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title"><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" id="page-desc" content="<?= htmlspecialchars($pageDesc) ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
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
    .faq-item summary { list-style: none; }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary .faq-chevron { transition: transform 0.2s ease; }
    .faq-item[open] summary .faq-chevron { transform: rotate(180deg); }
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

<?php if (!empty($faqs)): ?>
  <!-- FAQPage JSON-LD -->
  <script type="application/ld+json">
  <?= json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => array_map(fn($faq) => [
          '@type' => 'Question',
          'name' => $faq['question_en'],
          'acceptedAnswer' => [
              '@type' => 'Answer',
              'text' => $faq['answer_en'],
          ],
      ], $faqs),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
  </script>
<?php endif; ?>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "FAQ"}
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
            <li class="text-white font-medium" data-t="breadcrumbCurrent">FAQ</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold mb-2" data-t="faqTitle">Frequently Asked Questions</h1>
        <p class="text-lg opacity-90 max-w-2xl" data-t="faqSubtitle">Find answers to common questions about our tire, brake, oil change, and auto repair services.</p>
      </div>
    </section>

    <!-- FAQ Accordion -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-3xl">
<?php if ($dbError): ?>
        <div class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400 text-lg" data-t="faqError">Our FAQ section is temporarily unavailable. Please check back soon or contact us directly.</p>
          <a href="/contact" class="inline-block mt-4 bg-brand text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-800 transition" data-t="contactUs">Contact Us</a>
        </div>
<?php elseif (empty($faqs)): ?>
        <div class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400 text-lg" data-t="faqEmpty">No FAQs available yet. Please check back soon.</p>
        </div>
<?php else: ?>
        <div class="space-y-3">
<?php foreach ($faqs as $faq): ?>
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q-<?= (int)$faq['id'] ?>"><?= htmlspecialchars($faq['question_en']) ?></span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a-<?= (int)$faq['id'] ?>"><?= htmlspecialchars($faq['answer_en']) ?></div>
          </details>
<?php endforeach; ?>
        </div>
<?php endif; ?>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Still Have Questions?</h2>
        <p class="mb-6" data-t="ctaDesc">Contact us directly or book an appointment. We are happy to help in English or Spanish.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="bookEstimate">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="callUs">Call (503) 367-9714</a>
          <a href="/contact" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="contactUs">Contact Us</a>
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

  <!-- Bilingual Translation Script -->
  <?php
  // Build translation object in PHP, output as single JSON blob
  $translations = [
      'home'             => ['en' => 'Home', 'es' => 'Inicio'],
      'breadcrumbCurrent'=> ['en' => 'FAQ', 'es' => 'Preguntas Frecuentes'],
      'faqTitle'         => ['en' => 'Frequently Asked Questions', 'es' => 'Preguntas Frecuentes'],
      'faqSubtitle'      => ['en' => 'Find answers to common questions about our tire, brake, oil change, and auto repair services.', 'es' => 'Encuentre respuestas a preguntas comunes sobre nuestros servicios de llantas, frenos, cambios de aceite y reparación automotriz.'],
      'ctaTitle'         => ['en' => 'Still Have Questions?', 'es' => '¿Aún Tiene Preguntas?'],
      'ctaDesc'          => ['en' => 'Contact us directly or book an appointment. We are happy to help in English or Spanish.', 'es' => 'Contáctenos directamente o reserve una cita. Con gusto le ayudamos en inglés o español.'],
      'bookEstimate'     => ['en' => 'Book Free Estimate', 'es' => 'Reserve Estimado Gratis'],
      'callUs'           => ['en' => 'Call (503) 367-9714', 'es' => 'Llame (503) 367-9714'],
      'contactUs'        => ['en' => 'Contact Us', 'es' => 'Contáctenos'],
      'callNow'          => ['en' => 'Call Now', 'es' => 'Llamar'],
      'bookNow'          => ['en' => 'Book Now', 'es' => 'Reservar'],
      'faqError'         => ['en' => 'Our FAQ section is temporarily unavailable. Please check back soon or contact us directly.', 'es' => 'Nuestra sección de preguntas frecuentes no está disponible temporalmente. Vuelva pronto o contáctenos directamente.'],
      'faqEmpty'         => ['en' => 'No FAQs available yet. Please check back soon.', 'es' => 'Aún no hay preguntas frecuentes disponibles. Vuelva pronto.'],
      '_pageTitle'       => ['en' => $pageTitle, 'es' => $pageTitleEs],
      '_pageDesc'        => ['en' => $pageDesc, 'es' => $pageDescEs],
  ];
  foreach ($faqs as $faq) {
      $id = (int)$faq['id'];
      $translations["q-{$id}"] = ['en' => $faq['question_en'], 'es' => $faq['question_es']];
      $translations["a-{$id}"] = ['en' => $faq['answer_en'], 'es' => $faq['answer_es']];
  }
  ?>
  <script>
  (function(){
    var t = <?= json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang');
    if (!lang) {
      try { var saved = localStorage.getItem('oregontires_lang'); if (saved === 'es') lang = 'es'; } catch(e) {}
    }
    if (!lang) lang = 'en';
    window.currentLang = lang;

    if (lang === 'es') {
      document.documentElement.lang = 'es';
      document.getElementById('page-title').textContent = t._pageTitle.es;
      document.getElementById('page-desc').setAttribute('content', t._pageDesc.es);
    }

    document.querySelectorAll('[data-t]').forEach(function(el){
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][lang]) el.textContent = t[key][lang];
    });
  })();
  </script>
</body>
</html>
