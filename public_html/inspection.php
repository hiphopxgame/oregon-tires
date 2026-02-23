<!DOCTYPE html>
<html lang="en" class="dark">
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
    @media print {
        /* Force light background and black text */
        html, body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dark { color-scheme: light !important; }

        /* Hide non-content elements */
        header, footer, #lang-toggle, #photo-overlay, #estimate-cta, #print-report-btn { display: none !important; }

        /* Remove dark mode overrides for print */
        .dark\:bg-\[#0A0A0A\], .dark\:bg-gray-900, .dark\:bg-gray-800\/50, .dark\:bg-\[#111827\]\/90 { background: white !important; }
        .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 { color: #111 !important; }
        .dark\:border-gray-800, .dark\:border-gray-700, .dark\:border-gray-700\/50 { border-color: #e5e7eb !important; }

        /* Print header */
        #report-state::before {
            content: "Oregon Tires Auto Care \2014  Digital Vehicle Inspection";
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

        /* Dark mode traffic light overrides */
        .dark\:bg-green-900\/20 { background-color: #dcfce7 !important; }
        .dark\:bg-yellow-900\/20 { background-color: #fef9c3 !important; }
        .dark\:bg-red-900\/20 { background-color: #fee2e2 !important; }
        .dark\:bg-green-900\/40 { background-color: #bbf7d0 !important; }
        .dark\:bg-yellow-900\/40 { background-color: #fde68a !important; }
        .dark\:bg-red-900\/40 { background-color: #fecaca !important; }
        .dark\:text-green-400 { color: #16a34a !important; }
        .dark\:text-yellow-400 { color: #ca8a04 !important; }
        .dark\:text-red-400 { color: #dc2626 !important; }

        /* Photos at reasonable size */
        #items-container img { width: 60px !important; height: 60px !important; break-inside: avoid; }

        /* Avoid breaking inside category sections */
        #items-container > div { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12pt; }

        /* Overall badge */
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

            <!-- Print Button -->
            <div class="mb-6 text-center" id="print-report-btn">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span data-t="printReport">Print Report</span>
                </button>
            </div>

            <!-- Inspection Items by Category -->
            <div id="items-container" class="space-y-4 mb-6"></div>

            <!-- Estimate CTA -->
            <div id="estimate-cta" class="hidden">
                <a id="estimate-link" href="#" class="block w-full text-center px-6 py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition text-lg shadow-lg">
                    <span data-t="viewEstimate">Review & Approve Estimate</span>
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
    <div class="max-w-3xl w-full">
        <img id="photo-overlay-img" src="" class="w-full rounded-xl" alt="">
        <p id="photo-overlay-caption" class="text-white text-center mt-3 text-sm"></p>
    </div>
</div>

<!-- Footer -->
<footer class="py-6 text-center text-xs text-gray-400 dark:text-gray-600">
    <p>&copy; 2026 Oregon Tires Auto Care. All rights reserved.</p>
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
        printReport: 'Print Report',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando reporte de inspeccion...',
        errorTitle: 'Enlace Invalido',
        goHome: 'Ir al Inicio',
        inspTitle: 'Reporte de Inspeccion del Vehiculo',
        roLabel: 'OT:',
        good: 'Bueno',
        attention: 'Atencion Pronto',
        urgent: 'Urgente / Seguridad',
        viewEstimate: 'Revisar y Aprobar Presupuesto',
        techNotes: 'Notas del Tecnico',
        printReport: 'Imprimir Reporte',
    }
};

const overallLabels = { en: { green: 'GOOD', yellow: 'ATTENTION', red: 'URGENT' }, es: { green: 'BUENO', yellow: 'ATENCION', red: 'URGENTE' } };

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

var categoryLabels = {
    en: {tires:'Tires',brakes:'Brakes',suspension:'Suspension',fluids:'Fluids',lights:'Lights',engine:'Engine',exhaust:'Exhaust',hoses:'Hoses',belts:'Belts',battery:'Battery',wipers:'Wipers',other:'Other'},
    es: {tires:'Neumaticos',brakes:'Frenos',suspension:'Suspension',fluids:'Fluidos',lights:'Luces',engine:'Motor',exhaust:'Escape',hoses:'Mangueras',belts:'Correas',battery:'Bateria',wipers:'Limpiaparabrisas',other:'Otro'}
};

var ratingStyles = {
    green:  { bg: 'bg-green-50 dark:bg-green-900/20', border: 'border-green-200 dark:border-green-800', dot: 'bg-green-500', text: 'text-green-700 dark:text-green-400' },
    yellow: { bg: 'bg-yellow-50 dark:bg-yellow-900/20', border: 'border-yellow-200 dark:border-yellow-800', dot: 'bg-yellow-500', text: 'text-yellow-700 dark:text-yellow-400' },
    red:    { bg: 'bg-red-50 dark:bg-red-900/20', border: 'border-red-200 dark:border-red-800', dot: 'bg-red-500', text: 'text-red-700 dark:text-red-400' },
};

function showPhoto(url, caption) {
    var overlay = document.getElementById('photo-overlay');
    document.getElementById('photo-overlay-img').src = url;
    document.getElementById('photo-overlay-caption').textContent = caption || '';
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}

function closePhoto() {
    var overlay = document.getElementById('photo-overlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
}

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
        photoWrap.className = 'flex gap-2 mt-2 ml-6 overflow-x-auto';
        item.photos.forEach(function(p) {
            var img = document.createElement('img');
            img.src = p.image_url;
            img.alt = p.caption || '';
            img.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer flex-shrink-0';
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

        if (data.customer_language === 'spanish') {
            currentLang = 'es';
            applyTranslations();
        }

        document.getElementById('ro-number').textContent = data.ro_number;
        document.getElementById('vehicle-name').textContent = data.vehicle || 'Vehicle';

        if (data.vin) { var el = document.getElementById('vehicle-vin'); el.textContent = 'VIN: ' + data.vin; el.classList.remove('hidden'); }
        if (data.vehicle_color) { var el2 = document.getElementById('vehicle-color'); el2.textContent = data.vehicle_color; el2.classList.remove('hidden'); }
        if (data.license_plate) { var el3 = document.getElementById('vehicle-plate'); el3.textContent = data.license_plate; el3.classList.remove('hidden'); }

        // Overall badge
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

        // Group items by category
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

            // Category header
            var header = document.createElement('div');
            header.className = 'px-5 py-3 flex items-center justify-between border-b ' + colors.border;
            header.appendChild(createTextEl('h2', catLabel, 'font-bold text-gray-900 dark:text-white text-lg'));
            var headerDot = document.createElement('span');
            headerDot.className = 'w-3 h-3 rounded-full ' + colors.dot;
            header.appendChild(headerDot);
            section.appendChild(header);

            // Items
            var itemsList = document.createElement('div');
            itemsList.className = 'divide-y divide-gray-200 dark:divide-gray-700/50';
            items.forEach(function(item) {
                itemsList.appendChild(buildItemRow(item));
            });
            section.appendChild(itemsList);

            container.appendChild(section);
        });

        // Estimate CTA
        if (data.estimate_token) {
            document.getElementById('estimate-link').href = '/approve.php?token=' + encodeURIComponent(data.estimate_token);
            document.getElementById('estimate-cta').classList.remove('hidden');
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
