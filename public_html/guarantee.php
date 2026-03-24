<?php
/**
 * Oregon Tires — Our Guarantee
 * Bilingual (EN/ES) guarantee page with warranty, price match, satisfaction, inspections, pricing.
 */
$pageTitle = "Our Guarantee | Oregon Tires Auto Care";
$pageTitleEs = "Nuestra Garant\u00eda | Oregon Tires Auto Care";
$pageDesc = "Oregon Tires backs every service with a 12-month/12,000-mile warranty, price match guarantee, free inspections, and no-surprise pricing. Your satisfaction is guaranteed.";
$pageDescEs = "Oregon Tires respalda cada servicio con garant\u00eda de 12 meses/12,000 millas, igualaci\u00f3n de precios, inspecciones gratuitas y precios sin sorpresas.";
$canonicalUrl = 'https://oregon.tires/guarantee';
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

  <!-- JSON-LD: Breadcrumb -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Our Guarantee"}
    ]
  }
  </script>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold" data-t="skipLink">Skip to main content</a>

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
            <li class="text-white font-medium" data-t="breadcrumbCurrent">Our Guarantee</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">Our Guarantee to You</h1>
        <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto" data-t="heroSubtitle">We stand behind every service with warranties, price matching, and total transparency. No surprises, no shortcuts.</p>
      </div>
    </section>

    <!-- 12-Month / 12,000-Mile Warranty -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex flex-col md:flex-row items-start gap-6">
          <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
            <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-3" data-t="warrantyTitle">12-Month / 12,000-Mile Warranty</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4" data-t="warrantyDesc">Every repair and service we perform is backed by our 12-month or 12,000-mile warranty, whichever comes first. If something we fixed does not hold up, bring it back and we will make it right at no additional charge.</p>
            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="warrantyItem1">Covers parts and labor on all completed repairs</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="warrantyItem2">12 months or 12,000 miles, whichever comes first</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="warrantyItem3">No deductibles or hidden conditions</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="warrantyItem4">Applies to all services including brakes, alignment, engine work</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Price Match Guarantee -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex flex-col md:flex-row items-start gap-6">
          <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
            <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-3" data-t="priceMatchTitle">Price Match Guarantee</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-2 text-lg font-medium" data-t="priceMatchSubtitle">Found a lower price? We will match it.</p>
            <p class="text-gray-600 dark:text-gray-300 mb-4" data-t="priceMatchDesc">Bring us a written estimate from any local competitor for the same service and we will match their price. We believe in earning your business on value, not inflated pricing.</p>
            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-4">
              <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2" data-t="priceMatchCondLabel">Conditions:</p>
              <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-2"><span class="text-brand dark:text-green-400">&#8226;</span> <span data-t="priceMatchCond1">Must be for the same service on the same vehicle</span></li>
                <li class="flex items-start gap-2"><span class="text-brand dark:text-green-400">&#8226;</span> <span data-t="priceMatchCond2">Written estimate required (printed or digital)</span></li>
                <li class="flex items-start gap-2"><span class="text-brand dark:text-green-400">&#8226;</span> <span data-t="priceMatchCond3">Competitor must be a local Portland-area shop</span></li>
                <li class="flex items-start gap-2"><span class="text-brand dark:text-green-400">&#8226;</span> <span data-t="priceMatchCond4">Estimate must be dated within the last 30 days</span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Satisfaction Guarantee -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex flex-col md:flex-row items-start gap-6">
          <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
            <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-3" data-t="satisfactionTitle">Satisfaction Guarantee</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4" data-t="satisfactionDesc">If you are not completely satisfied with our work, we will make it right. Here is our simple resolution process:</p>
            <div class="grid sm:grid-cols-3 gap-4">
              <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center mx-auto mb-2 text-sm font-bold">1</div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1" data-t="step1Title">Contact Us</p>
                <p class="text-xs text-gray-500 dark:text-gray-400" data-t="step1Desc">Call or visit within 30 days of service</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center mx-auto mb-2 text-sm font-bold">2</div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1" data-t="step2Title">We Inspect</p>
                <p class="text-xs text-gray-500 dark:text-gray-400" data-t="step2Desc">Free re-inspection of the concern</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center mx-auto mb-2 text-sm font-bold">3</div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1" data-t="step3Title">We Fix It</p>
                <p class="text-xs text-gray-500 dark:text-gray-400" data-t="step3Desc">Corrected at no extra charge</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Free Multi-Point Inspection -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex flex-col md:flex-row items-start gap-6">
          <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-3" data-t="inspectionTitle">Free Multi-Point Inspection</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4" data-t="inspectionDesc">Every vehicle that comes through our shop receives a complimentary digital vehicle inspection. We check the critical systems so you know exactly where your car stands -- no cost, no obligation.</p>
            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="inspectionItem1">Brakes, tires, suspension, fluids, belts, and battery</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="inspectionItem2">Color-coded report (green / yellow / red) with photos</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="inspectionItem3">Digital report sent to your phone or email</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="inspectionItem4">No pressure -- review on your own time and decide</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- No Surprise Pricing -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex flex-col md:flex-row items-start gap-6">
          <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
            <svg class="w-8 h-8 text-brand dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-3" data-t="pricingTitle">No Surprise Pricing</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4" data-t="pricingDesc">You will always know the cost before we start any work. Our transparent pricing policy means:</p>
            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="pricingItem1">Written estimate provided before any work begins</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="pricingItem2">No hidden fees, shop supplies charges, or surprise add-ons</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="pricingItem3">Your approval required before any additional work</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="pricingItem4">Itemized invoice with parts and labor breakdown</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="pricingItem5">Approve or decline individual items on your estimate</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Banner -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Questions About Our Guarantees?</h2>
        <p class="mb-6 max-w-lg mx-auto" data-t="ctaSubtitle">We are happy to explain any of our policies in detail. Call us or book a visit -- we will take care of you.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="ctaBook">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaCall">Call (503) 367-9714</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> <span data-t="mobileCallNow">Call Now</span>
      </a>
      <a href="/book-appointment/" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> <span data-t="mobileBookNow">Book Now</span>
      </a>
    </div>
  </div>

  <!-- Bilingual Translation Script -->
  <script>
  (function(){
    var t = {
      skipLink: { en: 'Skip to main content', es: 'Saltar al contenido principal' },
      breadcrumbHome: { en: 'Home', es: 'Inicio' },
      breadcrumbCurrent: { en: 'Our Guarantee', es: 'Nuestra Garant\u00eda' },
      heroTitle: { en: 'Our Guarantee to You', es: 'Nuestra Garant\u00eda Para Usted' },
      heroSubtitle: { en: 'We stand behind every service with warranties, price matching, and total transparency. No surprises, no shortcuts.', es: 'Respaldamos cada servicio con garant\u00edas, igualaci\u00f3n de precios y total transparencia. Sin sorpresas, sin atajos.' },
      warrantyTitle: { en: '12-Month / 12,000-Mile Warranty', es: 'Garant\u00eda de 12 Meses / 12,000 Millas' },
      warrantyDesc: { en: 'Every repair and service we perform is backed by our 12-month or 12,000-mile warranty, whichever comes first. If something we fixed does not hold up, bring it back and we will make it right at no additional charge.', es: 'Cada reparaci\u00f3n y servicio que realizamos est\u00e1 respaldado por nuestra garant\u00eda de 12 meses o 12,000 millas, lo que ocurra primero. Si algo que arreglamos no funciona bien, tr\u00e1igalo de vuelta y lo corregiremos sin cargo adicional.' },
      warrantyItem1: { en: 'Covers parts and labor on all completed repairs', es: 'Cubre piezas y mano de obra en todas las reparaciones completadas' },
      warrantyItem2: { en: '12 months or 12,000 miles, whichever comes first', es: '12 meses o 12,000 millas, lo que ocurra primero' },
      warrantyItem3: { en: 'No deductibles or hidden conditions', es: 'Sin deducibles ni condiciones ocultas' },
      warrantyItem4: { en: 'Applies to all services including brakes, alignment, engine work', es: 'Aplica a todos los servicios incluyendo frenos, alineaci\u00f3n y motor' },
      priceMatchTitle: { en: 'Price Match Guarantee', es: 'Garant\u00eda de Igualaci\u00f3n de Precios' },
      priceMatchSubtitle: { en: 'Found a lower price? We will match it.', es: '\u00bfEncontr\u00f3 un precio m\u00e1s bajo? Lo igualamos.' },
      priceMatchDesc: { en: 'Bring us a written estimate from any local competitor for the same service and we will match their price. We believe in earning your business on value, not inflated pricing.', es: 'Tr\u00e1iganos un presupuesto escrito de cualquier competidor local por el mismo servicio y igualaremos su precio. Creemos en ganarnos su confianza con valor, no con precios inflados.' },
      priceMatchCondLabel: { en: 'Conditions:', es: 'Condiciones:' },
      priceMatchCond1: { en: 'Must be for the same service on the same vehicle', es: 'Debe ser por el mismo servicio en el mismo veh\u00edculo' },
      priceMatchCond2: { en: 'Written estimate required (printed or digital)', es: 'Se requiere presupuesto escrito (impreso o digital)' },
      priceMatchCond3: { en: 'Competitor must be a local Portland-area shop', es: 'El competidor debe ser un taller local del \u00e1rea de Portland' },
      priceMatchCond4: { en: 'Estimate must be dated within the last 30 days', es: 'El presupuesto debe tener fecha de los \u00faltimos 30 d\u00edas' },
      satisfactionTitle: { en: 'Satisfaction Guarantee', es: 'Garant\u00eda de Satisfacci\u00f3n' },
      satisfactionDesc: { en: 'If you are not completely satisfied with our work, we will make it right. Here is our simple resolution process:', es: 'Si no est\u00e1 completamente satisfecho con nuestro trabajo, lo corregiremos. Este es nuestro sencillo proceso de resoluci\u00f3n:' },
      step1Title: { en: 'Contact Us', es: 'Cont\u00e1ctenos' },
      step1Desc: { en: 'Call or visit within 30 days of service', es: 'Llame o vis\u00edtenos dentro de los 30 d\u00edas del servicio' },
      step2Title: { en: 'We Inspect', es: 'Inspeccionamos' },
      step2Desc: { en: 'Free re-inspection of the concern', es: 'Reinspecci\u00f3n gratuita del problema' },
      step3Title: { en: 'We Fix It', es: 'Lo Reparamos' },
      step3Desc: { en: 'Corrected at no extra charge', es: 'Corregido sin cargo adicional' },
      inspectionTitle: { en: 'Free Multi-Point Inspection', es: 'Inspecci\u00f3n Multipunto Gratuita' },
      inspectionDesc: { en: 'Every vehicle that comes through our shop receives a complimentary digital vehicle inspection. We check the critical systems so you know exactly where your car stands -- no cost, no obligation.', es: 'Cada veh\u00edculo que pasa por nuestro taller recibe una inspecci\u00f3n digital gratuita. Revisamos los sistemas cr\u00edticos para que sepa exactamente el estado de su carro -- sin costo, sin compromiso.' },
      inspectionItem1: { en: 'Brakes, tires, suspension, fluids, belts, and battery', es: 'Frenos, llantas, suspensi\u00f3n, fluidos, bandas y bater\u00eda' },
      inspectionItem2: { en: 'Color-coded report (green / yellow / red) with photos', es: 'Reporte con c\u00f3digo de colores (verde / amarillo / rojo) con fotos' },
      inspectionItem3: { en: 'Digital report sent to your phone or email', es: 'Reporte digital enviado a su tel\u00e9fono o correo electr\u00f3nico' },
      inspectionItem4: { en: 'No pressure -- review on your own time and decide', es: 'Sin presi\u00f3n -- rev\u00edselo a su tiempo y decida' },
      pricingTitle: { en: 'No Surprise Pricing', es: 'Precios Sin Sorpresas' },
      pricingDesc: { en: 'You will always know the cost before we start any work. Our transparent pricing policy means:', es: 'Siempre sabr\u00e1 el costo antes de que comencemos cualquier trabajo. Nuestra pol\u00edtica de precios transparentes significa:' },
      pricingItem1: { en: 'Written estimate provided before any work begins', es: 'Presupuesto escrito proporcionado antes de comenzar cualquier trabajo' },
      pricingItem2: { en: 'No hidden fees, shop supplies charges, or surprise add-ons', es: 'Sin cargos ocultos, costos de suministros del taller ni extras sorpresa' },
      pricingItem3: { en: 'Your approval required before any additional work', es: 'Su aprobaci\u00f3n es requerida antes de cualquier trabajo adicional' },
      pricingItem4: { en: 'Itemized invoice with parts and labor breakdown', es: 'Factura detallada con desglose de piezas y mano de obra' },
      pricingItem5: { en: 'Approve or decline individual items on your estimate', es: 'Apruebe o rechace art\u00edculos individuales en su presupuesto' },
      ctaTitle: { en: 'Questions About Our Guarantees?', es: '\u00bfPreguntas Sobre Nuestras Garant\u00edas?' },
      ctaSubtitle: { en: 'We are happy to explain any of our policies in detail. Call us or book a visit -- we will take care of you.', es: 'Con gusto le explicamos cualquiera de nuestras pol\u00edticas en detalle. Ll\u00e1menos o reserve una visita -- lo atenderemos.' },
      ctaBook: { en: 'Book Free Estimate', es: 'Reservar Presupuesto Gratis' },
      ctaCall: { en: 'Call (503) 367-9714', es: 'Llamar (503) 367-9714' },
      mobileCallNow: { en: 'Call Now', es: 'Llamar' },
      mobileBookNow: { en: 'Book Now', es: 'Reservar' }
    };
    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang');
    if (!lang) { lang = (navigator.language || '').startsWith('es') ? 'es' : 'en'; }
    window.currentLang = lang;
    if (lang === 'es') {
      document.documentElement.lang = 'es';
      document.title = '<?= $pageTitleEs ?>';
      var metaDesc = document.querySelector('meta[name="description"]');
      if (metaDesc) metaDesc.content = '<?= $pageDescEs ?>';
    }
    document.querySelectorAll('[data-t]').forEach(function(el){
      var key = el.getAttribute('data-t');
      if (t[key] && t[key][lang]) el.textContent = t[key][lang];
    });
  })();
  </script>
</body>
</html>
