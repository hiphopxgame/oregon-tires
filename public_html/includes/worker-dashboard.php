<?php
/**
 * Oregon Tires — Worker Dashboard (mobile-first read-only shell)
 *
 * Rendered by members.php when role === 'employee' or 'admin' AND
 * the explicit ?tab=worker query is present (so admins can opt-in
 * without changing the default /admin/ redirect for everyone).
 *
 * READ-ONLY scaffold: 3 tabs (Appointments / Work / Messages),
 * sticky bottom tab bar, top bar with refresh, bilingual via t().
 *
 * Data source: existing member-kit endpoints (HTML fragments).
 * No mutations. No new server endpoints. No DB changes.
 */

declare(strict_types=1);

if (!isset($lang)) { $lang = 'en'; }
$wdName = htmlspecialchars($_SESSION['member_email'] ?? 'Worker');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Worker Dashboard — Oregon Tires</title>
<link rel="stylesheet" href="/assets/styles.css">
<style>
  body { padding-bottom: calc(72px + env(safe-area-inset-bottom)); }
  .wd-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; }
  .wd-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; border:1px solid; }
  .wd-pill.green { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
  .wd-pill.amber { background:#fffbeb; color:#92400e; border-color:#fde68a; }
  .wd-pill.gray  { background:#f3f4f6; color:#374151; border-color:#d1d5db; }
  .wd-tab-btn[aria-current="page"] { color:#047857; }
  .wd-tab-btn[aria-current="page"] svg { stroke:#047857; }
</style>
</head>
<body class="bg-gray-50 text-gray-900">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-white text-black px-4 py-2 rounded shadow">Skip to content</a>

<!-- Top bar -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-30">
  <div class="flex flex-wrap gap-3 items-center justify-between px-4 py-3 max-w-3xl mx-auto">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold" aria-hidden="true">OT</div>
      <div class="min-w-0">
        <h1 class="text-base font-bold truncate" data-t="worker_dashboard">Worker Dashboard</h1>
        <div class="text-xs text-gray-500 truncate"><?= $wdName ?></div>
      </div>
    </div>
    <button type="button" id="wd-refresh" class="inline-flex items-center justify-center min-h-11 min-w-11 rounded-lg border border-gray-300 bg-white hover:bg-gray-50" aria-label="Refresh" data-t-aria="refresh" data-test="wd-refresh">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
    </button>
  </div>
</header>

<main id="main" class="max-w-3xl mx-auto px-4 py-4">

  <section id="wd-tab-appointments" data-test="tab-appointments" aria-labelledby="wd-h-appts" class="wd-tab-panel">
    <h2 id="wd-h-appts" class="text-lg font-bold mb-3" data-t="todays_appointments">Today's Appointments</h2>
    <div class="wd-list" data-wd-list="appointments">
      <div class="wd-card text-center text-gray-500" data-t="loading">Loading…</div>
    </div>
  </section>

  <section id="wd-tab-work" data-test="tab-work" aria-labelledby="wd-h-work" class="wd-tab-panel" hidden>
    <h2 id="wd-h-work" class="text-lg font-bold mb-3" data-t="my_work">My Work</h2>
    <div class="wd-list" data-wd-list="work">
      <div class="wd-card text-center text-gray-500" data-t="loading">Loading…</div>
    </div>
  </section>

  <section id="wd-tab-messages" data-test="tab-messages" aria-labelledby="wd-h-messages" class="wd-tab-panel" hidden>
    <h2 id="wd-h-messages" class="text-lg font-bold mb-3" data-t="messages">Messages</h2>
    <div class="wd-list" data-wd-list="messages">
      <div class="wd-card text-center text-gray-500" data-t="loading">Loading…</div>
    </div>
  </section>

</main>

<!-- Bottom tab bar -->
<nav class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-40" style="padding-bottom: env(safe-area-inset-bottom);" aria-label="Worker tabs">
  <div class="max-w-3xl mx-auto grid grid-cols-3">
    <button type="button" class="wd-tab-btn flex flex-col items-center justify-center gap-1 py-3 min-h-14 text-gray-600" data-test="tab-appointments" data-wd-tab="appointments" aria-current="page" aria-label="Appointments">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span class="text-xs font-medium" data-t="appointments">Appointments</span>
    </button>
    <button type="button" class="wd-tab-btn flex flex-col items-center justify-center gap-1 py-3 min-h-14 text-gray-600" data-test="tab-work" data-wd-tab="work" aria-label="Work">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
      <span class="text-xs font-medium" data-t="work">Work</span>
    </button>
    <button type="button" class="wd-tab-btn flex flex-col items-center justify-center gap-1 py-3 min-h-14 text-gray-600" data-test="tab-messages" data-wd-tab="messages" aria-label="Messages">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <span class="text-xs font-medium" data-t="messages">Messages</span>
    </button>
  </div>
</nav>

<script>
(function () {
  'use strict';

  var lang = (document.documentElement.lang || 'en').toLowerCase().indexOf('es') === 0 ? 'es' : 'en';

  var t = {
    en: {
      worker_dashboard: "Worker Dashboard",
      refresh: "Refresh",
      todays_appointments: "Today's Appointments",
      my_work: "My Work",
      messages: "Messages",
      appointments: "Appointments",
      work: "Work",
      loading: "Loading…",
      empty_appointments: "No appointments today.",
      empty_work: "No assigned work.",
      empty_messages: "No messages.",
      error_loading: "Could not load. Tap refresh to try again.",
      more_actions_soon: "More actions coming soon",
      tap_for_details: "Tap for details",
      saved: "Saved",
      note_added: "Note added",
      note_required: "Please enter a note",
      reply_sent: "Reply sent",
      reply_required: "Please enter a reply"
    },
    es: {
      worker_dashboard: "Panel del Trabajador",
      refresh: "Actualizar",
      todays_appointments: "Citas de Hoy",
      my_work: "Mi Trabajo",
      messages: "Mensajes",
      appointments: "Citas",
      work: "Trabajo",
      loading: "Cargando…",
      empty_appointments: "Sin citas hoy.",
      empty_work: "Sin trabajo asignado.",
      empty_messages: "Sin mensajes.",
      error_loading: "No se pudo cargar. Toque actualizar para reintentar.",
      more_actions_soon: "Más acciones próximamente",
      tap_for_details: "Toque para detalles",
      saved: "Guardado",
      note_added: "Nota agregada",
      note_required: "Por favor escriba una nota",
      reply_sent: "Respuesta enviada",
      reply_required: "Por favor escriba una respuesta"
    }
  };
  function tr(k) { return (t[lang] && t[lang][k]) || t.en[k] || k; }

  // Apply translations to elements with data-t / data-t-aria
  document.querySelectorAll('[data-t]').forEach(function (el) {
    var key = el.getAttribute('data-t');
    var v = tr(key);
    if (v) { el.textContent = v; }
  });
  document.querySelectorAll('[data-t-aria]').forEach(function (el) {
    var key = el.getAttribute('data-t-aria');
    var v = tr(key);
    if (v) { el.setAttribute('aria-label', v); }
  });

  var endpoints = {
    appointments: '/api/member/my-schedule.php',
    work: '/api/member/my-assigned-work.php',
    messages: '/api/member/my-messages.php'
  };

  var loaded = { appointments: false, work: false, messages: false };
  var current = 'appointments';

  function clearChildren(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
  }

  function makeCard(className) {
    var d = document.createElement('div');
    d.className = 'wd-card ' + (className || '');
    return d;
  }

  function showState(listEl, msg, kind) {
    clearChildren(listEl);
    var card = makeCard('text-center ' + (kind === 'error' ? 'text-red-600' : 'text-gray-500'));
    card.textContent = msg;
    listEl.appendChild(card);
  }

  // Convert HTML fragment text to a DocumentFragment WITHOUT using innerHTML
  // assignment on a live DOM node. We use DOMParser which is a parse-only
  // operation (no script execution, no DOM mutation of the page).
  function parseFragment(htmlText) {
    var doc = new DOMParser().parseFromString('<!DOCTYPE html><body>' + htmlText + '</body>', 'text/html');
    var frag = document.createDocumentFragment();
    var body = doc.body;
    while (body && body.firstChild) {
      frag.appendChild(document.adoptNode(body.firstChild));
    }
    return frag;
  }

  function renderFragment(listEl, htmlText, emptyMsg) {
    clearChildren(listEl);
    var trimmed = (htmlText || '').trim();
    if (!trimmed) {
      showState(listEl, emptyMsg, 'empty');
      return;
    }
    var card = makeCard('');
    try {
      card.appendChild(parseFragment(trimmed));
    } catch (e) {
      showState(listEl, tr('error_loading'), 'error');
      return;
    }
    listEl.appendChild(card);
  }

  function loadTab(name, force) {
    var listEl = document.querySelector('[data-wd-list="' + name + '"]');
    if (!listEl) return;
    if (loaded[name] && !force) return;
    showState(listEl, tr('loading'), 'loading');
    fetch(endpoints[name], { credentials: 'include', headers: { 'Accept': 'text/html' } })
      .then(function (r) {
        if (r.status === 401 || r.status === 403) {
          showState(listEl, tr('error_loading'), 'error');
          return null;
        }
        return r.text();
      })
      .then(function (text) {
        if (text === null) return;
        var emptyKey = 'empty_' + name;
        renderFragment(listEl, text, tr(emptyKey));
        loaded[name] = true;
      })
      .catch(function () {
        showState(listEl, tr('error_loading'), 'error');
      });
  }

  function activateTab(name) {
    current = name;
    document.querySelectorAll('.wd-tab-panel').forEach(function (p) {
      var match = p.getAttribute('data-test') === 'tab-' + name;
      if (match) { p.removeAttribute('hidden'); } else { p.setAttribute('hidden', ''); }
    });
    document.querySelectorAll('.wd-tab-btn').forEach(function (b) {
      if (b.getAttribute('data-wd-tab') === name) {
        b.setAttribute('aria-current', 'page');
      } else {
        b.removeAttribute('aria-current');
      }
    });
    loadTab(name, false);
  }

  document.querySelectorAll('.wd-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      activateTab(btn.getAttribute('data-wd-tab'));
    });
  });

  // ── Toast helper (no innerHTML) ──
  function toast(msg, kind) {
    var d = document.createElement('div');
    d.setAttribute('role', 'status');
    d.style.cssText = 'position:fixed;left:50%;bottom:90px;transform:translateX(-50%);background:' + (kind === 'error' ? '#dc2626' : '#047857') + ';color:#fff;padding:10px 18px;border-radius:9999px;font-weight:600;font-size:14px;z-index:60;box-shadow:0 4px 14px rgba(0,0,0,0.18);';
    d.textContent = msg;
    document.body.appendChild(d);
    setTimeout(function () { if (d.parentNode) d.parentNode.removeChild(d); }, 3000);
  }

  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function (r) {
      return r.json().catch(function () { return { success: false, error: 'Bad response' }; })
        .then(function (j) { return { ok: r.ok, status: r.status, body: j }; });
    });
  }

  // ── Event delegation: RO status buttons + RO note form + message reply form ──
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest && ev.target.closest('button[data-ro-action="status"]');
    if (!btn) return;
    var card = btn.closest('[data-ro-number]');
    if (!card) return;
    var roNum = card.getAttribute('data-ro-number');
    var toStatus = btn.getAttribute('data-to-status');
    if (!roNum || !toStatus) return;
    btn.disabled = true;
    postJson('/api/member/ro-status.php', { ro_number: roNum, new_status: toStatus })
      .then(function (res) {
        btn.disabled = false;
        if (res.ok && res.body && res.body.success) {
          toast(tr('saved'));
          loadTab('work', true);
        } else {
          toast((res.body && res.body.error) || tr('error_loading'), 'error');
        }
      })
      .catch(function () { btn.disabled = false; toast(tr('error_loading'), 'error'); });
  });

  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!form || !form.matches) return;

    if (form.matches('form[data-ro-action="note"]')) {
      ev.preventDefault();
      var card = form.closest('[data-ro-number]');
      if (!card) return;
      var roNum = card.getAttribute('data-ro-number');
      var ta = form.querySelector('[data-ro-note]');
      var note = (ta && ta.value || '').trim();
      if (!note) { toast(tr('note_required'), 'error'); return; }
      var subBtn = form.querySelector('button[type="submit"]');
      if (subBtn) subBtn.disabled = true;
      postJson('/api/member/ro-note.php', { ro_number: roNum, note: note })
        .then(function (res) {
          if (subBtn) subBtn.disabled = false;
          if (res.ok && res.body && res.body.success) {
            if (ta) ta.value = '';
            toast(tr('note_added'));
            loadTab('work', true);
          } else {
            toast((res.body && res.body.error) || tr('error_loading'), 'error');
          }
        })
        .catch(function () { if (subBtn) subBtn.disabled = false; toast(tr('error_loading'), 'error'); });
      return;
    }

    if (form.matches('form[data-test="message-reply-form"]')) {
      ev.preventDefault();
      var convId = parseInt(form.getAttribute('data-conv-id'), 10);
      var rta = form.querySelector('[data-reply-body]');
      var body = (rta && rta.value || '').trim();
      if (!convId || !body) { toast(tr('reply_required'), 'error'); return; }
      var sb = form.querySelector('button[type="submit"]');
      if (sb) sb.disabled = true;
      postJson('/api/member/message-reply.php', { conversation_id: convId, body: body })
        .then(function (res) {
          if (sb) sb.disabled = false;
          if (res.ok && res.body && res.body.success) {
            if (rta) rta.value = '';
            toast(tr('reply_sent'));
            loadTab('messages', true);
          } else {
            toast((res.body && res.body.error) || tr('error_loading'), 'error');
          }
        })
        .catch(function () { if (sb) sb.disabled = false; toast(tr('error_loading'), 'error'); });
    }
  });

  var refreshBtn = document.getElementById('wd-refresh');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function () {
      loadTab(current, true);
    });
  }

  // Initial load
  activateTab('appointments');
})();
</script>
</body>
</html>
