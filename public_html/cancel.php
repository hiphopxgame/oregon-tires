<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Appointment - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/favicon.ico" sizes="any">
    <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="/assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-white dark:bg-[#0A0A0A] min-h-screen flex flex-col">

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#111827]/90 backdrop-blur border-b border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="/assets/logo.webp" alt="Oregon Tires" class="h-10 w-10 rounded-lg" width="40" height="40">
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

        <!-- Appointment Details + Confirm Cancel -->
        <div id="confirm-state" class="hidden">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 shadow-sm">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white" data-t="cancelTitle">Cancel Appointment</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1" data-t="cancelDesc">Are you sure you want to cancel this appointment?</p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400 text-sm" data-t="refLabel">Reference</span>
                        <span id="detail-ref" class="font-semibold text-gray-900 dark:text-white text-sm"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400 text-sm" data-t="serviceLabel">Service</span>
                        <span id="detail-service" class="font-semibold text-gray-900 dark:text-white text-sm"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400 text-sm" data-t="dateLabel">Date</span>
                        <span id="detail-date" class="font-semibold text-gray-900 dark:text-white text-sm"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400 text-sm" data-t="timeLabel">Time</span>
                        <span id="detail-time" class="font-semibold text-gray-900 dark:text-white text-sm"></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500 dark:text-gray-400 text-sm" data-t="customerLabel">Customer</span>
                        <span id="detail-customer" class="font-semibold text-gray-900 dark:text-white text-sm"></span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="/" class="flex-1 px-4 py-3 text-center border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition" data-t="keepBtn">Keep Appointment</a>
                    <button id="cancel-btn" onclick="confirmCancel()" class="flex-1 px-4 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition" data-t="cancelBtn">Confirm Cancellation</button>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div id="success-state" class="hidden text-center py-16">
            <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2" data-t="successTitle">Appointment Cancelled</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-6" data-t="successDesc">Your appointment has been cancelled. A confirmation email has been sent.</p>
            <a href="/" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition" data-t="bookNew">Book New Appointment</a>
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
        cancelTitle: 'Cancel Appointment',
        cancelDesc: 'Are you sure you want to cancel this appointment?',
        refLabel: 'Reference',
        serviceLabel: 'Service',
        dateLabel: 'Date',
        timeLabel: 'Time',
        customerLabel: 'Customer',
        keepBtn: 'Keep Appointment',
        cancelBtn: 'Confirm Cancellation',
        successTitle: 'Appointment Cancelled',
        successDesc: 'Your appointment has been cancelled. A confirmation email has been sent.',
        bookNew: 'Book New Appointment',
        cancelling: 'Cancelling...',
    },
    es: {
        backToHome: 'Volver al Inicio',
        loading: 'Cargando detalles de la cita...',
        errorTitle: 'Enlace Invalido',
        goHome: 'Ir al Inicio',
        cancelTitle: 'Cancelar Cita',
        cancelDesc: 'Esta seguro que desea cancelar esta cita?',
        refLabel: 'Referencia',
        serviceLabel: 'Servicio',
        dateLabel: 'Fecha',
        timeLabel: 'Hora',
        customerLabel: 'Cliente',
        keepBtn: 'Mantener Cita',
        cancelBtn: 'Confirmar Cancelacion',
        successTitle: 'Cita Cancelada',
        successDesc: 'Su cita ha sido cancelada. Se ha enviado un correo de confirmacion.',
        bookNew: 'Reservar Nueva Cita',
        cancelling: 'Cancelando...',
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
    ['loading-state', 'error-state', 'confirm-state', 'success-state'].forEach(function(id) {
        document.getElementById(id).classList.toggle('hidden', id !== state);
    });
}

function showError(msg) {
    document.getElementById('error-message').textContent = msg;
    showState('error-state');
}

// Get token from URL
var params = new URLSearchParams(window.location.search);
var token = params.get('token') || '';

if (!token) {
    showError(currentLang === 'es' ? 'No se proporcion\u00f3 un enlace de cancelaci\u00f3n.' : 'No cancellation link provided.');
} else {
    // Fetch appointment details
    fetch('/api/appointment-cancel.php?token=' + encodeURIComponent(token), { credentials: 'include' })
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
            document.getElementById('detail-customer').textContent = d.customer_name;
            showState('confirm-state');
        })
        .catch(function() {
            showError(currentLang === 'es' ? 'Error al cargar los detalles.' : 'Failed to load appointment details.');
        });
}

function confirmCancel() {
    var btn = document.getElementById('cancel-btn');
    var dict = pageTranslations[currentLang] || pageTranslations.en;
    btn.disabled = true;
    btn.textContent = dict.cancelling;
    btn.classList.add('opacity-50', 'cursor-not-allowed');

    fetch('/api/appointment-cancel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ token: token })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showState('success-state');
        } else {
            showError(res.error || 'Cancellation failed.');
        }
    })
    .catch(function() {
        showError(currentLang === 'es' ? 'Error al cancelar la cita.' : 'Failed to cancel appointment.');
    });
}

applyPageLanguage();
</script>
</body>
</html>
