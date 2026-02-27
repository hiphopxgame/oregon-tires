(function() {
  'use strict';

  var STORAGE_KEY = 'oregontires_cookie_consent';

  // Already consented or declined — do nothing
  if (localStorage.getItem(STORAGE_KEY)) return;

  // Set default consent (deny until accepted)
  if (typeof gtag === 'function') {
    gtag('consent', 'default', {
      analytics_storage: 'denied',
      ad_storage: 'denied'
    });
  }

  // Detect language
  var lang = localStorage.getItem('oregontires_lang') || 'en';
  var isEs = lang === 'es';

  // Detect reduced motion preference
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Detect dark mode
  var isDark = document.documentElement.classList.contains('dark');

  // Build banner DOM
  var banner = document.createElement('div');
  banner.setAttribute('role', 'dialog');
  banner.setAttribute('aria-label', isEs ? 'Consentimiento de cookies' : 'Cookie consent');
  banner.className = 'fixed bottom-0 left-0 right-0 z-[60] p-4 shadow-[0_-4px_12px_rgba(0,0,0,0.15)]';
  banner.style.backgroundColor = isDark ? '#1f2937' : '#ffffff';
  banner.style.borderTop = isDark ? '1px solid #374151' : '1px solid #e5e7eb';

  // Animate in (slide up)
  if (!prefersReducedMotion) {
    banner.style.transform = 'translateY(100%)';
    banner.style.transition = 'transform 0.3s ease-out';
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        banner.style.transform = 'translateY(0)';
      });
    });
  }

  // Build inner wrapper
  var wrapper = document.createElement('div');
  wrapper.className = 'container mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3';

  // Text
  var text = document.createElement('p');
  text.className = 'text-sm ' + (isDark ? 'text-gray-300' : 'text-gray-600');
  text.textContent = isEs
    ? 'Usamos cookies para mejorar su experiencia y analizar el tr\u00e1fico del sitio.'
    : 'We use cookies to improve your experience and analyze site traffic.';

  // Button wrapper
  var btnWrap = document.createElement('div');
  btnWrap.className = 'flex gap-2 shrink-0';

  // Accept button
  var acceptBtn = document.createElement('button');
  acceptBtn.className = 'px-4 py-2 text-sm font-semibold rounded-lg bg-green-700 text-white hover:bg-green-800 transition';
  acceptBtn.textContent = isEs ? 'Aceptar Todo' : 'Accept All';

  // Decline button
  var declineBtn = document.createElement('button');
  declineBtn.className = 'px-4 py-2 text-sm font-semibold rounded-lg border ' +
    (isDark ? 'border-gray-500 text-gray-300 hover:bg-gray-700' : 'border-gray-300 text-gray-600 hover:bg-gray-100') + ' transition';
  declineBtn.textContent = isEs ? 'Rechazar' : 'Decline';

  // Assemble DOM
  btnWrap.appendChild(acceptBtn);
  btnWrap.appendChild(declineBtn);
  wrapper.appendChild(text);
  wrapper.appendChild(btnWrap);
  banner.appendChild(wrapper);

  // Dismiss handler
  function dismiss(accepted) {
    localStorage.setItem(STORAGE_KEY, accepted ? 'accepted' : 'declined');
    if (accepted && typeof gtag === 'function') {
      gtag('consent', 'update', {
        analytics_storage: 'granted',
        ad_storage: 'granted'
      });
    }
    if (!prefersReducedMotion) {
      banner.style.transform = 'translateY(100%)';
      setTimeout(function() { banner.remove(); }, 300);
    } else {
      banner.remove();
    }
  }

  // Event listeners
  acceptBtn.addEventListener('click', function() { dismiss(true); });
  declineBtn.addEventListener('click', function() { dismiss(false); });

  // Append to body
  document.body.appendChild(banner);
})();
