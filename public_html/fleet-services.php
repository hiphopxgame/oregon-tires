<?php
// Fleet Services B2B page — Oregon Tires Auto Care
require_once __DIR__ . '/includes/seo-lang.php';
$pageTitle = 'Fleet Services - Oregon Tires Auto Care Portland, OR';
$pageTitleEs = 'Servicios de Flota - Oregon Tires Auto Care Portland, OR';
$pageDesc = 'Fleet tire and auto service for Portland businesses. Volume discounts, dedicated account management, and priority scheduling for 5+ vehicles.';
$pageDescEs = 'Servicio de llantas y automotriz para flotas de empresas en Portland. Descuentos por volumen, gestión de cuentas dedicada y programación prioritaria para 5+ vehículos.';
?>
<!DOCTYPE html>
<html lang="<?= seoLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?></title>
  <meta name="description" content="<?= htmlspecialchars(seoMeta($pageDesc, $pageDescEs)) ?>">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
  <link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
  <meta name="theme-color" content="#15803d">
  <link rel="canonical" href="https://oregon.tires/fleet-services">
  <link rel="alternate" hreflang="en" href="https://oregon.tires/fleet-services?lang=en">
  <link rel="alternate" hreflang="es" href="https://oregon.tires/fleet-services?lang=es">
  <meta property="og:title" content="<?= htmlspecialchars(seoMeta($pageTitle, $pageTitleEs)) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(seoMeta($pageDesc, $pageDescEs)) ?>">
  <meta property="og:locale" content="<?= seoOgLocale() ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://oregon.tires/fleet-services">
  <link rel="stylesheet" href="/assets/styles.css">
  <style>
    html { scroll-behavior: smooth; }
    :root { --brand-primary: #15803d; --brand-dark: #0D3618; }
  </style>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type":"ListItem","position":1,"name":"Home","item":"https://oregon.tires/"},
      {"@type":"ListItem","position":2,"name":"Fleet Services","item":"https://oregon.tires/fleet-services"}
    ]
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "Fleet Auto Services",
    "description": "Commercial fleet tire and auto maintenance services with volume pricing for Portland-area businesses.",
    "provider": {
      "@type": "AutomotiveBusiness",
      "name": "Oregon Tires Auto Care",
      "url": "https://oregon.tires"
    },
    "areaServed": {
      "@type": "City",
      "name": "Portland",
      "addressRegion": "OR"
    },
    "serviceType": "Fleet Vehicle Maintenance"
  }
  </script>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold">Skip to main content</a>

  <?php require __DIR__ . '/templates/header.php'; ?>

  <main id="main-content" class="flex-1">
    <!-- Breadcrumb -->
    <nav class="container mx-auto px-4 pt-4 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
      <a href="/" class="hover:text-brand dark:hover:text-green-400" data-t="home">Home</a>
      <span aria-hidden="true">/</span>
      <span class="text-gray-700 dark:text-gray-200" data-t="fleetTitle">Fleet Services</span>
    </nav>

    <!-- Hero -->
    <section class="bg-brand text-white py-16">
      <div class="container mx-auto px-4 text-center max-w-3xl">
        <h1 class="text-3xl md:text-4xl font-bold mb-4" data-t="fleetHero">Fleet Services for Portland Businesses</h1>
        <p class="text-lg text-green-100 mb-6" data-t="fleetHeroSub">Keep your fleet running with volume discounts, priority scheduling, and dedicated account management.</p>
        <a href="#fleet-contact" class="inline-block bg-amber-500 text-black px-8 py-3 rounded-lg font-bold text-lg hover:bg-amber-600 transition" data-t="fleetCta">Get a Fleet Quote</a>
      </div>
    </section>

    <!-- Volume Discount Tiers -->
    <section class="container mx-auto px-4 py-12 max-w-5xl">
      <h2 class="text-2xl font-bold text-center text-brand dark:text-green-400 mb-8" data-t="fleetPricing">Volume Discount Tiers</h2>
      <div class="grid md:grid-cols-3 gap-6">
        <!-- Tier 1 -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center border-2 border-transparent hover:border-brand transition">
          <div class="text-4xl font-bold text-brand dark:text-green-400 mb-2">5+</div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" data-t="fleetVehicles">vehicles</p>
          <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1" data-t="fleetTier1">10% Off All Services</div>
          <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 mt-4 text-left">
            <li data-t="fleetTier1a">Priority scheduling</li>
            <li data-t="fleetTier1b">Monthly invoicing available</li>
            <li data-t="fleetTier1c">Dedicated service contact</li>
          </ul>
        </div>
        <!-- Tier 2 -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center border-2 border-brand relative">
          <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-black text-xs font-bold px-3 py-1 rounded-full" data-t="fleetPopular">Most Popular</span>
          <div class="text-4xl font-bold text-brand dark:text-green-400 mb-2">10+</div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" data-t="fleetVehicles">vehicles</p>
          <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1" data-t="fleetTier2">15% Off All Services</div>
          <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 mt-4 text-left">
            <li data-t="fleetTier2a">Everything in 5+ tier</li>
            <li data-t="fleetTier2b">Dedicated account manager</li>
            <li data-t="fleetTier2c">Fleet maintenance reports</li>
            <li data-t="fleetTier2d">Emergency same-day service</li>
          </ul>
        </div>
        <!-- Tier 3 -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 text-center border-2 border-transparent hover:border-brand transition">
          <div class="text-4xl font-bold text-brand dark:text-green-400 mb-2">25+</div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" data-t="fleetVehicles">vehicles</p>
          <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1" data-t="fleetTier3">20% Off + Custom Pricing</div>
          <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 mt-4 text-left">
            <li data-t="fleetTier3a">Everything in 10+ tier</li>
            <li data-t="fleetTier3b">Custom maintenance schedule</li>
            <li data-t="fleetTier3c">On-site service options</li>
            <li data-t="fleetTier3d">Quarterly fleet inspections</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Services Included -->
    <section class="bg-white dark:bg-gray-800 py-12">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-center text-brand dark:text-green-400 mb-8" data-t="fleetServices">Fleet Services Include</h2>
        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F527;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc1">Tire Installation & Rotation</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F6E2;&#xFE0F;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc2">Oil Changes</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F6DE;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc3">Brake Service</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x2699;&#xFE0F;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc4">Alignment & Suspension</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F50B;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc5">Battery Service</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x2744;&#xFE0F;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc6">A/C Service</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F4CB;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc7">Digital Inspections</p>
          </div>
          <div class="text-center p-4">
            <span class="text-3xl block mb-2" aria-hidden="true">&#x1F69A;</span>
            <p class="font-medium text-gray-700 dark:text-gray-300" data-t="fleetSvc8">On-Site Service (25+)</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Contact Form -->
    <section id="fleet-contact" class="container mx-auto px-4 py-12 max-w-2xl">
      <h2 class="text-2xl font-bold text-center text-brand dark:text-green-400 mb-2" data-t="fleetFormTitle">Get Your Fleet Quote</h2>
      <p class="text-center text-gray-500 dark:text-gray-400 mb-8" data-t="fleetFormSub">Tell us about your fleet and we'll prepare a custom quote within 24 hours.</p>

      <form id="fleet-form" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="fleet-company" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetCompany">Company Name *</label>
            <input type="text" id="fleet-company" name="company" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
          </div>
          <div>
            <label for="fleet-contact-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetContact">Contact Name *</label>
            <input type="text" id="fleet-contact-name" name="contact_name" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="fleet-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetEmail">Email *</label>
            <input type="email" id="fleet-email" name="email" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
          </div>
          <div>
            <label for="fleet-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetPhone">Phone *</label>
            <input type="tel" id="fleet-phone" name="phone" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
          </div>
        </div>
        <div>
          <label for="fleet-size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetSize">Fleet Size *</label>
          <select id="fleet-size" name="fleet_size" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100">
            <option value="">Select fleet size</option>
            <option value="5-9">5-9 vehicles</option>
            <option value="10-24">10-24 vehicles</option>
            <option value="25-49">25-49 vehicles</option>
            <option value="50+">50+ vehicles</option>
          </select>
        </div>
        <div>
          <label for="fleet-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="fleetNotes">Tell us about your fleet (vehicle types, current needs)</label>
          <textarea id="fleet-notes" name="notes" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"></textarea>
        </div>
        <div id="fleet-status" class="hidden text-sm font-medium p-3 rounded-lg"></div>
        <button type="submit" class="w-full bg-amber-500 text-black font-bold py-3 rounded-lg text-lg hover:bg-amber-600 transition" data-t="fleetSubmit">Request Fleet Quote</button>
      </form>
    </section>

    <!-- CTA -->
    <section class="bg-brand text-white py-10 text-center">
      <div class="container mx-auto px-4">
        <p class="text-xl font-bold mb-2" data-t="fleetCtaBottom">Ready to save on fleet maintenance?</p>
        <p class="text-green-100 mb-4" data-t="fleetCtaBottomSub">Call us to discuss your fleet needs today.</p>
        <a href="tel:5033679714" class="inline-block bg-white text-brand font-bold px-8 py-3 rounded-lg text-lg hover:bg-gray-100 transition">(503) 367-9714</a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-brand text-white py-6">
    <div class="container mx-auto px-4 text-center">
      <p class="text-gray-200">&copy; 2026 Oregon Tires Auto Care. All rights reserved.</p>
      <p class="mt-2 text-xs text-gray-300">Powered by <a href="https://1vsM.com" target="_blank" rel="noopener noreferrer" class="text-amber-200 hover:text-amber-100 transition-colors">1vsM.com</a></p>
    </div>
  </footer>

  <script>
  var currentLang = localStorage.getItem('oregontires_lang') || 'en';
  var t = {
    en: {
      home: 'Home', fleetTitle: 'Fleet Services',
      fleetHero: 'Fleet Services for Portland Businesses',
      fleetHeroSub: 'Keep your fleet running with volume discounts, priority scheduling, and dedicated account management.',
      fleetCta: 'Get a Fleet Quote',
      fleetPricing: 'Volume Discount Tiers',
      fleetVehicles: 'vehicles',
      fleetTier1: '10% Off All Services', fleetTier1a: 'Priority scheduling', fleetTier1b: 'Monthly invoicing available', fleetTier1c: 'Dedicated service contact',
      fleetPopular: 'Most Popular',
      fleetTier2: '15% Off All Services', fleetTier2a: 'Everything in 5+ tier', fleetTier2b: 'Dedicated account manager', fleetTier2c: 'Fleet maintenance reports', fleetTier2d: 'Emergency same-day service',
      fleetTier3: '20% Off + Custom Pricing', fleetTier3a: 'Everything in 10+ tier', fleetTier3b: 'Custom maintenance schedule', fleetTier3c: 'On-site service options', fleetTier3d: 'Quarterly fleet inspections',
      fleetServices: 'Fleet Services Include',
      fleetSvc1: 'Tire Installation & Rotation', fleetSvc2: 'Oil Changes', fleetSvc3: 'Brake Service', fleetSvc4: 'Alignment & Suspension',
      fleetSvc5: 'Battery Service', fleetSvc6: 'A/C Service', fleetSvc7: 'Digital Inspections', fleetSvc8: 'On-Site Service (25+)',
      fleetFormTitle: 'Get Your Fleet Quote', fleetFormSub: "Tell us about your fleet and we'll prepare a custom quote within 24 hours.",
      fleetCompany: 'Company Name *', fleetContact: 'Contact Name *', fleetEmail: 'Email *', fleetPhone: 'Phone *',
      fleetSize: 'Fleet Size *', fleetNotes: 'Tell us about your fleet (vehicle types, current needs)',
      fleetSubmit: 'Request Fleet Quote',
      fleetCtaBottom: 'Ready to save on fleet maintenance?', fleetCtaBottomSub: 'Call us to discuss your fleet needs today.',
      fleetSuccess: 'Thank you! We will contact you within 24 hours with your fleet quote.',
      fleetError: 'Something went wrong. Please call us at (503) 367-9714.'
    },
    es: {
      home: 'Inicio', fleetTitle: 'Servicios de Flota',
      fleetHero: 'Servicios de Flota para Negocios de Portland',
      fleetHeroSub: 'Mantenga su flota en marcha con descuentos por volumen, programación prioritaria y gestión de cuenta dedicada.',
      fleetCta: 'Obtener Cotización',
      fleetPricing: 'Niveles de Descuento por Volumen',
      fleetVehicles: 'vehículos',
      fleetTier1: '10% de Descuento', fleetTier1a: 'Programación prioritaria', fleetTier1b: 'Facturación mensual disponible', fleetTier1c: 'Contacto de servicio dedicado',
      fleetPopular: 'Más Popular',
      fleetTier2: '15% de Descuento', fleetTier2a: 'Todo en el nivel 5+', fleetTier2b: 'Gerente de cuenta dedicado', fleetTier2c: 'Reportes de mantenimiento', fleetTier2d: 'Servicio de emergencia el mismo día',
      fleetTier3: '20% + Precio Personalizado', fleetTier3a: 'Todo en el nivel 10+', fleetTier3b: 'Programa de mantenimiento personalizado', fleetTier3c: 'Opciones de servicio en sitio', fleetTier3d: 'Inspecciones trimestrales',
      fleetServices: 'Servicios de Flota Incluyen',
      fleetSvc1: 'Instalación y Rotación de Llantas', fleetSvc2: 'Cambios de Aceite', fleetSvc3: 'Servicio de Frenos', fleetSvc4: 'Alineación y Suspensión',
      fleetSvc5: 'Servicio de Batería', fleetSvc6: 'Servicio de A/C', fleetSvc7: 'Inspecciones Digitales', fleetSvc8: 'Servicio en Sitio (25+)',
      fleetFormTitle: 'Obtenga su Cotización de Flota', fleetFormSub: 'Cuéntenos sobre su flota y prepararemos una cotización personalizada en 24 horas.',
      fleetCompany: 'Nombre de Empresa *', fleetContact: 'Nombre de Contacto *', fleetEmail: 'Correo *', fleetPhone: 'Teléfono *',
      fleetSize: 'Tamaño de Flota *', fleetNotes: 'Cuéntenos sobre su flota (tipos de vehículos, necesidades actuales)',
      fleetSubmit: 'Solicitar Cotización',
      fleetCtaBottom: '\u00bfListo para ahorrar en mantenimiento de flota?', fleetCtaBottomSub: 'Llámenos para discutir las necesidades de su flota hoy.',
      fleetSuccess: '\u00a1Gracias! Le contactaremos dentro de 24 horas con su cotización.',
      fleetError: 'Algo salió mal. Por favor llámenos al (503) 367-9714.'
    }
  };

  // Apply language
  if (currentLang !== 'en') {
    document.documentElement.lang = currentLang;
    document.querySelectorAll('[data-t]').forEach(function(el) {
      var key = el.getAttribute('data-t');
      if (t[currentLang] && t[currentLang][key]) el.textContent = t[currentLang][key];
    });
  }

  // Fleet form submission (sends to contact API)
  document.getElementById('fleet-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    var statusEl = document.getElementById('fleet-status');
    var submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = currentLang === 'es' ? 'Enviando...' : 'Sending...';
    statusEl.className = 'hidden';

    var data = {
      name: document.getElementById('fleet-contact-name').value.trim(),
      email: document.getElementById('fleet-email').value.trim(),
      phone: document.getElementById('fleet-phone').value.trim(),
      message: 'FLEET INQUIRY\nCompany: ' + document.getElementById('fleet-company').value.trim()
        + '\nFleet Size: ' + document.getElementById('fleet-size').value
        + '\nNotes: ' + (document.getElementById('fleet-notes').value.trim() || 'N/A'),
      language: currentLang === 'en' ? 'english' : 'spanish'
    };

    try {
      var res = await fetch('/api/contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data)
      });
      var json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
      statusEl.textContent = t[currentLang].fleetSuccess;
      statusEl.className = 'text-sm font-medium p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300';
      this.reset();
      if (typeof gtag === 'function') gtag('event', 'fleet_inquiry', { event_category: 'lead', fleet_size: data.message });
    } catch (err) {
      statusEl.textContent = t[currentLang].fleetError;
      statusEl.className = 'text-sm font-medium p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = t[currentLang].fleetSubmit;
    }
  });
  </script>
</body>
</html>
