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
  var cachedServices = null;
  var customerSearchTimer = null;

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

  // ─── Fetch services list (cached) ──────────────────────────
  async function fetchServices() {
    if (cachedServices) return cachedServices;
    try {
      var res = await fetch('/api/services.php', { credentials: 'include' });
      var json = await res.json();
      if (json.success && json.data) {
        cachedServices = json.data;
        return cachedServices;
      }
    } catch (e) { /* ignore */ }
    return null;
  }

  // ─── Customer search for autofill ─────────────────────────
  function searchCustomers(query, dropdown) {
    if (customerSearchTimer) clearTimeout(customerSearchTimer);
    if (!query || query.length < 2) { dropdown.textContent = ''; dropdown.style.display = 'none'; return; }
    customerSearchTimer = setTimeout(async function() {
      try {
        var res = await fetch('/api/admin/customers.php?search=' + encodeURIComponent(query) + '&limit=5', { credentials: 'include' });
        var json = await res.json();
        if (!json.success || !json.data || !json.data.length) { dropdown.textContent = ''; dropdown.style.display = 'none'; return; }
        dropdown.textContent = '';
        dropdown.style.display = 'block';
        json.data.forEach(function(c) {
          var name = ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
          var detail = c.email || c.phone || '';
          var opt = el('div', 'px-3 py-2 cursor-pointer hover:bg-green-50 dark:hover:bg-gray-700 text-sm transition');
          opt.appendChild(el('span', 'font-medium text-gray-800 dark:text-gray-200', name));
          if (detail) { opt.appendChild(document.createTextNode(' ')); opt.appendChild(el('span', 'text-gray-400 text-xs', detail)); }
          opt.addEventListener('click', function() {
            document.getElementById('wq-fname').value = c.first_name || '';
            document.getElementById('wq-lname').value = c.last_name || '';
            document.getElementById('wq-email').value = c.email || '';
            document.getElementById('wq-phone').value = c.phone || '';
            dropdown.style.display = 'none';
          });
          dropdown.appendChild(opt);
        });
      } catch (e) { dropdown.style.display = 'none'; }
    }, 300);
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

    // Customer search dropdown (shared between name fields)
    var searchDrop = el('div', 'absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto');
    searchDrop.style.display = 'none';

    function onSearchInput() {
      var q = (document.getElementById('wq-fname').value + ' ' + document.getElementById('wq-lname').value).trim();
      searchCustomers(q, searchDrop);
    }

    // Name row
    var row1 = el('div', 'grid grid-cols-2 gap-3 relative');
    var fnWrap = el('div');
    fnWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqFirstName', 'First Name') + ' *'));
    var fnInp = document.createElement('input'); fnInp.id = 'wq-fname'; fnInp.className = iClass; fnInp.required = true;
    fnInp.placeholder = t('wqSearchOrType', 'Search or type...');
    fnInp.addEventListener('input', onSearchInput);
    fnWrap.appendChild(fnInp); row1.appendChild(fnWrap);
    var lnWrap = el('div');
    lnWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqLastName', 'Last Name') + ' *'));
    var lnInp = document.createElement('input'); lnInp.id = 'wq-lname'; lnInp.className = iClass; lnInp.required = true;
    lnInp.addEventListener('input', onSearchInput);
    lnWrap.appendChild(lnInp); row1.appendChild(lnWrap);
    row1.appendChild(searchDrop);
    pn.appendChild(row1);

    // Email & Phone row
    var row2 = el('div', 'grid grid-cols-2 gap-3 relative');
    var emWrap = el('div');
    emWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', 'Email'));
    var emInp = document.createElement('input'); emInp.id = 'wq-email'; emInp.type = 'email'; emInp.className = iClass;
    // Also search when typing email
    var emailDrop = el('div', 'absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto');
    emailDrop.style.display = 'none';
    emInp.addEventListener('input', function() { searchCustomers(emInp.value.trim(), emailDrop); });
    emWrap.appendChild(emInp); row2.appendChild(emWrap);
    var phWrap = el('div');
    phWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqPhone', 'Phone')));
    var phInp = document.createElement('input'); phInp.id = 'wq-phone'; phInp.type = 'tel'; phInp.className = iClass;
    // Also search when typing phone
    var phoneDrop = el('div', 'absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto');
    phoneDrop.style.display = 'none';
    phInp.addEventListener('input', function() { searchCustomers(phInp.value.trim(), phoneDrop); });
    phWrap.appendChild(phInp); row2.appendChild(phWrap);
    row2.appendChild(emailDrop);
    row2.appendChild(phoneDrop);
    pn.appendChild(row2);

    // Service dropdown (populated from DB)
    var svcWrap = el('div');
    svcWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqService', 'Service')));
    var svcSelect = document.createElement('select');
    svcSelect.id = 'wq-service';
    svcSelect.className = iClass;
    var defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = t('wqSelectService', 'Select a service...');
    svcSelect.appendChild(defaultOpt);
    svcWrap.appendChild(svcSelect);
    pn.appendChild(svcWrap);

    // Load services into dropdown
    fetchServices().then(function(services) {
      if (!services || !services.length) return;
      services.forEach(function(s) {
        var opt = document.createElement('option');
        opt.value = s.slug || s.name_en || s.name || '';
        opt.textContent = isEs() ? (s.name_es || s.name_en || s.name || '') : (s.name_en || s.name || '');
        svcSelect.appendChild(opt);
      });
    });

    // Notes
    var notesWrap = el('div');
    notesWrap.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1', t('wqNotes', 'Notes')));
    var notesInp = document.createElement('input');
    notesInp.id = 'wq-notes'; notesInp.className = iClass; notesInp.placeholder = t('wqNotesPlaceholder', 'Optional notes');
    notesWrap.appendChild(notesInp);
    pn.appendChild(notesWrap);

    // Buttons
    var btnRow = el('div', 'flex gap-3 pt-2');
    var canBtn = el('button', 'flex-1 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('cancel', 'Cancel'));
    canBtn.addEventListener('click', function() { ov.remove(); });
    btnRow.appendChild(canBtn);
    var addBtn = el('button', 'flex-1 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition', t('wqAddToQueue', 'Add to Queue'));
    addBtn.addEventListener('click', function() { submitAddToQueue(ov, addBtn); });
    btnRow.appendChild(addBtn);
    pn.appendChild(btnRow);

    // Close dropdowns when clicking elsewhere in modal
    pn.addEventListener('click', function(e) {
      if (!e.target.closest('#wq-fname') && !e.target.closest('#wq-lname')) searchDrop.style.display = 'none';
      if (!e.target.closest('#wq-email')) emailDrop.style.display = 'none';
      if (!e.target.closest('#wq-phone')) phoneDrop.style.display = 'none';
    });

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
