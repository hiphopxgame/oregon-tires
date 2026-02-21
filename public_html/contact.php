<?php
declare(strict_types=1);

/**
 * Oregon Tires — Contact Page
 * Standalone contact page using Form Kit.
 */

require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getDB();

$formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';
require_once $formKitPath . '/loader.php';

FormManager::init($pdo, [
    'site_key'        => 'oregon.tires',
    'recipient_email' => $_ENV['CONTACT_EMAIL'] ?? '',
    'subject_prefix'  => '[Oregon Tires]',
    'mail_from'       => $_ENV['SMTP_FROM'] ?? '',
    'mail_from_name'  => $_ENV['SMTP_FROM_NAME'] ?? 'Oregon Tires Auto Care',
    'mail_helper_path' => __DIR__ . '/includes/mail.php',
    'success_message' => 'Thank you for your message. We will get back to you soon.',
]);

$appUrl = htmlspecialchars($_ENV['APP_URL'] ?? 'https://oregon.tires', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Oregon Tires Auto Care</title>
    <meta name="description" content="Contact Oregon Tires Auto Care in Portland, OR. Professional bilingual automotive service.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= $appUrl ?>/contact">
    <link rel="icon" href="/assets/favicon.ico" sizes="any">
    <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="/assets/styles.css">
    <script>if(localStorage.getItem('oregontires_dark')==='true'||(!localStorage.getItem('oregontires_dark')&&window.matchMedia('(prefers-color-scheme:dark)').matches))document.documentElement.classList.add('dark');</script>
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
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="grid lg:grid-cols-5 gap-12">
            <!-- Contact Info -->
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2" data-t="contactTitle">Contact Us</h1>
                <p class="text-gray-500 dark:text-gray-400 mb-8" data-t="contactDesc">Get in touch with us for all your automotive needs.</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white" data-t="phoneLabel">Phone</p>
                            <a href="tel:5033679714" class="text-green-600 dark:text-green-400 hover:underline">(503) 367-9714</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white" data-t="locationLabel">Location</p>
                            <p class="text-gray-600 dark:text-gray-400">8536 SE 82nd Ave<br>Portland, OR 97266</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white" data-t="hoursLabel">Business Hours</p>
                            <p class="text-gray-600 dark:text-gray-400"><span data-t="hoursValue">Mon-Sat: 7AM - 7PM</span><br><span data-t="sundayHours">Sunday: Closed</span></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white" data-t="emailLabel">Email</p>
                            <a href="mailto:oregontirespdx@gmail.com" class="text-green-600 dark:text-green-400 hover:underline">oregontirespdx@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Kit Contact Form -->
            <div class="lg:col-span-3">
                <?php
                $formConfig = [
                    'site_key'       => 'oregon.tires',
                    'api_base'       => '/api/form',
                    'form_type'      => 'contact',
                    'show_phone'     => true,
                    'show_subject'   => false,
                    'honeypot_field' => '_hp_email',
                    'lang'           => 'en',
                    'theme'          => 'dark',
                    'accent_color'   => '#16a34a',
                    'success_message' => 'Thank you for your message. We will get back to you soon.',
                    'translations'   => [
                        'en' => [
                            'form_title'    => 'Send Us a Message',
                            'form_subtitle' => 'We typically respond within 24 hours',
                        ],
                        'es' => [
                            'form_title'    => 'Env&iacute;enos un Mensaje',
                            'form_subtitle' => 'Normalmente respondemos dentro de 24 horas',
                        ],
                    ],
                ];
                include FormManager::resolveTemplate('form/contact.php');
                ?>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-gray-100 dark:bg-[#111827] border-t border-gray-200 dark:border-gray-800 py-6">
    <div class="container mx-auto px-4 text-center text-sm text-gray-500 dark:text-gray-500">
        &copy; <?= date('Y') ?> Oregon Tires Auto Care. All rights reserved.
    </div>
</footer>

<script>
// Language system (matches site pattern)
var currentLang = localStorage.getItem('oregontires_lang') || (navigator.language.startsWith('es') ? 'es' : 'en');

var pageTranslations = {
    en: {
        backToHome: 'Back to Home',
        contactTitle: 'Contact Us',
        contactDesc: 'Get in touch with us for all your automotive needs.',
        phoneLabel: 'Phone',
        locationLabel: 'Location',
        hoursLabel: 'Business Hours',
        hoursValue: 'Mon-Sat: 7AM - 7PM',
        sundayHours: 'Sunday: Closed',
        emailLabel: 'Email',
    },
    es: {
        backToHome: 'Volver al Inicio',
        contactTitle: 'Contactenos',
        contactDesc: 'Pongase en contacto con nosotros para todas sus necesidades automotrices.',
        phoneLabel: 'Telefono',
        locationLabel: 'Ubicacion',
        hoursLabel: 'Horario de Atencion',
        hoursValue: 'Lun-Sab: 7AM - 7PM',
        sundayHours: 'Domingo: Cerrado',
        emailLabel: 'Correo Electronico',
    }
};

function applyPageLanguage() {
    var dict = pageTranslations[currentLang] || pageTranslations.en;
    document.querySelectorAll('[data-t]').forEach(function(el) {
        var key = el.getAttribute('data-t');
        if (dict[key]) {
            el.textContent = dict[key];
        }
    });
    var toggle = document.getElementById('lang-toggle');
    if (toggle) toggle.textContent = currentLang === 'en' ? 'EN | ES' : 'ES | EN';
}

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'es' : 'en';
    localStorage.setItem('oregontires_lang', currentLang);
    applyPageLanguage();
    // Also toggle the Form Kit form if it has its own language system
    if (typeof window.fkSetLanguage === 'function') {
        window.fkSetLanguage(currentLang);
    }
}

applyPageLanguage();
</script>
</body>
</html>
