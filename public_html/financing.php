<?php
/**
 * Oregon Tires — Financing Options
 * Bilingual page describing flexible payment options.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Override bootstrap's JSON Content-Type — this is an HTML page
header('Content-Type: text/html; charset=utf-8');
header_remove('X-API-Version');

$canonicalUrl = 'https://oregon.tires/financing';
$pageTitle = 'Financing Options | Oregon Tires Auto Care';
$pageTitleEs = 'Opciones de Financiamiento | Oregon Tires Auto Care';
$pageDesc = 'Flexible payment options for tire and auto repair services at Oregon Tires Auto Care in Portland, OR. Cash, credit cards, and payment plans available.';
$pageDescEs = 'Opciones de pago flexibles para servicios de llantas y reparacion automotriz en Oregon Tires Auto Care en Portland, OR.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
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
    details summary { cursor: pointer; list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    details summary::before { content: '+'; display: inline-block; width: 1.5rem; font-weight: bold; font-size: 1.25rem; transition: transform 0.2s; }
    details[open] summary::before { content: '\2212'; }
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

  <!-- JSON-LD: BreadcrumbList -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Financing Options"}
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
            <li class="text-white font-medium" data-t="financingTitle">Financing Options</li>
          </ol>
        </nav>
        <div class="flex items-center gap-3 mb-4">
          <span class="text-4xl" aria-hidden="true">&#x1F4B3;</span>
          <h1 class="text-3xl md:text-5xl font-bold" data-t="financingTitle">Financing Options</h1>
        </div>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="financingHeroDesc">Flexible payment options so you can get the auto care you need today.</p>
        <div class="flex flex-wrap gap-3">
          <a href="tel:5033679714" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg" data-t="financingCallCta">Call to Discuss Options</a>
          <a href="/book-appointment/" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition" data-t="financingBookCta">Book Appointment</a>
        </div>
      </div>
    </section>

    <!-- Payment Options -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="paymentOptionsTitle">We Make It Easy to Pay</h2>
        <div class="grid md:grid-cols-3 gap-6">
          <!-- Cash -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-700 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-brand dark:text-green-400 mb-2" data-t="payCash">Cash</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="payCashDesc">We accept cash payments for all services. No extra fees.</p>
          </div>
          <!-- Credit/Debit Cards -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center border border-gray-200 dark:border-gray-700">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-700 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-brand dark:text-green-400 mb-2" data-t="payCards">Credit & Debit Cards</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="payCardsDesc">Visa, Mastercard, American Express, and Discover accepted.</p>
          </div>
          <!-- Payment Plans -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center border border-gray-200 dark:border-gray-700 ring-2 ring-amber-400">
            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-brand dark:text-green-400 mb-2" data-t="payPlans">Payment Plans</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="payPlansDesc">Need to split payments? Ask about our flexible payment arrangements for larger repairs.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="howFinancingWorks">How Payment Plans Work</h2>
        <div class="space-y-6">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-brand text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">1</div>
            <div>
              <h3 class="font-bold text-gray-900 dark:text-white" data-t="finStep1Title">Get Your Free Estimate</h3>
              <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="finStep1Desc">Bring your vehicle in or call us for a no-obligation estimate on the work you need.</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-brand text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">2</div>
            <div>
              <h3 class="font-bold text-gray-900 dark:text-white" data-t="finStep2Title">Discuss Payment Options</h3>
              <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="finStep2Desc">Talk to our team about what works for your budget. We will work with you to find a solution.</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-brand text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">3</div>
            <div>
              <h3 class="font-bold text-gray-900 dark:text-white" data-t="finStep3Title">Get Your Vehicle Fixed</h3>
              <p class="text-gray-600 dark:text-gray-300 text-sm" data-t="finStep3Desc">We complete the work and you drive away safely. Pay according to the agreed schedule.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6 text-center" data-t="finFaqTitle">Financing FAQ</h2>
        <div class="space-y-3">
          <details class="bg-gray-50 dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="finFaq1Q">Do you offer financing for tire purchases?</span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="finFaq1A">Yes! We can discuss flexible payment arrangements for tire purchases and larger repairs. Call us at (503) 367-9714 to discuss your options.</p>
            </div>
          </details>
          <details class="bg-gray-50 dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="finFaq2Q">Is there a minimum amount for payment plans?</span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="finFaq2A">Payment arrangements are typically available for services over $200. Contact us to discuss your specific situation — we are happy to work with you.</p>
            </div>
          </details>
          <details class="bg-gray-50 dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="finFaq3Q">What credit cards do you accept?</span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="finFaq3A">We accept Visa, Mastercard, American Express, and Discover. We also accept cash and debit cards.</p>
            </div>
          </details>
          <details class="bg-gray-50 dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="finFaq4Q">Do I need a credit check for payment plans?</span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="finFaq4A">Our in-house payment arrangements do not require a credit check. We work directly with you based on the service needed and your budget.</p>
            </div>
          </details>
          <details class="bg-gray-50 dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
            <summary class="flex items-center gap-2 px-6 py-4 font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition">
              <span data-t="finFaq5Q">Can I combine a payment plan with a promotion?</span>
            </summary>
            <div class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              <p data-t="finFaq5A">In most cases, yes! Ask our team about combining current promotions with flexible payment options. We want to help you get the best deal possible.</p>
            </div>
          </details>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="finCtaTitle">Ready to Get Started?</h2>
        <p class="mb-6 max-w-2xl mx-auto" data-t="finCtaSubtitle">Call us to discuss payment options or book your appointment online. Free estimates, no obligation.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="tel:5033679714" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="finCtaCall">Call (503) 367-9714</a>
          <a href="/book-appointment/" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="finCtaBook">Book Free Estimate</a>
          <a href="sms:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="finCtaText">Text Us</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> <span data-t="mobileCall">Call Now</span>
      </a>
      <a href="/book-appointment/" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> <span data-t="mobileBook">Book Now</span>
      </a>
    </div>
  </div>

  <!-- Bilingual Toggle Script -->
  <script>
  (function() {
    var t = {
      financingTitle: 'Opciones de Financiamiento',
      financingHeroDesc: 'Opciones de pago flexibles para que obtenga el servicio automotriz que necesita hoy.',
      financingCallCta: 'Llame para Discutir Opciones',
      financingBookCta: 'Reservar Cita',
      paymentOptionsTitle: 'Facilitamos el Pago',
      payCash: 'Efectivo',
      payCashDesc: 'Aceptamos pagos en efectivo para todos los servicios. Sin cargos adicionales.',
      payCards: 'Tarjetas de Cr\u00e9dito y D\u00e9bito',
      payCardsDesc: 'Aceptamos Visa, Mastercard, American Express y Discover.',
      payPlans: 'Planes de Pago',
      payPlansDesc: '\u00bfNecesita dividir los pagos? Pregunte sobre nuestros arreglos de pago flexibles para reparaciones mayores.',
      howFinancingWorks: 'C\u00f3mo Funcionan los Planes de Pago',
      finStep1Title: 'Obtenga Su Estimado Gratis',
      finStep1Desc: 'Traiga su veh\u00edculo o ll\u00e1menos para un estimado sin compromiso del trabajo que necesita.',
      finStep2Title: 'Discuta Opciones de Pago',
      finStep2Desc: 'Hable con nuestro equipo sobre lo que funciona para su presupuesto. Trabajaremos con usted para encontrar una soluci\u00f3n.',
      finStep3Title: 'Repare Su Veh\u00edculo',
      finStep3Desc: 'Completamos el trabajo y usted se va seguro. Pague seg\u00fan el calendario acordado.',
      finFaqTitle: 'Preguntas Frecuentes sobre Financiamiento',
      finFaq1Q: '\u00bfOfrecen financiamiento para compra de llantas?',
      finFaq1A: '\u00a1S\u00ed! Podemos discutir arreglos de pago flexibles para compra de llantas y reparaciones mayores. Ll\u00e1menos al (503) 367-9714 para discutir sus opciones.',
      finFaq2Q: '\u00bfHay un monto m\u00ednimo para planes de pago?',
      finFaq2A: 'Los arreglos de pago generalmente est\u00e1n disponibles para servicios de m\u00e1s de $200. Cont\u00e1ctenos para discutir su situaci\u00f3n espec\u00edfica.',
      finFaq3Q: '\u00bfQu\u00e9 tarjetas de cr\u00e9dito aceptan?',
      finFaq3A: 'Aceptamos Visa, Mastercard, American Express y Discover. Tambi\u00e9n aceptamos efectivo y tarjetas de d\u00e9bito.',
      finFaq4Q: '\u00bfNecesito verificaci\u00f3n de cr\u00e9dito para planes de pago?',
      finFaq4A: 'Nuestros arreglos de pago internos no requieren verificaci\u00f3n de cr\u00e9dito. Trabajamos directamente con usted seg\u00fan el servicio necesario y su presupuesto.',
      finFaq5Q: '\u00bfPuedo combinar un plan de pago con una promoci\u00f3n?',
      finFaq5A: '\u00a1En la mayor\u00eda de los casos, s\u00ed! Pregunte a nuestro equipo sobre c\u00f3mo combinar promociones actuales con opciones de pago flexibles.',
      finCtaTitle: '\u00bfListo para Comenzar?',
      finCtaSubtitle: 'Ll\u00e1menos para discutir opciones de pago o reserve su cita en l\u00ednea. Estimados gratis, sin compromiso.',
      finCtaCall: 'Llamar (503) 367-9714',
      finCtaBook: 'Estimado Gratis',
      finCtaText: 'Env\u00edenos un Texto',
      mobileCall: 'Llamar',
      mobileBook: 'Reservar'
    };
    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang') || localStorage.getItem('oregontires_lang') || 'en';
    if (lang === 'es') {
      localStorage.setItem('oregontires_lang', 'es');
      document.documentElement.lang = 'es';
      document.title = '<?= addslashes($pageTitleEs) ?>';
      var meta = document.querySelector('meta[name="description"]');
      if (meta) meta.setAttribute('content', '<?= addslashes($pageDescEs) ?>');
      document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[key]) el.textContent = t[key];
      });
    } else {
      localStorage.setItem('oregontires_lang', 'en');
    }
  })();
  </script>
</body>
</html>
