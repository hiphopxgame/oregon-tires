/**
 * Oregon Tires — Offline Booking
 * IndexedDB queue for offline form submissions + Background Sync.
 * Loaded on booking page only.
 */

(function() {
  'use strict';

  var DB_NAME = 'oregon_tires_offline';
  var STORE_NAME = 'pending_bookings';
  var DB_VERSION = 1;
  var lang = (typeof currentLang !== 'undefined') ? currentLang : 'en';

  var strings = {
    en: {
      queued: 'Your booking has been saved and will be submitted when you\'re back online.',
      queuedRef: 'Offline Reference',
      syncing: 'Submitting your queued booking...',
      synced: 'Your offline booking has been submitted successfully!',
      syncFailed: 'Could not submit your offline booking. We\'ll try again later.',
    },
    es: {
      queued: 'Su reserva ha sido guardada y se enviará cuando vuelva a estar en línea.',
      queuedRef: 'Referencia sin conexión',
      syncing: 'Enviando su reserva en cola...',
      synced: 'Su reserva sin conexión se ha enviado correctamente.',
      syncFailed: 'No se pudo enviar su reserva. Lo intentaremos más tarde.',
    }
  };
  var t = strings[lang] || strings.en;

  // ─── IndexedDB Helpers ────────────────────────────────────────────────────

  function openOfflineDB() {
    return new Promise(function(resolve, reject) {
      if (!window.indexedDB) {
        reject(new Error('IndexedDB not supported'));
        return;
      }

      var request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = function(e) {
        var db = e.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, { keyPath: 'sync_id' });
        }
      };

      request.onsuccess = function(e) {
        resolve(e.target.result);
      };

      request.onerror = function() {
        reject(request.error);
      };
    });
  }

  function queueBooking(payload) {
    var syncId = generateSyncId();
    payload.sync_id = syncId;

    return openOfflineDB().then(function(db) {
      return new Promise(function(resolve, reject) {
        var tx = db.transaction(STORE_NAME, 'readwrite');
        var store = tx.objectStore(STORE_NAME);
        store.put({
          sync_id: syncId,
          payload: payload,
          queued_at: new Date().toISOString(),
        });
        tx.oncomplete = function() { resolve(syncId); };
        tx.onerror = function() { reject(tx.error); };
      });
    }).then(function(sid) {
      // Register Background Sync
      if ('serviceWorker' in navigator && 'SyncManager' in window) {
        navigator.serviceWorker.ready.then(function(reg) {
          reg.sync.register('offline-booking').catch(function() {});
        });
      }
      return sid;
    });
  }

  function generateSyncId() {
    var arr = new Uint8Array(16);
    crypto.getRandomValues(arr);
    return Array.from(arr, function(b) { return b.toString(16).padStart(2, '0'); }).join('');
  }

  // ─── Expose for service worker ───────────────────────────────────────────
  window.OTOffline = {
    openDB: openOfflineDB,
    queueBooking: queueBooking,
    DB_NAME: DB_NAME,
    STORE_NAME: STORE_NAME,
  };

  // ─── Intercept booking form submission when offline ───────────────────────
  var form = document.getElementById('booking-form');
  if (!form) return;

  // Wrap the existing submit handler
  var originalSubmitHandler = null;

  form.addEventListener('submit', function(e) {
    // Only intercept if offline
    if (navigator.onLine) return; // let normal flow handle it

    e.preventDefault();
    e.stopImmediatePropagation();

    // Gather form data (same as book-appointment/index.html)
    var serviceEl = form.querySelector('input[name="service"]:checked');
    if (!serviceEl) return;

    var timeVal = document.getElementById('booking-time');
    if (!timeVal || !timeVal.value) return;

    var body = {
      service: serviceEl.value,
      preferred_date: form.preferred_date ? form.preferred_date.value : '',
      preferred_time: timeVal.value,
      first_name: (form.first_name ? form.first_name.value.trim() : ''),
      last_name: (form.last_name ? form.last_name.value.trim() : ''),
      phone: (form.phone ? form.phone.value.trim() : ''),
      email: (form.email ? form.email.value.trim() : ''),
      vehicle_year: getVal('vehicle-year'),
      vehicle_make: getVal('vehicle-make'),
      vehicle_model: getVal('vehicle-model'),
      vehicle_vin: getVal('vehicle-vin'),
      license_plate: getVal('plate-input'),
      tire_size: getVal('tire-size-input'),
      notes: form.querySelector('textarea[name="notes"]') ? form.querySelector('textarea[name="notes"]').value : '',
      language: lang === 'es' ? 'spanish' : 'english',
      sms_opt_in: document.getElementById('sms-opt-in') && document.getElementById('sms-opt-in').checked ? 1 : 0,
    };

    // Validate required fields
    if (!body.first_name || !body.last_name || !body.phone || !body.email || !body.preferred_date) {
      return; // let existing validation handle it
    }

    // Queue to IndexedDB
    var submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = lang === 'es' ? 'Guardando...' : 'Saving...';
    }

    queueBooking(body).then(function(syncId) {
      showOfflineConfirmation(syncId);
    }).catch(function() {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = lang === 'es' ? 'Reservar Cita' : 'Book Appointment';
      }
    });
  }, true); // Use capture to fire before HTMX

  function getVal(id) {
    var el = document.getElementById(id);
    return el ? el.value : '';
  }

  function showOfflineConfirmation(syncId) {
    var container = document.getElementById('booking-form-container');
    if (!container) return;

    container.textContent = '';

    var card = document.createElement('div');
    card.className = 'bg-white rounded-xl shadow-md p-8 text-center dark:bg-gray-700';

    var icon = document.createElement('div');
    icon.className = 'text-5xl mb-4';
    icon.textContent = '\u{1F4F6}'; // antenna emoji
    card.appendChild(icon);

    var h2 = document.createElement('h2');
    h2.className = 'text-xl font-bold text-amber-600 dark:text-amber-400 mb-3';
    h2.textContent = lang === 'es' ? 'Reserva Guardada Sin Conexión' : 'Booking Saved Offline';
    card.appendChild(h2);

    var msg = document.createElement('p');
    msg.className = 'text-gray-600 dark:text-gray-300 mb-4';
    msg.textContent = t.queued;
    card.appendChild(msg);

    var refLabel = document.createElement('p');
    refLabel.className = 'text-sm text-gray-500 dark:text-gray-400';
    refLabel.textContent = t.queuedRef + ': ' + syncId.substring(0, 8).toUpperCase();
    card.appendChild(refLabel);

    var homeLink = document.createElement('a');
    homeLink.href = '/';
    homeLink.className = 'inline-block mt-6 bg-green-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-800 transition';
    homeLink.textContent = lang === 'es' ? 'Volver al Inicio' : 'Back to Home';
    card.appendChild(homeLink);

    container.appendChild(card);
    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Manual replay fallback (no Background Sync) ─────────────────────────
  window.addEventListener('online', function() {
    if ('SyncManager' in window) return; // Background Sync will handle it

    openOfflineDB().then(function(db) {
      var tx = db.transaction(STORE_NAME, 'readonly');
      var store = tx.objectStore(STORE_NAME);
      var getAll = store.getAll();

      getAll.onsuccess = function() {
        var entries = getAll.result;
        if (!entries || entries.length === 0) return;

        entries.forEach(function(entry) {
          fetch('/api/offline-sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              sync_id: entry.sync_id,
              action_type: 'booking',
              payload: entry.payload,
            }),
            credentials: 'include',
          })
          .then(function(res) { return res.json(); })
          .then(function(json) {
            if (json.success) {
              // Remove from IDB
              var delTx = db.transaction(STORE_NAME, 'readwrite');
              delTx.objectStore(STORE_NAME).delete(entry.sync_id);
            }
          })
          .catch(function() {});
        });
      };
    }).catch(function() {});
  });

})();
