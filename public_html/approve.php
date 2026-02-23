<!DOCTYPE html>
<html lang="en" class="dark">
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
        /* Force light background and black text */
        html, body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dark { color-scheme: light !important; }

        /* Hide non-content elements */
        header, footer, #lang-toggle, #print-estimate-btn, #action-buttons { display: none !important; }

        /* Hide checkboxes in print */
        #items-list input[type="checkbox"] { display: none !important; }

        /* Remove dark mode overrides for print */
        .dark\:bg-\[#0A0A0A\], .dark\:bg-gray-900, .dark\:bg-gray-800\/50, .dark\:bg-\[#111827\]\/90 { background: white !important; }
        .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 { color: #111 !important; }
        .dark\:border-gray-800, .dark\:border-gray-700 { border-color: #e5e7eb !important; }

        /* Print header */
        #estimate-state::before {
            content: "Oregon Tires Auto Care \2014  Estimate";
            display: block;
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8pt;
            margin-bottom: 16pt;
        }

        /* Clean layout */
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

        /* Totals section — keep visible */
        #display-total { color: #16a34a !important; font-weight: bold; }

        /* Responded state — keep visible and styled when shown */
        #responded-state:not(.hidden) { display: block !important; }
        .bg-green-50 { background-color: #f0fdf4 !important; }
        .text-green-800 { color: #166534 !important; }
        .dark\:text-green-300 { color: #166534 !important; }
        .dark\:text-green-400 { color: #15803d !important; }
        .dark\:bg-green-900\/20 { background-color: #f0fdf4 !important; }
        .dark\:bg-green-900\/40 { background-color: #dcfce7 !important; }

        /* Avoid page breaks inside sections */
        #estimate-state > div { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }

        /* Overall badge */
        .rounded-2xl { break-inside: avoid; page-break-inside: avoid; }
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

            <!-- Print Button -->
            <div class="mb-6 text-center" id="print-estimate-btn">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span data-t="printEstimate">Print Estimate</span>
                </button>
            </div>

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

            <!-- Submit Buttons -->
            <div id="action-buttons">
                <button id="approve-btn" onclick="submitApproval()" class="w-full px-6 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition text-lg shadow-lg mb-3">
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
<footer class="py-6 text-center text-xs text-gray-400 dark:text-gray-600">
    <p>&copy; 2026 Oregon Tires Auto Care. All rights reserved.</p>
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
        declined: 'Declined',
        submitting: 'Submitting...',
        printEstimate: 'Print Estimate',
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
        declined: 'Rechazado',
        submitting: 'Enviando...',
        printEstimate: 'Imprimir Presupuesto',
    }
};

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    applyTranslations();
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

function buildItemRow(item) {
    var row = document.createElement('div');
    row.className = 'px-6 py-4 flex items-center gap-4';

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
    });

    var textWrap = document.createElement('div');
    textWrap.className = 'flex-1 min-w-0';

    var descLine = document.createElement('div');
    descLine.className = 'flex items-center gap-2';

    var typeColors = {
        labor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        parts: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        tire: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        fee: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        discount: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        sublet: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    };

    var badge = document.createElement('span');
    badge.className = 'text-xs font-bold px-2 py-0.5 rounded ' + (typeColors[item.item_type] || typeColors.labor);
    badge.textContent = item.item_type.toUpperCase();
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

    label.appendChild(checkbox);
    label.appendChild(textWrap);
    row.appendChild(label);

    // Price
    var price = document.createElement('span');
    price.className = 'font-semibold text-gray-900 dark:text-white text-sm flex-shrink-0';
    price.textContent = formatMoney(item.total);
    row.appendChild(price);

    return row;
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
}

async function loadEstimate() {
    var params = new URLSearchParams(window.location.search);
    estimateToken = params.get('token');
    if (!estimateToken) return showError('No estimate token provided.');

    try {
        var res = await fetch('/api/estimate-approve.php?token=' + encodeURIComponent(estimateToken));
        var json = await res.json();

        if (!json.success) return showError(json.error || 'Estimate not found.');

        var data = json.data;

        if (data.customer_language === 'spanish') {
            currentLang = 'es';
            applyTranslations();
        }

        document.getElementById('ro-number').textContent = data.ro_number;
        document.getElementById('est-number').textContent = data.estimate_number;
        document.getElementById('vehicle-name').textContent = data.vehicle || 'Vehicle';

        taxRate = parseFloat(data.tax_rate || 0);
        estimateItems = data.items || [];

        // Build items list
        var container = document.getElementById('items-list');
        estimateItems.forEach(function(item) {
            container.appendChild(buildItemRow(item));
        });

        // Totals
        document.getElementById('display-subtotal').textContent = formatMoney(data.subtotal);
        document.getElementById('display-tax').textContent = formatMoney(data.tax_amount);
        document.getElementById('display-total').textContent = formatMoney(data.total);

        if (data.valid_until) {
            var vu = document.getElementById('valid-until');
            vu.textContent = (t[currentLang].validUntil || 'Valid until') + ': ' + data.valid_until;
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
                             data.status === 'partial' ? (t[currentLang].approved || 'Partially Approved') :
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

async function submitApproval() {
    var btn = document.getElementById('approve-btn');
    btn.disabled = true;
    btn.querySelector('span').textContent = t[currentLang].submitting || 'Submitting...';

    try {
        var res = await fetch('/api/estimate-approve.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                token: estimateToken,
                approvals: itemApprovals
            })
        });
        var json = await res.json();

        if (!json.success) {
            btn.disabled = false;
            btn.querySelector('span').textContent = t[currentLang].approveSelected;
            alert(json.error || 'Failed to submit. Please try again.');
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
        alert('Network error. Please try again.');
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
