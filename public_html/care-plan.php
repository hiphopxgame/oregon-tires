<?php
/**
 * Oregon Tires — Care Plan Membership Pricing
 * Bilingual (EN/ES) membership pricing page with enrollment modal.
 * Visibility controlled via admin site settings (show_care_plan_page).
 */
require_once __DIR__ . '/includes/bootstrap.php';
$pdo = getDB();
$cpStmt = $pdo->prepare("SELECT value_en FROM oretir_site_settings WHERE setting_key = 'show_care_plan_page' LIMIT 1");
$cpStmt->execute();
$cpVisible = $cpStmt->fetchColumn();
if (!$cpVisible || $cpVisible === '0' || $cpVisible === 'false' || $cpVisible === 'off') {
    http_response_code(404);
    if (file_exists(__DIR__ . '/404.html')) { include __DIR__ . '/404.html'; }
    else { echo '<h1>404 Not Found</h1>'; }
    exit;
}

$pageTitle = 'Care Plan Membership | Oregon Tires Auto Care';
$pageTitleEs = 'Plan de Cuidado | Oregon Tires Auto Care';
$pageDesc = 'Save hundreds per year with Oregon Tires Care Plans. Oil changes, tire rotations, service discounts &amp; priority scheduling starting at $19/month.';
$pageDescEs = 'Ahorre cientos de d&oacute;lares al a&ntilde;o con los Planes de Cuidado de Oregon Tires. Cambios de aceite, rotaci&oacute;n de llantas, descuentos y programaci&oacute;n prioritaria desde $19/mes.';
$canonicalUrl = 'https://oregon.tires/care-plan';
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
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-green-700 focus:font-semibold" data-t="skipToContent">Skip to main content</a>

  <?php include __DIR__ . '/templates/header.php'; ?>

  <main id="main-content">
    <!-- Status Banners (shown via JS based on query params) -->
    <div id="cp-enrolled-banner" class="hidden bg-green-600 text-white py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#10003;</span>
        <div>
          <p class="font-bold text-lg" data-t="enrolledTitle">Welcome to the Oregon Tires Care Plan!</p>
          <p class="text-sm opacity-90" data-t="enrolledSubtitle">Your enrollment is confirmed. Your benefits are active immediately.</p>
        </div>
      </div>
    </div>
    <div id="cp-cancelled-banner" class="hidden bg-amber-500 text-black py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#9888;</span>
        <div>
          <p class="font-bold" data-t="cancelledTitle">Enrollment not completed</p>
          <p class="text-sm" data-t="cancelledSubtitle">Payment was cancelled. You can enroll anytime below.</p>
        </div>
      </div>
    </div>
    <div id="cp-pending-banner" class="hidden bg-blue-600 text-white py-4 px-4 text-center">
      <div class="container mx-auto max-w-3xl flex items-center justify-center gap-3">
        <span class="text-2xl" aria-hidden="true">&#9993;</span>
        <div>
          <p class="font-bold" data-t="pendingTitle">Enrollment Received!</p>
          <p class="text-sm opacity-90" id="cp-pending-message" data-t="pendingSubtitle">We will contact you to complete payment setup.</p>
        </div>
      </div>
    </div>

    <!-- Hero -->
    <section class="bg-brand text-white py-16 relative">
      <div class="absolute inset-0 bg-gradient-to-br from-green-900/90 to-brand/95" aria-hidden="true"></div>
      <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-white/70 flex justify-center">
          <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-amber-300" data-t="breadcrumbHome">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-white font-medium" data-t="breadcrumbCarePlan">Care Plan</li>
          </ol>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold mb-4" data-t="heroTitle">Oregon Tires Care Plan</h1>
        <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto" data-t="heroSubtitle">Save money on every visit. One simple monthly plan covers oil changes, tire rotations, discounts, and priority scheduling.</p>
      </div>
    </section>

    <!-- Pricing Tiers -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-5xl">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 text-center mb-4" data-t="pricingTitle">Choose Your Plan</h2>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-10 max-w-xl mx-auto" data-t="pricingSubtitle">Every plan includes priority scheduling and savings that pay for themselves within months.</p>

        <div class="grid md:grid-cols-3 gap-6">
          <!-- Basic -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-md border border-gray-200 dark:border-gray-600 p-6 flex flex-col">
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1" data-t="planBasic">Basic</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$19</span><span class="text-gray-500 dark:text-gray-400" data-t="perMonth">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="basic1">1 oil change per year</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="basic2">5% off all services</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="basic3">Free tire rotations</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="basic4">Priority scheduling</span></li>
            </ul>
            <button type="button" data-plan="basic" data-plan-name="Basic Care Plan" data-plan-price="19" class="cp-enroll-btn block w-full text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition cursor-pointer" data-t="enrollNow">Enroll Now</button>
          </div>

          <!-- Standard (Popular) -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-lg border-2 border-amber-500 p-6 flex flex-col relative">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-black text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wide" data-t="mostPopular">Most Popular</span>
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1" data-t="planStandard">Standard</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$29</span><span class="text-gray-500 dark:text-gray-400" data-t="perMonth">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="std1">2 oil changes per year</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="std2">10% off all services</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="std3">Free tire rotations</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="std4">Priority scheduling</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="std5">Free multi-point inspections</span></li>
            </ul>
            <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn block w-full text-center bg-amber-500 text-black px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition shadow-md cursor-pointer" data-t="enrollNow">Enroll Now</button>
          </div>

          <!-- Premium -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-md border border-gray-200 dark:border-gray-600 p-6 flex flex-col">
            <h3 class="text-xl font-bold text-brand dark:text-green-400 mb-1" data-t="planPremium">Premium</h3>
            <div class="mb-4"><span class="text-4xl font-extrabold text-gray-900 dark:text-white">$49</span><span class="text-gray-500 dark:text-gray-400" data-t="perMonth">/mo</span></div>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-8 flex-1">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem1">Unlimited oil changes</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem2">15% off all services</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem3">Free tire rotations</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem4">Priority scheduling</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem5">Free multi-point inspections</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem6">Roadside assistance</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="prem7">Free alignment check</span></li>
            </ul>
            <button type="button" data-plan="premium" data-plan-name="Premium Care Plan" data-plan-price="49" class="cp-enroll-btn block w-full text-center bg-brand text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition cursor-pointer" data-t="enrollNow">Enroll Now</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ROI Calculator -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 mb-3" data-t="roiTitle">Average Portland Driver Saves $380/Year</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-10" data-t="roiSubtitle">Here is how the savings add up with a Standard plan:</p>
        <div class="grid sm:grid-cols-3 gap-6">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$70</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium" data-t="roiOilTitle">2 Oil Changes Saved</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" data-t="roiOilDesc">Avg $35 each included free</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$120</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium" data-t="roiTireTitle">Tire Rotations Saved</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" data-t="roiTireDesc">2-3 rotations/yr included free</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-extrabold text-amber-500 mb-1">$190+</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 font-medium" data-t="roiDiscountTitle">Service Discounts</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" data-t="roiDiscountDesc">10% off brakes, alignment, etc.</p>
          </div>
        </div>
        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400" data-t="roiDisclaimer">Based on average annual maintenance for Portland-area vehicles. Actual savings vary by vehicle and usage.</p>
      </div>
    </section>

    <!-- Loyalty & Referral Perks -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 text-center mb-4" data-t="perksTitle">Earn Even More with Loyalty &amp; Referrals</h2>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-10 max-w-xl mx-auto" data-t="perksSubtitle">Every visit earns loyalty points. Refer friends for bonus rewards.</p>
        <div class="grid sm:grid-cols-2 gap-6">
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
            <div class="text-3xl mb-3" aria-hidden="true">&#11088;</div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2" data-t="loyaltyTitle">Loyalty Points</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3" data-t="loyaltyDesc">Earn points on every service visit based on your invoice total. Redeem them for discounts, free services, and exclusive rewards.</p>
            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="loyaltyPerk1">Points earned automatically on every visit</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="loyaltyPerk2">Track your balance in your member dashboard</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="loyaltyPerk3">Redeem for discounts and free services</span></li>
            </ul>
          </div>
          <div class="bg-white dark:bg-gray-700 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
            <div class="text-3xl mb-3" aria-hidden="true">&#129309;</div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2" data-t="referralTitle">Refer a Friend</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3" data-t="referralDesc">Share your unique referral code. When a friend books and completes their first service, you both earn bonus loyalty points.</p>
            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="referralPerk1">You earn 100 bonus points per referral</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="referralPerk2">Your friend earns 50 welcome points</span></li>
              <li class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400 mt-0.5">&#10003;</span> <span data-t="referralPerk3">No limit on referrals — keep sharing!</span></li>
            </ul>
          </div>
        </div>
        <p class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400" data-t="perksCta">Sign in to your <a href="/members?tab=loyalty" class="text-brand dark:text-green-400 font-semibold hover:underline" data-t="perksDashboard">member dashboard</a> to view your points and get your referral code.</p>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-white dark:bg-gray-900">
      <div class="container mx-auto px-4 max-w-2xl">
        <h2 class="text-2xl md:text-3xl font-bold text-brand dark:text-green-400 text-center mb-10" data-t="faqTitle">Frequently Asked Questions</h2>
        <div class="space-y-4">
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between"><span data-t="faq1q">Can I cancel anytime?</span><span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm" data-t="faq1a">Yes. There are no contracts or cancellation fees. You can cancel your Care Plan at any time and your benefits continue through the end of your current billing period.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between"><span data-t="faq2q">Can I add family members?</span><span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm" data-t="faq2a">Each Care Plan covers one vehicle. Additional vehicles in your household can be enrolled at the same tier with a 10% multi-vehicle discount.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between"><span data-t="faq3q">When do my savings start?</span><span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm" data-t="faq3a">Immediately. Your service discounts and priority scheduling are active from your first visit. Oil changes and tire rotations are available as soon as your plan starts.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between"><span data-t="faq4q">Can I combine with other offers?</span><span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm" data-t="faq4a">Care Plan discounts apply on top of most seasonal promotions. They cannot be combined with other membership or loyalty discounts. Ask our team for details.</p>
          </details>
          <details class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-5 group">
            <summary class="font-semibold text-gray-900 dark:text-white cursor-pointer list-none flex items-center justify-between"><span data-t="faq5q">What services are covered?</span><span class="text-brand dark:text-green-400 text-xl ml-2 group-open:rotate-45 transition-transform">+</span></summary>
            <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm" data-t="faq5a">Your percentage discount applies to all services we offer: brakes, alignment, suspension, engine work, diagnostics, and more. Included oil changes use conventional oil (synthetic upgrade available at a reduced rate). Tire rotations are unlimited on all plans.</p>
          </details>
        </div>
      </div>
    </section>

    <!-- CTA Banner -->
    <section class="bg-amber-500 text-black py-10">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-3" data-t="ctaTitle">Ready to Start Saving?</h2>
        <p class="mb-6 max-w-lg mx-auto" data-t="ctaSubtitle">Join hundreds of Portland drivers who save money and skip the line with an Oregon Tires Care Plan.</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn bg-brand text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-800 transition shadow-lg cursor-pointer" data-t="enrollNow">Enroll Now</button>
          <a href="tel:5033679714" class="border-2 border-black text-black px-8 py-3 rounded-lg font-semibold hover:bg-black/10 transition" data-t="ctaCall">Call (503) 367-9714</a>
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
          <h2 id="cp-modal-title" class="text-xl font-bold text-gray-900 dark:text-white"><span data-t="modalEnrollIn">Enroll in</span> <span id="cp-modal-plan-name">Care Plan</span></h2>
          <button type="button" id="cp-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none" aria-label="Close">&times;</button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span data-t="modalMonthly">Monthly subscription:</span> <strong id="cp-modal-price" class="text-brand dark:text-green-400">$29/mo</strong></p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6" data-t="modalNoContract">Cancel anytime. No contracts or hidden fees.</p>

        <form id="cp-enroll-form" novalidate>
          <input type="hidden" id="cp-plan-type" name="plan_type" value="">
          <div class="space-y-4">
            <div>
              <label for="cp-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><span data-t="labelName">Full Name</span> <span class="text-red-500">*</span></label>
              <input type="text" id="cp-name" name="name" required autocomplete="name" maxlength="200"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
            <div>
              <label for="cp-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><span data-t="labelEmail">Email</span> <span class="text-red-500">*</span></label>
              <input type="email" id="cp-email" name="email" required autocomplete="email" maxlength="254"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
            <div>
              <label for="cp-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><span data-t="labelPhone">Phone</span> <span class="text-gray-400 text-xs" data-t="labelOptional">(optional)</span></label>
              <input type="tel" id="cp-phone" name="phone" autocomplete="tel" maxlength="30"
                     class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
            </div>
          </div>

          <div id="cp-form-error" class="hidden mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300"></div>

          <button type="submit" id="cp-submit-btn" class="mt-6 w-full bg-brand text-white py-3 rounded-lg font-semibold hover:bg-green-800 transition flex items-center justify-center gap-2">
            <span id="cp-submit-text" data-t="submitBtn">Continue to Payment</span>
            <span id="cp-submit-spinner" class="hidden">
              <svg class="motion-safe:animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            </span>
          </button>

          <p class="mt-3 text-xs text-center text-gray-500 dark:text-gray-400" data-t="modalPaypalHelp">Secure payment processed by PayPal. You will be redirected to complete payment.</p>
        </form>
      </div>
    </div>
  </div>

  <!-- Sticky Mobile CTA -->
  <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-brand shadow-[0_-4px_12px_rgba(0,0,0,0.15)] border-t border-green-700" role="complementary" aria-label="Quick actions">
    <div class="flex">
      <a href="tel:5033679714" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-white font-semibold text-sm border-r border-green-700">
        <span aria-hidden="true">&#x1F4DE;</span> <span data-t="callNow">Call Now</span>
      </a>
      <button type="button" data-plan="standard" data-plan-name="Standard Care Plan" data-plan-price="29" class="cp-enroll-btn flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-black font-semibold text-sm cursor-pointer border-0">
        <span aria-hidden="true">&#x1F4C5;</span> <span data-t="enrollNow">Enroll Now</span>
      </button>
    </div>
  </div>

  <!-- Bilingual translations -->
  <script>
  (function(){
    var t = {
      skipToContent: { en: 'Skip to main content', es: 'Saltar al contenido principal' },
      breadcrumbHome: { en: 'Home', es: 'Inicio' },
      breadcrumbCarePlan: { en: 'Care Plan', es: 'Plan de Cuidado' },
      heroTitle: { en: 'Oregon Tires Care Plan', es: 'Plan de Cuidado Oregon Tires' },
      heroSubtitle: { en: 'Save money on every visit. One simple monthly plan covers oil changes, tire rotations, discounts, and priority scheduling.', es: 'Ahorre dinero en cada visita. Un simple plan mensual cubre cambios de aceite, rotaci\u00f3n de llantas, descuentos y programaci\u00f3n prioritaria.' },
      enrolledTitle: { en: 'Welcome to the Oregon Tires Care Plan!', es: '\u00a1Bienvenido al Plan de Cuidado de Oregon Tires!' },
      enrolledSubtitle: { en: 'Your enrollment is confirmed. Your benefits are active immediately.', es: 'Su inscripci\u00f3n est\u00e1 confirmada. Sus beneficios est\u00e1n activos de inmediato.' },
      cancelledTitle: { en: 'Enrollment not completed', es: 'Inscripci\u00f3n no completada' },
      cancelledSubtitle: { en: 'Payment was cancelled. You can enroll anytime below.', es: 'El pago fue cancelado. Puede inscribirse en cualquier momento a continuaci\u00f3n.' },
      pendingTitle: { en: 'Enrollment Received!', es: '\u00a1Inscripci\u00f3n Recibida!' },
      pendingSubtitle: { en: 'We will contact you to complete payment setup.', es: 'Nos comunicaremos con usted para completar la configuraci\u00f3n de pago.' },
      pricingTitle: { en: 'Choose Your Plan', es: 'Elija Su Plan' },
      pricingSubtitle: { en: 'Every plan includes priority scheduling and savings that pay for themselves within months.', es: 'Cada plan incluye programaci\u00f3n prioritaria y ahorros que se pagan solos en pocos meses.' },
      planBasic: { en: 'Basic', es: 'B\u00e1sico' },
      planStandard: { en: 'Standard', es: 'Est\u00e1ndar' },
      planPremium: { en: 'Premium', es: 'Premium' },
      perMonth: { en: '/mo', es: '/mes' },
      mostPopular: { en: 'Most Popular', es: 'M\u00e1s Popular' },
      enrollNow: { en: 'Enroll Now', es: 'Inscr\u00edbase Ahora' },
      basic1: { en: '1 oil change per year', es: '1 cambio de aceite por a\u00f1o' },
      basic2: { en: '5% off all services', es: '5% de descuento en todos los servicios' },
      basic3: { en: 'Free tire rotations', es: 'Rotaci\u00f3n de llantas gratis' },
      basic4: { en: 'Priority scheduling', es: 'Programaci\u00f3n prioritaria' },
      std1: { en: '2 oil changes per year', es: '2 cambios de aceite por a\u00f1o' },
      std2: { en: '10% off all services', es: '10% de descuento en todos los servicios' },
      std3: { en: 'Free tire rotations', es: 'Rotaci\u00f3n de llantas gratis' },
      std4: { en: 'Priority scheduling', es: 'Programaci\u00f3n prioritaria' },
      std5: { en: 'Free multi-point inspections', es: 'Inspecciones multipunto gratis' },
      prem1: { en: 'Unlimited oil changes', es: 'Cambios de aceite ilimitados' },
      prem2: { en: '15% off all services', es: '15% de descuento en todos los servicios' },
      prem3: { en: 'Free tire rotations', es: 'Rotaci\u00f3n de llantas gratis' },
      prem4: { en: 'Priority scheduling', es: 'Programaci\u00f3n prioritaria' },
      prem5: { en: 'Free multi-point inspections', es: 'Inspecciones multipunto gratis' },
      prem6: { en: 'Roadside assistance', es: 'Asistencia en carretera' },
      prem7: { en: 'Free alignment check', es: 'Revisi\u00f3n de alineaci\u00f3n gratis' },
      roiTitle: { en: 'Average Portland Driver Saves $380/Year', es: 'El Conductor Promedio de Portland Ahorra $380/A\u00f1o' },
      roiSubtitle: { en: 'Here is how the savings add up with a Standard plan:', es: 'As\u00ed es como se acumulan los ahorros con un plan Est\u00e1ndar:' },
      roiOilTitle: { en: '2 Oil Changes Saved', es: '2 Cambios de Aceite Ahorrados' },
      roiOilDesc: { en: 'Avg $35 each included free', es: 'Promedio $35 cada uno incluidos gratis' },
      roiTireTitle: { en: 'Tire Rotations Saved', es: 'Rotaciones de Llantas Ahorradas' },
      roiTireDesc: { en: '2-3 rotations/yr included free', es: '2-3 rotaciones/a\u00f1o incluidas gratis' },
      roiDiscountTitle: { en: 'Service Discounts', es: 'Descuentos en Servicios' },
      roiDiscountDesc: { en: '10% off brakes, alignment, etc.', es: '10% de descuento en frenos, alineaci\u00f3n, etc.' },
      roiDisclaimer: { en: 'Based on average annual maintenance for Portland-area vehicles. Actual savings vary by vehicle and usage.', es: 'Basado en el mantenimiento anual promedio para veh\u00edculos del \u00e1rea de Portland. Los ahorros reales var\u00edan seg\u00fan el veh\u00edculo y el uso.' },
      faqTitle: { en: 'Frequently Asked Questions', es: 'Preguntas Frecuentes' },
      faq1q: { en: 'Can I cancel anytime?', es: '\u00bfPuedo cancelar en cualquier momento?' },
      faq1a: { en: 'Yes. There are no contracts or cancellation fees. You can cancel your Care Plan at any time and your benefits continue through the end of your current billing period.', es: 'S\u00ed. No hay contratos ni cargos por cancelaci\u00f3n. Puede cancelar su Plan de Cuidado en cualquier momento y sus beneficios contin\u00faan hasta el final de su per\u00edodo de facturaci\u00f3n actual.' },
      faq2q: { en: 'Can I add family members?', es: '\u00bfPuedo agregar miembros de mi familia?' },
      faq2a: { en: 'Each Care Plan covers one vehicle. Additional vehicles in your household can be enrolled at the same tier with a 10% multi-vehicle discount.', es: 'Cada Plan de Cuidado cubre un veh\u00edculo. Los veh\u00edculos adicionales en su hogar pueden inscribirse en el mismo nivel con un 10% de descuento por m\u00faltiples veh\u00edculos.' },
      faq3q: { en: 'When do my savings start?', es: '\u00bfCu\u00e1ndo empiezan mis ahorros?' },
      faq3a: { en: 'Immediately. Your service discounts and priority scheduling are active from your first visit. Oil changes and tire rotations are available as soon as your plan starts.', es: 'De inmediato. Sus descuentos en servicios y programaci\u00f3n prioritaria est\u00e1n activos desde su primera visita. Los cambios de aceite y rotaciones de llantas est\u00e1n disponibles tan pronto como comience su plan.' },
      faq4q: { en: 'Can I combine with other offers?', es: '\u00bfPuedo combinar con otras ofertas?' },
      faq4a: { en: 'Care Plan discounts apply on top of most seasonal promotions. They cannot be combined with other membership or loyalty discounts. Ask our team for details.', es: 'Los descuentos del Plan de Cuidado se aplican sobre la mayor\u00eda de las promociones de temporada. No se pueden combinar con otros descuentos de membres\u00eda o lealtad. Pregunte a nuestro equipo por detalles.' },
      faq5q: { en: 'What services are covered?', es: '\u00bfQu\u00e9 servicios est\u00e1n cubiertos?' },
      faq5a: { en: 'Your percentage discount applies to all services we offer: brakes, alignment, suspension, engine work, diagnostics, and more. Included oil changes use conventional oil (synthetic upgrade available at a reduced rate). Tire rotations are unlimited on all plans.', es: 'Su descuento porcentual se aplica a todos los servicios que ofrecemos: frenos, alineaci\u00f3n, suspensi\u00f3n, trabajo de motor, diagn\u00f3sticos y m\u00e1s. Los cambios de aceite incluidos usan aceite convencional (mejora a sint\u00e9tico disponible a precio reducido). Las rotaciones de llantas son ilimitadas en todos los planes.' },
      ctaTitle: { en: 'Ready to Start Saving?', es: '\u00bfListo para Empezar a Ahorrar?' },
      ctaSubtitle: { en: 'Join hundreds of Portland drivers who save money and skip the line with an Oregon Tires Care Plan.', es: '\u00danase a cientos de conductores de Portland que ahorran dinero y evitan la fila con un Plan de Cuidado de Oregon Tires.' },
      ctaCall: { en: 'Call (503) 367-9714', es: 'Llamar (503) 367-9714' },
      callNow: { en: 'Call Now', es: 'Llamar Ahora' },
      modalEnrollIn: { en: 'Enroll in', es: 'Inscribirse en' },
      modalMonthly: { en: 'Monthly subscription:', es: 'Suscripci\u00f3n mensual:' },
      modalNoContract: { en: 'Cancel anytime. No contracts or hidden fees.', es: 'Cancele cuando quiera. Sin contratos ni cargos ocultos.' },
      labelName: { en: 'Full Name', es: 'Nombre Completo' },
      labelEmail: { en: 'Email', es: 'Correo Electr\u00f3nico' },
      labelPhone: { en: 'Phone', es: 'Tel\u00e9fono' },
      labelOptional: { en: '(optional)', es: '(opcional)' },
      submitBtn: { en: 'Continue to Payment', es: 'Continuar al Pago' },
      modalPaypalHelp: { en: 'Secure payment processed by PayPal. You will be redirected to complete payment.', es: 'Pago seguro procesado por PayPal. Ser\u00e1 redirigido para completar el pago.' },
      processing: { en: 'Processing...', es: 'Procesando...' },
      errName: { en: 'Please enter your full name.', es: 'Por favor ingrese su nombre completo.' },
      errEmail: { en: 'Please enter a valid email address.', es: 'Por favor ingrese un correo electr\u00f3nico v\u00e1lido.' },
      errGeneric: { en: 'Something went wrong. Please try again.', es: 'Algo sali\u00f3 mal. Por favor intente de nuevo.' },
      errNetwork: { en: 'Network error. Please check your connection and try again.', es: 'Error de red. Por favor verifique su conexi\u00f3n e intente de nuevo.' },
      perksTitle: { en: 'Earn Even More with Loyalty & Referrals', es: 'Gane A\u00fan M\u00e1s con Lealtad y Referencias' },
      perksSubtitle: { en: 'Every visit earns loyalty points. Refer friends for bonus rewards.', es: 'Cada visita acumula puntos de lealtad. Refiera amigos para recompensas adicionales.' },
      loyaltyTitle: { en: 'Loyalty Points', es: 'Puntos de Lealtad' },
      loyaltyDesc: { en: 'Earn points on every service visit based on your invoice total. Redeem them for discounts, free services, and exclusive rewards.', es: 'Gane puntos en cada visita de servicio basado en el total de su factura. Canj\u00e9elos por descuentos, servicios gratis y recompensas exclusivas.' },
      loyaltyPerk1: { en: 'Points earned automatically on every visit', es: 'Puntos ganados autom\u00e1ticamente en cada visita' },
      loyaltyPerk2: { en: 'Track your balance in your member dashboard', es: 'Consulte su saldo en su panel de miembro' },
      loyaltyPerk3: { en: 'Redeem for discounts and free services', es: 'Canjee por descuentos y servicios gratis' },
      referralTitle: { en: 'Refer a Friend', es: 'Referir un Amigo' },
      referralDesc: { en: 'Share your unique referral code. When a friend books and completes their first service, you both earn bonus loyalty points.', es: 'Comparta su c\u00f3digo de referencia. Cuando un amigo reserva y completa su primer servicio, ambos ganan puntos de lealtad adicionales.' },
      referralPerk1: { en: 'You earn 100 bonus points per referral', es: 'Usted gana 100 puntos de bonificaci\u00f3n por referencia' },
      referralPerk2: { en: 'Your friend earns 50 welcome points', es: 'Su amigo gana 50 puntos de bienvenida' },
      referralPerk3: { en: 'No limit on referrals \u2014 keep sharing!', es: '\u00a1Sin l\u00edmite de referencias \u2014 siga compartiendo!' },
      perksCta: { en: 'Sign in to your member dashboard to view your points and get your referral code.', es: 'Inicie sesi\u00f3n en su panel de miembro para ver sus puntos y obtener su c\u00f3digo de referencia.' },
      perksDashboard: { en: 'member dashboard', es: 'panel de miembro' }
    };
    var params = new URLSearchParams(window.location.search);
    var lang = params.get('lang');
    if (!lang) { lang = (navigator.language || '').startsWith('es') ? 'es' : 'en'; }
    window.currentLang = lang;
    window._cpT = t;
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

  <script>
  (function() {
    'use strict';
    var lang = window.currentLang || 'en';
    var t = window._cpT || {};
    function tr(key) { return (t[key] && t[key][lang]) ? t[key][lang] : (t[key] ? t[key].en : key); }

    // ─── Bilingual plan names for modal ─────────────────────────────
    var planNamesI18n = {
      basic:    { en: 'Basic Care Plan', es: 'Plan B\u00e1sico de Cuidado' },
      standard: { en: 'Standard Care Plan', es: 'Plan Est\u00e1ndar de Cuidado' },
      premium:  { en: 'Premium Care Plan', es: 'Plan Premium de Cuidado' }
    };
    function getPlanName(planType) {
      var p = planNamesI18n[planType];
      return p ? (p[lang] || p.en) : planType;
    }

    // ─── Query param banners ──────────────────────────────────────────
    var params = new URLSearchParams(window.location.search);
    if (params.get('enrolled') === 'true') {
      var enrolledBanner = document.getElementById('cp-enrolled-banner');
      if (enrolledBanner) enrolledBanner.classList.remove('hidden');
      // Clean URL (preserve lang param)
      var cleanUrl = window.location.pathname;
      if (lang !== 'en') cleanUrl += '?lang=' + lang;
      window.history.replaceState({}, '', cleanUrl);
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
      var cleanUrl2 = window.location.pathname;
      if (lang !== 'en') cleanUrl2 += '?lang=' + lang;
      window.history.replaceState({}, '', cleanUrl2);
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
      var perMo = lang === 'es' ? '/mes' : '/mo';
      priceEl.textContent = '$' + planPrice + perMo;
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
        var name = getPlanName(plan);
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
      submitText.textContent = loading ? tr('processing') : tr('submitBtn');
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
          showError(tr('errName'));
          return;
        }
        if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
          showError(tr('errEmail'));
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
            showError(json.error || tr('errGeneric'));
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
          showError(tr('errNetwork'));
        });
      });
    }

    // ─── Auto-open modal from URL hash ────────────────────────────────
    if (window.location.hash) {
      var hashPlan = window.location.hash.replace('#enroll-', '');
      if (['basic', 'standard', 'premium'].indexOf(hashPlan) !== -1) {
        var planPrices = { basic: '19', standard: '29', premium: '49' };
        openModal(hashPlan, getPlanName(hashPlan), planPrices[hashPlan]);
      }
    }
  })();
  </script>
</body>
</html>
