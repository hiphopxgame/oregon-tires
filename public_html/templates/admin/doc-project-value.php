<?php
/**
 * Oregon Tires Auto Care — Project Value Report
 *
 * Comprehensive feature inventory and market valuation.
 * Synced with feature-data.js (admin "Your Platform" tab) as single source of truth.
 * 113 features across 17 categories. Total value: $157,200.
 * Updated March 2026.
 */

$reportDate = 'March 2026';
$totalFeatures = 113;
$totalValue = 157200;
$totalTables = 53;
$totalMigrations = 53;
$totalEndpoints = 119;
$totalCronJobs = 7;

$categories = [
    [
        'id' => 1,
        'name' => 'Website Foundation',
        'icon' => '&#x1F3D7;',
        'count' => 7,
        'value' => 9700,
        'features' => [
            ['Custom responsive website (Tailwind CSS v4)', 3000, 'Mobile-first layout, component-based design'],
            ['Bilingual system (EN/ES)', 2500, 'Full inline translation system with data-t attributes'],
            ['Dark mode support', 800, 'Tailwind v4 class-based dark theme toggle'],
            ['SSL + security headers + .htaccess hardening', 500, 'HTTPS redirect, XSS/clickjack protection, file blocking'],
            ['SEO foundation (meta tags, OG, JSON-LD schema)', 1500, 'Per-page config, Organization schema, canonical URLs'],
            ['Clean URL routing (.php stripping + path-based)', 1000, '301 redirects, friendly URLs throughout'],
            ['Social media links + footer integration', 400, 'Dynamic social links across all pages'],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Public Pages',
        'icon' => '&#x1F4C4;',
        'count' => 17,
        'value' => 15400,
        'features' => [
            ['Homepage with hero + dynamic background', 2000, 'Hero section, 7 service cards, CTAs, bilingual'],
            ['Contact page + form', 800, 'Bilingual, validates, stores to DB'],
            ['FAQ page (dynamic)', 600, 'Pulls from DB, bilingual, admin-managed'],
            ['Why Us / About page', 500, 'Value proposition, trust signals'],
            ['Google Reviews display page', 1000, 'Live Google reviews via Places API'],
            ['Service Guarantee page', 400, 'Static content page'],
            ['Blog listing + single post pages', 1500, 'CMS-driven, SEO-optimized'],
            ['Promotions page', 800, 'Dynamic from DB, image + placement targeting'],
            ['Care Plan info + enrollment page', 1500, 'PayPal subscription integration'],
            ['Checkout page', 1200, 'Card + PayPal payment flow'],
            ['Financing page', 400, 'Informational + lead capture'],
            ['Tire Quote request page', 800, 'Form + DB storage + admin management'],
            ['Photo gallery page', 800, 'Bilingual captions, video support, lightbox'],
            ['10 service pages (tire, brake, oil, etc.)', 1500, 'SEO-optimized service descriptions'],
            ['Service Areas overview page', 500, 'Regional targeting'],
            ['Feedback submission page', 600, 'Customer feedback form + DB storage'],
            ['System status page', 500, 'Platform health + uptime display'],
        ]
    ],
    [
        'id' => 3,
        'name' => 'Regional SEO Pages',
        'icon' => '&#x1F4CD;',
        'count' => 1,
        'value' => 2400,
        'features' => [
            ['8 regional SEO pages (Portland neighborhoods)', 2400, 'SE Portland, Woodstock, Lents, Happy Valley, etc.'],
        ]
    ],
    [
        'id' => 4,
        'name' => 'Booking &amp; Appointments',
        'icon' => '&#x1F4C5;',
        'count' => 9,
        'value' => 13300,
        'features' => [
            ['Online booking form with time slot availability', 3500, 'Date picker, slot API, auto-create customer + vehicle'],
            ['VIN decode in booking (NHTSA API)', 2000, 'Permanent DB cache, auto-fill vehicle info'],
            ['License plate lookup in booking', 1500, 'Plate-to-vehicle with DB cache'],
            ['Appointment cancel + reschedule (token-based)', 1500, 'Email links, bilingual confirmation'],
            ['SMS opt-in + booking confirmation emails', 800, 'Opt-in checkbox, bilingual confirmation with calendar link'],
            ['Calendar event (.ics) download', 500, 'Downloadable calendar event for any booking'],
            ['Configurable business hours + holiday calendar', 1500, 'Admin-editable hours, holiday closures, slot blocking'],
            ['Multi-bay capacity + schedule-aware slots', 1200, 'Per-slot bay limits, employee schedule integration'],
            ['Service-specific intake fields', 800, 'Tire preference (new/used), tire count, service type selection'],
        ]
    ],
    [
        'id' => 5,
        'name' => 'Shop Management &mdash; Repair Orders',
        'icon' => '&#x1F527;',
        'count' => 8,
        'value' => 20000,
        'features' => [
            ['Repair Order (RO) lifecycle (10 stages)', 5000, 'intake to invoiced, full status tracking'],
            ['Kanban board (drag-and-drop)', 3000, 'Visual RO management, time-in-status display'],
            ['Digital Vehicle Inspection (DVI)', 3500, '35 items across 12 categories, traffic light system, photo capture'],
            ['Estimate builder + approval system', 2500, 'Auto-generate from inspection, per-item approve/decline, 8 statuses'],
            ['Invoice generation from completed ROs', 2000, 'Token-based customer view, bilingual'],
            ['Labor hours tracking per RO', 2000, 'Technician time tracking, admin UI'],
            ['Visit tracking (check-in/out)', 1200, 'Customer visit log with timestamps'],
            ['Print-optimized reports', 800, 'Inspection + estimate + invoice print layouts'],
        ]
    ],
    [
        'id' => 6,
        'name' => 'Customer Management',
        'icon' => '&#x1F465;',
        'count' => 5,
        'value' => 6000,
        'features' => [
            ['Customer database with search', 2000, 'Persistent records, email unique, admin CRUD'],
            ['Vehicle records per customer', 1500, 'VIN, year/make/model, tire sizes, member linking'],
            ['Tire fitment lookup (API + cache)', 1000, '90-day DB cache, year/make/model lookup'],
            ['Smart account linking', 1000, 'Auto-links booking customers to member accounts'],
            ['Customer language preference tracking', 500, 'Per-customer EN/ES preference for communications'],
        ]
    ],
    [
        'id' => 7,
        'name' => 'Customer Portal &mdash; Member Dashboard',
        'icon' => '&#x1F464;',
        'count' => 5,
        'value' => 7500,
        'features' => [
            ['Member registration + login (bilingual)', 2000, 'Custom auth UI, email verification'],
            ['My Appointments (view/reschedule/cancel)', 1500, 'Member booking history + actions'],
            ['My Vehicles + My Estimates + My Invoices', 1500, 'Customer self-service portal'],
            ['My Messages (customer-to-shop)', 1500, 'Two-way conversation threads'],
            ['My Care Plan (subscription status)', 1000, 'Billing status, plan details'],
        ]
    ],
    [
        'id' => 8,
        'name' => 'Employee Portal',
        'icon' => '&#x1F477;',
        'count' => 6,
        'value' => 7700,
        'features' => [
            ['Employee schedule management', 1500, 'Admin sets schedules, employees view theirs'],
            ['My Assigned Work (employee RO view)', 1500, 'Employee sees their assigned repair orders'],
            ['My Customers (employee view)', 1500, 'Employee customer relationships'],
            ['Skills &amp; certifications tracking', 1000, 'Employee qualifications, searchable by admin'],
            ['Schedule overrides + daily capacity', 1000, 'Per-date exceptions, per-employee bay limits'],
            ['Job assignment + notification system', 1200, 'Assign tech to appointment, auto-notify via email'],
        ]
    ],
    [
        'id' => 9,
        'name' => 'Authentication &amp; Security',
        'icon' => '&#x1F512;',
        'count' => 5,
        'value' => 6000,
        'features' => [
            ['Role-based access control (admin/employee/member)', 2000, 'Tab visibility, API authorization'],
            ['Google OAuth login', 1500, 'Login with Google, link/unlink account'],
            ['Password reset flow (token-based, bilingual)', 1000, 'Email-based reset with secure tokens'],
            ['CSRF protection + session management', 1000, 'Token validation, session regeneration'],
            ['Admin setup email system (invite tokens)', 500, 'Onboard new admin users via email'],
        ]
    ],
    [
        'id' => 10,
        'name' => 'Admin Panel',
        'icon' => '&#x2699;',
        'count' => 11,
        'value' => 15900,
        'features' => [
            ['Admin dashboard with analytics charts', 2500, 'Chart.js &mdash; revenue, appointments, traffic, conversion funnel'],
            ['Appointment management tab', 1500, 'Calendar + list view, status management'],
            ['Customer + Vehicle management tabs', 1500, 'CRUD, search, vehicle history'],
            ['Employee management + skills tracking', 1500, 'CRUD, certifications, schedule config'],
            ['Content management (Blog, FAQ, Promotions, Testimonials)', 2000, '4 content types, bilingual, image upload'],
            ['Gallery management (bilingual captions)', 800, 'Image upload, ordering, video support, bilingual'],
            ['Subscriber management', 700, 'Newsletter list, export'],
            ['Site settings editor + email template config', 1500, 'Editable site content, business hours, email templates'],
            ['Resource planner (multi-date scheduling)', 2500, 'Employee grid, skill gaps, hourly breakdown, recommendations'],
            ['Business hours + holiday configuration', 800, 'Admin UI for hours, holidays, slot capacity'],
            ['Feedback management tab', 600, 'View + respond to customer feedback submissions'],
        ]
    ],
    [
        'id' => 11,
        'name' => 'Customer Engagement',
        'icon' => '&#x2B50;',
        'count' => 6,
        'value' => 9700,
        'features' => [
            ['Care plan subscriptions (PayPal recurring)', 2500, '3-tier plans, enrollment, webhook billing'],
            ['Loyalty points program', 2000, 'Points ledger, redeemable rewards catalog'],
            ['Customer referral program', 1500, 'Referral codes, tracking, bonus points'],
            ['Walk-in waitlist queue', 1500, 'Join/check queue, admin management'],
            ['Tire quote request system', 1000, 'Submit request, admin responds'],
            ['Roadside assistance estimator', 1200, 'Service-specific cost estimation tool'],
        ]
    ],
    [
        'id' => 12,
        'name' => 'Communications',
        'icon' => '&#x1F4E7;',
        'count' => 7,
        'value' => 11800,
        'features' => [
            ['Bilingual email system (PHPMailer)', 2000, '6+ template types, branded HTML emails'],
            ['In-app messaging (admin-to-customer)', 2500, 'Conversation threads, real-time notification bell'],
            ['Inbound email integration (IMAP)', 2500, 'Auto-fetch emails into conversations, Message-ID threading'],
            ['SMS notifications (Twilio)', 1500, 'Appointment reminders, inspection/estimate/ready alerts'],
            ['Email audit trail + logging', 1000, 'Full email log with status tracking'],
            ['Email template variable system', 1500, 'Dynamic template vars, admin reference'],
            ['Estimate expiry reminder emails', 800, 'Automated follow-up for pending estimates'],
        ]
    ],
    [
        'id' => 13,
        'name' => 'Push Notifications &amp; PWA',
        'icon' => '&#x1F514;',
        'count' => 5,
        'value' => 9000,
        'features' => [
            ['Progressive Web App (PWA)', 2000, 'Installable, manifest, service worker caching'],
            ['Web Push notifications (VAPID)', 2500, 'Browser push subscriptions, language prefs'],
            ['Notification queue (bilingual, targeted)', 1500, 'Subscription/customer/broadcast targeting, retry logic'],
            ['Offline booking form (IndexedDB + Background Sync)', 2000, 'Queue submissions offline, replay when online'],
            ['Admin push broadcast (rate-limited)', 1000, 'Send to opted-in subscribers, 5/day limit'],
        ]
    ],
    [
        'id' => 14,
        'name' => 'Automation &amp; Cron',
        'icon' => '&#x23F0;',
        'count' => 7,
        'value' => 7000,
        'features' => [
            ['Appointment reminder emails (next-day)', 1000, 'Daily 6PM cron, email + SMS + push'],
            ['Review request emails (post-service)', 1000, 'Daily 10AM cron, Google review prompts'],
            ['Google Reviews auto-fetch + cache', 1000, 'Daily 6AM cron, Places API'],
            ['Push notification queue processor', 800, 'Every 5 min, processes queued notifications'],
            ['Service reminder automation', 1000, 'Weekly Mon 9AM, due date tracking'],
            ['Estimate expiry reminder emails', 1200, 'Automated follow-up for pending estimates'],
            ['Inbound email fetch (IMAP polling)', 1000, 'Every 2 min, auto-thread into conversations'],
        ]
    ],
    [
        'id' => 15,
        'name' => 'Integrations',
        'icon' => '&#x1F517;',
        'count' => 6,
        'value' => 7300,
        'features' => [
            ['PayPal payments + webhooks', 2000, 'Checkout, subscriptions, IPN handling'],
            ['Google OAuth integration', 1000, 'API integration for identity'],
            ['Google Places API (reviews)', 1500, 'Fetch + cache business reviews'],
            ['NHTSA vPIC API (VIN decode)', 1500, 'Permanent cache, vehicle info auto-fill'],
            ['Cloudflare CDN integration', 800, 'Edge caching, DDoS protection, content negotiation'],
            ['API versioning system', 500, 'v1 alias via .htaccess, X-API-Version header'],
        ]
    ],
    [
        'id' => 16,
        'name' => 'Data &amp; Admin Tools',
        'icon' => '&#x1F4CA;',
        'count' => 5,
        'value' => 5500,
        'features' => [
            ['Data export (CSV)', 800, 'Customers, appointments, ROs'],
            ['Rate limiting (per-IP/user)', 700, 'Protects public APIs from abuse'],
            ['Global search (Ctrl+K)', 1500, 'Search across all admin data'],
            ['Advanced reporting dashboard', 1500, 'Employee productivity, revenue trends, conversion funnel'],
            ['Enhanced analytics (multi-chart)', 1000, 'Stacked bars, trend lines, period comparison'],
        ]
    ],
    [
        'id' => 17,
        'name' => 'Performance &amp; Infrastructure',
        'icon' => '&#x26A1;',
        'count' => 3,
        'value' => 3000,
        'features' => [
            ['Image optimization pipeline (AVIF + WebP)', 1000, 'Responsive picture tags, content negotiation'],
            ['Error tracking (engine-kit, DB fallback)', 1000, '3-tier: Sentry, DB, error_log'],
            ['Health check endpoint + deploy system', 1000, 'Automated deploys, health verification'],
        ]
    ],
];

$comparisons = [
    ['Agency quote for equivalent custom platform', '$150,000 &ndash; $250,000'],
    ['Off-the-shelf shop management SaaS (annual)', '$6,000 &ndash; $18,000/yr'],
    ['Custom WordPress + plugins (approximation)', '$40,000 &ndash; $80,000'],
];

$saasReplacements = [
    ['Shop management software (Tekmetric, ShopBoss)', '$300&ndash;500/mo'],
    ['Appointment booking (Calendly/Acuity)', '$50/mo'],
    ['Email marketing (Mailchimp)', '$50/mo'],
    ['Push notifications (OneSignal)', '$50/mo'],
    ['Customer portal (custom)', 'N/A'],
    ['Loyalty/referral program (Smile.io)', '$100/mo'],
    ['Website + hosting', '$100/mo'],
];

$differentiators = [
    'Fully bilingual (EN/ES) &mdash; every page, email, notification, admin panel',
    'Integrated DVI &rarr; Estimate &rarr; Approval &rarr; Invoice pipeline',
    'Token-based customer portals &mdash; customers view inspections/estimates without login',
    'Offline-capable PWA with Web Push &mdash; works without internet, syncs when back online',
    'Smart account linking &mdash; booking customers auto-linked to member accounts',
    'Inbound email threading &mdash; customer email replies appear in messaging inbox',
    'Custom loyalty + referral + care plan programs &mdash; all integrated, not third-party',
    'Resource planner + schedule-aware booking &mdash; capacity planning built into scheduling',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oregon Tires Auto Care &mdash; Project Value Report</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/styles.css">
    <style>
        :root {
            --brand: #15803d;
            --brand-light: #22c55e;
            --brand-dark: #0D3618;
            --surface: #0f1a13;
            --surface-card: #132319;
            --surface-hover: #1E3325;
            --border: #2D4A33;
            --text: #DCE8DD;
            --text-muted: #8FAF92;
            --amber: #f59e0b;
        }
        body {
            background: linear-gradient(160deg, #0a110d 0%, #0f1a13 50%, #0a110d 100%);
            color: var(--text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .card {
            background: var(--surface-card);
            border: 1px solid var(--border);
        }
        .card-inner {
            background: rgba(21, 128, 61, 0.04);
            border: 1px solid rgba(21, 128, 61, 0.12);
        }
        .text-brand { color: var(--brand-light); }
        .text-muted { color: var(--text-muted); }
        .border-brand { border-color: var(--border); }
        .bg-brand-subtle { background: rgba(21, 128, 61, 0.08); }
        .value-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.6rem;
            background: rgba(21, 128, 61, 0.12);
            border: 1px solid rgba(21, 128, 61, 0.25);
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-light);
            font-variant-numeric: tabular-nums;
        }
        .total-highlight {
            background: linear-gradient(135deg, rgba(21, 128, 61, 0.15) 0%, rgba(245, 158, 11, 0.08) 100%);
            border: 1px solid rgba(21, 128, 61, 0.3);
        }
        @media print {
            body { background: #fff; color: #1a1a1a; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card { background: #f9fafb; border-color: #e5e7eb; break-inside: avoid; }
            .card-inner { background: #f0fdf4; border-color: #bbf7d0; }
            .no-print { display: none !important; }
            .text-brand { color: #15803d; }
            .text-muted { color: #6b7280; }
            .value-badge { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
            .total-highlight { background: #f0fdf4; border-color: #15803d; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body class="p-4 md:p-8 lg:p-12">

<div class="max-w-5xl mx-auto">

    <!-- ===== HEADER ===== -->
    <div class="no-print mb-4">
        <a href="/admin/" class="text-muted hover:text-brand text-sm">&larr; Back to Admin</a>
    </div>

    <div class="card rounded-2xl px-6 py-5 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <picture><source srcset="/assets/logo.webp" type="image/webp"><img src="/assets/logo.png" alt="Oregon Tires Auto Care" class="h-10 w-auto" loading="eager"></picture>
                </div>
                <p class="text-muted text-sm">Project Value Report &bull; <?= htmlspecialchars($reportDate) ?></p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-brand">$<?= number_format($totalValue) ?></p>
                <p class="text-muted text-sm"><?= $totalFeatures ?> features built</p>
            </div>
        </div>
    </div>

    <!-- ===== TITLE ===== -->
    <div class="text-center mb-10">
        <h1 class="font-bold text-3xl md:text-4xl text-white mb-3">Project Value Report</h1>
        <p class="text-muted text-lg max-w-2xl mx-auto leading-relaxed">
            Complete inventory of every feature built for the Oregon Tires Auto Care platform, with fair market valuations based on US agency and freelancer rates.
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-6">
            <div class="card rounded-lg px-4 py-2 text-center">
                <p class="text-xs text-muted uppercase tracking-wider">Features</p>
                <p class="font-bold text-xl text-brand"><?= $totalFeatures ?></p>
            </div>
            <div class="card rounded-lg px-4 py-2 text-center">
                <p class="text-xs text-muted uppercase tracking-wider">Categories</p>
                <p class="font-bold text-xl text-brand"><?= count($categories) ?></p>
            </div>
            <div class="card rounded-lg px-4 py-2 text-center">
                <p class="text-xs text-muted uppercase tracking-wider">Total Value</p>
                <p class="font-bold text-xl text-amber-400">$<?= number_format($totalValue) ?></p>
            </div>
            <div class="card rounded-lg px-4 py-2 text-center no-print">
                <button onclick="window.print()" class="text-xs text-muted uppercase tracking-wider hover:text-brand transition cursor-pointer">
                    Print / PDF
                </button>
                <p class="font-bold text-xl text-brand">&#x1F5A8;</p>
            </div>
        </div>
    </div>

    <!-- ===== PRICING METHODOLOGY ===== -->
    <div class="card rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-lg text-white mb-2">Pricing Methodology</h2>
        <p class="text-muted leading-relaxed text-sm">
            Based on US market rates for custom web development ($125&ndash;175/hr senior developer), compared against equivalent SaaS subscriptions and agency quotes. Each feature is priced as a standalone deliverable at what it would cost to build from scratch by a professional development team.
        </p>
    </div>

    <!-- ===== TABLE OF CONTENTS ===== -->
    <div class="card rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-lg text-white mb-4">Table of Contents</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <?php foreach ($categories as $cat): ?>
            <a href="#s<?= $cat['id'] ?>" class="flex items-center justify-between p-2.5 rounded-lg card-inner hover:bg-brand-subtle transition-all no-print">
                <span>
                    <span class="text-brand font-semibold"><?= $cat['id'] ?>.</span>
                    <span class="text-sm"><?= $cat['name'] ?></span>
                </span>
                <span class="value-badge text-xs">$<?= number_format($cat['value']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== CATEGORY SECTIONS ===== -->
    <?php foreach ($categories as $cat): ?>
    <div id="s<?= $cat['id'] ?>" class="card rounded-2xl p-6 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
            <div>
                <p class="text-xs uppercase tracking-widest text-muted font-semibold mb-1">Section <?= $cat['id'] ?> &bull; <?= $cat['count'] ?> feature<?= $cat['count'] !== 1 ? 's' : '' ?></p>
                <h2 class="font-bold text-xl text-white"><?= $cat['icon'] ?> <?= $cat['name'] ?></h2>
            </div>
            <div class="text-right">
                <span class="value-badge text-base">$<?= number_format($cat['value']) ?></span>
            </div>
        </div>

        <div class="space-y-2">
            <?php foreach ($cat['features'] as $i => $feat): ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 card-inner rounded-lg gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium"><?= $feat[0] ?></p>
                    <p class="text-xs text-muted mt-0.5"><?= $feat[2] ?></p>
                </div>
                <div class="sm:text-right flex-shrink-0">
                    <span class="value-badge">$<?= number_format($feat[1]) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- ===== TOTAL SUMMARY ===== -->
    <div class="total-highlight rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-xl text-white mb-4">Total Project Value</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-brand">
                        <th class="text-left py-2 text-muted font-semibold">Category</th>
                        <th class="text-center py-2 text-muted font-semibold">Features</th>
                        <th class="text-right py-2 text-muted font-semibold">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr class="border-b border-brand/20">
                        <td class="py-2"><?= $cat['name'] ?></td>
                        <td class="py-2 text-center text-muted"><?= $cat['count'] ?></td>
                        <td class="py-2 text-right font-medium text-brand">$<?= number_format($cat['value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-brand">
                        <td class="py-3 font-bold text-white text-base">TOTAL</td>
                        <td class="py-3 text-center font-bold text-white text-base"><?= $totalFeatures ?></td>
                        <td class="py-3 text-right font-bold text-amber-400 text-xl">$<?= number_format($totalValue) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ===== MARKET COMPARISON ===== -->
    <div class="card rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-xl text-white mb-4">Comparable Market Context</h2>
        <div class="space-y-3 mb-6">
            <?php foreach ($comparisons as $comp): ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 card-inner rounded-lg gap-1">
                <span class="text-sm"><?= $comp[0] ?></span>
                <span class="text-muted font-medium text-sm"><?= $comp[1] ?></span>
            </div>
            <?php endforeach; ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 total-highlight rounded-lg gap-1">
                <span class="text-sm font-bold text-white">Oregon Tires platform (current value)</span>
                <span class="font-bold text-amber-400 text-lg">$<?= number_format($totalValue) ?></span>
            </div>
        </div>
    </div>

    <!-- ===== SAAS REPLACEMENT ===== -->
    <div class="card rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-xl text-white mb-2">What This Replaces</h2>
        <p class="text-muted text-sm mb-4">Monthly SaaS equivalents you would otherwise be paying for:</p>
        <div class="space-y-2 mb-4">
            <?php foreach ($saasReplacements as $svc): ?>
            <div class="flex items-center justify-between p-2.5 card-inner rounded-lg">
                <span class="text-sm"><?= $svc[0] ?></span>
                <span class="text-muted text-sm font-medium"><?= $svc[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="total-highlight rounded-lg p-4 text-center">
            <p class="text-muted text-sm">Estimated total SaaS replacement cost</p>
            <p class="text-2xl font-bold text-amber-400 mt-1">~$650&ndash;850/mo</p>
            <p class="text-muted text-sm mt-1">($7,800&ndash;$10,200 per year)</p>
        </div>
    </div>

    <!-- ===== KEY DIFFERENTIATORS ===== -->
    <div class="card rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-xl text-white mb-4">Key Differentiators</h2>
        <p class="text-muted text-sm mb-4">Features not available in off-the-shelf solutions:</p>
        <ol class="space-y-2">
            <?php foreach ($differentiators as $i => $d): ?>
            <li class="flex items-start gap-3 p-3 card-inner rounded-lg">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-subtle text-brand font-bold text-xs flex items-center justify-center border border-brand/30"><?= $i + 1 ?></span>
                <span class="text-sm leading-relaxed"><?= $d ?></span>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <!-- ===== DATABASE SCALE ===== -->
    <div class="card rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-xl text-white mb-4">Platform Scale</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card-inner rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-brand"><?= $totalTables ?></p>
                <p class="text-xs text-muted mt-1">Database Tables</p>
            </div>
            <div class="card-inner rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-brand"><?= $totalMigrations ?></p>
                <p class="text-xs text-muted mt-1">SQL Migrations</p>
            </div>
            <div class="card-inner rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-brand"><?= $totalEndpoints ?></p>
                <p class="text-xs text-muted mt-1">API Endpoints</p>
            </div>
            <div class="card-inner rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-brand"><?= $totalCronJobs ?></p>
                <p class="text-xs text-muted mt-1">Automated Cron Jobs</p>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <div class="text-center text-muted text-xs pb-8">
        <p>&copy; <?= date('Y') ?> Oregon Tires Auto Care &bull; oregon.tires</p>
        <p class="mt-1">Report generated <?= $reportDate ?> &bull; Powered by <a href="https://1vsM.com" class="text-brand hover:underline">1vsM.com</a></p>
    </div>

</div>

</body>
</html>
