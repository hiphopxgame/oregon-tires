<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="assets/favicon.ico" sizes="any">
    <link rel="icon" href="assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
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
<main class="flex-1 py-12">
    <div class="container mx-auto px-4 max-w-lg">

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-16">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-500 dark:text-gray-400" data-t="loading">Loading appointment details...</p>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden text-center py-16">
            <div class="w-16 h-16 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2" data-t="errorTitle">Invalid Link</h2>
            <p id="error-message" class="text-gray-500 dark:text-gray-400 mb-6"></p>
            <a href="/" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition" data-t="goHome">Go to Homepage</a>
        </div>

        <!-- Reschedule Form -->
        <div id="reschedule-state" class="hidden">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 shadow-sm">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white" data-t="rescheduleTitle">Reschedule Appointment</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1" data-t="rescheduleDesc">Select a new date and time for your appointment.</p>
                </div>

                <!-- Current Appointment Info -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-6">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2" data-t="currentAppt">Current Appointment</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-400 dark:text-gray-500" data-t="refLabel">Reference:</span>
                            <span id="detail-ref" class="text-gray-900 dark:text-white font-medium ml-1"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 dark:text-gray-500" data-t="serviceLabel">Service:</span>
                            <span id="detail-service" class="text-gray-900 dark:text-white font-medium ml-1"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 dark:text-gray-500" data-t="dateLabel">Date:</span>
                            <span id="detail-date" class="text-gray-900 dark:text-white font-medium ml-1"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 dark:text-gray-500" data-t="timeLabel">Time:</span>
                            <span id="detail-time" class="text-gray-900 dark:text-white font-medium ml-1"></span>
                        </div>
                    </div>
                </div>

                <!-- New Date/Time Selection -->
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="newDateLabel">New Date</label>
                        <input type="date" id="new-date" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" data-t="newTimeLabel">New Time</label>
                        <select id="new-time" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="" data-t="selectTime">Select a time...</option>
                        </select>
                    </div>
                </div>

                <div id="form-error" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm"></div>

                <div class="flex gap-3">
                    <a href="/" class="flex-1 px-4 py-3 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition" data-t="keepBtn">Keep Current</a>
                    <button id="reschedule-btn" onclick="confirmReschedule()" class="flex-1 px-4 py-3 bg-amber-500 text-white font-semibold rounded-xl hover:bg-amber-600 transition" data-t="rescheduleBtn">Confirm Reschedule</button>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div id="success-state" class="hidden text-center py-16">
            <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2" data-t="successTitle">Appointment Rescheduled</h2>
            <p id="success-details" class="text-gray-500 dark:text-gray-400 mb-6"></p>
            <a href="/" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition" data-t="goHome">Go to Homepage</a>
        </div>

    </div>
</main>

<!-- Footer -->
<footer class="bg-gray-100 dark:bg-[#111827] border-t border-gray-200 dark:border-gray-800 py-6">
    <div class="container mx-auto px-4 text-center text-sm text-gray-500 dark:text-gray-500">
        &copy; <?php echo date('Y'); ?> Oregon Tires Auto Care. All rights reserved.
    </div>
</footer>

<script>
var currentLang = localStorage.getItem('oregontires_lang') || (navigator.language.startsWith('es') ? 'es' : 'en');

var pageTranslations = {
    en: {
        backToHome: 'Back to Home',
        loading: 'Loading appointment details...',
        errorTitle: 'Invalid Link',
        goHome: 'Go to Homepage',
        rescheduleTitle: 'Reschedule Appointment',
        rescheduleDesc: 'Select a new date and time for your appointment.',
        currentAppt: 'Current Appointment',
        refLabel: 'Reference:',
        serviceLabel: 'Service:',
        dateLabel: 'Date:',
        timeLabel: 'Time:',
        newDateLabel: 'New Date',
        newTimeLabel: 'New Time',
        selectTime: 'Select a time...',
        keepBtn: 'Keep Current',
        rescheduleBtn: 'Confirm Reschedule',
        successTitle: 'Appointment Rescheduled',
        rescheduling: 'Rescheduling...',
        selectBoth: 'Please select both a new date and time.',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando detalles de la cita...',
        errorTitle: 'Enlace Invalido',
        goHome: 'Ir al Inicio',
        rescheduleTitle: 'Reprogramar Cita',
        rescheduleDesc: 'Seleccione una nueva fecha y hora para su cita.',
        currentAppt: 'Cita Actual',
        refLabel: 'Referencia:',
        serviceLabel: 'Servicio:',
        dateLabel: 'Fecha:',
        timeLabel: 'Hora:',
        newDateLabel: 'Nueva Fecha',
        newTimeLabel: 'Nueva Hora',
        selectTime: 'Seleccione una hora...',
        keepBtn: 'Mantener Actual',
        rescheduleBtn: 'Confirmar Reprogramacion',
        successTitle: 'Cita Reprogramada',
        rescheduling: 'Reprogramando...',
        selectBoth: 'Por favor seleccione una nueva fecha y hora.',
    }
};

function applyPageLanguage() {
    var dict = pageTranslations[currentLang] || pageTranslations.en;
    document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (dict[key]) el.textContent = dict[key];
    });
    var toggle = document.getElementById('lang-toggle');
    if (toggle) toggle.textContent = currentLang === 'en' ? 'EN | ES' : 'ES | EN';
}

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    localStorage.setItem('oregontires_lang', currentLang);
    applyPageLanguage();
}

function showState(state) {
    ['loading-state', 'error-state', 'reschedule-state', 'success-state'].forEach(function(id) {
        document.getElementById(id).classList.toggle('hidden', id !== state);
    });
}

function showError(msg) {
    document.getElementById('error-message').textContent = msg;
    showState('error-state');
}

function showFormError(msg) {
    var el = document.getElementById('form-error');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function hideFormError() {
    document.getElementById('form-error').classList.add('hidden');
}

// Build time slots (Oregon Tires: 7:00 AM - 6:00 PM)
function buildTimeSlots() {
    var select = document.getElementById('new-time');
    // Keep the first "Select a time" option
    while (select.options.length > 1) select.remove(1);

    for (var h = 7; h <= 18; h++) {
        var ampm = h >= 12 ? 'PM' : 'AM';
        var hour12 = h > 12 ? h - 12 : (h === 0 ? 12 : h);
        var value = (h < 10 ? '0' : '') + h + ':00';
        var label = hour12 + ':00 ' + ampm;
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        select.appendChild(opt);

        if (h < 18) {
            var value30 = (h < 10 ? '0' : '') + h + ':30';
            var label30 = hour12 + ':30 ' + ampm;
            var opt30 = document.createElement('option');
            opt30.value = value30;
            opt30.textContent = label30;
            select.appendChild(opt30);
        }
    }
}

// Set minimum date to tomorrow and disable Sundays
function setupDatePicker() {
    var dateInput = document.getElementById('new-date');
    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = tomorrow.toISOString().split('T')[0];

    dateInput.addEventListener('change', function() {
        hideFormError();
        var d = new Date(this.value + 'T12:00:00');
        if (d.getDay() === 0) {
            var dict = pageTranslations[currentLang] || pageTranslations.en;
            showFormError(currentLang === 'es' ? 'No abrimos los domingos.' : 'We are closed on Sundays.');
            this.value = '';
        }
    });
}

var params = new URLSearchParams(window.location.search);
var token = params.get('token') || '';

if (!token) {
    showError(currentLang === 'es' ? 'No se proporcion\u00f3 un enlace.' : 'No reschedule link provided.');
} else {
    fetch('/api/appointment-reschedule.php?token=' + encodeURIComponent(token), { credentials: 'include' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success) {
                showError(res.error || 'Invalid link.');
                return;
            }
            var d = res.data;
            document.getElementById('detail-ref').textContent = d.reference_number;
            document.getElementById('detail-service').textContent = d.service;
            document.getElementById('detail-date').textContent = d.date;
            document.getElementById('detail-time').textContent = d.time;

            buildTimeSlots();
            setupDatePicker();
            showState('reschedule-state');
        })
        .catch(function() {
            showError(currentLang === 'es' ? 'Error al cargar los detalles.' : 'Failed to load appointment details.');
        });
}

function confirmReschedule() {
    hideFormError();
    var dict = pageTranslations[currentLang] || pageTranslations.en;

    var newDate = document.getElementById('new-date').value;
    var newTime = document.getElementById('new-time').value;

    if (!newDate || !newTime) {
        showFormError(dict.selectBoth);
        return;
    }

    var btn = document.getElementById('reschedule-btn');
    btn.disabled = true;
    btn.textContent = dict.rescheduling;
    btn.classList.add('opacity-50', 'cursor-not-allowed');

    fetch('/api/appointment-reschedule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ token: token, preferred_date: newDate, preferred_time: newTime })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var d = res.data;
            var successMsg = currentLang === 'es'
                ? 'Su cita ha sido reprogramada para ' + d.new_date + ' a las ' + d.new_time + '. Se ha enviado un correo de confirmaci\u00f3n.'
                : 'Your appointment has been rescheduled to ' + d.new_date + ' at ' + d.new_time + '. A confirmation email has been sent.';
            document.getElementById('success-details').textContent = successMsg;
            showState('success-state');
        } else {
            showFormError(res.error || 'Reschedule failed.');
            btn.disabled = false;
            btn.textContent = dict.rescheduleBtn;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    })
    .catch(function() {
        showFormError(currentLang === 'es' ? 'Error al reprogramar.' : 'Failed to reschedule.');
        btn.disabled = false;
        btn.textContent = dict.rescheduleBtn;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}

applyPageLanguage();
</script>
</body>
</html>
