// Oregon Tires Admin — GA4 Hybrid Loader + Error Tracking
// Extracted from admin/index.html inline scripts

// === GA4 Hybrid Loader ===
(function(){
  var DEFAULT_GA = 'G-CHYMTNB6LH';
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
