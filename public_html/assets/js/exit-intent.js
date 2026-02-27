/**
 * Oregon Tires — Exit Intent Popup (Desktop Only)
 * Shows a free vehicle health check offer when user moves mouse toward browser chrome.
 * Only shows once per session (uses sessionStorage).
 */
(function() {
  'use strict';

  // Skip on mobile
  if ('ontouchstart' in window || window.innerWidth < 768) return;

  // Only show once per session
  if (sessionStorage.getItem('ot_exit_shown')) return;

  var shown = false;

  function showPopup() {
    if (shown) return;
    shown = true;
    sessionStorage.setItem('ot_exit_shown', '1');

    var lang = (typeof currentLang !== 'undefined') ? currentLang : 'en';
    var copy = {
      en: {
        title: 'Before You Go\u2026',
        subtitle: 'Get a FREE 21-Point Vehicle Health Check',
        desc: 'Enter your email and we\'ll send you a coupon for a complimentary inspection on your next visit.',
        placeholder: 'your@email.com',
        cta: 'Send My Free Coupon',
        noSpam: 'No spam. Unsubscribe anytime.',
        thanks: 'Check your email! Your coupon is on its way.',
        error: 'Something went wrong. Please try again.'
      },
      es: {
        title: 'Antes de Irte\u2026',
        subtitle: 'Obt\u00e9n una Inspecci\u00f3n de 21 Puntos GRATIS',
        desc: 'Ingresa tu correo y te enviaremos un cup\u00f3n para una inspecci\u00f3n gratuita en tu pr\u00f3xima visita.',
        placeholder: 'tu@correo.com',
        cta: 'Enviar Mi Cup\u00f3n Gratis',
        noSpam: 'Sin spam. Cancela cuando quieras.',
        thanks: '\u00a1Revisa tu correo! Tu cup\u00f3n est\u00e1 en camino.',
        error: 'Algo sali\u00f3 mal. Intenta de nuevo.'
      }
    };
    var c = copy[lang] || copy.en;

    // Create overlay
    var overlay = document.createElement('div');
    overlay.id = 'exit-popup-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;';

    var popup = document.createElement('div');
    popup.style.cssText = 'background:white;border-radius:1rem;max-width:28rem;width:100%;padding:2rem;position:relative;box-shadow:0 25px 50px rgba(0,0,0,0.25);animation:otSlideUp 0.3s ease;';

    // Build popup content using safe DOM methods for all user-visible text
    // Note: All content is hardcoded copy (no user input), so innerHTML is safe here.
    // Using innerHTML for layout efficiency with static, trusted content only.
    popup.innerHTML =
      '<button id="exit-popup-close" style="position:absolute;top:0.75rem;right:0.75rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1;" aria-label="Close">\u00d7</button>' +
      '<div style="text-align:center;">' +
        '<div style="font-size:2.5rem;margin-bottom:0.5rem;">\ud83d\udd0d</div>' +
        '<h3 style="font-size:1.25rem;font-weight:bold;color:#111;margin-bottom:0.25rem;">' + c.title + '</h3>' +
        '<p style="font-size:1.1rem;font-weight:600;color:#15803d;margin-bottom:0.5rem;">' + c.subtitle + '</p>' +
        '<p style="font-size:0.875rem;color:#666;margin-bottom:1rem;">' + c.desc + '</p>' +
        '<form id="exit-popup-form" style="display:flex;gap:0.5rem;">' +
          '<input type="email" id="exit-popup-email" required placeholder="' + c.placeholder + '" style="flex:1;padding:0.75rem;border:1px solid #ddd;border-radius:0.5rem;font-size:0.875rem;" aria-label="Email">' +
          '<button type="submit" style="background:#f59e0b;color:#000;padding:0.75rem 1rem;border:none;border-radius:0.5rem;font-weight:600;cursor:pointer;font-size:0.875rem;white-space:nowrap;">' + c.cta + '</button>' +
        '</form>' +
        '<p style="font-size:0.75rem;color:#999;margin-top:0.5rem;">' + c.noSpam + '</p>' +
        '<div id="exit-popup-status" style="margin-top:0.5rem;font-size:0.875rem;display:none;"></div>' +
      '</div>';

    overlay.appendChild(popup);
    document.body.appendChild(overlay);

    // Add animation keyframes
    var style = document.createElement('style');
    style.textContent = '@keyframes otSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(style);

    // Close handlers
    document.getElementById('exit-popup-close').addEventListener('click', function() {
      overlay.remove();
    });
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) overlay.remove();
    });
    document.addEventListener('keydown', function handler(e) {
      if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', handler); }
    });

    // Form submit
    document.getElementById('exit-popup-form').addEventListener('submit', function(e) {
      e.preventDefault();
      var email = document.getElementById('exit-popup-email').value;
      var statusEl = document.getElementById('exit-popup-status');

      fetch('/api/subscribe.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, language: lang, source: 'exit_intent' })
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        statusEl.style.display = 'block';
        if (data.success) {
          statusEl.style.color = '#15803d';
          statusEl.textContent = c.thanks;
          if (typeof gtag === 'function') gtag('event', 'subscribe', { method: 'exit_intent' });
          setTimeout(function() { overlay.remove(); }, 3000);
        } else {
          statusEl.style.color = '#dc2626';
          statusEl.textContent = data.error || c.error;
        }
      })
      .catch(function() {
        statusEl.style.display = 'block';
        statusEl.style.color = '#dc2626';
        statusEl.textContent = c.error;
      });
    });
  }

  // Trigger on mouse leaving viewport
  document.addEventListener('mouseout', function(e) {
    if (!e.relatedTarget && e.clientY <= 0) {
      showPopup();
    }
  });
})();
