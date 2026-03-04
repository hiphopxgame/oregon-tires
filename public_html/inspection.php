<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Inspection Report - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="assets/favicon.ico" sizes="any">
    <link rel="icon" href="assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
    <style>
    /* Score ring animation */
    @keyframes scoreRingFill {
        from { stroke-dashoffset: 408; }
    }
    .score-ring-circle {
        transition: stroke-dashoffset 1.2s ease-out;
    }
    /* Scorecard row staggered reveal */
    .scorecard-row {
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
    }
    .scorecard-row.revealed {
        opacity: 1;
        transform: translateY(0);
    }
    .scorecard-row:hover {
        background: rgba(0,0,0,0.02);
        cursor: pointer;
    }
    .dark .scorecard-row:hover {
        background: rgba(255,255,255,0.03);
    }
    /* SVG dark mode via CSS custom properties */
    .score-ring-bg { stroke: #e5e7eb; }
    .dark .score-ring-bg { stroke: #374151; }
    .score-ring-pct { fill: #6b7280; }
    .dark .score-ring-pct { fill: #9ca3af; }
    /* Details chevron rotation */
    details[open] > summary svg { transform: rotate(90deg); }
    /* Respect reduced motion preference */
    @media (prefers-reduced-motion: reduce) {
        .animate-pulse { animation: none !important; }
        .score-ring-circle { transition: none !important; }
        .scorecard-row { transition: none !important; }
    }
    /* Mobile: show full description */
    @media (max-width: 639px) {
        .scorecard-desc { white-space: normal; overflow: visible; text-overflow: unset; }
    }

    @page { margin: 0.75in; size: letter portrait; }
    @media print {
        html, body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dark { color-scheme: light !important; }

        header, footer, #lang-toggle, #photo-overlay, #estimate-cta, #print-report-btn { display: none !important; }

        .dark\:bg-\[#0A0A0A\], .dark\:bg-gray-900, .dark\:bg-gray-800\/50, .dark\:bg-\[#111827\]\/90 { background: white !important; }
        .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 { color: #111 !important; }
        .dark\:border-gray-800, .dark\:border-gray-700, .dark\:border-gray-700\/50 { border-color: #e5e7eb !important; }

        #report-state::before {
            content: "Oregon Tires Auto Care \2014  Digital Vehicle Inspection";
            display: block; text-align: center; font-size: 11pt; font-weight: bold; color: #333;
            border-bottom: 2px solid #16a34a; padding-bottom: 8pt; margin-bottom: 16pt;
        }

        body { min-height: auto !important; }
        main { padding: 0 !important; }
        .container { max-width: 100% !important; padding: 0 !important; }
        .shadow-sm, .shadow-lg { box-shadow: none !important; }

        /* Preserve traffic light colors */
        .bg-green-50, .bg-green-100 { background-color: #dcfce7 !important; }
        .bg-yellow-50, .bg-yellow-100 { background-color: #fef9c3 !important; }
        .bg-red-50, .bg-red-100 { background-color: #fee2e2 !important; }
        .bg-green-500 { background-color: #22c55e !important; }
        .bg-yellow-500 { background-color: #eab308 !important; }
        .bg-red-500 { background-color: #ef4444 !important; }
        .text-green-600, .text-green-700 { color: #16a34a !important; }
        .text-yellow-600, .text-yellow-700 { color: #ca8a04 !important; }
        .text-red-600, .text-red-700 { color: #dc2626 !important; }

        .dark\:bg-green-900\/20 { background-color: #dcfce7 !important; }
        .dark\:bg-yellow-900\/20 { background-color: #fef9c3 !important; }
        .dark\:bg-red-900\/20 { background-color: #fee2e2 !important; }
        .dark\:bg-green-900\/40 { background-color: #bbf7d0 !important; }
        .dark\:bg-yellow-900\/40 { background-color: #fde68a !important; }
        .dark\:bg-red-900\/40 { background-color: #fecaca !important; }
        .dark\:text-green-400 { color: #16a34a !important; }
        .dark\:text-yellow-400 { color: #ca8a04 !important; }
        .dark\:text-red-400 { color: #dc2626 !important; }

        /* Score ring print */
        #health-score-ring svg { width: 100px !important; height: 100px !important; }
        #health-score-ring { break-inside: avoid; }

        /* Scorecard print */
        #scorecard-table { break-inside: avoid; }
        .scorecard-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Priority sections print — expand green items */
        #priority-sections .priority-section { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }
        #priority-green-toggle { display: none !important; }
        #priority-green-items { display: block !important; }

        /* Detailed findings — auto expand for print */
        #detailed-findings[open] { display: block; }
        #detailed-findings summary { display: none !important; }
        #items-container { display: block !important; }

        #items-container img { width: 60px !important; height: 60px !important; break-inside: avoid; }
        #items-container > div { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }
        #overall-badge { border: 1px solid currentColor !important; }
    }
    </style>
</head>
<body class="bg-white dark:bg-[#0A0A0A] min-h-screen flex flex-col">

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#111827]/90 backdrop-blur border-b border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="assets/logo.webp" alt="Oregon Tires" class="h-10 w-10 rounded-lg" width="40" height="40">
            <span class="text-lg font-bold text-gray-900 dark:text-white">Oregon Tires</span>
        </a>
        <nav class="flex items-center gap-4">
            <a href="/" class="text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 text-sm font-medium" data-t="backToHome">Back to Home</a>
            <button onclick="toggleLanguage()" class="text-xs font-bold px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700" id="lang-toggle">EN | ES</button>
        </nav>
    </div>
</header>

<!-- Page Content -->
<main class="flex-1 py-8">
    <div class="container mx-auto px-4 max-w-2xl">

        <!-- Loading -->
        <div id="loading-state" class="text-center py-16">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-500 dark:text-gray-400" data-t="loading">Loading inspection report...</p>
        </div>

        <!-- Error -->
        <div id="error-state" class="hidden text-center py-16">
            <div class="w-16 h-16 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2" data-t="errorTitle">Invalid Link</h2>
            <p id="error-message" class="text-gray-500 dark:text-gray-400 mb-6"></p>
            <a href="/" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition" data-t="goHome">Go to Homepage</a>
        </div>

        <!-- Inspection Report -->
        <div id="report-state" class="hidden">

            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white" data-t="inspTitle">Vehicle Inspection Report</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            <span data-t="roLabel">RO:</span> <strong id="ro-number"></strong>
                        </p>
                    </div>
                    <div id="overall-badge" class="px-4 py-2 rounded-full text-sm font-bold"></div>
                </div>

                <!-- Vehicle Info -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 mb-4">
                    <p class="font-semibold text-gray-900 dark:text-white" id="vehicle-name"></p>
                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <span id="vehicle-vin" class="hidden"></span>
                        <span id="vehicle-color" class="hidden"></span>
                        <span id="vehicle-plate" class="hidden"></span>
                    </div>
                    <p id="inspection-date" class="hidden text-sm text-gray-500 dark:text-gray-400 mt-2">
                        <span data-t="inspDate">Inspection Date</span>: <strong id="inspection-date-value"></strong>
                    </p>
                </div>

                <!-- Summary Counts -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-green-600" id="green-count">0</div>
                        <div class="text-xs text-green-700 dark:text-green-400 font-medium" data-t="good">Good</div>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-yellow-600" id="yellow-count">0</div>
                        <div class="text-xs text-yellow-700 dark:text-yellow-400 font-medium" data-t="attention">Attention</div>
                    </div>
                    <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-red-600" id="red-count">0</div>
                        <div class="text-xs text-red-700 dark:text-red-400 font-medium" data-t="urgent">Urgent</div>
                    </div>
                </div>
            </div>

            <!-- Overall Health Score Ring -->
            <div id="health-score-ring" class="hidden bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm text-center"></div>

            <!-- Scorecard Table -->
            <div id="scorecard-table" class="hidden bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden mb-6 shadow-sm">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white" data-t="scorecard">Vehicle Scorecard</h2>
                </div>
                <div id="scorecard-body"></div>
            </div>

            <!-- Priority Sections -->
            <div id="priority-sections" class="space-y-4 mb-6"></div>

            <!-- Action Buttons (Print + Share) -->
            <div class="mb-6 flex justify-center gap-3 flex-wrap" id="print-report-btn">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span data-t="printReport">Print Report</span>
                </button>
                <button id="share-btn" onclick="shareReport()" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span data-t="shareReport">Share</span>
                </button>
            </div>

            <!-- Detailed Findings (existing category view, wrapped in details) -->
            <details id="detailed-findings" class="mb-6">
                <summary class="cursor-pointer font-bold text-gray-900 dark:text-white text-lg mb-4 flex items-center gap-2 select-none">
                    <svg class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span data-t="detailedFindings">Detailed Findings</span>
                </summary>
                <div id="items-container" class="space-y-4"></div>
            </details>

            <!-- Estimate CTA -->
            <div id="estimate-cta" class="hidden mb-6">
                <a id="estimate-link" href="#" class="block w-full text-center px-6 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition text-lg shadow-lg">
                    <span data-t="viewEstimate">Review & Approve Estimate</span>
                    <span id="estimate-urgency" class="hidden block text-sm font-normal mt-1 opacity-90"></span>
                </a>
            </div>

            <!-- Notes -->
            <div id="notes-section" class="hidden mt-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2" data-t="techNotes">Technician Notes</h3>
                    <p id="inspection-notes" class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed"></p>
                </div>
            </div>

            <!-- Shop Info -->
            <div class="mt-8 text-center text-sm text-gray-400 dark:text-gray-500">
                <p class="font-semibold text-gray-600 dark:text-gray-400">Oregon Tires Auto Care</p>
                <p>8536 SE 82nd Ave, Portland, OR 97266</p>
                <p><a href="tel:+15033679714" class="text-green-600 hover:underline">(503) 367-9714</a></p>
            </div>
        </div>
    </div>
</main>

<!-- Photo Overlay -->
<div id="photo-overlay" class="fixed inset-0 z-50 bg-black/80 hidden items-center justify-center p-4" onclick="closePhoto()">
    <button onclick="closePhoto()" class="fixed top-4 right-4 z-50 text-white text-4xl leading-none font-light w-12 h-12 flex items-center justify-center rounded-full bg-black/40 hover:bg-black/70 transition" aria-label="Close">&times;</button>
    <button id="photo-prev" onclick="event.stopPropagation(); navigatePhoto(-1)" class="fixed left-3 top-1/2 -translate-y-1/2 z-50 text-white text-3xl w-11 h-11 flex items-center justify-center rounded-full bg-black/40 hover:bg-black/70 transition" aria-label="Previous">&#8249;</button>
    <button id="photo-next" onclick="event.stopPropagation(); navigatePhoto(1)" class="fixed right-3 top-1/2 -translate-y-1/2 z-50 text-white text-3xl w-11 h-11 flex items-center justify-center rounded-full bg-black/40 hover:bg-black/70 transition" aria-label="Next">&#8250;</button>
    <div class="max-w-3xl w-full" onclick="event.stopPropagation()">
        <img id="photo-overlay-img" src="" class="w-full rounded-xl" alt="">
        <p id="photo-overlay-caption" class="text-white text-center mt-3 text-sm"></p>
        <p id="photo-overlay-counter" class="text-white/60 text-center mt-1 text-xs"></p>
    </div>
</div>

<!-- Footer -->
<footer class="bg-brand text-white py-8">
    <div class="container mx-auto px-4">
      <div class="grid sm:grid-cols-3 gap-6 text-sm text-gray-200 mb-6">
        <div>
          <p class="font-semibold text-white mb-1">Oregon Tires Auto Care</p>
          <p>8536 SE 82nd Ave, Portland, OR 97266</p>
        </div>
        <div>
          <p><a href="tel:5033679714" class="hover:text-amber-300">(503) 367-9714</a></p>
          <p><a href="mailto:oregontirespdx@gmail.com" class="hover:text-amber-300">oregontirespdx@gmail.com</a></p>
        </div>
        <div class="flex items-center gap-4 sm:justify-end">
          <a href="https://www.facebook.com/61571913202998/" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300">Facebook</a>
          <a href="https://www.instagram.com/oregontires" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300">Instagram</a>
        </div>
      </div>
      <div class="border-t border-green-600 pt-4 text-center text-xs text-gray-300">
        <p>&copy; 2026 Oregon Tires Auto Care. All rights reserved.</p>
        <p class="mt-1">Powered by <a href="https://1vsM.com" target="_blank" rel="noopener noreferrer" class="text-amber-200 hover:text-amber-100">1vsM.com</a></p>
      </div>
    </div>
</footer>

<script>
let currentLang = 'en';
const t = {
    en: {
        backToHome: 'Back to Home',
        loading: 'Loading inspection report...',
        errorTitle: 'Invalid Link',
        goHome: 'Go to Homepage',
        inspTitle: 'Vehicle Inspection Report',
        roLabel: 'RO:',
        good: 'Good',
        attention: 'Attention Soon',
        urgent: 'Urgent / Safety',
        viewEstimate: 'Review & Approve Estimate',
        techNotes: 'Technician Notes',
        inspDate: 'Inspection Date',
        printReport: 'Print Report',
        overallHealth: 'Overall Vehicle Health',
        scorecard: 'Vehicle Scorecard',
        needsAttentionNow: 'Needs Attention Now',
        watchList: 'Watch List',
        lookingGreat: 'Looking Great',
        detailedFindings: 'Detailed Findings',
        showItems: 'Show items',
        hideItems: 'Hide items',
        shareReport: 'Share',
        shareCopied: 'Link copied!',
        safetyItemsWarning: ' safety item(s) need attention',
        scheduleRepair: 'Schedule a Repair',
        photoOf: 'of',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando reporte de inspección...',
        errorTitle: 'Enlace Inválido',
        goHome: 'Ir al Inicio',
        inspTitle: 'Reporte de Inspección del Vehículo',
        inspDate: 'Fecha de Inspección',
        roLabel: 'OT:',
        good: 'Bueno',
        attention: 'Atención Pronto',
        urgent: 'Urgente / Seguridad',
        viewEstimate: 'Revisar y Aprobar Presupuesto',
        techNotes: 'Notas del Técnico',
        printReport: 'Imprimir Reporte',
        overallHealth: 'Salud General del Vehículo',
        scorecard: 'Tarjeta de Calificación',
        needsAttentionNow: 'Necesita Atención Ahora',
        watchList: 'Lista de Seguimiento',
        lookingGreat: 'En Buen Estado',
        detailedFindings: 'Hallazgos Detallados',
        showItems: 'Mostrar elementos',
        hideItems: 'Ocultar elementos',
        shareReport: 'Compartir',
        shareCopied: 'Enlace copiado!',
        safetyItemsWarning: ' elemento(s) de seguridad necesitan atención',
        scheduleRepair: 'Agendar una Reparación',
        photoOf: 'de',
    }
};

const overallLabels = { en: { green: 'GOOD', yellow: 'ATTENTION', red: 'URGENT' }, es: { green: 'BUENO', yellow: 'ATENCIÓN', red: 'URGENTE' } };

// Friendly language descriptions: category x tier x lang (12 categories x 5 tiers x 2 langs)
var gradeDescriptions = {
    en: {
        tires: {
            excellent: 'Your tires are in excellent condition with plenty of tread life remaining.',
            good: 'Tires are in good shape. Keep monitoring tread depth at your next service.',
            fair: 'Some tire wear detected. Consider replacement planning in the near future.',
            poor: 'Significant tire wear found. Replacement recommended soon for safety.',
            critical: 'Tires need immediate replacement. This is a safety concern.'
        },
        brakes: {
            excellent: 'Brake system is performing at peak condition.',
            good: 'Brakes are in good working order with adequate pad life.',
            fair: 'Brake wear is progressing. Plan for service within the next few months.',
            poor: 'Brake pads are wearing thin. Service recommended before they become unsafe.',
            critical: 'Brake system needs immediate service. This is a safety concern.'
        },
        suspension: {
            excellent: 'Suspension components are all in excellent condition.',
            good: 'Suspension is performing well with no immediate concerns.',
            fair: 'Some suspension wear detected. Monitor for changes in ride quality.',
            poor: 'Suspension issues found that affect ride quality and handling.',
            critical: 'Suspension problems need immediate attention for safe driving.'
        },
        fluids: {
            excellent: 'All fluid levels and conditions are excellent.',
            good: 'Fluids are at proper levels and in good condition.',
            fair: 'Some fluids may need attention at your next service.',
            poor: 'Fluid levels or conditions need attention soon.',
            critical: 'Critical fluid issues found. Service needed immediately.'
        },
        lights: {
            excellent: 'All lights are functioning perfectly.',
            good: 'Lighting system is in good working order.',
            fair: 'Some lights may need attention soon.',
            poor: 'Multiple lighting issues found. Repair recommended.',
            critical: 'Critical lighting failures. Immediate repair needed for safety.'
        },
        engine: {
            excellent: 'Engine is running smoothly with no concerns.',
            good: 'Engine performance is good with minor items to watch.',
            fair: 'Engine has items that should be addressed at next service.',
            poor: 'Engine issues detected that need attention soon.',
            critical: 'Engine problems need immediate diagnosis and repair.'
        },
        exhaust: {
            excellent: 'Exhaust system is in excellent condition.',
            good: 'Exhaust system is functioning properly.',
            fair: 'Minor exhaust wear detected. Monitor at next visit.',
            poor: 'Exhaust issues found that need repair.',
            critical: 'Exhaust system failure. Immediate repair recommended.'
        },
        hoses: {
            excellent: 'All hoses are in excellent condition.',
            good: 'Hoses are in good shape with no visible wear.',
            fair: 'Some hose wear detected. Plan for replacement.',
            poor: 'Hoses showing significant wear. Replace soon to prevent leaks.',
            critical: 'Hose failure risk is high. Immediate replacement needed.'
        },
        belts: {
            excellent: 'All belts are in excellent condition.',
            good: 'Belts are in good condition with normal wear.',
            fair: 'Belt wear is progressing. Plan for replacement.',
            poor: 'Belts are worn and should be replaced soon.',
            critical: 'Belt failure is imminent. Replace immediately.'
        },
        battery: {
            excellent: 'Battery is in excellent condition with strong charge.',
            good: 'Battery is performing well.',
            fair: 'Battery showing some age. Consider testing at next service.',
            poor: 'Battery is weak. Replacement recommended to avoid breakdowns.',
            critical: 'Battery is failing. Replace immediately to prevent being stranded.'
        },
        wipers: {
            excellent: 'Wiper blades are in excellent condition.',
            good: 'Wipers are working properly.',
            fair: 'Wiper blades showing some wear. Replace before rainy season.',
            poor: 'Wiper blades are worn. Replace for clear visibility.',
            critical: 'Wipers are not clearing properly. Replace for safe driving.'
        },
        other: {
            excellent: 'All other inspected items are in excellent condition.',
            good: 'Other components are in good working order.',
            fair: 'Some items need attention at your next visit.',
            poor: 'Several items need attention soon.',
            critical: 'Critical issues found that need immediate attention.'
        }
    },
    es: {
        tires: {
            excellent: 'Sus neumáticos están en excelente condición con suficiente vida de rodadura.',
            good: 'Los neumáticos están en buen estado. Siga monitoreando la profundidad del dibujo.',
            fair: 'Se detectó algún desgaste en los neumáticos. Considere un reemplazo próximo.',
            poor: 'Desgaste significativo encontrado. Se recomienda reemplazo pronto por seguridad.',
            critical: 'Los neumáticos necesitan reemplazo inmediato. Es un tema de seguridad.'
        },
        brakes: {
            excellent: 'El sistema de frenos está funcionando en condición óptima.',
            good: 'Los frenos están en buen estado con vida útil adecuada.',
            fair: 'El desgaste de frenos está avanzando. Planifique servicio en los próximos meses.',
            poor: 'Las pastillas de freno se están desgastando. Servicio recomendado pronto.',
            critical: 'El sistema de frenos necesita servicio inmediato. Es un tema de seguridad.'
        },
        suspension: {
            excellent: 'Todos los componentes de suspensión están en excelente condición.',
            good: 'La suspensión funciona bien sin preocupaciones inmediatas.',
            fair: 'Se detectó algún desgaste en la suspensión. Monitoree cambios en la conducción.',
            poor: 'Problemas de suspensión encontrados que afectan la calidad de manejo.',
            critical: 'Problemas de suspensión necesitan atención inmediata para conducir seguro.'
        },
        fluids: {
            excellent: 'Todos los niveles y condiciones de fluidos son excelentes.',
            good: 'Los fluidos están en niveles adecuados y en buena condición.',
            fair: 'Algunos fluidos pueden necesitar atención en su próximo servicio.',
            poor: 'Los niveles o condiciones de fluidos necesitan atención pronto.',
            critical: 'Problemas críticos de fluidos encontrados. Servicio necesario de inmediato.'
        },
        lights: {
            excellent: 'Todas las luces funcionan perfectamente.',
            good: 'El sistema de iluminación está en buen estado.',
            fair: 'Algunas luces pueden necesitar atención pronto.',
            poor: 'Múltiples problemas de iluminación encontrados. Reparación recomendada.',
            critical: 'Fallas críticas de iluminación. Reparación inmediata necesaria por seguridad.'
        },
        engine: {
            excellent: 'El motor funciona suavemente sin preocupaciones.',
            good: 'El rendimiento del motor es bueno con elementos menores a vigilar.',
            fair: 'El motor tiene elementos que deben atenderse en el próximo servicio.',
            poor: 'Problemas del motor detectados que necesitan atención pronto.',
            critical: 'Problemas del motor necesitan diagnóstico y reparación inmediata.'
        },
        exhaust: {
            excellent: 'El sistema de escape está en excelente condición.',
            good: 'El sistema de escape funciona correctamente.',
            fair: 'Desgaste menor del escape detectado. Monitorear en la próxima visita.',
            poor: 'Problemas de escape encontrados que necesitan reparación.',
            critical: 'Falla del sistema de escape. Reparación inmediata recomendada.'
        },
        hoses: {
            excellent: 'Todas las mangueras están en excelente condición.',
            good: 'Las mangueras están en buen estado sin desgaste visible.',
            fair: 'Se detectó algún desgaste en mangueras. Planifique reemplazo.',
            poor: 'Mangueras con desgaste significativo. Reemplace pronto para evitar fugas.',
            critical: 'Riesgo alto de falla de mangueras. Reemplazo inmediato necesario.'
        },
        belts: {
            excellent: 'Todas las correas están en excelente condición.',
            good: 'Las correas están en buena condición con desgaste normal.',
            fair: 'El desgaste de correas está avanzando. Planifique reemplazo.',
            poor: 'Las correas están desgastadas y deben reemplazarse pronto.',
            critical: 'Falla de correa es inminente. Reemplace inmediatamente.'
        },
        battery: {
            excellent: 'La batería está en excelente condición con carga fuerte.',
            good: 'La batería funciona bien.',
            fair: 'La batería muestra algo de edad. Considere pruebas en el próximo servicio.',
            poor: 'La batería está débil. Reemplazo recomendado para evitar fallas.',
            critical: 'La batería está fallando. Reemplace inmediatamente.'
        },
        wipers: {
            excellent: 'Las plumas limpiaparabrisas están en excelente condición.',
            good: 'Los limpiaparabrisas funcionan correctamente.',
            fair: 'Las plumas muestran algo de desgaste. Reemplace antes de la temporada de lluvias.',
            poor: 'Las plumas están desgastadas. Reemplace para visibilidad clara.',
            critical: 'Los limpiaparabrisas no limpian correctamente. Reemplace para conducir seguro.'
        },
        other: {
            excellent: 'Todos los demás elementos inspeccionados están en excelente condición.',
            good: 'Otros componentes están en buen estado.',
            fair: 'Algunos elementos necesitan atención en su próxima visita.',
            poor: 'Varios elementos necesitan atención pronto.',
            critical: 'Problemas críticos encontrados que necesitan atención inmediata.'
        }
    }
};

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    applyTranslations();
    if (window._inspectionData) renderReportCard(window._inspectionData);
}

function applyTranslations() {
    document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[currentLang] && t[currentLang][key]) {
            el.textContent = t[currentLang][key];
        }
    });
}

var categoryLabels = {
    en: {tires:'Tires',brakes:'Brakes',suspension:'Suspension',fluids:'Fluids',lights:'Lights',engine:'Engine',exhaust:'Exhaust',hoses:'Hoses',belts:'Belts',battery:'Battery',wipers:'Wipers',other:'Other'},
    es: {tires:'Neumáticos',brakes:'Frenos',suspension:'Suspensión',fluids:'Fluidos',lights:'Luces',engine:'Motor',exhaust:'Escape',hoses:'Mangueras',belts:'Correas',battery:'Batería',wipers:'Limpiaparabrisas',other:'Otro'}
};

var ratingStyles = {
    green:  { bg: 'bg-green-50 dark:bg-green-900/20', border: 'border-green-200 dark:border-green-800', dot: 'bg-green-500', text: 'text-green-700 dark:text-green-400' },
    yellow: { bg: 'bg-yellow-50 dark:bg-yellow-900/20', border: 'border-yellow-200 dark:border-yellow-800', dot: 'bg-yellow-500', text: 'text-yellow-700 dark:text-yellow-400' },
    red:    { bg: 'bg-red-50 dark:bg-red-900/20', border: 'border-red-200 dark:border-red-800', dot: 'bg-red-500', text: 'text-red-700 dark:text-red-400' },
};

// ─── Scoring Algorithm ────────────────────────────────────────────────────
var categoryWeights = {
    tires: 15, brakes: 15, suspension: 10, lights: 10,
    fluids: 8, engine: 8, exhaust: 6, hoses: 6,
    belts: 6, battery: 6, wipers: 5, other: 5
};

function ratingToPoints(rating) {
    if (rating === 'green') return 100;
    if (rating === 'yellow') return 50;
    return 0;
}

function computeScores(items) {
    var grouped = {};
    items.forEach(function(item) {
        if (!grouped[item.category]) grouped[item.category] = [];
        grouped[item.category].push(item);
    });

    var catScores = {};
    var totalWeight = 0;
    var weightedSum = 0;

    Object.keys(grouped).forEach(function(cat) {
        var catItems = grouped[cat];
        var sum = 0;
        catItems.forEach(function(i) { sum += ratingToPoints(i.condition_rating); });
        var avg = sum / catItems.length;
        catScores[cat] = { score: Math.round(avg), items: catItems };

        var weight = categoryWeights[cat] || 5;
        totalWeight += weight;
        weightedSum += avg * weight;
    });

    var overall = totalWeight > 0 ? Math.round(weightedSum / totalWeight) : 100;
    return { overall: overall, categories: catScores };
}

function scoreToGrade(score) {
    if (score >= 97) return 'A+';
    if (score >= 93) return 'A';
    if (score >= 90) return 'A-';
    if (score >= 87) return 'B+';
    if (score >= 83) return 'B';
    if (score >= 80) return 'B-';
    if (score >= 70) return 'C';
    if (score >= 60) return 'D';
    return 'F';
}

function scoreToTier(score) {
    if (score >= 90) return 'excellent';
    if (score >= 75) return 'good';
    if (score >= 50) return 'fair';
    if (score >= 25) return 'poor';
    return 'critical';
}

function tierColor(tier) {
    if (tier === 'excellent' || tier === 'good') return { stroke: '#22c55e', text: 'text-green-600', bg: 'bg-green-100 dark:bg-green-900/30', barBg: 'bg-green-500' };
    if (tier === 'fair') return { stroke: '#eab308', text: 'text-yellow-600', bg: 'bg-yellow-100 dark:bg-yellow-900/30', barBg: 'bg-yellow-500' };
    if (tier === 'poor') return { stroke: '#f97316', text: 'text-orange-600', bg: 'bg-orange-100 dark:bg-orange-900/30', barBg: 'bg-orange-500' };
    return { stroke: '#ef4444', text: 'text-red-600', bg: 'bg-red-100 dark:bg-red-900/30', barBg: 'bg-red-500' };
}

// ─── SVG Icon Builders (safe DOM, no innerHTML) ───────────────────────────
function createSvgIcon(pathData, className) {
    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('class', className || 'w-5 h-5');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('viewBox', '0 0 24 24');
    pathData.forEach(function(d) {
        var path = document.createElementNS(svgNS, 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('stroke-width', '2');
        path.setAttribute('d', d);
        svg.appendChild(path);
    });
    return svg;
}

var priorityIcons = {
    red: ['M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'],
    yellow: ['M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
    green: ['M5 13l4 4L19 7']
};

// ─── UI Builders ──────────────────────────────────────────────────────────
function clearElement(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
}

function buildHealthScoreRing(score) {
    var grade = scoreToGrade(score);
    var tier = scoreToTier(score);
    var colors = tierColor(tier);
    var circumference = 408;
    var offset = circumference - (score / 100) * circumference;

    var tierLabels = {
        en: { excellent: 'Excellent', good: 'Good', fair: 'Fair', poor: 'Poor', critical: 'Critical' },
        es: { excellent: 'Excelente', good: 'Bueno', fair: 'Regular', poor: 'Deficiente', critical: 'Crítico' }
    };

    var container = document.getElementById('health-score-ring');
    clearElement(container);

    var title = document.createElement('h2');
    title.className = 'font-bold text-gray-900 dark:text-white text-lg mb-4';
    title.setAttribute('data-t', 'overallHealth');
    title.textContent = t[currentLang].overallHealth;
    container.appendChild(title);

    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('width', '160');
    svg.setAttribute('height', '160');
    svg.setAttribute('viewBox', '0 0 160 160');
    svg.className.baseVal = 'mx-auto';

    var bgCircle = document.createElementNS(svgNS, 'circle');
    bgCircle.setAttribute('cx', '80');
    bgCircle.setAttribute('cy', '80');
    bgCircle.setAttribute('r', '65');
    bgCircle.setAttribute('fill', 'none');
    bgCircle.setAttribute('stroke-width', '12');
    bgCircle.classList.add('score-ring-bg');
    svg.appendChild(bgCircle);

    var scoreCircle = document.createElementNS(svgNS, 'circle');
    scoreCircle.setAttribute('cx', '80');
    scoreCircle.setAttribute('cy', '80');
    scoreCircle.setAttribute('r', '65');
    scoreCircle.setAttribute('fill', 'none');
    scoreCircle.setAttribute('stroke', colors.stroke);
    scoreCircle.setAttribute('stroke-width', '12');
    scoreCircle.setAttribute('stroke-linecap', 'round');
    scoreCircle.setAttribute('stroke-dasharray', String(circumference));
    scoreCircle.setAttribute('stroke-dashoffset', String(circumference));
    scoreCircle.setAttribute('transform', 'rotate(-90 80 80)');
    scoreCircle.classList.add('score-ring-circle');
    svg.appendChild(scoreCircle);

    var gradeText = document.createElementNS(svgNS, 'text');
    gradeText.setAttribute('x', '80');
    gradeText.setAttribute('y', '72');
    gradeText.setAttribute('text-anchor', 'middle');
    gradeText.setAttribute('font-size', '32');
    gradeText.setAttribute('font-weight', 'bold');
    gradeText.setAttribute('fill', colors.stroke);
    gradeText.textContent = grade;
    svg.appendChild(gradeText);

    var pctText = document.createElementNS(svgNS, 'text');
    pctText.setAttribute('x', '80');
    pctText.setAttribute('y', '98');
    pctText.setAttribute('text-anchor', 'middle');
    pctText.setAttribute('font-size', '14');
    pctText.classList.add('score-ring-pct');
    pctText.textContent = score + '%';
    svg.appendChild(pctText);

    container.appendChild(svg);

    var tierLabel = document.createElement('p');
    tierLabel.className = 'mt-3 text-sm font-semibold ' + colors.text;
    tierLabel.textContent = (tierLabels[currentLang] || tierLabels.en)[tier];
    container.appendChild(tierLabel);

    container.classList.remove('hidden');

    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            scoreCircle.setAttribute('stroke-dashoffset', String(offset));
        });
    });
}

function buildScorecardTable(catScores) {
    var body = document.getElementById('scorecard-body');
    clearElement(body);

    var cats = Object.keys(catScores).sort(function(a, b) {
        return catScores[a].score - catScores[b].score;
    });

    cats.forEach(function(cat) {
        var data = catScores[cat];
        var grade = scoreToGrade(data.score);
        var tier = scoreToTier(data.score);
        var colors = tierColor(tier);
        var catLabel = (categoryLabels[currentLang] || categoryLabels.en)[cat] || cat;
        var desc = ((gradeDescriptions[currentLang] || gradeDescriptions.en)[cat] || {})[tier] || '';

        var row = document.createElement('div');
        row.className = 'px-5 py-3 flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 last:border-b-0 scorecard-row';
        row.dataset.category = cat;
        row.setAttribute('role', 'button');
        row.setAttribute('tabindex', '0');
        row.setAttribute('aria-label', catLabel + ' — ' + grade + ' (' + data.score + '%)');

        // Click-to-scroll: find matching priority section item
        row.addEventListener('click', function() { scrollToCategory(cat); });
        row.addEventListener('keydown', function(e) { if (e.key === 'Enter') scrollToCategory(cat); });

        var gradeBadge = document.createElement('span');
        gradeBadge.className = 'w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm ' + colors.bg + ' ' + colors.text;
        gradeBadge.textContent = grade;
        row.appendChild(gradeBadge);

        var middle = document.createElement('div');
        middle.className = 'flex-1 min-w-0';

        var nameEl = document.createElement('p');
        nameEl.className = 'font-semibold text-gray-900 dark:text-white text-sm';
        nameEl.textContent = catLabel;
        middle.appendChild(nameEl);

        var descEl = document.createElement('p');
        descEl.className = 'text-xs text-gray-500 dark:text-gray-400 truncate scorecard-desc';
        descEl.textContent = desc;
        descEl.title = desc;
        middle.appendChild(descEl);

        row.appendChild(middle);

        var rightSide = document.createElement('div');
        rightSide.className = 'flex items-center gap-2 flex-shrink-0';

        var barWrap = document.createElement('div');
        barWrap.className = 'w-16 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden';
        var barFill = document.createElement('div');
        barFill.className = 'h-full rounded-full scorecard-bar ' + colors.barBg;
        barFill.style.width = data.score + '%';
        barWrap.appendChild(barFill);
        rightSide.appendChild(barWrap);

        var pctEl = document.createElement('span');
        pctEl.className = 'text-xs font-mono w-8 text-right ' + colors.text;
        pctEl.textContent = data.score + '%';
        rightSide.appendChild(pctEl);

        row.appendChild(rightSide);
        body.appendChild(row);
    });

    document.getElementById('scorecard-table').classList.remove('hidden');

    // Staggered reveal animation
    var rows = body.querySelectorAll('.scorecard-row');
    rows.forEach(function(row, i) {
        setTimeout(function() { row.classList.add('revealed'); }, 80 * (i + 1));
    });
}

function scrollToCategory(cat) {
    // Find matching items in priority sections
    var prioritySections = document.getElementById('priority-sections');
    var allItems = prioritySections.querySelectorAll('[data-category="' + cat + '"]');

    if (allItems.length > 0) {
        // Expand green section if target is there
        var greenItems = document.getElementById('priority-green-items');
        if (greenItems && greenItems.style.display === 'none') {
            var parent = allItems[0].closest('#priority-green-items');
            if (parent) {
                greenItems.style.display = '';
                var toggleBtn = document.getElementById('priority-green-toggle');
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    toggleBtn.textContent = t[currentLang].hideItems + ' (' + greenItems.children.length + ')';
                }
            }
        }
        allItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        allItems[0].style.outline = '2px solid #22c55e';
        allItems[0].style.outlineOffset = '2px';
        allItems[0].style.borderRadius = '8px';
        setTimeout(function() {
            allItems[0].style.outline = '';
            allItems[0].style.outlineOffset = '';
        }, 2000);
        return;
    }

    // Fallback: scroll to detailed findings
    var details = document.getElementById('detailed-findings');
    if (details) {
        details.open = true;
        details.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function buildPrioritySections(items) {
    var container = document.getElementById('priority-sections');
    clearElement(container);

    var redItems = items.filter(function(i) { return i.condition_rating === 'red'; });
    var yellowItems = items.filter(function(i) { return i.condition_rating === 'yellow'; });
    var greenItems = items.filter(function(i) { return i.condition_rating === 'green'; });

    var sections = [
        {
            items: redItems, key: 'needsAttentionNow',
            bg: 'bg-red-50 dark:bg-red-900/20', border: 'border-red-200 dark:border-red-800',
            headerBg: 'bg-red-100 dark:bg-red-900/40', headerText: 'text-red-800 dark:text-red-300',
            iconKey: 'red', collapsed: false, id: 'priority-red'
        },
        {
            items: yellowItems, key: 'watchList',
            bg: 'bg-yellow-50 dark:bg-yellow-900/20', border: 'border-yellow-200 dark:border-yellow-800',
            headerBg: 'bg-yellow-100 dark:bg-yellow-900/40', headerText: 'text-yellow-800 dark:text-yellow-300',
            iconKey: 'yellow', collapsed: false, id: 'priority-yellow'
        },
        {
            items: greenItems, key: 'lookingGreat',
            bg: 'bg-green-50 dark:bg-green-900/20', border: 'border-green-200 dark:border-green-800',
            headerBg: 'bg-green-100 dark:bg-green-900/40', headerText: 'text-green-800 dark:text-green-300',
            iconKey: 'green', collapsed: true, id: 'priority-green'
        }
    ];

    sections.forEach(function(sec) {
        if (sec.items.length === 0) return;

        var section = document.createElement('div');
        section.className = sec.bg + ' border ' + sec.border + ' rounded-2xl overflow-hidden priority-section';
        section.id = sec.id + '-section';
        section.setAttribute('role', 'region');
        section.setAttribute('aria-label', t[currentLang][sec.key]);

        var header = document.createElement('div');
        header.className = 'px-5 py-3 flex items-center justify-between ' + sec.headerBg;

        var headerLeft = document.createElement('div');
        headerLeft.className = 'flex items-center gap-2 ' + sec.headerText;
        headerLeft.appendChild(createSvgIcon(priorityIcons[sec.iconKey], 'w-5 h-5'));
        var headerLabel = document.createElement('span');
        headerLabel.className = 'font-bold text-sm';
        headerLabel.setAttribute('data-t', sec.key);
        headerLabel.textContent = t[currentLang][sec.key];
        headerLeft.appendChild(headerLabel);
        header.appendChild(headerLeft);

        var countBadge = document.createElement('span');
        countBadge.className = 'text-xs font-bold px-2 py-0.5 rounded-full ' + sec.headerText + ' bg-white/50 dark:bg-black/20';
        countBadge.textContent = sec.items.length;
        header.appendChild(countBadge);

        section.appendChild(header);

        var itemsList = document.createElement('div');
        itemsList.className = 'divide-y divide-gray-200 dark:divide-gray-700/50';
        itemsList.id = sec.id + '-items';

        sec.items.forEach(function(item) {
            itemsList.appendChild(buildItemRow(item));
        });

        if (sec.collapsed) {
            itemsList.style.display = 'none';

            var toggleBtn = document.createElement('button');
            toggleBtn.className = 'w-full px-5 py-2 text-xs font-medium ' + sec.headerText + ' hover:bg-white/30 dark:hover:bg-black/10 transition';
            toggleBtn.id = sec.id + '-toggle';
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.setAttribute('aria-controls', sec.id + '-items');
            toggleBtn.textContent = t[currentLang].showItems + ' (' + sec.items.length + ')';
            toggleBtn.addEventListener('click', function() {
                var isHidden = itemsList.style.display === 'none';
                itemsList.style.display = isHidden ? '' : 'none';
                toggleBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                toggleBtn.textContent = (isHidden ? t[currentLang].hideItems : t[currentLang].showItems) + ' (' + sec.items.length + ')';
            });
            section.appendChild(toggleBtn);
        }

        section.appendChild(itemsList);
        container.appendChild(section);
    });
}

function renderReportCard(data) {
    var items = data.items || [];
    if (items.length === 0) return;

    var scores = computeScores(items);

    // Update overall badge to show letter grade
    var badge = document.getElementById('overall-badge');
    badge.textContent = scoreToGrade(scores.overall);

    buildHealthScoreRing(scores.overall);
    buildScorecardTable(scores.categories);
    buildPrioritySections(items);

    // Estimate CTA urgency
    var redCount = items.filter(function(i) { return i.condition_rating === 'red'; }).length;
    if (redCount > 0 && data.estimate_token) {
        var urgencyEl = document.getElementById('estimate-urgency');
        if (urgencyEl) {
            urgencyEl.textContent = redCount + (t[currentLang].safetyItemsWarning || ' safety item(s) need attention');
            urgencyEl.classList.remove('hidden');
            // Make CTA red when safety items exist
            var ctaLink = document.getElementById('estimate-link');
            if (ctaLink) {
                ctaLink.className = 'block w-full text-center px-6 py-4 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 transition text-lg shadow-lg animate-pulse';
            }
        }
    }
}

function shareReport() {
    var url = window.location.href;
    var title = t[currentLang].inspTitle || 'Vehicle Inspection Report';

    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function() {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            var btn = document.getElementById('share-btn');
            var origText = btn.querySelector('span').textContent;
            btn.querySelector('span').textContent = t[currentLang].shareCopied || 'Link copied!';
            setTimeout(function() { btn.querySelector('span').textContent = origText; }, 2000);
        }).catch(function() {});
    }
}

var allPhotos = [];
var currentPhotoIndex = 0;
var touchStartX = 0;

function collectAllPhotos(data) {
    allPhotos = [];
    (data.items || []).forEach(function(item) {
        if (item.photos && item.photos.length > 0) {
            item.photos.forEach(function(p) {
                allPhotos.push({ url: p.image_url, caption: p.caption || '' });
            });
        }
    });
}

function showPhoto(url, caption) {
    // Find index in allPhotos
    currentPhotoIndex = allPhotos.findIndex(function(p) { return p.url === url; });
    if (currentPhotoIndex === -1) currentPhotoIndex = 0;
    renderOverlayPhoto();
    var overlay = document.getElementById('photo-overlay');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}

function renderOverlayPhoto() {
    var photo = allPhotos[currentPhotoIndex];
    if (!photo) return;
    document.getElementById('photo-overlay-img').src = photo.url;
    document.getElementById('photo-overlay-caption').textContent = photo.caption;
    document.getElementById('photo-overlay-counter').textContent = (currentPhotoIndex + 1) + ' ' + (t[currentLang].photoOf || 'of') + ' ' + allPhotos.length;
    // Show/hide arrows
    document.getElementById('photo-prev').style.display = allPhotos.length > 1 ? '' : 'none';
    document.getElementById('photo-next').style.display = allPhotos.length > 1 ? '' : 'none';
}

function navigatePhoto(dir) {
    currentPhotoIndex = (currentPhotoIndex + dir + allPhotos.length) % allPhotos.length;
    renderOverlayPhoto();
}

function closePhoto() {
    var overlay = document.getElementById('photo-overlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    var overlay = document.getElementById('photo-overlay');
    if (overlay.classList.contains('hidden')) return;
    if (e.key === 'ArrowLeft') { navigatePhoto(-1); e.preventDefault(); }
    else if (e.key === 'ArrowRight') { navigatePhoto(1); e.preventDefault(); }
    else if (e.key === 'Escape') { closePhoto(); e.preventDefault(); }
});

// Touch swipe
document.getElementById('photo-overlay').addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
}, { passive: true });
document.getElementById('photo-overlay').addEventListener('touchend', function(e) {
    var delta = e.changedTouches[0].screenX - touchStartX;
    if (Math.abs(delta) > 50) {
        navigatePhoto(delta < 0 ? 1 : -1);
        e.preventDefault();
    }
});

function createTextEl(tag, text, className) {
    var el = document.createElement(tag);
    el.textContent = text;
    if (className) el.className = className;
    return el;
}

function buildItemRow(item) {
    var ic = ratingStyles[item.condition_rating] || ratingStyles.green;
    var row = document.createElement('div');
    row.className = 'px-5 py-3';
    if (item.category) row.dataset.category = item.category;

    var topRow = document.createElement('div');
    topRow.className = 'flex items-center justify-between';

    var leftSide = document.createElement('div');
    leftSide.className = 'flex items-center gap-3';

    var dot = document.createElement('span');
    dot.className = 'w-2.5 h-2.5 rounded-full ' + ic.dot + ' flex-shrink-0';
    leftSide.appendChild(dot);

    leftSide.appendChild(createTextEl('span', item.label, 'font-medium text-gray-900 dark:text-white text-sm'));

    if (item.position) {
        leftSide.appendChild(createTextEl('span', '(' + item.position + ')', 'text-xs text-gray-400'));
    }
    topRow.appendChild(leftSide);

    if (item.measurement) {
        topRow.appendChild(createTextEl('span', item.measurement, 'text-xs font-mono ' + ic.text));
    }
    row.appendChild(topRow);

    if (item.notes) {
        row.appendChild(createTextEl('p', item.notes, 'text-sm text-gray-500 dark:text-gray-400 mt-1 ml-6'));
    }

    if (item.photos && item.photos.length > 0) {
        var photoWrap = document.createElement('div');
        photoWrap.className = 'flex gap-2 mt-2 ml-6 overflow-x-auto scroll-snap-x';
        photoWrap.style.scrollSnapType = 'x mandatory';
        item.photos.forEach(function(p) {
            var img = document.createElement('img');
            img.src = p.image_url;
            img.alt = p.caption || 'Inspection photo';
            img.loading = 'lazy';
            img.decoding = 'async';
            img.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer flex-shrink-0';
            img.style.scrollSnapAlign = 'start';
            img.onerror = function() {
                this.onerror = null;
                this.src = '';
                this.alt = 'Photo unavailable';
                this.className = 'w-20 h-20 rounded-lg border border-gray-200 dark:border-gray-700 flex-shrink-0 bg-gray-200 dark:bg-gray-700';
                this.style.display = 'flex';
                this.removeAttribute('cursor-pointer');
            };
            img.addEventListener('click', function(e) { e.stopPropagation(); showPhoto(p.image_url, p.caption || ''); });
            photoWrap.appendChild(img);
        });
        row.appendChild(photoWrap);
    }

    return row;
}

async function loadInspection() {
    var params = new URLSearchParams(window.location.search);
    var token = params.get('token');
    if (!token) return showError('No inspection token provided.');

    try {
        var res = await fetch('/api/inspection-view.php?token=' + encodeURIComponent(token));
        var json = await res.json();

        if (!json.success) return showError(json.error || 'Inspection not found.');

        var data = json.data;
        window._inspectionData = data;

        if (data.customer_language === 'spanish') {
            currentLang = 'es';
            applyTranslations();
        }

        document.getElementById('ro-number').textContent = data.ro_number;
        document.getElementById('vehicle-name').textContent = data.vehicle || 'Vehicle';

        if (data.vin) { var el = document.getElementById('vehicle-vin'); el.textContent = 'VIN: ' + data.vin; el.classList.remove('hidden'); }
        if (data.vehicle_color) { var el2 = document.getElementById('vehicle-color'); el2.textContent = data.vehicle_color; el2.classList.remove('hidden'); }
        if (data.license_plate) { var el3 = document.getElementById('vehicle-plate'); el3.textContent = data.license_plate; el3.classList.remove('hidden'); }

        if (data.created_at) {
            var dateObj = new Date(data.created_at);
            var opts = currentLang === 'es'
                ? { year: 'numeric', month: 'long', day: 'numeric' }
                : { year: 'numeric', month: 'long', day: 'numeric' };
            var locale = currentLang === 'es' ? 'es-MX' : 'en-US';
            document.getElementById('inspection-date-value').textContent = dateObj.toLocaleDateString(locale, opts);
            document.getElementById('inspection-date').classList.remove('hidden');
        }

        // Overall badge (letter grade replaces text after scoring)
        var badge = document.getElementById('overall-badge');
        var oc = data.overall_condition || 'green';
        badge.textContent = (overallLabels[currentLang] || overallLabels.en)[oc] || oc.toUpperCase();
        var badgeColors = oc === 'red' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' :
             oc === 'yellow' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400' :
             'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
        badge.className = 'px-4 py-2 rounded-full text-sm font-bold ' + badgeColors;

        document.getElementById('green-count').textContent = data.green_count;
        document.getElementById('yellow-count').textContent = data.yellow_count;
        document.getElementById('red-count').textContent = data.red_count;

        // Collect all photos for overlay navigation
        collectAllPhotos(data);

        // Build report card (score ring, scorecard, priority sections)
        renderReportCard(data);

        // Group items by category for detailed findings
        var grouped = {};
        (data.items || []).forEach(function(item) {
            if (!grouped[item.category]) grouped[item.category] = [];
            grouped[item.category].push(item);
        });

        var container = document.getElementById('items-container');
        var catOrder = ['tires','brakes','suspension','fluids','lights','engine','exhaust','hoses','belts','battery','wipers','other'];

        catOrder.forEach(function(cat) {
            if (!grouped[cat]) return;
            var items = grouped[cat];
            var catLabel = (categoryLabels[currentLang] || categoryLabels.en)[cat] || cat;

            var hasRed = items.some(function(i) { return i.condition_rating === 'red'; });
            var hasYellow = items.some(function(i) { return i.condition_rating === 'yellow'; });
            var catRating = hasRed ? 'red' : (hasYellow ? 'yellow' : 'green');
            var colors = ratingStyles[catRating];

            var section = document.createElement('div');
            section.className = colors.bg + ' border ' + colors.border + ' rounded-2xl overflow-hidden';

            var header = document.createElement('div');
            header.className = 'px-5 py-3 flex items-center justify-between border-b ' + colors.border;
            header.appendChild(createTextEl('h2', catLabel, 'font-bold text-gray-900 dark:text-white text-lg'));
            var headerDot = document.createElement('span');
            headerDot.className = 'w-3 h-3 rounded-full ' + colors.dot;
            header.appendChild(headerDot);
            section.appendChild(header);

            var itemsList = document.createElement('div');
            itemsList.className = 'divide-y divide-gray-200 dark:divide-gray-700/50';
            items.forEach(function(item) {
                itemsList.appendChild(buildItemRow(item));
            });
            section.appendChild(itemsList);

            container.appendChild(section);
        });

        // Estimate CTA or Booking CTA
        if (data.estimate_token) {
            document.getElementById('estimate-link').href = '/approve/' + encodeURIComponent(data.estimate_token);
            document.getElementById('estimate-cta').classList.remove('hidden');
        } else if (data.red_count > 0 || data.yellow_count > 0) {
            var ctaDiv = document.getElementById('estimate-cta');
            var ctaLink = document.getElementById('estimate-link');
            ctaLink.href = '/book-appointment/';
            ctaLink.querySelector('[data-t="viewEstimate"]').setAttribute('data-t', 'scheduleRepair');
            ctaLink.querySelector('[data-t="scheduleRepair"]').textContent = t[currentLang].scheduleRepair;
            ctaDiv.classList.remove('hidden');
        }

        // Notes
        if (data.notes) {
            document.getElementById('inspection-notes').textContent = data.notes;
            document.getElementById('notes-section').classList.remove('hidden');
        }

        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('report-state').classList.remove('hidden');

    } catch (err) {
        showError('Failed to load inspection report. Please try again.');
    }
}

function showError(msg) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-message').textContent = msg;
    document.getElementById('error-state').classList.remove('hidden');
}

loadInspection();
</script>
</body>
</html>
