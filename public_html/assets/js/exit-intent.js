/**
 * Oregon Tires — Exit Intent Popup (Desktop Only)
 * Shows a promotional offer when user moves mouse toward browser chrome.
 * Content is fetched from admin-managed promotions API.
 * Only shows once per session (uses sessionStorage).
 */
(function() {
  'use strict';

  // Skip on mobile
  if ('ontouchstart' in window || window.innerWidth < 768) return;

  // Only show once per session
  if (sessionStorage.getItem('ot_exit_shown')) return;

  var shown = false;
  var promoData = null;

  // Fetch exit-intent promotion from API on page load
  fetch('/api/promotions.php?placement=exit_intent', { credentials: 'include' })
    .then(function(r) { return r.json(); })
    .then(function(json) {
      if (json.success && json.data) {
        promoData = json.data;
      }
    })
    .catch(function() { /* silently skip — popup won't show */ });

  function showPopup() {
    if (shown || !promoData) return;
    shown = true;
    sessionStorage.setItem('ot_exit_shown', '1');

    var lang = (typeof currentLang !== 'undefined') ? currentLang : 'en';
    var d = promoData;

    var title     = (lang === 'es' ? d.title_es : d.title_en) || d.title_en || '';
    var subtitle  = (lang === 'es' ? d.subtitle_es : d.subtitle_en) || d.subtitle_en || '';
    var desc      = (lang === 'es' ? d.body_es : d.body_en) || d.body_en || '';
    var cta       = (lang === 'es' ? d.cta_text_es : d.cta_text_en) || d.cta_text_en || 'Subscribe';
    var placeholder = (lang === 'es' ? d.placeholder_es : d.placeholder_en) || d.placeholder_en || 'your@email.com';
    var noSpam    = (lang === 'es' ? d.nospam_es : d.nospam_en) || d.nospam_en || '';
    var thanks    = (lang === 'es' ? d.success_msg_es : d.success_msg_en) || d.success_msg_en || 'Thank you!';
    var errorMsg  = (lang === 'es' ? d.error_msg_es : d.error_msg_en) || d.error_msg_en || 'Something went wrong.';
    var icon      = d.popup_icon || '';

    // Create overlay
    var overlay = document.createElement('div');
    overlay.id = 'exit-popup-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:var(--color-overlay, rgba(0,0,0,0.6));z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;';

    var popup = document.createElement('div');
    popup.setAttribute('role', 'dialog');
    popup.setAttribute('aria-modal', 'true');
    popup.setAttribute('aria-label', title || 'Special Offer');
    var animateSlide = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    popup.style.cssText = 'background:var(--ot-card-bg);border-radius:1rem;max-width:28rem;width:100%;padding:2rem;position:relative;box-shadow:0 25px 50px rgba(0,0,0,0.25);' + (animateSlide ? 'animation:otSlideUp 0.3s ease;' : '');

    // Close button
    var closeBtn = document.createElement('button');
    closeBtn.id = 'exit-popup-close';
    closeBtn.style.cssText = 'position:absolute;top:0.75rem;right:0.75rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--ot-text-secondary);line-height:1;';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.textContent = '\u00d7';
    popup.appendChild(closeBtn);

    // Content wrapper
    var content = document.createElement('div');
    content.style.textAlign = 'center';

    // Icon
    if (icon) {
      var iconDiv = document.createElement('div');
      iconDiv.style.cssText = 'font-size:2.5rem;margin-bottom:0.5rem;';
      iconDiv.textContent = icon;
      content.appendChild(iconDiv);
    }

    // Title
    var h3 = document.createElement('h3');
    h3.style.cssText = 'font-size:1.25rem;font-weight:bold;color:var(--ot-text-primary);margin-bottom:0.25rem;';
    h3.textContent = title;
    content.appendChild(h3);

    // Subtitle
    if (subtitle) {
      var subtitleP = document.createElement('p');
      subtitleP.style.cssText = 'font-size:1.1rem;font-weight:600;color:var(--ot-green-mid);margin-bottom:0.5rem;';
      subtitleP.textContent = subtitle;
      content.appendChild(subtitleP);
    }

    // Description
    if (desc) {
      var descP = document.createElement('p');
      descP.style.cssText = 'font-size:0.875rem;color:var(--ot-text-secondary);margin-bottom:1rem;';
      descP.textContent = desc;
      content.appendChild(descP);
    }

    // Form
    var form = document.createElement('form');
    form.id = 'exit-popup-form';
    form.style.cssText = 'display:flex;gap:0.5rem;';

    var emailInput = document.createElement('input');
    emailInput.type = 'email';
    emailInput.id = 'exit-popup-email';
    emailInput.required = true;
    emailInput.placeholder = placeholder;
    emailInput.style.cssText = 'flex:1;padding:0.75rem;border:1px solid var(--ot-input-border);border-radius:0.5rem;font-size:0.875rem;background:var(--ot-input-bg);color:var(--ot-text-primary);';
    emailInput.setAttribute('aria-label', 'Email');
    form.appendChild(emailInput);

    var submitBtn = document.createElement('button');
    submitBtn.type = 'submit';
    submitBtn.style.cssText = 'background:var(--ot-amber);color:var(--color-text, #000);padding:0.75rem 1rem;border:none;border-radius:0.5rem;font-weight:600;cursor:pointer;font-size:0.875rem;white-space:nowrap;';
    submitBtn.textContent = cta;
    form.appendChild(submitBtn);

    content.appendChild(form);

    // No spam text
    if (noSpam) {
      var noSpamP = document.createElement('p');
      noSpamP.style.cssText = 'font-size:0.75rem;color:var(--ot-text-secondary);margin-top:0.5rem;';
      noSpamP.textContent = noSpam;
      content.appendChild(noSpamP);
    }

    // Status div
    var statusEl = document.createElement('div');
    statusEl.id = 'exit-popup-status';
    statusEl.style.cssText = 'margin-top:0.5rem;font-size:0.875rem;display:none;';
    content.appendChild(statusEl);

    popup.appendChild(content);
    overlay.appendChild(popup);
    document.body.appendChild(overlay);

    // Add animation keyframes (respects reduced motion preference)
    if (animateSlide) {
      var style = document.createElement('style');
      style.textContent = '@keyframes otSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}';
      document.head.appendChild(style);
    }

    // Focus email input for keyboard users
    setTimeout(function() { emailInput.focus(); }, 100);

    // Close handlers
    closeBtn.addEventListener('click', function() {
      overlay.remove();
    });
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) overlay.remove();
    });
    document.addEventListener('keydown', function handler(e) {
      if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', handler); }
    });

    // Form submit
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var email = emailInput.value;

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
          statusEl.style.color = 'var(--ot-green-mid)';
          statusEl.textContent = thanks;
          if (typeof gtag === 'function') gtag('event', 'subscribe', { method: 'exit_intent' });
          setTimeout(function() { overlay.remove(); }, 3000);
        } else {
          statusEl.style.color = 'var(--ot-error)';
          statusEl.textContent = data.error || errorMsg;
        }
      })
      .catch(function() {
        statusEl.style.display = 'block';
        statusEl.style.color = 'var(--ot-error)';
        statusEl.textContent = errorMsg;
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
