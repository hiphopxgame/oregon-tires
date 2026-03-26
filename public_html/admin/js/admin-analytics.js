// Oregon Tires Admin — GA4 Error Tracking
// The static gtag.js snippet is loaded in the admin page <head>.
// This file provides error tracking only.

// Ensure gtag is available
if (typeof window.gtag !== 'function') {
  window.dataLayer = window.dataLayer || [];
  window.gtag = function(){dataLayer.push(arguments);};
}

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
