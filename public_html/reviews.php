<?php
/**
 * Oregon Tires — Reviews & Testimonials Page
 * Bilingual (EN/ES) reviews page with Google review CTA and customer testimonials.
 */
$pageTitle = 'Reviews & Testimonials | Oregon Tires Auto Care';
$pageTitleEs = 'Rese&ntilde;as y Testimonios | Oregon Tires Auto Care';
$pageDesc = 'Read what Portland drivers say about Oregon Tires Auto Care. 4.8-star rating with 150+ Google reviews. Bilingual English & Spanish service.';
$pageDescEs = 'Lee lo que dicen los conductores de Portland sobre Oregon Tires Auto Care. Calificaci&oacute;n de 4.8 estrellas con m&aacute;s de 150 rese&ntilde;as en Google.';
$canonicalUrl = 'https://oregon.tires/reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDesc ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= $pageDesc ?>">
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

  <!-- AutomotiveBusiness JSON-LD with AggregateRating -->
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
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.8",
      "reviewCount": "150",
      "bestRating": "5"
    }
  }
  </script>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Reviews"}
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
            <li><a href="/" class="hover:text-amber-300" data-t="home">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="breadcrumbCurrent">Reviews</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">What Our Customers Say</h1>
        <p class="text-lg md:text-xl mb-6 max-w-3xl opacity-90" data-t="heroSubtitle">Portland drivers trust Oregon Tires Auto Care for honest, bilingual service. See why we have a 4.8-star rating.</p>
        <!-- Star Rating Display -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
          <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
            <div class="flex" aria-label="4.8 out of 5 stars">
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <span class="text-2xl font-bold">4.8</span>
            <span class="text-white/80" data-t="heroReviewCount">150+ Google Reviews</span>
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <a href="https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="bg-amber-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-lg inline-flex items-center gap-2" data-t="leaveReview">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Leave Us a Review on Google
          </a>
          <a href="https://www.google.com/maps/place/?q=place_id:ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition inline-flex items-center gap-2" data-t="viewOnGoogle">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            View on Google Maps
          </a>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-12 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="testimonialsTitle">Customer Testimonials</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Maria R. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review1Text">"Excellent service! They installed my tires quickly and the price was very fair."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Maria R.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review1Service">Tire Installation</p>
          </blockquote>
          <!-- James T. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review2Text">"Honest mechanics who explain what needs to be done. No upselling."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; James T.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review2Service">General Repair</p>
          </blockquote>
          <!-- Carlos M. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review3Text">"Finally found a shop where I can communicate in Spanish. Great work on my brakes."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Carlos M.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review3Service">Brake Service</p>
          </blockquote>
          <!-- Sarah L. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review4Text">"Best tire prices in SE Portland. Quick service and friendly staff."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Sarah L.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review4Service">Tire Sales</p>
          </blockquote>
          <!-- Roberto G. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review5Text">"Brought my fleet vehicles here. Reliable, fast turnaround, fair pricing."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Roberto G.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review5Service">Fleet Service</p>
          </blockquote>
          <!-- Jennifer K. -->
          <blockquote class="bg-white dark:bg-gray-700 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex mb-3">
              <span class="text-yellow-400 text-lg" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm italic mb-4" data-t="review6Text">"They caught a brake issue during my oil change. Saved me from an expensive repair later."</p>
            <cite class="text-brand dark:text-green-400 font-semibold not-italic text-sm">&mdash; Jennifer K.</cite>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" data-t="review6Service">Oil Change</p>
          </blockquote>
        </div>

        <!-- Leave a Review CTA (inline) -->
        <div class="mt-10 text-center">
          <a href="https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="leaveReviewBtn">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Leave Us a Review on Google
          </a>
        </div>
      </div>
    </section>

    <!-- Why Customers Trust Us -->
    <section class="py-12 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-8 text-center" data-t="trustTitle">Why Customers Trust Us</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">15+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statYears">Years in Portland</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">150+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statReviews">Google Reviews</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex justify-center gap-1 text-brand dark:text-green-400">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            </div>
            <div class="text-lg font-bold text-brand dark:text-green-400 mt-1">EN/ES</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statBilingual">Fully Bilingual</div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-brand dark:text-green-400">12mo</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1" data-t="statWarranty">12K Mile Warranty</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Ready to Experience the Difference?</h2>
        <p class="mb-6" data-t="ctaSubtitle">Join 150+ happy customers. Book online or call for same-day service.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg" data-t="ctaBook">Book Free Estimate</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaCall">Call (503) 367-9714</a>
          <a href="/contact" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaContact">Contact Us</a>
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
      home:               { en: 'Home', es: 'Inicio' },
      breadcrumbCurrent:  { en: 'Reviews', es: 'Rese\u00f1as' },
      heroTitle:          { en: 'What Our Customers Say', es: 'Lo Que Dicen Nuestros Clientes' },
      heroSubtitle:       { en: 'Portland drivers trust Oregon Tires Auto Care for honest, bilingual service. See why we have a 4.8-star rating.', es: 'Los conductores de Portland conf\u00edan en Oregon Tires Auto Care por su servicio honesto y biling\u00fce. Descubre por qu\u00e9 tenemos una calificaci\u00f3n de 4.8 estrellas.' },
      heroReviewCount:    { en: '150+ Google Reviews', es: '150+ Rese\u00f1as en Google' },
      leaveReview:        { en: 'Leave Us a Review on Google', es: 'D\u00e9janos una Rese\u00f1a en Google' },
      viewOnGoogle:       { en: 'View on Google Maps', es: 'Ver en Google Maps' },
      testimonialsTitle:  { en: 'Customer Testimonials', es: 'Testimonios de Clientes' },
      review1Text:        { en: '\u201cExcellent service! They installed my tires quickly and the price was very fair.\u201d', es: '\u201c\u00a1Excelente servicio! Instalaron mis llantas r\u00e1pidamente y el precio fue muy justo.\u201d' },
      review1Service:     { en: 'Tire Installation', es: 'Instalaci\u00f3n de Llantas' },
      review2Text:        { en: '\u201cHonest mechanics who explain what needs to be done. No upselling.\u201d', es: '\u201cMec\u00e1nicos honestos que explican lo que se necesita hacer. Sin ventas adicionales.\u201d' },
      review2Service:     { en: 'General Repair', es: 'Reparaci\u00f3n General' },
      review3Text:        { en: '\u201cFinally found a shop where I can communicate in Spanish. Great work on my brakes.\u201d', es: '\u201cPor fin encontr\u00e9 un taller donde puedo comunicarme en espa\u00f1ol. Excelente trabajo en mis frenos.\u201d' },
      review3Service:     { en: 'Brake Service', es: 'Servicio de Frenos' },
      review4Text:        { en: '\u201cBest tire prices in SE Portland. Quick service and friendly staff.\u201d', es: '\u201cLos mejores precios de llantas en el sureste de Portland. Servicio r\u00e1pido y personal amable.\u201d' },
      review4Service:     { en: 'Tire Sales', es: 'Venta de Llantas' },
      review5Text:        { en: '\u201cBrought my fleet vehicles here. Reliable, fast turnaround, fair pricing.\u201d', es: '\u201cTraje mis veh\u00edculos de flota aqu\u00ed. Confiable, r\u00e1pido y precios justos.\u201d' },
      review5Service:     { en: 'Fleet Service', es: 'Servicio de Flotas' },
      review6Text:        { en: '\u201cThey caught a brake issue during my oil change. Saved me from an expensive repair later.\u201d', es: '\u201cDetectaron un problema de frenos durante mi cambio de aceite. Me ahorraron una reparaci\u00f3n costosa despu\u00e9s.\u201d' },
      review6Service:     { en: 'Oil Change', es: 'Cambio de Aceite' },
      leaveReviewBtn:     { en: 'Leave Us a Review on Google', es: 'D\u00e9janos una Rese\u00f1a en Google' },
      trustTitle:         { en: 'Why Customers Trust Us', es: 'Por Qu\u00e9 los Clientes Conf\u00edan en Nosotros' },
      statYears:          { en: 'Years in Portland', es: 'A\u00f1os en Portland' },
      statReviews:        { en: 'Google Reviews', es: 'Rese\u00f1as en Google' },
      statBilingual:      { en: 'Fully Bilingual', es: 'Completamente Biling\u00fce' },
      statWarranty:       { en: '12K Mile Warranty', es: '12K Millas de Garant\u00eda' },
      ctaTitle:           { en: 'Ready to Experience the Difference?', es: '\u00bfListo para Experimentar la Diferencia?' },
      ctaSubtitle:        { en: 'Join 150+ happy customers. Book online or call for same-day service.', es: '\u00danete a m\u00e1s de 150 clientes satisfechos. Reserva en l\u00ednea o llama para servicio el mismo d\u00eda.' },
      ctaBook:            { en: 'Book Free Estimate', es: 'Reserve Estimado Gratis' },
      ctaCall:            { en: 'Call (503) 367-9714', es: 'Llame (503) 367-9714' },
      ctaContact:         { en: 'Contact Us', es: 'Cont\u00e1ctenos' },
      callNow:            { en: 'Call Now', es: 'Llamar' },
      bookNow:            { en: 'Book Now', es: 'Reservar' }
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
