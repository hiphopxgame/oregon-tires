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
    <!-- Status Banners (shown via JS based on query params) -->
    <div id="cp-enrolled-banner" class="hidden bg-green-600 text-white py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#10003;</span>
        <div>
          <p class="font-bold text-lg">Welcome to the Oregon Tires Care Plan!</p>
          <p class="text-sm opacity-90">Your enrollment is confirmed. Your benefits are active immediately.</p>
        </div>
      </div>
    </div>
    <div id="cp-cancelled-banner" class="hidden bg-amber-500 text-black py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#9888;</span>
        <div>
          <p class="font-bold">Enrollment not completed</p>
          <p class="text-sm">Payment was cancelled. You can enroll anytime below.</p>
        </div>
      </div>
    </div>
    <div id="cp-pending-banner" class="hidden bg-blue-600 text-white py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#9993;</span>
        <div>
          <p class="font-bold">Enrollment Received!</p>
          <p class="text-sm opacity-90" id="cp-pending-message">We will contact you to complete payment setup.</p>
        </div>
      </div>
    </div>

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
            <button type="button" data-plan="basic" data-plan-name="Basic Care Plan" data-plan-price="19" class="cp-enroll-btn block w-full text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition cursor-pointer">Enroll Now</button>
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
            <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn block w-full text-center bg-amber-500 text-black px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-md cursor-pointer">Enroll Now</button>
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
            <button type="button" data-plan="premium" data-plan-name="Premium Care Plan" data-plan-price="49" class="cp-enroll-btn block w-full text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition cursor-pointer">Enroll Now</button>
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
          <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg cursor-pointer">Enroll Now</button>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition">Call (503) 367-9714</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/templates/footer.php'; ?>

  <!-- Enrollment Modal -->
  <div id="cp-modal-overlay" class="hidden fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="cp-modal-title">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 id="cp-modal-title" class="text-xl font-bold text-gray-900 dark:text-white">Enroll in <span id="cp-modal-plan-name">Care Plan</span></h2>
          <button type="button" id="cp-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none" aria-label="Close">&times;</button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Monthly subscription: <strong id="cp-modal-price" class="text-brand dark:text-green-400">$29/mo</strong></p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Cancel anytime. No contracts or hidden fees.</p>

        <form id="cp-enroll-form" novalidate>
          <input type="hidden" id="cp-plan-type" name="plan_type" value="">
          <div class="space-y-4">
            <div>
              <label for="cp-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name <span class="text-red-500">*</span></label>
              <input type="text" id="cp-name" name="name" required autocomplete="name" maxlength="200"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
            <div>
              <label for="cp-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
              <input type="email" id="cp-email" name="email" required autocomplete="email" maxlength="254"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
            <div>
              <label for="cp-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone <span class="text-gray-400 text-xs">(optional)</span></label>
              <input type="tel" id="cp-phone" name="phone" autocomplete="tel" maxlength="30"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
          </div>

          <div id="cp-form-error" class="hidden mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300"></div>

          <button type="submit" id="cp-submit-btn" class="mt-6 w-full bg-brand text-white py-3 rounded-lg font-semibold hover:bg-green-800 transition flex items-center justify-center gap-2">
            <span id="cp-submit-text">Continue to Payment</span>
            <span id="cp-submit-spinner" class="hidden">
              <svg class="motion-safe:animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            </span>
          </button>

          <p class="mt-3 text-xs text-center text-gray-500 dark:text-gray-400">Secure payment processed by PayPal. You will be redirected to complete payment.</p>
        </form>
      </div>
    </div>
  </div>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> Call Now
      </a>
      <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm cursor-pointer border-0">
        <span aria-hidden="true">&#x1F4C5;</span> Enroll Now
      </button>
    </div>
  </div>

  <script>
  (function() {
    'use strict';

    // ─── Query param banners ──────────────────────────────────────────
    var params = new URLSearchParams(window.location.search);
    if (params.get('enrolled') === 'true') {
      var enrolledBanner = document.getElementById('cp-enrolled-banner');
      if (enrolledBanner) enrolledBanner.classList.remove('hidden');
      // Clean URL
      window.history.replaceState({}, '', window.location.pathname);
      // GA4 event
      if (typeof gtag === 'function') {
        gtag('event', 'care_plan_enrolled', {
          plan_type: params.get('plan') || 'unknown'
        });
      }
    }
    if (params.get('cancelled') === 'true') {
      var cancelledBanner = document.getElementById('cp-cancelled-banner');
      if (cancelledBanner) cancelledBanner.classList.remove('hidden');
      window.history.replaceState({}, '', window.location.pathname);
    }

    // ─── Modal controls ───────────────────────────────────────────────
    var overlay = document.getElementById('cp-modal-overlay');
    var closeBtn = document.getElementById('cp-modal-close');
    var form = document.getElementById('cp-enroll-form');
    var planTypeInput = document.getElementById('cp-plan-type');
    var planNameEl = document.getElementById('cp-modal-plan-name');
    var priceEl = document.getElementById('cp-modal-price');
    var errorEl = document.getElementById('cp-form-error');
    var submitBtn = document.getElementById('cp-submit-btn');
    var submitText = document.getElementById('cp-submit-text');
    var submitSpinner = document.getElementById('cp-submit-spinner');

    function openModal(planType, planName, planPrice) {
      planTypeInput.value = planType;
      planNameEl.textContent = planName;
      priceEl.textContent = '$' + planPrice + '/mo';
      errorEl.classList.add('hidden');
      errorEl.textContent = '';
      form.reset();
      planTypeInput.value = planType;
      overlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      // Focus first input
      var nameInput = document.getElementById('cp-name');
      if (nameInput) {
        setTimeout(function() { nameInput.focus(); }, 100);
      }
    }

    function closeModal() {
      overlay.classList.add('hidden');
      document.body.style.overflow = '';
    }

    // Attach click handlers to all enroll buttons
    var enrollBtns = document.querySelectorAll('.cp-enroll-btn');
    for (var i = 0; i < enrollBtns.length; i++) {
      enrollBtns[i].addEventListener('click', function() {
        var plan = this.getAttribute('data-plan');
        var name = this.getAttribute('data-plan-name');
        var price = this.getAttribute('data-plan-price');
        openModal(plan, name, price);
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', closeModal);
    }

    // Close on overlay click (not on modal content)
    if (overlay) {
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
      });
    }

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !overlay.classList.contains('hidden')) {
        closeModal();
      }
    });

    // ─── Form submission ──────────────────────────────────────────────
    function setLoading(loading) {
      submitBtn.disabled = loading;
      submitText.textContent = loading ? 'Processing...' : 'Continue to Payment';
      if (loading) {
        submitSpinner.classList.remove('hidden');
      } else {
        submitSpinner.classList.add('hidden');
      }
    }

    function showError(msg) {
      errorEl.textContent = msg;
      errorEl.classList.remove('hidden');
    }

    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        errorEl.classList.add('hidden');

        var nameVal = document.getElementById('cp-name').value.trim();
        var emailVal = document.getElementById('cp-email').value.trim();
        var phoneVal = document.getElementById('cp-phone').value.trim();
        var planVal = planTypeInput.value;

        // Client-side validation
        if (!nameVal) {
          showError('Please enter your full name.');
          return;
        }
        if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
          showError('Please enter a valid email address.');
          return;
        }

        setLoading(true);

        fetch('/api/care-plan-enroll.php', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            plan_type: planVal,
            name: nameVal,
            email: emailVal,
            phone: phoneVal
          })
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
          setLoading(false);

          if (!json.success) {
            showError(json.error || 'Something went wrong. Please try again.');
            return;
          }

          var data = json.data || {};

          // If PayPal approval URL is available, redirect there
          if (data.approval_url) {
            window.location.href = data.approval_url;
            return;
          }

          // Otherwise, show pending confirmation
          closeModal();
          var pendingBanner = document.getElementById('cp-pending-banner');
          var pendingMsg = document.getElementById('cp-pending-message');
          if (pendingBanner) {
            if (data.message) pendingMsg.textContent = data.message;
            pendingBanner.classList.remove('hidden');
            pendingBanner.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }

          // GA4 event
          if (typeof gtag === 'function') {
            gtag('event', 'care_plan_enroll_submitted', {
              plan_type: planVal,
              enrollment_id: data.enrollment_id
            });
          }
        })
        .catch(function(err) {
          setLoading(false);
          showError('Network error. Please check your connection and try again.');
        });
      });
    }

    // ─── Auto-open modal from URL hash ────────────────────────────────
    if (window.location.hash) {
      var hashPlan = window.location.hash.replace('#enroll-', '');
      if (['basic', 'standard', 'premium'].indexOf(hashPlan) !== -1) {
        var planNames = { basic: 'Basic Care Plan', standard: 'Standard Care Plan', premium: 'Premium Care Plan' };
        var planPrices = { basic: '19', standard: '29', premium: '49' };
        openModal(hashPlan, planNames[hashPlan], planPrices[hashPlan]);
      }
    }
  })();
  </script>
</body>
</html>
