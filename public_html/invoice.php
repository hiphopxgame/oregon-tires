<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/favicon.ico" sizes="any">
    <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="/assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
    <style>
    @media print {
        html, body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dark { color-scheme: light !important; }

        header, footer, #lang-toggle, #print-invoice-btn, #related-links { display: none !important; }
        footer { background: transparent !important; }

        .dark\:bg-\[#0A0A0A\], .dark\:bg-gray-900, .dark\:bg-gray-800\/50, .dark\:bg-\[#111827\]\/90 { background: white !important; }
        .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 { color: #111 !important; }
        .dark\:border-gray-800, .dark\:border-gray-700 { border-color: #e5e7eb !important; }

        #invoice-state::before {
            content: "Oregon Tires Auto Care \2014  Invoice";
            display: block; text-align: center; font-size: 11pt; font-weight: bold; color: #333;
            border-bottom: 2px solid #16a34a; padding-bottom: 8pt; margin-bottom: 16pt;
        }

        body { min-height: auto !important; }
        main { padding: 0 !important; }
        .container { max-width: 100% !important; padding: 0 !important; }
        .shadow-sm, .shadow-lg { box-shadow: none !important; }

        /* Preserve badge colors */
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
        .bg-red-100 { background-color: #fee2e2 !important; }
        .text-red-700 { color: #b91c1c !important; }

        #display-total { color: #16a34a !important; font-weight: bold; }

        #invoice-state > div { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }
        .rounded-2xl { break-inside: avoid; page-break-inside: avoid; }
    }
    </style>
</head>
<body class="bg-white dark:bg-[#0A0A0A] min-h-screen flex flex-col">

<!-- Skip to Content -->
<a href="#invoice-state" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-green-600 focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold">Skip to content</a>

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#111827]/90 backdrop-blur border-b border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center">
            <img src="/assets/logo.webp" alt="Oregon Tires Auto Care" class="h-10 rounded-lg" width="113" height="40">
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
            <p class="text-gray-500 dark:text-gray-400" data-t="loading">Loading invoice...</p>
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

        <!-- Invoice View -->
        <div id="invoice-state" class="hidden">

            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm">
                <p id="customer-greeting" class="text-sm text-gray-500 dark:text-gray-400 mb-2"></p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-t="invTitle">Invoice</h1>
                <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span><span data-t="invoiceLabel">Invoice:</span> <strong id="inv-number"></strong></span>
                    <span><span data-t="roLabel">RO:</span> <strong id="ro-number"></strong></span>
                </div>

                <!-- Payment Status Badge -->
                <div class="mt-3" id="status-badge-wrap"></div>

                <!-- Vehicle Info -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 mt-4">
                    <p class="font-semibold text-gray-900 dark:text-white" id="vehicle-name"></p>
                    <div id="vehicle-details" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></div>
                </div>
            </div>

            <!-- Related Links -->
            <div id="related-links" class="hidden flex flex-wrap gap-3 mb-4">
                <a id="inspection-link" href="#" class="hidden inline-flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:underline font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span data-t="viewInspection">View Inspection</span>
                </a>
                <a id="estimate-link" href="#" class="hidden inline-flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:underline font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span data-t="viewEstimate">View Estimate</span>
                </a>
            </div>

            <!-- Print Button -->
            <div class="mb-6 text-center" id="print-invoice-btn">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span data-t="printInvoice">Print Invoice</span>
                </button>
            </div>

            <!-- Line Items -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden mb-6 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white" data-t="services">Services Performed</h2>
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
                    <div id="discount-row" class="hidden flex justify-between text-gray-500 dark:text-gray-400">
                        <span data-t="discount">Discount</span>
                        <span id="display-discount" class="text-green-600">-$0.00</span>
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
                <div id="payment-info" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400" data-t="paymentMethod">Payment Method</span>
                        <span id="display-payment-method" class="font-medium text-gray-900 dark:text-white"></span>
                    </div>
                    <div id="paid-date-row" class="hidden flex justify-between text-sm mt-1">
                        <span class="text-gray-500 dark:text-gray-400" data-t="paidOn">Paid On</span>
                        <span id="display-paid-date" class="font-medium text-gray-900 dark:text-white"></span>
                    </div>
                </div>
                <p id="due-date-text" class="hidden text-xs text-gray-400 mt-3"></p>
            </div>

            <!-- Notes -->
            <div id="notes-section" class="hidden mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2" data-t="notes">Notes</h3>
                    <p id="invoice-notes" class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed"></p>
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
var invoiceData = null;

var t = {
    en: {
        backToHome: 'Back to Home',
        loading: 'Loading invoice...',
        errorTitle: 'Invalid Link',
        goHome: 'Go to Homepage',
        invTitle: 'Invoice',
        invoiceLabel: 'Invoice:',
        roLabel: 'RO:',
        services: 'Services Performed',
        subtotal: 'Subtotal',
        discount: 'Discount',
        tax: 'Tax',
        total: 'Total',
        notes: 'Notes',
        printInvoice: 'Print Invoice',
        viewInspection: 'View Inspection',
        viewEstimate: 'View Estimate',
        greeting: 'Hello',
        paymentMethod: 'Payment Method',
        paidOn: 'Paid On',
        dueDate: 'Due:',
        statusDraft: 'Draft',
        statusSent: 'Sent',
        statusViewed: 'Viewed',
        statusPaid: 'Paid',
        statusOverdue: 'Overdue',
        statusVoid: 'Void',
        methodCash: 'Cash',
        methodCard: 'Card',
        methodCheck: 'Check',
        methodPaypal: 'PayPal',
        methodOther: 'Other',
        typeLaborLabel: 'LABOR',
        typePartsLabel: 'PARTS',
        typeTireLabel: 'TIRE',
        typeFeeLabel: 'FEE',
        typeDiscountLabel: 'DISCOUNT',
        typeSubletLabel: 'SUBLET',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando factura...',
        errorTitle: 'Enlace Invalido',
        goHome: 'Ir al Inicio',
        invTitle: 'Factura',
        invoiceLabel: 'Factura:',
        roLabel: 'OT:',
        services: 'Servicios Realizados',
        subtotal: 'Subtotal',
        discount: 'Descuento',
        tax: 'Impuesto',
        total: 'Total',
        notes: 'Notas',
        printInvoice: 'Imprimir Factura',
        viewInspection: 'Ver Inspeccion',
        viewEstimate: 'Ver Presupuesto',
        greeting: 'Hola',
        paymentMethod: 'Metodo de Pago',
        paidOn: 'Pagado el',
        dueDate: 'Vence:',
        statusDraft: 'Borrador',
        statusSent: 'Enviada',
        statusViewed: 'Vista',
        statusPaid: 'Pagada',
        statusOverdue: 'Vencida',
        statusVoid: 'Anulada',
        methodCash: 'Efectivo',
        methodCard: 'Tarjeta',
        methodCheck: 'Cheque',
        methodPaypal: 'PayPal',
        methodOther: 'Otro',
        typeLaborLabel: 'MANO DE OBRA',
        typePartsLabel: 'REPUESTOS',
        typeTireLabel: 'NEUMATICO',
        typeFeeLabel: 'CARGO',
        typeDiscountLabel: 'DESCUENTO',
        typeSubletLabel: 'SUBCONTRATO',
    }
};

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    applyTranslations();
    if (invoiceData) {
        renderStatusBadge(invoiceData.status);
        renderPaymentInfo(invoiceData);
        updateGreeting(invoiceData.customer_name);
        updateDueDate(invoiceData.due_date);
    }
    updateItemTypeBadges();
}

function applyTranslations() {
    document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (t[currentLang] && t[currentLang][key]) {
            el.textContent = t[currentLang][key];
        }
    });
}

function formatMoney(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

function getTypeLabel(itemType) {
    var typeKeys = { labor: 'typeLaborLabel', parts: 'typePartsLabel', tire: 'typeTireLabel', fee: 'typeFeeLabel', discount: 'typeDiscountLabel', sublet: 'typeSubletLabel' };
    return t[currentLang][typeKeys[itemType]] || itemType.toUpperCase();
}

function updateItemTypeBadges() {
    document.querySelectorAll('.type-badge').forEach(function(el) {
        el.textContent = getTypeLabel(el.dataset.itemType);
    });
}

function getStatusLabel(status) {
    var key = 'status' + status.charAt(0).toUpperCase() + status.slice(1);
    return t[currentLang][key] || status;
}

function getStatusBadgeClass(status) {
    var classes = {
        draft: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        sent: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        viewed: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        paid: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        void: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500',
    };
    return classes[status] || classes.draft;
}

function renderStatusBadge(status) {
    var wrap = document.getElementById('status-badge-wrap');
    while (wrap.firstChild) { wrap.removeChild(wrap.firstChild); }
    var badge = document.createElement('span');
    badge.className = 'inline-block text-sm font-bold px-3 py-1 rounded-full ' + getStatusBadgeClass(status);
    badge.textContent = getStatusLabel(status);
    wrap.appendChild(badge);
}

function getPaymentMethodLabel(method) {
    if (!method) return '';
    var key = 'method' + method.charAt(0).toUpperCase() + method.slice(1);
    return t[currentLang][key] || method;
}

function renderPaymentInfo(data) {
    var paymentWrap = document.getElementById('payment-info');
    var methodEl = document.getElementById('display-payment-method');
    var paidRow = document.getElementById('paid-date-row');
    var paidDateEl = document.getElementById('display-paid-date');

    if (data.payment_method) {
        paymentWrap.classList.remove('hidden');
        methodEl.textContent = getPaymentMethodLabel(data.payment_method);
    } else {
        paymentWrap.classList.add('hidden');
    }

    if (data.paid_at) {
        paidRow.classList.remove('hidden');
        paidDateEl.textContent = new Date(data.paid_at).toLocaleDateString(currentLang === 'es' ? 'es-MX' : 'en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    } else {
        paidRow.classList.add('hidden');
    }
}

function updateGreeting(name) {
    var el = document.getElementById('customer-greeting');
    el.textContent = (t[currentLang].greeting || 'Hello') + ', ' + name;
}

function updateDueDate(dueDate) {
    var el = document.getElementById('due-date-text');
    if (dueDate) {
        el.classList.remove('hidden');
        var formatted = new Date(dueDate + 'T00:00:00').toLocaleDateString(currentLang === 'es' ? 'es-MX' : 'en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        el.textContent = (t[currentLang].dueDate || 'Due:') + ' ' + formatted;
    } else {
        el.classList.add('hidden');
    }
}

function buildItemRow(item) {
    var row = document.createElement('div');
    row.className = 'px-6 py-4';

    var mainRow = document.createElement('div');
    mainRow.className = 'flex items-center justify-between gap-4';

    var textWrap = document.createElement('div');
    textWrap.className = 'flex-1 min-w-0';

    var descLine = document.createElement('div');
    descLine.className = 'flex items-center gap-2 flex-wrap';

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
    desc.className = 'text-sm font-medium text-gray-900 dark:text-white';
    desc.textContent = item.description;
    descLine.appendChild(desc);

    textWrap.appendChild(descLine);

    if (parseFloat(item.quantity) > 1) {
        var qtyLine = document.createElement('p');
        qtyLine.className = 'text-xs text-gray-400 mt-0.5';
        qtyLine.textContent = parseFloat(item.quantity) + ' x ' + formatMoney(item.unit_price);
        textWrap.appendChild(qtyLine);
    }

    mainRow.appendChild(textWrap);

    var priceEl = document.createElement('span');
    priceEl.className = 'text-sm font-semibold whitespace-nowrap';
    if (item.item_type === 'discount') {
        priceEl.className += ' text-green-600';
        priceEl.textContent = '-' + formatMoney(Math.abs(parseFloat(item.total)));
    } else {
        priceEl.className += ' text-gray-900 dark:text-white';
        priceEl.textContent = formatMoney(item.total);
    }
    mainRow.appendChild(priceEl);

    row.appendChild(mainRow);
    return row;
}

function loadInvoice() {
    var params = new URLSearchParams(window.location.search);
    var token = params.get('token');

    if (!token) {
        // Try path-based token: /invoice/TOKEN
        var pathParts = window.location.pathname.split('/');
        var invIdx = pathParts.indexOf('invoice');
        if (invIdx >= 0 && pathParts[invIdx + 1]) {
            token = pathParts[invIdx + 1];
        }
    }

    if (!token) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        document.getElementById('error-message').textContent = 'No invoice token provided.';
        return;
    }

    fetch('/api/invoice-view.php?token=' + encodeURIComponent(token), { credentials: 'include' })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            document.getElementById('loading-state').classList.add('hidden');

            if (!json.success) {
                document.getElementById('error-state').classList.remove('hidden');
                document.getElementById('error-message').textContent = json.error || 'Invoice not found.';
                return;
            }

            var data = json.data;
            invoiceData = data;

            // Set language based on customer preference
            if (data.customer_language === 'spanish') {
                currentLang = 'es';
                applyTranslations();
            }

            document.getElementById('invoice-state').classList.remove('hidden');

            // Populate header
            updateGreeting(data.customer_name);
            document.getElementById('inv-number').textContent = data.invoice_number;
            document.getElementById('ro-number').textContent = data.ro_number;

            // Status badge
            renderStatusBadge(data.status);

            // Vehicle
            document.getElementById('vehicle-name').textContent = data.vehicle || 'Vehicle';
            var detailParts = [];
            if (data.vehicle_color) detailParts.push(data.vehicle_color);
            if (data.license_plate) detailParts.push(data.license_plate);
            if (data.vin) detailParts.push('VIN: ' + data.vin);
            if (detailParts.length > 0) {
                document.getElementById('vehicle-details').textContent = detailParts.join(' | ');
            }

            // Related links
            var hasLinks = false;
            if (data.inspection_token) {
                var inspLink = document.getElementById('inspection-link');
                inspLink.href = '/inspection/' + data.inspection_token;
                inspLink.classList.remove('hidden');
                hasLinks = true;
            }
            if (data.estimate_token) {
                var estLink = document.getElementById('estimate-link');
                estLink.href = '/approve/' + data.estimate_token;
                estLink.classList.remove('hidden');
                hasLinks = true;
            }
            if (hasLinks) {
                document.getElementById('related-links').classList.remove('hidden');
            }

            // Items
            var itemsList = document.getElementById('items-list');
            while (itemsList.firstChild) { itemsList.removeChild(itemsList.firstChild); }
            if (data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    itemsList.appendChild(buildItemRow(item));
                });
            }

            // Totals
            document.getElementById('display-subtotal').textContent = formatMoney(data.subtotal);
            document.getElementById('display-tax').textContent = formatMoney(data.tax_amount);
            document.getElementById('display-total').textContent = formatMoney(data.total);

            // Discount
            if (parseFloat(data.discount_amount) > 0) {
                document.getElementById('discount-row').classList.remove('hidden');
                document.getElementById('display-discount').textContent = '-' + formatMoney(data.discount_amount);
            }

            // Payment info
            renderPaymentInfo(data);

            // Due date
            updateDueDate(data.due_date);

            // Notes
            if (data.notes) {
                document.getElementById('notes-section').classList.remove('hidden');
                document.getElementById('invoice-notes').textContent = data.notes;
            }
        })
        .catch(function(err) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.remove('hidden');
            document.getElementById('error-message').textContent = 'Network error. Please try again.';
        });
}

document.addEventListener('DOMContentLoaded', loadInvoice);
</script>
</body>
</html>
