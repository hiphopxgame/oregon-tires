/**
 * Oregon Tires — Walk-In Queue Manager
 * Real walk-in customer queue using oretir_waitlist table.
 * Distinct from waitlist.js which shows RO holds.
 */
(function() {
  'use strict';

  var API = '/api/admin/waitlist.php';
  var entries = [];
  var estimatedWait = 0;
  var refreshTimer = null;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function isEs() { return typeof currentLang !== 'undefined' && currentLang === 'es'; }
  function getCsrf() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
  function hdrs(json) { var h = { 'X-CSRF-Token': getCsrf() }; if (json) h['Content-Type'] = 'application/json'; return h; }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, !!err); }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  function timeAgo(dateStr) {
    if (!dateStr) return '';
    var diff = Date.now() - new Date(dateStr.replace(' ', 'T')).getTime();
    if (diff < 0) diff = 0;
    var m = Math.floor(diff / 60000);
    if (m < 60) return m + 'm';
    return Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
  }

  var STATUS_MAP = {
    waiting:    { label: 'Waiting',    labelEs: 'Esperando',  cls: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    notified:   { label: 'Notified',   labelEs: 'Notificado', cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    checked_in: { label: 'Checked In', labelEs: 'Registrado', cls: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
    serving:    { label: 'Serving',    labelEs: 'Atendiendo', cls: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }
  };

  // ─── Load ──────────────────────────────────────────────────
  async function loadWalkInQueue() {
    try {
      var res = await fetch(API, { credentials: 'include' });
      var json = await res.json();
      if (!json.success) return;
      entries = json.data.entries || [];
      estimatedWait = json.data.estimated_wait_minutes || 0;
      render();
    } catch (err) {
      console.error('loadWalkInQueue:', err);
      toast(t('wqLoadFail', 'Failed to load queue'), true);
    }
    startAutoRefresh();
  }

  // ─── Render ────────────────────────────────────────────────
  function render() {
    var c = document.getElementById('walkinqueue-container');
    if (!c) return;
    c.textContent = '';

    // Stats
    var waitingCount = entries.filter(function(e) { return e.status === 'waiting'; }).length;
    var servingCount = entries.filter(function(e) { return e.status === 'serving'; }).length;
    var stats = el('div', 'grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6');
    [[String(entries.length), t('wqTotalInQueue', 'In Queue'), 'text-blue-600 dark:text-blue-400'],
     [String(waitingCount), t('wqWaiting', 'Waiting'), 'text-amber-600 dark:text-amber-400'],
     [String(servingCount), t('wqBeingServed', 'Being Served'), 'text-green-600 dark:text-green-400'],
     [estimatedWait + 'min', t('wqEstWait', 'Est. Wait'), 'text-gray-600 dark:text-gray-400']
    ].forEach(function(s) {
      var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 shadow border dark:border-gray-700 text-center');
      card.appendChild(el('div', 'text-2xl font-bold ' + s[2], s[0]));
      card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mt-1', s[1]));
      stats.appendChild(card);
    });
    c.appendChild(stats);

    if (!entries.length) {
      var emptyCard = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow p-8 text-center');
      emptyCard.appendChild(el('div', 'text-4xl mb-3', '🚶'));
      emptyCard.appendChild(el('p', 'text-gray-500 dark:text-gray-400 mb-2', t('wqEmpty', 'No customers in queue')));
      emptyCard.appendChild(el('p', 'text-sm text-gray-400 dark:text-gray-500', t('wqAddFirst', 'Add a walk-in customer to get started')));
      c.appendChild(emptyCard);
      return;
    }

    // Queue cards
    var grid = el('div', 'space-y-3');
    entries.forEach(function(entry) {
      grid.appendChild(buildCard(entry));
    });
    c.appendChild(grid);
  }

  function buildCard(e) {
    var name = ((e.first_name || '') + ' ' + (e.last_name || '')).trim() || 'Walk-in';
    var info = STATUS_MAP[e.status] || STATUS_MAP.waiting;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 flex items-center justify-between hover:shadow-md transition');

    // Left: info
    var left = el('div', 'flex-1 min-w-0');
    var nameRow = el('div', 'flex items-center gap-2 flex-wrap');
    nameRow.appendChild(el('span', 'text-lg font-medium text-gray-500 dark:text-gray-400', '#' + (e.position || '?')));
    nameRow.appendChild(el('span', 'font-semibold text-gray-800 dark:text-gray-200', name));
    nameRow.appendChild(el('span', 'text-xs px-2 py-0.5 rounded-full font-medium ' + info.cls, isEs() ? info.labelEs : info.label));
    left.appendChild(nameRow);

    var details = [];
    if (e.service) details.push(e.service.replace(/-/g, ' '));
    if (e.phone) details.push(e.phone);
    if (e.created_at) details.push(t('wqWaiting', 'Waiting') + ': ' + timeAgo(e.created_at));
    if (details.length) {
      left.appendChild(el('p', 'text-xs text-gray-500 dark:text-gray-400 mt-1', details.join(' \u2022 ')));
    }
    if (e.notes) {
      left.appendChild(el('p', 'text-xs text-gray-400 dark:text-gray-500 mt-0.5 italic', e.notes));
    }
    card.appendChild(left);

    // Right: actions
    var actions = el('div', 'flex gap-2 ml-3 shrink-0 flex-wrap');

    if (e.status === 'waiting') {
      var notifyBtn = el('button', 'px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition', t('wqNotify', 'Notify'));
      notifyBtn.addEventListener('click', function() { updateStatus(e.id, 'notified'); });
      actions.appendChild(notifyBtn);
      var serveBtn = el('button', 'px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 transition', t('wqServe', 'Serve'));
      serveBtn.addEventListener('click', function() { updateStatus(e.id, 'serving'); });
      actions.appendChild(serveBtn);
    }
    if (e.status === 'notified') {
      var checkinBtn = el('button', 'px-3 py-1.5 bg-cyan-600 text-white rounded-lg text-xs font-medium hover:bg-cyan-700 transition', t('wqCheckIn', 'Check In'));
      checkinBtn.addEventListener('click', function() { updateStatus(e.id, 'checked_in'); });
      actions.appendChild(checkinBtn);
    }
    if (e.status === 'checked_in') {
      var serveBtn2 = el('button', 'px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 transition', t('wqServe', 'Serve'));
      serveBtn2.addEventListener('click', function() { updateStatus(e.id, 'serving'); });
      actions.appendChild(serveBtn2);
    }
    if (e.status === 'serving') {
      var doneBtn = el('button', 'px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition', t('wqComplete', 'Complete'));
      doneBtn.addEventListener('click', function() { updateStatus(e.id, 'completed'); });
      actions.appendChild(doneBtn);
    }
    // Cancel for any active status
    var cancelBtn = el('button', 'px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('wqCancel', 'Cancel'));
    cancelBtn.addEventListener('click', function() { updateStatus(e.id, 'cancelled'); });
    actions.appendChild(cancelBtn);

    card.appendChild(actions);
    return card;
  }

  // ─── Update Status ─────────────────────────────────────────
  async function updateStatus(id, status) {
    try {
      var res = await fetch(API, {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({ id: id, status: status })
      });
      var json = await res.json();
      if (json.success) {
        var info = STATUS_MAP[status];
        var label = info ? (isEs() ? info.labelEs : info.label) : status;
        toast(t('wqStatusUpdated', 'Queue updated') + ': ' + label);
        if (json.visit_id) toast(t('wqAutoCheckedIn', 'Auto-checked in to Visit Tracker'));
        loadWalkInQueue();
      } else { toast(json.error || 'Update failed', true); }
    } catch (err) {
      console.error('wq updateStatus:', err);
      toast(t('wqNetworkError', 'Network error'), true);
    }
  }

  // ─── Remove ────────────────────────────────────────────────
  async function removeEntry(id) {
    try {
      var res = await fetch(API + '?id=' + id, {
        method: 'DELETE', credentials: 'include', headers: hdrs()
      });
      var json = await res.json();
      if (json.success) { toast(t('wqRemoved', 'Removed from queue')); loadWalkInQueue(); }
      else { toast(json.error || 'Remove failed', true); }
    } catch (err) { toast(t('wqNetworkError', 'Network error'), true); }
  }

  // ─── Add to Queue Modal ────────────────────────────────────
  function openAddToQueue() {
    var existing = document.getElementById('wq-modal-overlay');
    if (existing) existing.remove();

    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'wq-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) ov.remove(); });

    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('wqAddTitle', 'Add to Queue')));

    var iClass = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';

    function addField(id, label, type, ph, req) {
      var wrap = el('div');
      wrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', label + (req ? ' *' : '')));
      var inp = document.createElement(type === 'select' ? 'select' : 'input');
      inp.id = id;
      inp.className = iClass;
      if (type !== 'select') inp.type = type;
      if (ph) inp.placeholder = ph;
      if (req) inp.required = true;
      wrap.appendChild(inp);
      pn.appendChild(wrap);
      return inp;
    }

    // Form fields
    var row1 = el('div', 'grid grid-cols-2 gap-3');
    var fnWrap = el('div');
    fnWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqFirstName', 'First Name') + ' *'));
    var fnInp = document.createElement('input'); fnInp.id = 'wq-fname'; fnInp.className = iClass; fnInp.required = true;
    fnWrap.appendChild(fnInp); row1.appendChild(fnWrap);
    var lnWrap = el('div');
    lnWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqLastName', 'Last Name') + ' *'));
    var lnInp = document.createElement('input'); lnInp.id = 'wq-lname'; lnInp.className = iClass; lnInp.required = true;
    lnWrap.appendChild(lnInp); row1.appendChild(lnWrap);
    pn.appendChild(row1);

    var row2 = el('div', 'grid grid-cols-2 gap-3');
    var emWrap = el('div');
    emWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', 'Email'));
    var emInp = document.createElement('input'); emInp.id = 'wq-email'; emInp.type = 'email'; emInp.className = iClass;
    emWrap.appendChild(emInp); row2.appendChild(emWrap);
    var phWrap = el('div');
    phWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqPhone', 'Phone')));
    var phInp = document.createElement('input'); phInp.id = 'wq-phone'; phInp.type = 'tel'; phInp.className = iClass;
    phWrap.appendChild(phInp); row2.appendChild(phWrap);
    pn.appendChild(row2);

    addField('wq-service', t('wqService', 'Service'), 'text', 'e.g. tire-installation, oil-change');
    addField('wq-notes', t('wqNotes', 'Notes'), 'text', t('wqNotesPlaceholder', 'Optional notes'));

    // Buttons
    var btnRow = el('div', 'flex gap-3 pt-2');
    var canBtn = el('button', 'flex-1 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('cancel', 'Cancel'));
    canBtn.addEventListener('click', function() { ov.remove(); });
    btnRow.appendChild(canBtn);
    var addBtn = el('button', 'flex-1 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition', t('wqAddToQueue', 'Add to Queue'));
    addBtn.addEventListener('click', function() { submitAddToQueue(ov, addBtn); });
    btnRow.appendChild(addBtn);
    pn.appendChild(btnRow);

    ov.appendChild(pn);
    document.body.appendChild(ov);
    fnInp.focus();
  }

  async function submitAddToQueue(ov, btn) {
    var firstName = document.getElementById('wq-fname').value.trim();
    var lastName = document.getElementById('wq-lname').value.trim();
    if (!firstName || !lastName) { toast(t('wqNameRequired', 'Name is required'), true); return; }

    btn.disabled = true;
    btn.textContent = t('wqAdding', 'Adding...');

    // Use the public waitlist endpoint to add
    try {
      var res = await fetch('/api/waitlist.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          first_name: firstName,
          last_name: lastName,
          email: document.getElementById('wq-email').value.trim() || undefined,
          phone: document.getElementById('wq-phone').value.trim() || undefined,
          service: document.getElementById('wq-service').value.trim() || undefined,
          notes: document.getElementById('wq-notes').value.trim() || undefined
        })
      });
      var json = await res.json();
      if (json.success) {
        toast(t('wqAdded', 'Added to queue') + ' (#' + (json.data.position || '?') + ')');
        ov.remove();
        loadWalkInQueue();
      } else {
        toast(json.error || 'Failed to add', true);
        btn.disabled = false;
        btn.textContent = t('wqAddToQueue', 'Add to Queue');
      }
    } catch (err) {
      console.error('submitAddToQueue:', err);
      toast(t('wqNetworkError', 'Network error'), true);
      btn.disabled = false;
      btn.textContent = t('wqAddToQueue', 'Add to Queue');
    }
  }

  // ─── Auto-refresh ──────────────────────────────────────────
  function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(function() {
      if (document.getElementById('walkinqueue-container')) loadWalkInQueue();
      else clearInterval(refreshTimer);
    }, 15000); // refresh every 15s for real-time queue
  }

  // ─── Expose ────────────────────────────────────────────────
  window.loadWalkInQueue = loadWalkInQueue;
  window.openAddToQueue = openAddToQueue;
})();
