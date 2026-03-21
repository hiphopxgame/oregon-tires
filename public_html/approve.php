<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Estimate - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="assets/favicon.ico" sizes="any">
    <link rel="icon" href="assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
    <style>
    @media print {
        html, body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dark { color-scheme: light !important; }

        header, footer, #lang-toggle, #print-estimate-btn, #action-buttons, #inspection-link-wrap, #decline-reason-wrap { display: none !important; }
        footer { background: transparent !important; }

        #items-list input[type="checkbox"] { display: none !important; }

        .dark\:bg-\[#0A0A0A\], .dark\:bg-gray-900, .dark\:bg-gray-800\/50, .dark\:bg-\[#111827\]\/90 { background: white !important; }
        .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 { color: #111 !important; }
        .dark\:border-gray-800, .dark\:border-gray-700 { border-color: #e5e7eb !important; }

        #estimate-state::before {
            content: "Oregon Tires Auto Care \2014  Estimate";
            display: block; text-align: center; font-size: 11pt; font-weight: bold; color: #333;
            border-bottom: 2px solid #16a34a; padding-bottom: 8pt; margin-bottom: 16pt;
        }

        body { min-height: auto !important; }
        main { padding: 0 !important; }
        .container { max-width: 100% !important; padding: 0 !important; }
        .shadow-sm, .shadow-lg { box-shadow: none !important; }

        /* Preserve type badge colors */
        .bg-blue-100 { background-color: #dbeafe !important; }
        .text-blue-700 { color: #1d4ed8 !important; }
        .bg-purple-100 { background-color: #f3e8ff !important; }
        .text-purple-700 { color: #7e22ce !important; }
        .bg-green-100 { background-color: #dcfce7 !important; }
        .text-green-700 { color: #15803d !important; }
        .text-green-600 { color: #16a34a !important; }
        .bg-orange-100 { background-color: #ffedd5 !important; }
        .text-orange-700 { color: #c2410c !important; }
        .bg-yellow-100 { background-color: #fef9c3 !important; }
        .text-yellow-700 { color: #a16207 !important; }

        /* Priority badge print colors */
        .bg-red-100 { background-color: #fee2e2 !important; }
        .text-red-700 { color: #b91c1c !important; }

        /* Priority summary print */
        #priority-summary { -webkit-print-color-adjust: exact; print-color-adjust: exact; break-inside: avoid; }
        #priority-summary .priority-box { border: 1px solid #e5e7eb !important; }

        /* Why it matters — show in print */
        .why-it-matters { display: block !important; }

        #display-total { color: #16a34a !important; font-weight: bold; }

        #responded-state:not(.hidden) { display: block !important; }
        .bg-green-50 { background-color: #f0fdf4 !important; }
        .text-green-800 { color: #166534 !important; }
        .dark\:text-green-300 { color: #166534 !important; }
        .dark\:text-green-400 { color: #15803d !important; }
        .dark\:bg-green-900\/20 { background-color: #f0fdf4 !important; }
        .dark\:bg-green-900\/40 { background-color: #dcfce7 !important; }

        #estimate-state > div { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }
        .rounded-2xl { break-inside: avoid; page-break-inside: avoid; }
    }
    </style>
</head>
<body class="bg-white dark:bg-[#0A0A0A] min-h-screen flex flex-col">

<!-- Skip to Content -->
<a href="#estimate-state" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-green-600 focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold">Skip to content</a>

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#111827]/90 backdrop-blur border-b border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="/assets/logo.webp" alt="Oregon Tires" class="h-10 w-10 rounded-lg" width="40" height="40">
            <span class="text-lg font-bold text-gray-900 dark:text-white">Oregon Tires</span>
        </a>
        <nav class="flex items-center gap-4">
            <a href="/" class="text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 text-sm font-medium" data-t="backToHome">Back to Home</a>
            <button onclick="toggleLanguage()" class="text-sm font-bold px-4 py-2 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 min-h-[44px] min-w-[44px]" id="lang-toggle" aria-label="Toggle language between English and Spanish">EN | ES</button>
        </nav>
    </div>
</header>

<!-- Page Content -->
<main class="flex-1 py-8">
    <div class="container mx-auto px-4 max-w-2xl">

        <!-- Loading -->
        <div id="loading-state" class="text-center py-16">
            <div class="motion-safe:animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-500 dark:text-gray-400" data-t="loading">Loading estimate...</p>
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

        <!-- Estimate View -->
        <div id="estimate-state" class="hidden">

            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm">
                <p id="customer-greeting" class="text-sm text-gray-500 dark:text-gray-400 mb-2"></p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-t="estTitle">Service Estimate</h1>
                <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span><span data-t="roLabel">RO:</span> <strong id="ro-number"></strong></span>
                    <span><span data-t="estLabel">Estimate:</span> <strong id="est-number"></strong></span>
                </div>

                <!-- Vehicle Info -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 mt-4">
                    <p class="font-semibold text-gray-900 dark:text-white" id="vehicle-name"></p>
                </div>
            </div>

            <!-- View Inspection Link -->
            <div id="inspection-link-wrap" class="hidden mb-4">
                <a id="inspection-link" href="#" class="inline-flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:underline font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span data-t="viewInspection">View Full Inspection</span>
                </a>
            </div>

            <!-- Print Button -->
            <div class="mb-6 text-center" id="print-estimate-btn">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span data-t="printEstimate">Print Estimate</span>
                </button>
            </div>

            <!-- Priority Cost Summary -->
            <div id="priority-summary" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6" role="region" data-t-aria="costBreakdown" aria-label="Cost breakdown by priority"></div>

            <!-- Line Items -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden mb-6 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white" data-t="services">Recommended Services</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" data-t="toggleHint">Toggle each service to approve or decline</p>
                </div>
                <div id="items-list" class="divide-y divide-gray-100 dark:divide-gray-800"></div>
            </div>

            <!-- Totals -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span data-t="subtotal">Subtotal</span>
                        <span id="display-subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span data-t="tax">Tax</span>
                        <span id="display-tax">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span data-t="total">Total</span>
                        <span id="display-total" class="text-green-600">$0.00</span>
                    </div>
                </div>
                <p id="valid-until" class="text-xs text-gray-400 mt-3"></p>
            </div>

            <!-- Notes -->
            <div id="notes-section" class="hidden mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2" data-t="notes">Notes</h3>
                    <p id="estimate-notes" class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed"></p>
                </div>
            </div>

            <!-- Decline Reason (shown when all items unchecked) -->
            <div id="decline-reason-wrap" class="hidden mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" data-t="declineReasonLabel">Reason for declining (optional)</label>
                <select id="decline-reason" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-base focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="" data-t="selectReason">— Select a reason —</option>
                    <option value="too_expensive" data-t="tooExpensive">Too expensive</option>
                    <option value="will_do_later" data-t="willDoLater">Will do later</option>
                    <option value="already_done" data-t="alreadyDone">Already done elsewhere</option>
                </select>
            </div>

            <!-- Submit Buttons -->
            <div id="action-buttons">
                <div id="submit-error" class="hidden mb-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400"></div>
                <button id="approve-btn" onclick="submitApproval()" class="w-full px-6 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition text-lg shadow-lg mb-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <span data-t="approveSelected">Approve Selected Services</span>
                </button>
                <p class="text-center text-xs text-gray-400 dark:text-gray-500" data-t="approveHint">You can approve all or select specific services above</p>
            </div>

            <!-- Already Responded -->
            <div id="responded-state" class="hidden text-center">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6">
                    <div class="w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="font-bold text-green-800 dark:text-green-300 text-lg" data-t="alreadyResponded">Estimate Already Responded</h3>
                    <p class="text-green-700 dark:text-green-400 text-sm mt-2" id="responded-status"></p>
                </div>
            </div>

            <!-- Shop Info -->
            <div class="mt-8 text-center text-sm text-gray-400 dark:text-gray-500">
                <p class="font-semibold text-gray-600 dark:text-gray-400">Oregon Tires Auto Care</p>
                <p>8536 SE 82nd Ave, Portland, OR 97266</p>
                <p><a href="tel:+15033679714" class="text-green-600 hover:underline">(503) 367-9714</a></p>
            </div>
        </div>

        <!-- Success State -->
        <div id="success-state" class="hidden text-center py-16">
            <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="success-title"></h2>
            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto" id="success-message"></p>
            <a href="/" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition" data-t="goHome">Go to Homepage</a>
        </div>
    </div>
</main>

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
var currentLang = 'en';
var estimateToken = '';
var estimateItems = [];
var itemApprovals = {};
var taxRate = 0;

var t = {
    en: {
        backToHome: 'Back to Home',
        loading: 'Loading estimate...',
        errorTitle: 'Invalid Link',
        goHome: 'Go to Homepage',
        estTitle: 'Service Estimate',
        roLabel: 'RO:',
        estLabel: 'Estimate:',
        services: 'Recommended Services',
        toggleHint: 'Toggle each service to approve or decline',
        subtotal: 'Subtotal',
        tax: 'Tax',
        total: 'Total',
        notes: 'Notes',
        approveSelected: 'Approve Selected Services',
        approveHint: 'You can approve all or select specific services above',
        alreadyResponded: 'Estimate Already Responded',
        successApproved: 'Estimate Approved!',
        successPartial: 'Services Partially Approved',
        successDeclined: 'Services Declined',
        msgApproved: 'Thank you! We will begin work on your vehicle shortly. You will receive a notification when it is ready.',
        msgPartial: 'We will begin work on the approved services. You will receive a notification when your vehicle is ready.',
        msgDeclined: 'No services were approved. Please contact us if you change your mind or have questions.',
        validUntil: 'Valid until',
        approved: 'Approved',
        partiallyApproved: 'Partially Approved',
        declined: 'Declined',
        submitting: 'Submitting...',
        printEstimate: 'Print Estimate',
        costBreakdown: 'Cost breakdown by priority',
        safetyCritical: 'Safety-Critical',
        recommended: 'Recommended',
        preventive: 'Preventive',
        typeLaborLabel: 'LABOR',
        typePartsLabel: 'PARTS',
        typeTireLabel: 'TIRE',
        typeFeeLabel: 'FEE',
        typeDiscountLabel: 'DISCOUNT',
        typeSubletLabel: 'SUBLET',
        submitError: 'Failed to submit. Please try again.',
        networkError: 'Network error. Please try again.',
        greeting: 'Hello',
        viewInspection: 'View Full Inspection',
        approveXofY: 'Approve {x} of {y} Services',
        declineAll: 'Decline All',
        approveAll: 'Approve All Services',
        confirmDecline: 'Are you sure you want to decline all services? This cannot be undone.',
        declineReasonLabel: 'Reason for declining (optional)',
        selectReason: '— Select a reason —',
        tooExpensive: 'Too expensive',
        willDoLater: 'Will do later',
        alreadyDone: 'Already done elsewhere',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando presupuesto...',
        errorTitle: 'Enlace Invalido',
        goHome: 'Ir al Inicio',
        estTitle: 'Presupuesto de Servicios',
        roLabel: 'OT:',
        estLabel: 'Presupuesto:',
        services: 'Servicios Recomendados',
        toggleHint: 'Active o desactive cada servicio para aprobar o rechazar',
        subtotal: 'Subtotal',
        tax: 'Impuesto',
        total: 'Total',
        notes: 'Notas',
        approveSelected: 'Aprobar Servicios Seleccionados',
        approveHint: 'Puede aprobar todos o seleccionar servicios especificos',
        alreadyResponded: 'Presupuesto Ya Respondido',
        successApproved: 'Presupuesto Aprobado!',
        successPartial: 'Servicios Parcialmente Aprobados',
        successDeclined: 'Servicios Rechazados',
        msgApproved: 'Gracias! Comenzaremos a trabajar en su vehiculo pronto. Recibira una notificacion cuando este listo.',
        msgPartial: 'Comenzaremos con los servicios aprobados. Recibira una notificacion cuando su vehiculo este listo.',
        msgDeclined: 'Ningun servicio fue aprobado. Contactenos si cambia de opinion o tiene preguntas.',
        validUntil: 'Valido hasta',
        approved: 'Aprobado',
        partiallyApproved: 'Parcialmente Aprobado',
        declined: 'Rechazado',
        submitting: 'Enviando...',
        printEstimate: 'Imprimir Presupuesto',
        costBreakdown: 'Desglose de costos por prioridad',
        safetyCritical: 'Seguridad Critica',
        recommended: 'Recomendado',
        preventive: 'Preventivo',
        typeLaborLabel: 'MANO DE OBRA',
        typePartsLabel: 'REPUESTOS',
        typeTireLabel: 'NEUMATICO',
        typeFeeLabel: 'CARGO',
        typeDiscountLabel: 'DESCUENTO',
        typeSubletLabel: 'SUBCONTRATO',
        submitError: 'Error al enviar. Por favor intente de nuevo.',
        networkError: 'Error de red. Por favor intente de nuevo.',
        greeting: 'Hola',
        viewInspection: 'Ver Inspección Completa',
        approveXofY: 'Aprobar {x} de {y} Servicios',
        declineAll: 'Rechazar Todos',
        approveAll: 'Aprobar Todos los Servicios',
        confirmDecline: '¿Está seguro de que desea rechazar todos los servicios? Esto no se puede deshacer.',
        declineReasonLabel: 'Razón del rechazo (opcional)',
        selectReason: '— Seleccione una razón —',
        tooExpensive: 'Muy caro',
        willDoLater: 'Lo haré después',
        alreadyDone: 'Ya se hizo en otro lugar',
    }
};

// "Why it matters" descriptions keyed by category + priority level
var whyItMatters = {
    en: {
        tires:      { safety: 'Tire failure can cause loss of vehicle control.',         recommended: 'Tire wear affects handling, fuel efficiency, and ride quality.',        preventive: 'Regular tire maintenance extends tire life and saves money.' },
        brakes:     { safety: 'Brake failure can cause accidents. This is a critical safety item.', recommended: 'Brake wear reduces stopping power over time.',                  preventive: 'Routine brake service prevents costly emergency repairs.' },
        suspension: { safety: 'Suspension failure affects steering control and stability.', recommended: 'Worn suspension impacts ride comfort and tire wear.',              preventive: 'Maintaining suspension prevents premature tire and steering wear.' },
        fluids:     { safety: 'Low or contaminated fluids can cause engine or brake failure.', recommended: 'Fluid condition affects component longevity and performance.',   preventive: 'Regular fluid changes protect your engine and transmission.' },
        lights:     { safety: 'Non-functioning lights are a safety hazard and legal issue.',   recommended: 'Dim or flickering lights reduce nighttime visibility.',          preventive: 'Keeping all lights working ensures safe visibility.' },
        engine:     { safety: 'Engine problems can cause breakdowns or further damage.',       recommended: 'Addressing engine issues early prevents expensive repairs.',      preventive: 'Routine engine maintenance extends vehicle life.' },
        exhaust:    { safety: 'Exhaust leaks can allow harmful gases into the cabin.',         recommended: 'Exhaust wear affects emissions and fuel efficiency.',             preventive: 'Maintaining the exhaust system protects air quality.' },
        hoses:      { safety: 'Hose failure can cause overheating or loss of power steering.',  recommended: 'Aging hoses are prone to leaks and can fail unexpectedly.',      preventive: 'Replacing hoses on schedule prevents roadside breakdowns.' },
        belts:      { safety: 'A broken belt can disable power steering or cooling.',           recommended: 'Worn belts can slip, causing reduced performance.',             preventive: 'Belt replacement on schedule avoids unexpected breakdowns.' },
        battery:    { safety: 'A failing battery can leave you stranded.',                      recommended: 'Weak batteries struggle in cold weather and extreme heat.',      preventive: 'Battery testing catches problems before they leave you stranded.' },
        wipers:     { safety: 'Poor wipers severely reduce visibility in rain.',                recommended: 'Worn wipers leave streaks that impair vision.',                 preventive: 'Fresh wiper blades ensure clear visibility year-round.' },
        other:      { safety: 'This item needs immediate attention for safety.',                recommended: 'Addressing this item will improve vehicle reliability.',         preventive: 'Preventive care keeps your vehicle running smoothly.' }
    },
    es: {
        tires:      { safety: 'La falla de neumaticos puede causar perdida de control del vehiculo.',   recommended: 'El desgaste de neumaticos afecta el manejo y la eficiencia.',        preventive: 'El mantenimiento regular extiende la vida de los neumaticos.' },
        brakes:     { safety: 'La falla de frenos puede causar accidentes. Es un item critico.',        recommended: 'El desgaste de frenos reduce la potencia de frenado.',                preventive: 'El servicio de frenos previene reparaciones de emergencia costosas.' },
        suspension: { safety: 'La falla de suspension afecta el control de direccion.',                 recommended: 'La suspension desgastada impacta la comodidad y desgaste de neumaticos.', preventive: 'Mantener la suspension previene desgaste prematuro.' },
        fluids:     { safety: 'Fluidos bajos o contaminados pueden causar fallas del motor o frenos.',  recommended: 'La condicion de fluidos afecta la longevidad de componentes.',       preventive: 'Los cambios regulares protegen su motor y transmision.' },
        lights:     { safety: 'Las luces que no funcionan son un peligro y problema legal.',             recommended: 'Las luces tenues reducen la visibilidad nocturna.',                  preventive: 'Mantener todas las luces asegura visibilidad segura.' },
        engine:     { safety: 'Los problemas del motor pueden causar averias o mas dano.',              recommended: 'Atender problemas del motor temprano previene reparaciones caras.',   preventive: 'El mantenimiento rutinario extiende la vida del vehiculo.' },
        exhaust:    { safety: 'Las fugas de escape pueden permitir gases daninos en la cabina.',         recommended: 'El desgaste del escape afecta emisiones y eficiencia.',              preventive: 'Mantener el escape protege la calidad del aire.' },
        hoses:      { safety: 'La falla de mangueras puede causar sobrecalentamiento.',                 recommended: 'Las mangueras envejecidas son propensas a fugas.',                   preventive: 'Reemplazar mangueras previene averias en la carretera.' },
        belts:      { safety: 'Una correa rota puede desactivar la direccion o enfriamiento.',           recommended: 'Las correas desgastadas pueden patinar reduciendo rendimiento.',     preventive: 'El reemplazo de correas evita averias inesperadas.' },
        battery:    { safety: 'Una bateria fallando puede dejarlo varado.',                             recommended: 'Las baterias debiles tienen problemas en clima extremo.',             preventive: 'Las pruebas de bateria detectan problemas a tiempo.' },
        wipers:     { safety: 'Limpiaparabrisas deficientes reducen severamente la visibilidad.',        recommended: 'Las plumas desgastadas dejan rayas que afectan la vision.',          preventive: 'Plumas nuevas aseguran visibilidad clara todo el ano.' },
        other:      { safety: 'Este item necesita atencion inmediata por seguridad.',                   recommended: 'Atender este item mejorara la confiabilidad del vehiculo.',          preventive: 'El cuidado preventivo mantiene su vehiculo funcionando bien.' }
    }
};

function getTypeLabel(itemType) {
    var typeKeys = { labor: 'typeLaborLabel', parts: 'typePartsLabel', tire: 'typeTireLabel', fee: 'typeFeeLabel', discount: 'typeDiscountLabel', sublet: 'typeSubletLabel' };
    return t[currentLang][typeKeys[itemType]] || itemType.toUpperCase();
}

function updateItemTranslations() {
    document.querySelectorAll('.priority-badge').forEach(function(el) {
        el.textContent = getPriorityLabel(el.dataset.priority);
    });
    document.querySelectorAll('.type-badge').forEach(function(el) {
        el.textContent = getTypeLabel(el.dataset.itemType);
    });
    document.querySelectorAll('.why-it-matters').forEach(function(el) {
        var catDescs = (whyItMatters[currentLang] || whyItMatters.en)[el.dataset.category];
        if (catDescs) el.textContent = catDescs[el.dataset.priority] || '';
    });
}

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    applyTranslations();
    updateItemTranslations();
    updatePrioritySummary();
    updateButtonLabel();
    // Refresh greeting with new language
    var greetEl = document.getElementById('customer-greeting');
    if (greetEl && greetEl._customerName) {
        greetEl.textContent = (t[currentLang].greeting || 'Hello') + ', ' + greetEl._customerName;
    }
}

function applyTranslations() {
    document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[currentLang] && t[currentLang][key]) {
            el.textContent = t[currentLang][key];
        }
    });
    document.querySelectorAll('[data-t-aria]').forEach(function(el) {
        var key = el.getAttribute('data-t-aria');
        if (t[currentLang] && t[currentLang][key]) {
            el.setAttribute('aria-label', t[currentLang][key]);
        }
    });
}

function formatMoney(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

// Map inspection_rating to priority level
function getPriority(item) {
    if (item.inspection_rating === 'red') return 'safety';
    if (item.inspection_rating === 'yellow') return 'recommended';
    return 'preventive';
}

function getPriorityLabel(priority) {
    if (priority === 'safety') return t[currentLang].safetyCritical;
    if (priority === 'recommended') return t[currentLang].recommended;
    return t[currentLang].preventive;
}

function getPriorityBadgeClass(priority) {
    if (priority === 'safety') return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    if (priority === 'recommended') return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
    return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
}

function buildItemRow(item) {
    var row = document.createElement('div');
    row.className = 'px-6 py-4';

    var mainRow = document.createElement('div');
    mainRow.className = 'flex items-center gap-4';

    // Toggle checkbox
    var label = document.createElement('label');
    label.className = 'flex items-center gap-4 flex-1 cursor-pointer';

    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.checked = true;
    checkbox.className = 'w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 flex-shrink-0';
    checkbox.dataset.itemId = item.id;
    itemApprovals[item.id] = true;

    checkbox.addEventListener('change', function() {
        itemApprovals[item.id] = this.checked;
        recalculateTotals();
        updateButtonLabel();
    });

    var textWrap = document.createElement('div');
    textWrap.className = 'flex-1 min-w-0';

    var descLine = document.createElement('div');
    descLine.className = 'flex items-center gap-2 flex-wrap';

    // Priority badge (before type badge)
    var priority = getPriority(item);
    var priorityBadge = document.createElement('span');
    priorityBadge.className = 'text-xs font-bold px-2 py-0.5 rounded priority-badge ' + getPriorityBadgeClass(priority);
    priorityBadge.dataset.priority = priority;
    priorityBadge.textContent = getPriorityLabel(priority);
    descLine.appendChild(priorityBadge);

    // Type badge
    var typeColors = {
        labor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        parts: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        tire: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        fee: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        discount: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        sublet: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    };

    var badge = document.createElement('span');
    badge.className = 'text-xs font-bold px-2 py-0.5 rounded type-badge ' + (typeColors[item.item_type] || typeColors.labor);
    badge.dataset.itemType = item.item_type;
    badge.textContent = getTypeLabel(item.item_type);
    descLine.appendChild(badge);

    var desc = document.createElement('span');
    desc.className = 'text-sm font-medium text-gray-900 dark:text-white truncate';
    desc.textContent = item.description;
    descLine.appendChild(desc);

    textWrap.appendChild(descLine);

    if (item.quantity > 1) {
        var qtyLine = document.createElement('p');
        qtyLine.className = 'text-xs text-gray-400 mt-0.5';
        qtyLine.textContent = item.quantity + ' x ' + formatMoney(item.unit_price);
        textWrap.appendChild(qtyLine);
    }

    // "Why it matters" description
    if (item.inspection_category) {
        var catDescs = (whyItMatters[currentLang] || whyItMatters.en)[item.inspection_category];
        if (catDescs) {
            var whyText = catDescs[priority] || '';
            if (whyText) {
                var whyEl = document.createElement('p');
                whyEl.className = 'text-xs italic text-gray-400 dark:text-gray-500 mt-1 why-it-matters';
                whyEl.dataset.category = item.inspection_category;
                whyEl.dataset.priority = priority;
                whyEl.textContent = whyText;
                textWrap.appendChild(whyEl);
            }
        }
    }

    label.appendChild(checkbox);
    label.appendChild(textWrap);
    mainRow.appendChild(label);

    // Price
    var price = document.createElement('span');
    price.className = 'font-semibold text-gray-900 dark:text-white text-sm flex-shrink-0';
    price.textContent = formatMoney(item.total);
    mainRow.appendChild(price);

    row.appendChild(mainRow);
    return row;
}

function buildPrioritySummary() {
    var container = document.getElementById('priority-summary');
    while (container.firstChild) container.removeChild(container.firstChild);

    var priorities = [
        { key: 'safety', label: t[currentLang].safetyCritical, bg: 'bg-red-50 dark:bg-red-900/20', border: 'border-red-200 dark:border-red-800', text: 'text-red-700 dark:text-red-400', amountId: 'priority-safety-amount' },
        { key: 'recommended', label: t[currentLang].recommended, bg: 'bg-yellow-50 dark:bg-yellow-900/20', border: 'border-yellow-200 dark:border-yellow-800', text: 'text-yellow-700 dark:text-yellow-400', amountId: 'priority-recommended-amount' },
        { key: 'preventive', label: t[currentLang].preventive, bg: 'bg-blue-50 dark:bg-blue-900/20', border: 'border-blue-200 dark:border-blue-800', text: 'text-blue-700 dark:text-blue-400', amountId: 'priority-preventive-amount' }
    ];

    priorities.forEach(function(p) {
        var box = document.createElement('div');
        box.className = 'text-center p-3 rounded-xl border priority-box ' + p.bg + ' ' + p.border;

        var amountEl = document.createElement('div');
        amountEl.className = 'text-lg font-bold ' + p.text;
        amountEl.id = p.amountId;
        amountEl.textContent = '$0.00';
        box.appendChild(amountEl);

        var labelEl = document.createElement('div');
        labelEl.className = 'text-xs font-medium ' + p.text;
        labelEl.textContent = p.label;
        box.appendChild(labelEl);

        container.appendChild(box);
    });

    container.classList.remove('hidden');
}

function updatePrioritySummary() {
    var safetyTotal = 0, recommendedTotal = 0, preventiveTotal = 0;
    var safetyHas = false, recHas = false, prevHas = false;

    estimateItems.forEach(function(item) {
        var priority = getPriority(item);
        if (priority === 'safety') safetyHas = true;
        else if (priority === 'recommended') recHas = true;
        else prevHas = true;

        if (!itemApprovals[item.id]) return;
        var amount = parseFloat(item.total || 0);
        if (priority === 'safety') safetyTotal += amount;
        else if (priority === 'recommended') recommendedTotal += amount;
        else preventiveTotal += amount;
    });

    // Hide boxes for priority levels with zero items (not just zero approved)
    var safetyBox = document.getElementById('priority-safety-amount');
    var recBox = document.getElementById('priority-recommended-amount');
    var prevBox = document.getElementById('priority-preventive-amount');
    if (safetyBox) safetyBox.parentElement.style.display = safetyHas ? '' : 'none';
    if (recBox) recBox.parentElement.style.display = recHas ? '' : 'none';
    if (prevBox) prevBox.parentElement.style.display = prevHas ? '' : 'none';

    // Adjust grid to match visible count
    var visibleCount = (safetyHas ? 1 : 0) + (recHas ? 1 : 0) + (prevHas ? 1 : 0);
    var container = document.getElementById('priority-summary');
    container.className = container.className.replace(/grid-cols-\d/, 'grid-cols-' + visibleCount);

    var safetyEl = safetyBox;
    var recEl = recBox;
    var prevEl = prevBox;

    if (safetyEl) safetyEl.textContent = formatMoney(safetyTotal);
    if (recEl) recEl.textContent = formatMoney(recommendedTotal);
    if (prevEl) prevEl.textContent = formatMoney(preventiveTotal);
}

function recalculateTotals() {
    var subtotal = 0;
    estimateItems.forEach(function(item) {
        if (itemApprovals[item.id]) {
            subtotal += parseFloat(item.total || 0);
        }
    });
    var taxAmount = Math.round(subtotal * taxRate * 100) / 100;
    var total = subtotal + taxAmount;

    document.getElementById('display-subtotal').textContent = formatMoney(subtotal);
    document.getElementById('display-tax').textContent = formatMoney(taxAmount);
    document.getElementById('display-total').textContent = formatMoney(total);

    updatePrioritySummary();
}

function updateButtonLabel() {
    var total = Object.keys(itemApprovals).length;
    var approved = 0;
    for (var id in itemApprovals) { if (itemApprovals[id]) approved++; }
    var btn = document.getElementById('approve-btn');
    if (!btn) return;
    var span = btn.querySelector('span');
    if (approved === 0) {
        span.textContent = t[currentLang].declineAll;
    } else if (approved === total) {
        span.textContent = t[currentLang].approveAll;
    } else {
        span.textContent = (t[currentLang].approveXofY || 'Approve {x} of {y} Services').replace('{x}', approved).replace('{y}', total);
    }
    var wrap = document.getElementById('decline-reason-wrap');
    if (wrap) wrap.classList.toggle('hidden', approved > 0);
}

async function loadEstimate() {
    // Token can come from URL path (/approve/TOKEN) or query string (?token=TOKEN)
    var params = new URLSearchParams(window.location.search);
    estimateToken = params.get('token');
    if (!estimateToken) {
        // Extract from path: /approve/abc123...
        var pathMatch = window.location.pathname.match(/\/approve\/([a-f0-9]+)/i);
        if (pathMatch) estimateToken = pathMatch[1];
    }
    if (!estimateToken) return showError('No estimate token provided.');

    try {
        var res = await fetch('/api/estimate-approve.php?token=' + encodeURIComponent(estimateToken), { credentials: 'include' });
        var json = await res.json();

        if (!json.success) return showError(json.error || 'Estimate not found.');

        var data = json.data;

        if (data.customer_language === 'spanish') {
            currentLang = 'es';
            applyTranslations();
        }

        // #13: Customer greeting
        if (data.customer_name) {
            var greetEl = document.getElementById('customer-greeting');
            greetEl.textContent = (t[currentLang].greeting || 'Hello') + ', ' + data.customer_name;
            greetEl._customerName = data.customer_name;
        }

        // #14: Inspection link
        if (data.inspection_token) {
            document.getElementById('inspection-link').href = '/inspection/' + encodeURIComponent(data.inspection_token);
            document.getElementById('inspection-link-wrap').classList.remove('hidden');
        }

        document.getElementById('ro-number').textContent = data.ro_number;
        document.getElementById('est-number').textContent = data.estimate_number;
        document.getElementById('vehicle-name').textContent = data.vehicle || 'Vehicle';

        taxRate = parseFloat(data.tax_rate || 0);
        estimateItems = data.items || [];

        // Build priority cost summary
        buildPrioritySummary();

        // Build items list
        var container = document.getElementById('items-list');
        estimateItems.forEach(function(item) {
            container.appendChild(buildItemRow(item));
        });

        // Totals
        document.getElementById('display-subtotal').textContent = formatMoney(data.subtotal);
        document.getElementById('display-tax').textContent = formatMoney(data.tax_amount);
        document.getElementById('display-total').textContent = formatMoney(data.total);

        // Update priority summary with initial totals
        updatePrioritySummary();
        updateButtonLabel();

        if (data.valid_until) {
            var vu = document.getElementById('valid-until');
            var locale = currentLang === 'es' ? 'es-MX' : 'en-US';
            var formattedDate = new Date(data.valid_until).toLocaleDateString(locale, { year: 'numeric', month: 'long', day: 'numeric' });
            vu.textContent = (t[currentLang].validUntil || 'Valid until') + ': ' + formattedDate;
        }

        // Notes
        if (data.notes) {
            document.getElementById('estimate-notes').textContent = data.notes;
            document.getElementById('notes-section').classList.remove('hidden');
        }

        // Check if already responded
        if (!data.can_respond) {
            document.getElementById('action-buttons').classList.add('hidden');
            var statusLabel = data.status === 'approved' ? (t[currentLang].approved || 'Approved') :
                             data.status === 'partial' ? (t[currentLang].partiallyApproved || 'Partially Approved') :
                             (t[currentLang].declined || 'Declined');
            document.getElementById('responded-status').textContent = statusLabel;
            document.getElementById('responded-state').classList.remove('hidden');

            // Disable checkboxes
            document.querySelectorAll('#items-list input[type="checkbox"]').forEach(function(cb) {
                cb.disabled = true;
                var itemId = parseInt(cb.dataset.itemId);
                var item = estimateItems.find(function(i) { return i.id === itemId; });
                if (item) cb.checked = item.is_approved === 1 || item.is_approved === '1';
            });
        }

        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('estimate-state').classList.remove('hidden');

    } catch (err) {
        showError('Failed to load estimate. Please try again.');
    }
}

function showSubmitError(msg) {
    var el = document.getElementById('submit-error');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideSubmitError() {
    document.getElementById('submit-error').classList.add('hidden');
}

async function submitApproval() {
    hideSubmitError();
    var btn = document.getElementById('approve-btn');

    // Check if all items declined — confirm before proceeding
    var approvedCount = 0;
    for (var id in itemApprovals) { if (itemApprovals[id]) approvedCount++; }
    if (approvedCount === 0) {
        if (!confirm(t[currentLang].confirmDecline)) return;
    }

    btn.disabled = true;
    btn.querySelector('span').textContent = t[currentLang].submitting || 'Submitting...';

    var reason = document.getElementById('decline-reason');
    try {
        var res = await fetch('/api/estimate-approve.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                token: estimateToken,
                approvals: itemApprovals,
                decline_reason: reason ? reason.value : ''
            })
        });
        var json = await res.json();

        if (!json.success) {
            btn.disabled = false;
            btn.querySelector('span').textContent = t[currentLang].approveSelected;
            showSubmitError(json.error || t[currentLang].submitError);
            return;
        }

        var data = json.data;

        var titleKey = data.status === 'approved' ? 'successApproved' :
                      data.status === 'partial' ? 'successPartial' : 'successDeclined';
        var msgKey = data.status === 'approved' ? 'msgApproved' :
                    data.status === 'partial' ? 'msgPartial' : 'msgDeclined';

        document.getElementById('success-title').textContent = t[currentLang][titleKey];
        document.getElementById('success-message').textContent = t[currentLang][msgKey];

        document.getElementById('estimate-state').classList.add('hidden');
        document.getElementById('success-state').classList.remove('hidden');

    } catch (err) {
        btn.disabled = false;
        btn.querySelector('span').textContent = t[currentLang].approveSelected;
        showSubmitError(t[currentLang].networkError);
    }
}

function showError(msg) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-message').textContent = msg;
    document.getElementById('error-state').classList.remove('hidden');
}

loadEstimate();
</script>
</body>
</html>
