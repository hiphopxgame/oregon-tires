<?php
/**
 * Oregon Tires — Care Plan Membership Pricing
 */
$canonicalUrl = 'https://oregon.tires/care-plan';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Care Plan Membership | Oregon Tires Auto Care</title>
  <meta name="description" content="Save hundreds per year with Oregon Tires Care Plans. Oil changes, tire rotations, service discounts & priority scheduling starting at $19/month.">
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= $canonicalUrl ?>?lang=en">
  <link rel="alternate" hreflang="es" href="<?= $canonicalUrl ?>?lang=es">
  <link rel="alternate" hreflang="x-default" href="<?= $canonicalUrl ?>">
  <meta property="og:title" content="Care Plan Membership | Oregon Tires Auto Care">
  <meta property="og:description" content="Save hundreds per year with Oregon Tires Care Plans. Oil changes, tire rotations, discounts & more starting at $19/month.">
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

  <!-- JSON-LD: Product/Offer -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Oregon Tires Care Plan",
    "description": "Monthly auto care membership with oil changes, tire rotations, service discounts, and priority scheduling.",
    "brand": {"@type": "Brand", "name": "Oregon Tires Auto Care"},
    "offers": [
      {"@type": "Offer", "name": "Basic Care Plan", "price": "19.00", "priceCurrency": "USD", "priceSpecification": {"@type": "UnitPriceSpecification", "price": "19.00", "priceCurrency": "USD", "unitText": "month"}, "url": "https://oregon.tires/care-plan"},
      {"@type": "Offer", "name": "Standard Care Plan", "price": "29.00", "priceCurrency": "USD", "priceSpecification": {"@type": "UnitPriceSpecification", "price": "29.00", "priceCurrency": "USD", "unitText": "month"}, "url": "https://oregon.tires/care-plan"},
      {"@type": "Offer", "name": "Premium Care Plan", "price": "49.00", "priceCurrency": "USD", "priceSpecification": {"@type": "UnitPriceSpecification", "price": "49.00", "priceCurrency": "USD", "unitText": "month"}, "url": "https://oregon.tires/care-plan"}
    ]
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oregon.tires/"},
      {"@type": "ListItem", "position": 2, "name": "Care Plan"}
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
      <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70 flex justify-center">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium">Care Plan</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4">Oregon Tires Care Plan</h1>
        <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">Save money on every visit. One simple monthly plan covers oil changes, tire rotations, discounts, and priority scheduling.</p>
      </div>
    </section>

    <!-- Pricing Tiers -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 text-center mb-4">Choose Your Plan</h2>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-10 max-w-xl mx-auto">Every plan includes priority scheduling and savings that pay for themselves within months.</p>

        <div class="grid md:grid-cols-3 gap-6">
          <!-- Basic -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-md border border-gray-200 dark:border-gray-600 p-6 flex flex-col">
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1">Basic</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$19</span><span class="text-gray-500 dark:text-gray-400">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> 1 oil change per year</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> 5% off all services</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free tire rotations</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Priority scheduling</li>
            </ul>
            <a href="/book-appointment/?plan=basic" class="block text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition">Get Started</a>
          </div>

          <!-- Standard (Popular) -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg border-2 border-amber-500 p-6 flex flex-col relative">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-black text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wide">Most Popular</span>
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1">Standard</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$29</span><span class="text-gray-500 dark:text-gray-400">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> 2 oil changes per year</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> 10% off all services</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free tire rotations</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Priority scheduling</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free multi-point inspections</li>
            </ul>
            <a href="/book-appointment/?plan=standard" class="block text-center bg-amber-500 text-black px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-md">Get Started</a>
          </div>

          <!-- Premium -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-md border border-gray-200 dark:border-gray-600 p-6 flex flex-col">
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1">Premium</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$49</span><span class="text-gray-500 dark:text-gray-400">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Unlimited oil changes</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> 15% off all services</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free tire rotations</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Priority scheduling</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free multi-point inspections</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Roadside assistance</li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> Free alignment check</li>
            </ul>
            <a href="/book-appointment/?plan=premium" class="block text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition">Get Started</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ROI Calculator -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 mb-3">Average Portland Driver Saves $380/Year</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-10">Here is how the savings add up with a Standard plan:</p>
        <div class="grid sm:grid-cols-3 gap-6">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$70</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium">2 Oil Changes Saved</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Avg $35 each included free</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$120</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium">Tire Rotations Saved</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">2-3 rotations/yr included free</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$190+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium">Service Discounts</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">10% off brakes, alignment, etc.</p>
          </div>
        </div>
        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">Based on average annual maintenance for Portland-area vehicles. Actual savings vary by vehicle and usage.</p>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-2xl">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 text-center mb-10">Frequently Asked Questions</h2>
        <div class="space-y-4">
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between">Can I cancel anytime?<span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">Yes. There are no contracts or cancellation fees. You can cancel your Care Plan at any time and your benefits continue through the end of your current billing period.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between">Can I add family members?<span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">Each Care Plan covers one vehicle. Additional vehicles in your household can be enrolled at the same tier with a 10% multi-vehicle discount.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between">When do my savings start?<span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">Immediately. Your service discounts and priority scheduling are active from your first visit. Oil changes and tire rotations are available as soon as your plan starts.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between">Can I combine with other offers?<span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">Care Plan discounts apply on top of most seasonal promotions. They cannot be combined with other membership or loyalty discounts. Ask our team for details.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between">What services are covered?<span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">Your percentage discount applies to all services we offer: brakes, alignment, suspension, engine work, diagnostics, and more. Included oil changes use conventional oil (synthetic upgrade available at a reduced rate). Tire rotations are unlimited on all plans.</p>
          </details>
        </div>
      </div>
    </section>

    <!-- CTA Banner -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3">Ready to Start Saving?</h2>
        <p class="mb-6 max-w-lg mx-auto">Join hundreds of Portland drivers who save money and skip the line with an Oregon Tires Care Plan.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <a href="/book-appointment/?plan=standard" class="bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg">Enroll Now</a>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Call (503) 367-9714</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> Call Now
      </a>
      <a href="/book-appointment/?plan=standard" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm">
        <span aria-hidden="true">&#x1F4C5;</span> Enroll Now
      </a>
    </div>
  </div>
</body>
</html>
