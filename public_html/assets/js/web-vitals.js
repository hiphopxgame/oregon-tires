// Oregon Tires — Web Vitals Monitoring (LCP, CLS, INP, FCP, TTFB)
// All 5 Core Web Vitals sent to GA4 as web_vitals events

if ('PerformanceObserver' in window) {
  // LCP — Largest Contentful Paint
  try {
    new PerformanceObserver(function(list) {
      var entries = list.getEntries();
      var lastEntry = entries[entries.length - 1];
      if (typeof gtag === 'function') gtag('event', 'web_vitals', { event_category: 'Web Vitals', event_label: 'LCP', value: Math.round(lastEntry.startTime) });
    }).observe({ type: 'largest-contentful-paint', buffered: true });
  } catch(e) {}

  // CLS — Cumulative Layout Shift
  try {
    var clsValue = 0;
    new PerformanceObserver(function(list) {
      for (var i = 0; i < list.getEntries().length; i++) {
        var entry = list.getEntries()[i];
        if (!entry.hadRecentInput) clsValue += entry.value;
      }
      if (typeof gtag === 'function') gtag('event', 'web_vitals', { event_category: 'Web Vitals', event_label: 'CLS', value: Math.round(clsValue * 1000) });
    }).observe({ type: 'layout-shift', buffered: true });
  } catch(e) {}

  // INP — Interaction to Next Paint (replaces FID per Google 2024)
  try {
    var maxINP = 0;
    new PerformanceObserver(function(list) {
      for (var i = 0; i < list.getEntries().length; i++) {
        var entry = list.getEntries()[i];
        if (entry.duration > maxINP) maxINP = entry.duration;
      }
      if (typeof gtag === 'function') gtag('event', 'web_vitals', { event_category: 'Web Vitals', event_label: 'INP', value: Math.round(maxINP) });
    }).observe({ type: 'event', buffered: true, durationThreshold: 16 });
  } catch(e) {}

  // FCP — First Contentful Paint
  try {
    new PerformanceObserver(function(list) {
      var entries = list.getEntries();
      for (var i = 0; i < entries.length; i++) {
        if (entries[i].name === 'first-contentful-paint') {
          if (typeof gtag === 'function') gtag('event', 'web_vitals', { event_category: 'Web Vitals', event_label: 'FCP', value: Math.round(entries[i].startTime) });
        }
      }
    }).observe({ type: 'paint', buffered: true });
  } catch(e) {}

  // TTFB — Time to First Byte
  try {
    new PerformanceObserver(function(list) {
      var entries = list.getEntries();
      if (entries.length > 0) {
        var nav = entries[0];
        if (typeof gtag === 'function') gtag('event', 'web_vitals', { event_category: 'Web Vitals', event_label: 'TTFB', value: Math.round(nav.responseStart) });
      }
    }).observe({ type: 'navigation', buffered: true });
  } catch(e) {}
}
