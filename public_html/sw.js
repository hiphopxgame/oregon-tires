const CACHE_NAME = 'oregon-tires-v1';

const PRECACHE_URLS = [
  '/',
  '/manifest.json',
  '/assets/logo.png',
  '/assets/hero-bg.png',
  '/assets/favicon.png',
  '/images/fast-cars.jpg',
  '/images/tire-services.jpg',
  '/images/quality-parts.jpg',
  '/images/expert-technicians.jpg',
  '/images/specialized-services.jpg',
  '/images/auto-maintenance.jpg',
  '/images/bilingual-service.jpg',
];

// ─── Install — Pre-cache critical assets ────────────────────────────────────

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
      .catch(err => {
        console.error('SW install failed:', err);
        return self.skipWaiting();
      })
  );
});

// ─── Activate — Clean up old caches ─────────────────────────────────────────

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ─── Fetch — Strategy per resource type ─────────────────────────────────────

self.addEventListener('fetch', (event) => {
  const { request } = event;

  // 1. Supabase API calls — network-only, never cache
  if (request.url.includes('supabase.co')) {
    return;
  }

  // 2. HTML / navigation requests — network-first, fall back to cache
  if (request.mode === 'navigate' || request.destination === 'document') {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          }
          return response;
        })
        .catch(() =>
          caches.match(request).then(cached => {
            if (cached) return cached;
            // Offline fallback — simple HTML page
            return new Response(
              `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline — Oregon Tires</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f3f4f6;color:#1f2937;text-align:center;padding:2rem}.card{background:#fff;border-radius:1rem;padding:2.5rem;max-width:28rem;box-shadow:0 4px 24px rgba(0,0,0,.08)}h1{font-size:1.5rem;margin-bottom:.75rem;color:#15803d}p{line-height:1.6;margin-bottom:1rem}button{background:#15803d;color:#fff;border:none;padding:.75rem 1.5rem;border-radius:.5rem;font-size:1rem;cursor:pointer}button:hover{background:#166534}</style></head><body><div class="card"><h1>You are offline</h1><p>Oregon Tires Auto Care is not available right now. Please check your internet connection and try again.</p><button onclick="location.reload()">Try Again</button></div></body></html>`,
              { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            );
          })
        )
    );
    return;
  }

  // 3. Images — cache-first, fall back to network (then cache the response)
  if (request.destination === 'image') {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // 4. All other static assets (CSS, JS, fonts) — stale-while-revalidate
  event.respondWith(
    caches.match(request).then(cached => {
      const networkFetch = fetch(request).then(response => {
        if (response.ok) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      });
      return cached || networkFetch;
    })
  );
});
