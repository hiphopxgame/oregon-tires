<?php
/**
 * Oregon Tires — Tire Quote Request Page
 * Bilingual (EN/ES) customer-facing form for requesting a tire quote.
 */
$pageTitle = 'Get a Tire Quote | Oregon Tires Auto Care';
$pageTitleEs = 'Cotización de Llantas | Oregon Tires Auto Care';
$pageDesc = 'Request a free tire quote from Oregon Tires Auto Care. We offer competitive prices on new and used tires for all vehicle types in Portland, OR.';
$pageDescEs = 'Solicite una cotización gratuita de llantas de Oregon Tires Auto Care. Ofrecemos precios competitivos en llantas nuevas y usadas para todo tipo de vehículos en Portland, OR.';
$canonicalUrl = 'https://oregon.tires/tire-quote';
require_once __DIR__ . '/includes/seo-lang.php';
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
  <style>html { scroll-behavior: smooth; } :root { --brand-primary: #15803d; --brand-dark: #0D3618; }</style>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
  <script>(function(){if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');})();</script>

  <!-- JSON-LD: Breadcrumbs -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Tire Quote"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold" data-t="skipToContent">Skip to main content</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="main-content">
    <!-- Hero -->
    <section class="bg-brand text-white py-16 relative">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70 flex justify-center">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="breadcrumbHome">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="breadcrumbTireQuote">Tire Quote</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="tqHeroTitle">Get a Free Tire Quote</h1>
        <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto" data-t="tqHeroSub">Tell us about your vehicle and tire needs. We will respond within 24 hours with competitive pricing options.</p>
      </div>
    </section>

    <!-- Form Section -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-3xl">

        <!-- Success State (hidden by default) -->
        <div id="tq-success" class="hidden">
          <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-2xl p-8 text-center">
            <div class="text-5xl mb-4" aria-hidden="true">&#10003;</div>
            <h2 class="text-2xl font-bold text-green-700 dark:text-green-400 mb-2" data-t="tqSuccessTitle">Quote Request Received!</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6" data-t="tqSuccessMsg">Thank you! Our team will review your request and contact you within 24 hours with pricing options.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <a href="/book-appointment/" class="inline-block bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition" data-t="tqBookAppt">Book an Appointment</a>
              <a href="/" class="inline-block border-2 border-brand text-brand dark:text-green-400 dark:border-green-400 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 dark:hover:bg-gray-700 transition" data-t="tqBackHome">Back to Home</a>
            </div>
          </div>
        </div>

        <!-- Quote Form -->
        <form id="tq-form" class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg p-8" novalidate>
          <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-6" data-t="tqFormTitle">Request Your Tire Quote</h2>

          <!-- Contact Info -->
          <fieldset class="mb-8">
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="bg-brand text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold" aria-hidden="true">1</span>
              <span data-t="tqStep1">Contact Information</span>
            </legend>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label for="tq-first-name" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqFirstName">First Name *</label>
                <input type="text" id="tq-first-name" name="first_name" required maxlength="100"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
              <div>
                <label for="tq-last-name" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqLastName">Last Name</label>
                <input type="text" id="tq-last-name" name="last_name" maxlength="100"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
              <div>
                <label for="tq-email" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqEmail">Email *</label>
                <input type="email" id="tq-email" name="email" required maxlength="254"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
              <div>
                <label for="tq-phone" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqPhone">Phone</label>
                <input type="tel" id="tq-phone" name="phone" maxlength="30"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
            </div>
          </fieldset>

          <!-- Vehicle Info -->
          <fieldset class="mb-8">
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="bg-brand text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold" aria-hidden="true">2</span>
              <span data-t="tqStep2">Vehicle Information</span>
            </legend>
            <div class="grid md:grid-cols-3 gap-4 mb-4">
              <div>
                <label for="tq-year" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqYear">Year</label>
                <select id="tq-year" name="vehicle_year"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                  <option value="">Select Year</option>
                </select>
              </div>
              <div>
                <label for="tq-make" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqMake">Make</label>
                <input type="text" id="tq-make" name="vehicle_make" maxlength="50"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
              <div>
                <label for="tq-model" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqModel">Model</label>
                <input type="text" id="tq-model" name="vehicle_model" maxlength="50"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
            </div>

            <!-- Find My Tire Size button -->
            <div class="mb-4">
              <button type="button" id="tq-find-size-btn"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-black rounded-lg font-semibold text-sm hover:bg-amber-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span data-t="tqFindSize">Find My Tire Size</span>
              </button>
              <span id="tq-fitment-status" class="ml-3 text-sm text-gray-500 dark:text-gray-400 hidden"></span>
            </div>

            <div>
              <label for="tq-tire-size" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqTireSize">Tire Size</label>
              <input type="text" id="tq-tire-size" name="tire_size" maxlength="50" placeholder="e.g. 225/65R17"
                     class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" data-t="tqTireSizeHelp">Check your tire sidewall or use the Find My Tire Size button above.</p>
            </div>
          </fieldset>

          <!-- Tire Preferences -->
          <fieldset class="mb-8">
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="bg-brand text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold" aria-hidden="true">3</span>
              <span data-t="tqStep3">Tire Preferences</span>
            </legend>
            <div class="grid md:grid-cols-2 gap-4 mb-4">
              <div>
                <label for="tq-count" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqCount">Number of Tires</label>
                <select id="tq-count" name="tire_count"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="4" selected>4</option>
                  <option value="5">5 (includes spare)</option>
                  <option value="6">6</option>
                  <option value="8">8</option>
                </select>
              </div>
              <div>
                <label for="tq-preference" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqPreference">Tire Condition</label>
                <select id="tq-preference" name="tire_preference"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                  <option value="either" data-t="tqEither">Either (New or Used)</option>
                  <option value="new" data-t="tqNew">New Only</option>
                  <option value="used" data-t="tqUsed">Used Only</option>
                </select>
              </div>
              <div>
                <label for="tq-budget" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqBudget">Budget Range</label>
                <select id="tq-budget" name="budget_range"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                  <option value="no_preference" data-t="tqNoPref">No Preference</option>
                  <option value="economy" data-t="tqEconomy">Economy</option>
                  <option value="mid" data-t="tqMid">Mid-Range</option>
                  <option value="premium" data-t="tqPremium">Premium</option>
                </select>
              </div>
              <div>
                <label for="tq-date" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300" data-t="tqDate">Preferred Date</label>
                <input type="date" id="tq-date" name="preferred_date"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
              </div>
            </div>

            <div class="flex items-center gap-3 mb-4">
              <input type="checkbox" id="tq-install" name="include_installation" checked
                     class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
              <label for="tq-install" class="text-sm text-gray-700 dark:text-gray-300" data-t="tqInstall">Include professional installation</label>
            </div>
          </fieldset>

          <!-- Notes -->
          <fieldset class="mb-8">
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="bg-brand text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold" aria-hidden="true">4</span>
              <span data-t="tqStep4">Additional Notes</span>
            </legend>
            <textarea id="tq-notes" name="notes" rows="3" maxlength="2000"
                      placeholder="Any special requests or questions?"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition resize-y"
                      data-t-placeholder="tqNotesPlaceholder"></textarea>
          </fieldset>

          <!-- Error message -->
          <div id="tq-error" class="hidden mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm" role="alert"></div>

          <!-- Submit -->
          <button type="submit" id="tq-submit"
                  class="w-full bg-brand text-white py-4 rounded-lg font-bold text-lg hover:bg-green-800 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <span data-t="tqSubmit">Get My Free Quote</span>
            <svg id="tq-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
          </button>

          <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-3" data-t="tqDisclaimer">By submitting, you agree to be contacted regarding your tire quote. We do not share your information.</p>
        </form>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 text-center mb-8" data-t="tqWhyTitle">Why Get Your Tires From Us?</h2>
        <div class="grid md:grid-cols-3 gap-6 text-center">
          <div class="p-6">
            <div class="text-4xl mb-3" aria-hidden="true">&#128176;</div>
            <h3 class="font-bold text-lg mb-2" data-t="tqWhy1Title">Competitive Pricing</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400" data-t="tqWhy1Desc">We offer some of the best tire prices in Portland, with options for every budget.</p>
          </div>
          <div class="p-6">
            <div class="text-4xl mb-3" aria-hidden="true">&#128295;</div>
            <h3 class="font-bold text-lg mb-2" data-t="tqWhy2Title">Expert Installation</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400" data-t="tqWhy2Desc">Certified technicians ensure your tires are mounted, balanced, and aligned properly.</p>
          </div>
          <div class="p-6">
            <div class="text-4xl mb-3" aria-hidden="true">&#128338;</div>
            <h3 class="font-bold text-lg mb-2" data-t="tqWhy3Title">Fast Turnaround</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400" data-t="tqWhy3Desc">Most tire installations completed in under an hour. Walk-ins welcome!</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <script>
  (function() {
    'use strict';

    // ─── Translations ──────────────────────────────────────────────────
    var t = {
      skipToContent: { en: 'Skip to main content', es: 'Saltar al contenido principal' },
      breadcrumbHome: { en: 'Home', es: 'Inicio' },
      breadcrumbTireQuote: { en: 'Tire Quote', es: 'Cotización de Llantas' },
      tqHeroTitle: { en: 'Get a Free Tire Quote', es: 'Obtén una Cotización Gratuita de Llantas' },
      tqHeroSub: { en: 'Tell us about your vehicle and tire needs. We will respond within 24 hours with competitive pricing options.', es: 'Cuéntanos sobre tu vehículo y tus necesidades de llantas. Te responderemos en 24 horas con opciones de precios competitivos.' },
      tqFormTitle: { en: 'Request Your Tire Quote', es: 'Solicita Tu Cotización de Llantas' },
      tqStep1: { en: 'Contact Information', es: 'Información de Contacto' },
      tqStep2: { en: 'Vehicle Information', es: 'Información del Vehículo' },
      tqStep3: { en: 'Tire Preferences', es: 'Preferencias de Llantas' },
      tqStep4: { en: 'Additional Notes', es: 'Notas Adicionales' },
      tqFirstName: { en: 'First Name *', es: 'Nombre *' },
      tqLastName: { en: 'Last Name', es: 'Apellido' },
      tqEmail: { en: 'Email *', es: 'Correo Electrónico *' },
      tqPhone: { en: 'Phone', es: 'Teléfono' },
      tqYear: { en: 'Year', es: 'Año' },
      tqMake: { en: 'Make', es: 'Marca' },
      tqModel: { en: 'Model', es: 'Modelo' },
      tqFindSize: { en: 'Find My Tire Size', es: 'Buscar Mi Tamaño de Llanta' },
      tqTireSize: { en: 'Tire Size', es: 'Tamaño de Llanta' },
      tqTireSizeHelp: { en: 'Check your tire sidewall or use the Find My Tire Size button above.', es: 'Revisa el costado de tu llanta o usa el botón Buscar Mi Tamaño arriba.' },
      tqCount: { en: 'Number of Tires', es: 'Número de Llantas' },
      tqPreference: { en: 'Tire Condition', es: 'Condición de Llanta' },
      tqEither: { en: 'Either (New or Used)', es: 'Cualquiera (Nueva o Usada)' },
      tqNew: { en: 'New Only', es: 'Solo Nueva' },
      tqUsed: { en: 'Used Only', es: 'Solo Usada' },
      tqBudget: { en: 'Budget Range', es: 'Rango de Presupuesto' },
      tqNoPref: { en: 'No Preference', es: 'Sin Preferencia' },
      tqEconomy: { en: 'Economy', es: 'Económico' },
      tqMid: { en: 'Mid-Range', es: 'Rango Medio' },
      tqPremium: { en: 'Premium', es: 'Premium' },
      tqDate: { en: 'Preferred Date', es: 'Fecha Preferida' },
      tqInstall: { en: 'Include professional installation', es: 'Incluir instalación profesional' },
      tqNotesPlaceholder: { en: 'Any special requests or questions?', es: '¿Alguna solicitud especial o pregunta?' },
      tqSubmit: { en: 'Get My Free Quote', es: 'Obtener Mi Cotización Gratis' },
      tqDisclaimer: { en: 'By submitting, you agree to be contacted regarding your tire quote. We do not share your information.', es: 'Al enviar, acepta ser contactado sobre su cotización de llantas. No compartimos su información.' },
      tqSuccessTitle: { en: 'Quote Request Received!', es: '¡Solicitud de Cotización Recibida!' },
      tqSuccessMsg: { en: 'Thank you! Our team will review your request and contact you within 24 hours with pricing options.', es: '¡Gracias! Nuestro equipo revisará su solicitud y le contactará en 24 horas con opciones de precios.' },
      tqBookAppt: { en: 'Book an Appointment', es: 'Agendar una Cita' },
      tqBackHome: { en: 'Back to Home', es: 'Volver al Inicio' },
      tqWhyTitle: { en: 'Why Get Your Tires From Us?', es: '¿Por Qué Comprar Sus Llantas Con Nosotros?' },
      tqWhy1Title: { en: 'Competitive Pricing', es: 'Precios Competitivos' },
      tqWhy1Desc: { en: 'We offer some of the best tire prices in Portland, with options for every budget.', es: 'Ofrecemos algunos de los mejores precios de llantas en Portland, con opciones para cada presupuesto.' },
      tqWhy2Title: { en: 'Expert Installation', es: 'Instalación Experta' },
      tqWhy2Desc: { en: 'Certified technicians ensure your tires are mounted, balanced, and aligned properly.', es: 'Técnicos certificados aseguran que sus llantas estén montadas, balanceadas y alineadas correctamente.' },
      tqWhy3Title: { en: 'Fast Turnaround', es: 'Entrega Rápida' },
      tqWhy3Desc: { en: 'Most tire installations completed in under an hour. Walk-ins welcome!', es: '¡La mayoría de instalaciones completadas en menos de una hora! ¡Walk-ins bienvenidos!' },
      tqErrorRequired: { en: 'Please fill in all required fields.', es: 'Por favor complete todos los campos requeridos.' },
      tqErrorEmail: { en: 'Please provide a valid email address.', es: 'Por favor proporcione un correo electrónico válido.' },
      tqErrorServer: { en: 'An error occurred. Please try again.', es: 'Ocurrió un error. Por favor intente de nuevo.' }
    };

    var currentLang = localStorage.getItem('oregontires_lang') || 'en';

    function applyTranslations() {
      document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[key]) {
          el.textContent = t[key][currentLang] || t[key].en;
        }
      });
      document.querySelectorAll('[data-t-placeholder]').forEach(function(el) {
        var key = el.getAttribute('data-t-placeholder');
        if (t[key]) {
          el.placeholder = t[key][currentLang] || t[key].en;
        }
      });
      document.documentElement.lang = currentLang === 'es' ? 'es' : 'en';
      document.title = currentLang === 'es' ? '<?= $pageTitleEs ?>' : '<?= $pageTitle ?>';
    }

    // Listen for language changes from header toggle
    document.addEventListener('languageChanged', function(e) {
      currentLang = e.detail.lang || 'en';
      applyTranslations();
    });

    // ─── Populate year dropdown ────────────────────────────────────────
    var yearSelect = document.getElementById('tq-year');
    var currentYear = new Date().getFullYear() + 1;
    for (var y = currentYear; y >= 1990; y--) {
      var opt = document.createElement('option');
      opt.value = y;
      opt.textContent = y;
      yearSelect.appendChild(opt);
    }

    // ─── Set min date on preferred_date ─────────────────────────────────
    var dateInput = document.getElementById('tq-date');
    if (dateInput) {
      dateInput.min = new Date().toISOString().split('T')[0];
    }

    // ─── Find My Tire Size (tire fitment lookup) ────────────────────────
    var findSizeBtn = document.getElementById('tq-find-size-btn');
    var fitmentStatus = document.getElementById('tq-fitment-status');

    findSizeBtn.addEventListener('click', function() {
      var year = document.getElementById('tq-year').value;
      var make = document.getElementById('tq-make').value.trim();
      var model = document.getElementById('tq-model').value.trim();

      if (!year || !make || !model) {
        fitmentStatus.textContent = currentLang === 'es'
          ? 'Por favor ingrese año, marca y modelo primero.'
          : 'Please enter year, make, and model first.';
        fitmentStatus.classList.remove('hidden');
        fitmentStatus.className = fitmentStatus.className.replace(/text-green-\d+/g, '').replace(/text-red-\d+/g, '') + ' text-red-500';
        return;
      }

      fitmentStatus.textContent = currentLang === 'es' ? 'Buscando...' : 'Looking up...';
      fitmentStatus.classList.remove('hidden');
      fitmentStatus.className = fitmentStatus.className.replace(/text-green-\d+/g, '').replace(/text-red-\d+/g, '') + ' text-gray-500';

      fetch('/api/tire-fitment.php?year=' + encodeURIComponent(year) + '&make=' + encodeURIComponent(make) + '&model=' + encodeURIComponent(model), {
        credentials: 'include'
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success && data.data && data.data.tire_sizes && data.data.tire_sizes.length > 0) {
          var size = data.data.tire_sizes[0];
          document.getElementById('tq-tire-size').value = size;
          fitmentStatus.textContent = (currentLang === 'es' ? 'Encontrado: ' : 'Found: ') + size;
          fitmentStatus.className = fitmentStatus.className.replace(/text-gray-\d+/g, '').replace(/text-red-\d+/g, '') + ' text-green-600';
        } else {
          fitmentStatus.textContent = currentLang === 'es'
            ? 'No se encontraron tamaños. Por favor ingréselo manualmente.'
            : 'No sizes found. Please enter manually.';
          fitmentStatus.className = fitmentStatus.className.replace(/text-gray-\d+/g, '').replace(/text-green-\d+/g, '') + ' text-red-500';
        }
      })
      .catch(function() {
        fitmentStatus.textContent = currentLang === 'es'
          ? 'Error en la búsqueda. Por favor intente de nuevo.'
          : 'Lookup error. Please try again.';
        fitmentStatus.className = fitmentStatus.className.replace(/text-gray-\d+/g, '').replace(/text-green-\d+/g, '') + ' text-red-500';
      });
    });

    // ─── Form submission ────────────────────────────────────────────────
    var form = document.getElementById('tq-form');
    var errorDiv = document.getElementById('tq-error');
    var submitBtn = document.getElementById('tq-submit');
    var spinner = document.getElementById('tq-spinner');

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      // Clear previous error
      errorDiv.classList.add('hidden');
      errorDiv.textContent = '';

      var firstName = document.getElementById('tq-first-name').value.trim();
      var email = document.getElementById('tq-email').value.trim();

      if (!firstName || !email) {
        showError(t.tqErrorRequired[currentLang] || t.tqErrorRequired.en);
        return;
      }

      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showError(t.tqErrorEmail[currentLang] || t.tqErrorEmail.en);
        return;
      }

      // Disable submit
      submitBtn.disabled = true;
      spinner.classList.remove('hidden');

      var payload = {
        first_name: firstName,
        last_name: document.getElementById('tq-last-name').value.trim(),
        email: email,
        phone: document.getElementById('tq-phone').value.trim(),
        vehicle_year: document.getElementById('tq-year').value,
        vehicle_make: document.getElementById('tq-make').value.trim(),
        vehicle_model: document.getElementById('tq-model').value.trim(),
        tire_size: document.getElementById('tq-tire-size').value.trim(),
        tire_count: parseInt(document.getElementById('tq-count').value, 10) || 4,
        tire_preference: document.getElementById('tq-preference').value,
        budget_range: document.getElementById('tq-budget').value,
        include_installation: document.getElementById('tq-install').checked,
        preferred_date: document.getElementById('tq-date').value || null,
        notes: document.getElementById('tq-notes').value.trim(),
        language: currentLang === 'es' ? 'spanish' : 'english'
      };

      fetch('/api/tire-quote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(payload)
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        submitBtn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
          form.classList.add('hidden');
          document.getElementById('tq-success').classList.remove('hidden');
          window.scrollTo({ top: 0, behavior: 'smooth' });

          // Track GA event
          if (typeof gtag === 'function') {
            gtag('event', 'tire_quote_request', {
              event_category: 'engagement',
              event_label: payload.tire_size || 'unknown',
              value: payload.tire_count
            });
          }
        } else {
          showError(data.error || (t.tqErrorServer[currentLang] || t.tqErrorServer.en));
        }
      })
      .catch(function() {
        submitBtn.disabled = false;
        spinner.classList.add('hidden');
        showError(t.tqErrorServer[currentLang] || t.tqErrorServer.en);
      });
    });

    function showError(msg) {
      errorDiv.textContent = msg;
      errorDiv.classList.remove('hidden');
      errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Apply translations on load
    applyTranslations();
  })();
  </script>
</body>
</html>
