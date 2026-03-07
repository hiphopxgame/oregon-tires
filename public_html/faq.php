<?php
/**
 * Oregon Tires — FAQ Page
 * Bilingual (EN/ES) frequently asked questions with accordion layout.
 */
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

  <!-- FAQPage JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "What are your hours of operation?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We are open Monday through Saturday, 7:00 AM to 7:00 PM. We are closed on Sundays. Walk-ins are welcome during business hours."
        }
      },
      {
        "@type": "Question",
        "name": "Do you speak Spanish?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes! Our entire team is fully bilingual in English and Spanish. We can assist you in whichever language you prefer, from scheduling to explaining repairs."
        }
      },
      {
        "@type": "Question",
        "name": "Do I need an appointment?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Appointments are recommended but not required. Walk-ins are always welcome and we offer same-day service for most jobs. Booking online helps us prepare for your visit and reduce wait times."
        }
      },
      {
        "@type": "Question",
        "name": "How long does a tire installation take?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "A standard 4-tire installation typically takes 45 minutes to 1 hour. This includes mounting, balancing, and a complimentary safety inspection."
        }
      },
      {
        "@type": "Question",
        "name": "Do you offer free estimates?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes, we provide free estimates on all services. Bring your vehicle in or book online and we will inspect it and give you an honest quote before any work begins."
        }
      },
      {
        "@type": "Question",
        "name": "What payment methods do you accept?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We accept cash, all major credit and debit cards (Visa, Mastercard, American Express, Discover), Apple Pay, and Google Pay."
        }
      },
      {
        "@type": "Question",
        "name": "Do you have a warranty on services?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes, all our services come with a 12-month / 12,000-mile warranty. If something is not right, bring it back and we will make it right at no extra cost."
        }
      },
      {
        "@type": "Question",
        "name": "How do I know if I need new tires?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Signs you need new tires include: tread depth below 2/32 of an inch, visible cracks or bulges in the sidewall, uneven tread wear, vibration while driving, or if your tires are over 6 years old. We offer free tire inspections to help you decide."
        }
      },
      {
        "@type": "Question",
        "name": "How often should I get an oil change?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Most vehicles need an oil change every 3,000 to 5,000 miles for conventional oil, or every 5,000 to 7,500 miles for synthetic oil. Check your owner's manual for your specific vehicle's recommendation, or ask our team."
        }
      },
      {
        "@type": "Question",
        "name": "Do you offer fleet services?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes, we offer fleet maintenance programs for businesses of all sizes. This includes priority scheduling, volume pricing, detailed service records, and dedicated account management. Contact us to set up a fleet account."
        }
      }
    ]
  }
  </script>
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
        <div class="space-y-3">

          <!-- Q1: Hours -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q1">What are your hours of operation?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a1">We are open Monday through Saturday, 7:00 AM to 7:00 PM. We are closed on Sundays. Walk-ins are welcome during business hours.</div>
          </details>

          <!-- Q2: Spanish -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q2">Do you speak Spanish?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a2">Yes! Our entire team is fully bilingual in English and Spanish. We can assist you in whichever language you prefer, from scheduling to explaining repairs.</div>
          </details>

          <!-- Q3: Appointment -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q3">Do I need an appointment?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a3">Appointments are recommended but not required. Walk-ins are always welcome and we offer same-day service for most jobs. Booking online helps us prepare for your visit and reduce wait times.</div>
          </details>

          <!-- Q4: Tire installation time -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q4">How long does a tire installation take?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a4">A standard 4-tire installation typically takes 45 minutes to 1 hour. This includes mounting, balancing, and a complimentary safety inspection.</div>
          </details>

          <!-- Q5: Free estimates -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q5">Do you offer free estimates?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a5">Yes, we provide free estimates on all services. Bring your vehicle in or book online and we will inspect it and give you an honest quote before any work begins.</div>
          </details>

          <!-- Q6: Payment methods -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q6">What payment methods do you accept?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a6">We accept cash, all major credit and debit cards (Visa, Mastercard, American Express, Discover), Apple Pay, and Google Pay.</div>
          </details>

          <!-- Q7: Warranty -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q7">Do you have a warranty on services?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a7">Yes, all our services come with a 12-month / 12,000-mile warranty. If something is not right, bring it back and we will make it right at no extra cost.</div>
          </details>

          <!-- Q8: New tires -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q8">How do I know if I need new tires?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a8">Signs you need new tires include: tread depth below 2/32 of an inch, visible cracks or bulges in the sidewall, uneven tread wear, vibration while driving, or if your tires are over 6 years old. We offer free tire inspections to help you decide.</div>
          </details>

          <!-- Q9: Oil change -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q9">How often should I get an oil change?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a9">Most vehicles need an oil change every 3,000 to 5,000 miles for conventional oil, or every 5,000 to 7,500 miles for synthetic oil. Check your owner's manual for your specific vehicle's recommendation, or ask our team.</div>
          </details>

          <!-- Q10: Fleet services -->
          <details class="faq-item border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 shadow-sm">
            <summary class="flex justify-between items-center p-4 cursor-pointer font-semibold text-gray-800 dark:text-gray-100 hover:text-brand dark:hover:text-green-400 transition-colors">
              <span data-t="q10">Do you offer fleet services?</span>
              <svg class="faq-chevron w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 text-gray-600 dark:text-gray-300" data-t="a10">Yes, we offer fleet maintenance programs for businesses of all sizes. This includes priority scheduling, volume pricing, detailed service records, and dedicated account management. Contact us to set up a fleet account.</div>
          </details>

        </div>
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
  <script>
  (function(){
    var t = {
      home:             { en: 'Home', es: 'Inicio' },
      breadcrumbCurrent:{ en: 'FAQ', es: 'Preguntas Frecuentes' },
      faqTitle:         { en: 'Frequently Asked Questions', es: 'Preguntas Frecuentes' },
      faqSubtitle:      { en: 'Find answers to common questions about our tire, brake, oil change, and auto repair services.', es: 'Encuentre respuestas a preguntas comunes sobre nuestros servicios de llantas, frenos, cambios de aceite y reparaci\u00f3n automotriz.' },
      q1:  { en: 'What are your hours of operation?', es: '\u00bfCu\u00e1l es su horario de atenci\u00f3n?' },
      a1:  { en: 'We are open Monday through Saturday, 7:00 AM to 7:00 PM. We are closed on Sundays. Walk-ins are welcome during business hours.', es: 'Estamos abiertos de lunes a s\u00e1bado, de 7:00 AM a 7:00 PM. Cerramos los domingos. Se aceptan visitas sin cita durante el horario de atenci\u00f3n.' },
      q2:  { en: 'Do you speak Spanish?', es: '\u00bfHablan espa\u00f1ol?' },
      a2:  { en: 'Yes! Our entire team is fully bilingual in English and Spanish. We can assist you in whichever language you prefer, from scheduling to explaining repairs.', es: '\u00a1S\u00ed! Todo nuestro equipo es completamente biling\u00fce en ingl\u00e9s y espa\u00f1ol. Podemos atenderle en el idioma que prefiera, desde programar citas hasta explicar reparaciones.' },
      q3:  { en: 'Do I need an appointment?', es: '\u00bfNecesito una cita?' },
      a3:  { en: 'Appointments are recommended but not required. Walk-ins are always welcome and we offer same-day service for most jobs. Booking online helps us prepare for your visit and reduce wait times.', es: 'Las citas son recomendadas pero no obligatorias. Siempre aceptamos visitas sin cita y ofrecemos servicio el mismo d\u00eda para la mayor\u00eda de los trabajos. Reservar en l\u00ednea nos ayuda a prepararnos para su visita y reducir tiempos de espera.' },
      q4:  { en: 'How long does a tire installation take?', es: '\u00bfCu\u00e1nto tiempo toma una instalaci\u00f3n de llantas?' },
      a4:  { en: 'A standard 4-tire installation typically takes 45 minutes to 1 hour. This includes mounting, balancing, and a complimentary safety inspection.', es: 'Una instalaci\u00f3n est\u00e1ndar de 4 llantas generalmente toma de 45 minutos a 1 hora. Esto incluye montaje, balanceo y una inspecci\u00f3n de seguridad de cortes\u00eda.' },
      q5:  { en: 'Do you offer free estimates?', es: '\u00bfOfrecen presupuestos gratis?' },
      a5:  { en: 'Yes, we provide free estimates on all services. Bring your vehicle in or book online and we will inspect it and give you an honest quote before any work begins.', es: 'S\u00ed, ofrecemos presupuestos gratis en todos los servicios. Traiga su veh\u00edculo o reserve en l\u00ednea y lo inspeccionaremos y le daremos una cotizaci\u00f3n honesta antes de comenzar cualquier trabajo.' },
      q6:  { en: 'What payment methods do you accept?', es: '\u00bfQu\u00e9 m\u00e9todos de pago aceptan?' },
      a6:  { en: 'We accept cash, all major credit and debit cards (Visa, Mastercard, American Express, Discover), Apple Pay, and Google Pay.', es: 'Aceptamos efectivo, todas las tarjetas de cr\u00e9dito y d\u00e9bito principales (Visa, Mastercard, American Express, Discover), Apple Pay y Google Pay.' },
      q7:  { en: 'Do you have a warranty on services?', es: '\u00bfTienen garant\u00eda en los servicios?' },
      a7:  { en: 'Yes, all our services come with a 12-month / 12,000-mile warranty. If something is not right, bring it back and we will make it right at no extra cost.', es: 'S\u00ed, todos nuestros servicios incluyen una garant\u00eda de 12 meses / 12,000 millas. Si algo no est\u00e1 bien, tr\u00e1igalo de vuelta y lo corregiremos sin costo adicional.' },
      q8:  { en: 'How do I know if I need new tires?', es: '\u00bfC\u00f3mo s\u00e9 si necesito llantas nuevas?' },
      a8:  { en: 'Signs you need new tires include: tread depth below 2/32 of an inch, visible cracks or bulges in the sidewall, uneven tread wear, vibration while driving, or if your tires are over 6 years old. We offer free tire inspections to help you decide.', es: 'Se\u00f1ales de que necesita llantas nuevas incluyen: profundidad del dibujo menor a 2/32 de pulgada, grietas o protuberancias visibles en la pared lateral, desgaste desigual, vibraci\u00f3n al conducir, o si sus llantas tienen m\u00e1s de 6 a\u00f1os. Ofrecemos inspecciones de llantas gratis para ayudarle a decidir.' },
      q9:  { en: 'How often should I get an oil change?', es: '\u00bfCon qu\u00e9 frecuencia debo cambiar el aceite?' },
      a9:  { en: 'Most vehicles need an oil change every 3,000 to 5,000 miles for conventional oil, or every 5,000 to 7,500 miles for synthetic oil. Check your owner\'s manual for your specific vehicle\'s recommendation, or ask our team.', es: 'La mayor\u00eda de los veh\u00edculos necesitan un cambio de aceite cada 3,000 a 5,000 millas para aceite convencional, o cada 5,000 a 7,500 millas para aceite sint\u00e9tico. Consulte el manual de su veh\u00edculo para la recomendaci\u00f3n espec\u00edfica, o pregunte a nuestro equipo.' },
      q10: { en: 'Do you offer fleet services?', es: '\u00bfOfrecen servicios para flotas?' },
      a10: { en: 'Yes, we offer fleet maintenance programs for businesses of all sizes. This includes priority scheduling, volume pricing, detailed service records, and dedicated account management. Contact us to set up a fleet account.', es: 'S\u00ed, ofrecemos programas de mantenimiento para flotas de empresas de todos los tama\u00f1os. Esto incluye programaci\u00f3n prioritaria, precios por volumen, registros detallados de servicio y gesti\u00f3n de cuenta dedicada. Cont\u00e1ctenos para configurar una cuenta de flota.' },
      ctaTitle:     { en: 'Still Have Questions?', es: '\u00bfA\u00fan Tiene Preguntas?' },
      ctaDesc:      { en: 'Contact us directly or book an appointment. We are happy to help in English or Spanish.', es: 'Cont\u00e1ctenos directamente o reserve una cita. Con gusto le ayudamos en ingl\u00e9s o espa\u00f1ol.' },
      bookEstimate: { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      callUs:       { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      contactUs:    { en: 'Contact Us', es: 'Cont\u00e1ctenos' },
      callNow:      { en: 'Call Now', es: 'Llamar' },
      bookNow:      { en: 'Book Now', es: 'Reservar' }
    };

    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang');
    if (!lang) {
      try { var saved = localStorage.getItem('oregontires_lang'); if (saved === 'es') lang = 'es'; } catch(e) {}
    }
    if (!lang) lang = 'en';
    window.currentLang = lang;

    if (lang === 'es') {
      document.documentElement.lang = 'es';
      document.getElementById('page-title').textContent = '<?= addslashes($pageTitleEs) ?>';
      document.getElementById('page-desc').setAttribute('content', '<?= addslashes($pageDescEs) ?>');
    }

    document.querySelectorAll('[data-t]').forEach(function(el){
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][lang]) el.textContent = t[key][lang];
    });
  })();
  </script>
</body>
</html>
