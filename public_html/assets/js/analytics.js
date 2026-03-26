// Oregon Tires — GA4 Hybrid Loader + Error Tracking + Enhanced Event Tracking
// Extracted from index.html inline scripts

// === GA4 Hybrid Loader (configurable via admin panel) ===
(function(){
  var DEFAULT_GA = 'G-PCK6ZYFHQ0';
  var cached = null;
  try { cached = localStorage.getItem('oregontires_ga_id'); } catch(e){}
  var id = cached || DEFAULT_GA;
  var s = document.createElement('script');
  s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
  document.head.appendChild(s);
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  window.gtag = gtag;
  gtag('js', new Date());
  gtag('config', id);
  window.__gaId = id;
  window.__updateGaId = function(newId) {
    if (!newId || newId === window.__gaId) return;
    if (!/^G-[A-Z0-9]{6,12}$/.test(newId)) return;
    window.__gaId = newId;
    try { localStorage.setItem('oregontires_ga_id', newId); } catch(e){}
    gtag('config', newId, { send_page_view: false });
  };
})();

// === Basic Error Tracking via GA4 ===
window.addEventListener('error', function(e) {
  if (typeof gtag === 'function') gtag('event', 'exception', {
    description: e.message + ' at ' + (e.filename || 'unknown') + ':' + (e.lineno || 0),
    fatal: false
  });
});
window.addEventListener('unhandledrejection', function(e) {
  if (typeof gtag === 'function') gtag('event', 'exception', {
    description: 'Unhandled promise rejection: ' + (e.reason?.message || e.reason || 'unknown'),
    fatal: false
  });
});

// === GA4 Enhanced Event Tracking ===
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag !== 'function') return;

  // Scroll depth tracking
  var scrollMarks = {25: false, 50: false, 75: false, 100: false};
  window.addEventListener('scroll', function() {
    var scrollPct = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);
    [25, 50, 75, 100].forEach(function(mark) {
      if (scrollPct >= mark && !scrollMarks[mark]) {
        scrollMarks[mark] = true;
        gtag('event', 'scroll_depth', { percent: mark });
      }
    });
  }, { passive: true });

  // CTA click tracking
  document.addEventListener('click', function(e) {
    var link = e.target.closest('a[href*="book-appointment"], a[href^="tel:"]');
    if (link) {
      var label = link.href.includes('tel:') ? 'phone_call' : 'booking_click';
      var ctx = link.closest('section');
      gtag('event', 'cta_click', {
        cta_type: label,
        cta_text: link.textContent.trim().substring(0, 50),
        section: ctx ? ctx.id || 'unknown' : 'floating'
      });
    }
  });

  // FAQ accordion tracking
  document.querySelectorAll('.faq-item').forEach(function(item) {
    item.addEventListener('toggle', function() {
      if (item.open) {
        var q = item.querySelector('summary span[data-t]');
        gtag('event', 'faq_expand', { question: q ? q.textContent.trim() : 'unknown' });
      }
    });
  });

  // Contact form tracking
  var contactForm = document.getElementById('contact-form');
  if (contactForm) {
    var formStarted = false;
    contactForm.addEventListener('focusin', function() {
      if (!formStarted) { formStarted = true; gtag('event', 'form_start', { form: 'contact' }); }
    });
    contactForm.addEventListener('submit', function() { gtag('event', 'form_submit', { form: 'contact' }); });
  }

  // Subscribe form tracking
  var subForm = document.getElementById('subscribe-form');
  if (subForm) {
    subForm.addEventListener('submit', function() { gtag('event', 'form_submit', { form: 'subscribe' }); });
  }

  // Language toggle tracking
  var langToggles = document.querySelectorAll('#lang-toggle, #footer-lang-toggle');
  langToggles.forEach(function(btn) {
    btn.addEventListener('click', function() {
      gtag('event', 'language_switch', { from: currentLang, to: currentLang === 'en' ? 'es' : 'en' });
    });
  });

  // Map interaction tracking
  var mapPlaceholder = document.getElementById('map-placeholder');
  if (mapPlaceholder) {
    mapPlaceholder.addEventListener('click', function() { gtag('event', 'map_interact'); }, { once: true });
  }

  // Floating CTA visibility on scroll
  var floatingCta = document.getElementById('floating-cta');
  if (floatingCta) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 600) {
        floatingCta.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
        floatingCta.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
      } else {
        floatingCta.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
        floatingCta.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
      }
    }, { passive: true });
  }

  // Subscribe form handler
  var subscribeForm = document.getElementById('subscribe-form');
  if (subscribeForm) {
    subscribeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var email = document.getElementById('subscribe-email').value;
      var status = document.getElementById('subscribe-status');
      var lang = typeof currentLang !== 'undefined' ? currentLang : 'en';
      fetch('/api/subscribe.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, language: lang, source: 'footer_bar' })
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        status.classList.remove('hidden');
        if (data.success) {
          status.className = 'mt-3 text-sm text-green-400';
          status.textContent = lang === 'es' ? '\u00a1Gracias! Te mantendremos informado.' : 'Thanks! We\'ll keep you posted.';
          subscribeForm.reset();
          gtag('event', 'subscribe', { method: 'email' });
        } else {
          status.className = 'mt-3 text-sm text-red-400';
          status.textContent = data.error || (lang === 'es' ? 'Error. Intenta de nuevo.' : 'Error. Please try again.');
        }
      })
      .catch(function() {
        status.classList.remove('hidden');
        status.className = 'mt-3 text-sm text-red-400';
        status.textContent = lang === 'es' ? 'Error de conexi\u00f3n.' : 'Connection error.';
      });
    });
  }

  // Pricing section view tracking
  var pricingSection = document.getElementById('pricing');
  if (pricingSection && pricingSection.style.display !== 'none') {
    var pricingObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          gtag('event', 'view_pricing', { section: 'pricing_cards' });
          pricingObserver.disconnect();
        }
      });
    }, { threshold: 0.3 });
    pricingObserver.observe(pricingSection);
  }

  // Service card click tracking
  document.querySelectorAll('#pricing a[href*="book-appointment"]').forEach(function(link) {
    link.addEventListener('click', function() {
      var card = this.closest('.rounded-xl');
      var serviceName = card ? card.querySelector('[data-t]')?.textContent.trim() : 'unknown';
      gtag('event', 'select_service', { service_name: serviceName, source: 'pricing_card' });
    });
  });

  // Form field abandonment tracking
  var contactFormFields = document.querySelectorAll('#contact-form input, #contact-form textarea');
  contactFormFields.forEach(function(field) {
    field.addEventListener('blur', function() {
      if (this.value.trim()) {
        gtag('event', 'form_field_interact', { form: 'contact', field: this.name || this.id });
      }
    });
  });

  // SMS/text link tracking
  document.addEventListener('click', function(e) {
    var smsLink = e.target.closest('a[href^="sms:"]');
    if (smsLink) {
      gtag('event', 'sms_click', { section: smsLink.closest('section')?.id || 'unknown' });
    }
  });

  // UTM parameter capture
  (function() {
    var params = new URLSearchParams(window.location.search);
    var utmSource = params.get('utm_source');
    var utmMedium = params.get('utm_medium');
    var utmCampaign = params.get('utm_campaign');
    if (utmSource || utmMedium || utmCampaign) {
      gtag('event', 'campaign_visit', {
        utm_source: utmSource || '(none)',
        utm_medium: utmMedium || '(none)',
        utm_campaign: utmCampaign || '(none)'
      });
      try { sessionStorage.setItem('oregontires_utm', JSON.stringify({ source: utmSource, medium: utmMedium, campaign: utmCampaign })); } catch(e) {}
    }
  })();

  // Email capture bar view tracking
  var emailCapture = document.getElementById('email-capture');
  if (emailCapture) {
    var emailObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          gtag('event', 'view_email_capture', { section: 'footer_bar' });
          emailObserver.disconnect();
        }
      });
    }, { threshold: 0.5 });
    emailObserver.observe(emailCapture);
  }
});
