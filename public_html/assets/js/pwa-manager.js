/**
 * Oregon Tires — PWA Manager
 * Install prompt, push subscription, and online/offline UX.
 * Loaded on every page. Uses createElement (no innerHTML per project rules).
 */

(function() {
  'use strict';

  // ─── Translation strings ──────────────────────────────────────────────────
  var lang = (typeof currentLang !== 'undefined') ? currentLang : 'en';
  var strings = {
    en: {
      installTitle: 'Install Oregon Tires App',
      installBody: 'Get quick access to booking, appointment status, and notifications.',
      installBtn: 'Install App',
      installDismiss: 'Not now',
      iosInstall: 'Tap the share button and select "Add to Home Screen"',
      offlineToast: "You're offline — some features may be limited",
      onlineToast: "You're back online",
      pushAsk: 'Get notified about your appointments?',
      pushYes: 'Enable Notifications',
      pushNo: 'No thanks',
    },
    es: {
      installTitle: 'Instalar Oregon Tires',
      installBody: 'Acceso rápido a reservas, estado de citas y notificaciones.',
      installBtn: 'Instalar App',
      installDismiss: 'Ahora no',
      iosInstall: 'Toque el botón compartir y seleccione "Agregar a pantalla de inicio"',
      offlineToast: 'Estás sin conexión — algunas funciones pueden estar limitadas',
      onlineToast: 'Estás de nuevo en línea',
      pushAsk: '¿Recibir notificaciones sobre sus citas?',
      pushYes: 'Activar Notificaciones',
      pushNo: 'No gracias',
    }
  };
  var t = strings[lang] || strings.en;

  // ─── Install Prompt ───────────────────────────────────────────────────────
  var deferredPrompt = null;
  var installBannerShown = false;
  var pwaInstallEnabled = false;

  // Check if admin has enabled the install popup via site settings
  fetch('/api/settings.php', { credentials: 'include' })
    .then(function(res) { return res.json(); })
    .then(function(json) {
      if (json.success && json.data) {
        for (var i = 0; i < json.data.length; i++) {
          if (json.data[i].setting_key === 'pwa_install_enabled') {
            pwaInstallEnabled = (json.data[i].value_en === '1');
            break;
          }
        }
      }
    })
    .catch(function() { /* leave disabled */ });

  window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;

    // Show banner after 30s or on second visit
    var visits = parseInt(localStorage.getItem('ot_visit_count') || '0', 10) + 1;
    localStorage.setItem('ot_visit_count', String(visits));

    if (localStorage.getItem('ot_install_dismissed')) return;

    var delay = visits > 1 ? 5000 : 30000;
    setTimeout(showInstallBanner, delay);
  });

  window.addEventListener('appinstalled', function() {
    deferredPrompt = null;
    removeInstallBanner();
  });

  function showInstallBanner() {
    if (!pwaInstallEnabled || installBannerShown || !deferredPrompt) return;
    installBannerShown = true;

    var banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.setAttribute('role', 'alert');
    banner.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-5 z-50 border border-green-200 dark:border-green-800';
    banner.style.cssText = 'animation: slideUp 0.3s ease-out;';

    var title = document.createElement('p');
    title.className = 'font-bold text-gray-800 dark:text-gray-100 text-lg mb-1';
    title.textContent = t.installTitle;
    banner.appendChild(title);

    var body = document.createElement('p');
    body.className = 'text-sm text-gray-600 dark:text-gray-300 mb-4';
    body.textContent = t.installBody;
    banner.appendChild(body);

    var btnRow = document.createElement('div');
    btnRow.className = 'flex gap-3';

    var installBtn = document.createElement('button');
    installBtn.className = 'flex-1 bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-green-800 transition text-sm';
    installBtn.textContent = t.installBtn;
    installBtn.addEventListener('click', function() {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function() {
          deferredPrompt = null;
          removeInstallBanner();
        });
      }
    });
    btnRow.appendChild(installBtn);

    var dismissBtn = document.createElement('button');
    dismissBtn.className = 'px-4 py-2.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm';
    dismissBtn.textContent = t.installDismiss;
    dismissBtn.addEventListener('click', function() {
      localStorage.setItem('ot_install_dismissed', '1');
      removeInstallBanner();
    });
    btnRow.appendChild(dismissBtn);

    banner.appendChild(btnRow);
    document.body.appendChild(banner);
  }

  function removeInstallBanner() {
    var banner = document.getElementById('pwa-install-banner');
    if (banner) banner.remove();
    installBannerShown = false;
  }

  // iOS detection — show manual instructions
  var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  var isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;

  if (isIOS && !isStandalone && !localStorage.getItem('ot_install_dismissed')) {
    setTimeout(function() {
      if (pwaInstallEnabled) showIOSInstallHint();
    }, 45000);
  }

  function showIOSInstallHint() {
    if (document.getElementById('pwa-install-banner')) return;

    var banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.setAttribute('role', 'alert');
    banner.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-5 z-50 border border-green-200 dark:border-green-800';

    var title = document.createElement('p');
    title.className = 'font-bold text-gray-800 dark:text-gray-100 mb-2';
    title.textContent = t.installTitle;
    banner.appendChild(title);

    var hint = document.createElement('p');
    hint.className = 'text-sm text-gray-600 dark:text-gray-300 mb-3';
    hint.textContent = t.iosInstall;
    banner.appendChild(hint);

    var dismiss = document.createElement('button');
    dismiss.className = 'text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200';
    dismiss.textContent = t.installDismiss;
    dismiss.addEventListener('click', function() {
      localStorage.setItem('ot_install_dismissed', '1');
      banner.remove();
    });
    banner.appendChild(dismiss);

    document.body.appendChild(banner);
  }

  // ─── Push Subscription ────────────────────────────────────────────────────
  window.OTPush = {
    subscribe: subscribeToPush,
    promptAfterBooking: promptPushAfterBooking,
  };

  function subscribeToPush() {
    if (!('PushManager' in window) || !('serviceWorker' in navigator)) return Promise.resolve(null);
    if (Notification.permission === 'denied') return Promise.resolve(null);

    return navigator.serviceWorker.ready.then(function(reg) {
      return fetch('/api/push-vapid-key.php', { credentials: 'include' })
        .then(function(res) { return res.json(); })
        .then(function(json) {
          if (!json.success || !json.data.vapid_public_key) return null;

          var vapidKey = urlBase64ToUint8Array(json.data.vapid_public_key);
          return reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: vapidKey,
          });
        })
        .then(function(subscription) {
          if (!subscription) return null;

          var key = subscription.getKey('p256dh');
          var auth = subscription.getKey('auth');

          return fetch('/api/push-subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              endpoint: subscription.endpoint,
              keys: {
                p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
                auth: btoa(String.fromCharCode.apply(null, new Uint8Array(auth))),
              },
              language: lang === 'es' ? 'spanish' : 'english',
            }),
            credentials: 'include',
          })
          .then(function(res) { return res.json(); })
          .then(function(json) {
            if (json.success) {
              localStorage.setItem('ot_push_subscribed', '1');
              localStorage.setItem('ot_push_sub_id', String(json.data.subscription_id));
            }
            return json;
          });
        });
    });
  }

  function promptPushAfterBooking() {
    if (localStorage.getItem('ot_push_subscribed')) return;
    if (!('PushManager' in window)) return;
    if (Notification.permission === 'denied') return;
    if (Notification.permission === 'granted') {
      subscribeToPush();
      return;
    }

    // Show push prompt
    var banner = document.createElement('div');
    banner.id = 'pwa-push-prompt';
    banner.setAttribute('role', 'alert');
    banner.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-5 z-50 border border-green-200 dark:border-green-800';

    var msg = document.createElement('p');
    msg.className = 'font-semibold text-gray-800 dark:text-gray-100 mb-3';
    msg.textContent = t.pushAsk;
    banner.appendChild(msg);

    var btnRow = document.createElement('div');
    btnRow.className = 'flex gap-3';

    var yesBtn = document.createElement('button');
    yesBtn.className = 'flex-1 bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-green-800 transition text-sm';
    yesBtn.textContent = t.pushYes;
    yesBtn.addEventListener('click', function() {
      banner.remove();
      Notification.requestPermission().then(function(perm) {
        if (perm === 'granted') subscribeToPush();
      });
    });
    btnRow.appendChild(yesBtn);

    var noBtn = document.createElement('button');
    noBtn.className = 'px-4 py-2.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm';
    noBtn.textContent = t.pushNo;
    noBtn.addEventListener('click', function() {
      banner.remove();
    });
    btnRow.appendChild(noBtn);

    banner.appendChild(btnRow);
    document.body.appendChild(banner);
  }

  // ─── Online/Offline Indicator ─────────────────────────────────────────────
  window.addEventListener('online', function() {
    document.body.classList.remove('app-offline');
    showToast(t.onlineToast, 'green');

    // Trigger Background Sync replay
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
      navigator.serviceWorker.ready.then(function(reg) {
        reg.sync.register('offline-booking').catch(function() {});
      });
    }
  });

  window.addEventListener('offline', function() {
    document.body.classList.add('app-offline');
    showToast(t.offlineToast, 'amber');
  });

  function showToast(message, color) {
    var existing = document.getElementById('pwa-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.id = 'pwa-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    var bgClass = color === 'green'
      ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200'
      : 'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200';

    toast.className = 'fixed top-4 left-4 right-4 md:left-auto md:right-4 md:w-96 ' + bgClass + ' rounded-lg border px-4 py-3 text-sm font-medium z-50 shadow-lg';
    toast.style.cssText = 'animation: slideDown 0.3s ease-out;';
    toast.textContent = message;

    document.body.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) toast.remove();
    }, 4000);
  }

  // ─── Utility ──────────────────────────────────────────────────────────────
  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  // ─── Add animation keyframes ──────────────────────────────────────────────
  var style = document.createElement('style');
  style.textContent = '@keyframes slideUp{from{transform:translateY(100px);opacity:0}to{transform:translateY(0);opacity:1}}@keyframes slideDown{from{transform:translateY(-20px);opacity:0}to{transform:translateY(0);opacity:1}}';
  document.head.appendChild(style);

})();
