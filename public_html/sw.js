// Bump version on each deploy to bust stale caches
const CACHE_VERSION = '39';
const CACHE_NAME = 'oregon-tires-v' + CACHE_VERSION;

const PRECACHE_URLS = [
  '/',
  '/manifest.json',
  '/offline.html',
  '/assets/logo.webp',
  '/assets/logo.png',
  '/assets/hero-bg.webp',
  '/assets/favicon.png',
  '/assets/js/pwa-manager.js',
  '/images/fast-cars.webp',
  '/images/tire-services.webp',
  '/images/quality-parts.webp',
  '/images/expert-technicians.webp',
  '/images/specialized-services.webp',
  '/images/auto-maintenance.webp',
  '/images/bilingual-service.webp',
  '/tire-installation',
  '/tire-repair',
  '/oil-change',
  '/brake-service',
  '/wheel-alignment',
  '/engine-diagnostics',
  '/suspension-repair',
  '/fleet-services',
  '/book-appointment/',
];

// ─── Install — Pre-cache critical assets ────────────────────────────────────

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => Promise.allSettled(PRECACHE_URLS.map(url => cache.add(url))))
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

  // 1. API calls — network-only, never cache
  if (request.url.includes('/api/')) {
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
            // Serve bilingual offline fallback page
            return caches.match('/offline.html');
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

// ─── Push Notifications ─────────────────────────────────────────────────────

self.addEventListener('push', (event) => {
  if (!event.data) return;

  let payload;
  try {
    payload = event.data.json();
  } catch (e) {
    payload = { title: 'Oregon Tires', body: event.data.text() };
  }

  const title = payload.title || 'Oregon Tires Auto Care';
  const options = {
    body: payload.body || '',
    icon: payload.icon || '/assets/icon-192.png',
    badge: payload.badge || '/assets/favicon.png',
    tag: payload.tag || 'oregon-tires',
    vibrate: [200, 100, 200],
    data: {
      url: payload.url || '/',
      type: payload.data?.type || 'general',
    },
    actions: [],
  };

  // Add contextual actions based on notification type (bilingual)
  const type = payload.data?.type || '';
  const lang = payload.data?.lang || (self.navigator?.language?.startsWith('es') ? 'es' : 'en');
  const NT = {
    en: { viewBooking: 'View Booking', viewEstimate: 'View Estimate', viewDetails: 'View Details', dismiss: 'Dismiss' },
    es: { viewBooking: 'Ver Reserva', viewEstimate: 'Ver Presupuesto', viewDetails: 'Ver Detalles', dismiss: 'Descartar' }
  }[lang] || { viewBooking: 'View Booking', viewEstimate: 'View Estimate', viewDetails: 'View Details', dismiss: 'Dismiss' };

  if (type === 'booking_confirmed' || type === 'appointment_reminder') {
    options.actions = [
      { action: 'view', title: NT.viewBooking },
      { action: 'dismiss', title: NT.dismiss },
    ];
  } else if (type === 'estimate_ready') {
    options.actions = [
      { action: 'view', title: NT.viewEstimate },
      { action: 'dismiss', title: NT.dismiss },
    ];
  } else if (type === 'vehicle_ready') {
    options.actions = [
      { action: 'view', title: NT.viewDetails },
    ];
  }

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// ─── Notification Click Handler ─────────────────────────────────────────────

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'dismiss') return;

  const url = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      // Focus existing window if available
      for (const client of windowClients) {
        if (client.url.includes(new URL(url, self.location.origin).pathname) && 'focus' in client) {
          return client.focus();
        }
      }
      // Open new window
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});

// ─── Background Sync — Offline Booking Replay ───────────────────────────────

self.addEventListener('sync', (event) => {
  if (event.tag === 'offline-booking') {
    event.waitUntil(replayOfflineBookings());
  }
});

function openOfflineDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('oregon_tires_offline', 1);
    request.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('pending_bookings')) {
        db.createObjectStore('pending_bookings', { keyPath: 'sync_id' });
      }
    };
    request.onsuccess = (e) => resolve(e.target.result);
    request.onerror = () => reject(request.error);
  });
}

async function replayOfflineBookings() {
  try {
    const db = await openOfflineDB();

    const entries = await new Promise((resolve, reject) => {
      const tx = db.transaction('pending_bookings', 'readonly');
      const store = tx.objectStore('pending_bookings');
      const getAll = store.getAll();
      getAll.onsuccess = () => resolve(getAll.result);
      getAll.onerror = () => reject(getAll.error);
    });

    if (!entries || entries.length === 0) return;

    for (const entry of entries) {
      try {
        const response = await fetch('/api/offline-sync.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            sync_id: entry.sync_id,
            action_type: 'booking',
            payload: entry.payload,
          }),
        });

        const json = await response.json();

        if (json.success) {
          // Remove from IDB
          await new Promise((resolve, reject) => {
            const delTx = db.transaction('pending_bookings', 'readwrite');
            delTx.objectStore('pending_bookings').delete(entry.sync_id);
            delTx.oncomplete = resolve;
            delTx.onerror = () => reject(delTx.error);
          });

          // Show confirmation notification
          const ref = json.data?.result?.reference_number || '';
          const isEs = (self.navigator?.language || '').startsWith('es');
          await self.registration.showNotification(isEs ? '¡Reserva Enviada!' : 'Booking Submitted!', {
            body: ref
              ? (isEs ? 'Tu reserva offline está confirmada. Ref: ' + ref : 'Your offline booking is confirmed. Ref: ' + ref)
              : (isEs ? 'Tu reserva offline ha sido enviada.' : 'Your offline booking has been submitted.'),
            icon: '/assets/icon-192.png',
            badge: '/assets/favicon.png',
            tag: 'offline-sync-' + entry.sync_id,
            data: { url: '/book-appointment/' },
          });
        }
      } catch (fetchErr) {
        // Will retry on next sync
        console.error('SW: offline booking replay failed', fetchErr);
      }
    }
  } catch (err) {
    console.error('SW: replayOfflineBookings error', err);
  }
}
