/**
 * Oregon Tires — Admin Tire Quote Manager
 * Handles tire quote request listing, filtering, responding, and status updates.
 */
(function() {
  'use strict';

  var API = '/api/admin/tire-quotes.php';
  var quotes = [];
  var currentFilter = 'all';
  var statusMap = {
    new:       { en: 'New',       es: 'Nuevo',     cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' },
    quoted:    { en: 'Quoted',    es: 'Cotizado',  cls: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' },
    accepted:  { en: 'Accepted',  es: 'Aceptado',  cls: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' },
    ordered:   { en: 'Ordered',   es: 'Ordenado',  cls: 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' },
    installed: { en: 'Installed', es: 'Instalado', cls: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300' },
    cancelled: { en: 'Cancelled', es: 'Cancelado', cls: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }
  };

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function isEs() { return typeof currentLang !== 'undefined' && currentLang === 'es'; }
  function getCsrf() { return (typeof csrfToken !== 'undefined' && csrfToken) ? csrfToken : ''; }
  function hdrs(json) { var h = { 'X-CSRF-Token': getCsrf() }; if (json) h['Content-Type'] = 'application/json'; return h; }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, !!err); }
  function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  function fmtDate(s) {
    if (!s) return '-';
    return new Date(s).toLocaleDateString(isEs() ? 'es-MX' : 'en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  function customerName(q) {
    return [q.first_name, q.last_name].filter(Boolean).join(' ') || q.email || '-';
  }

  function vehicleStr(q) {
    return [q.vehicle_year, q.vehicle_make, q.vehicle_model].filter(Boolean).join(' ') || '-';
  }

  function removeModal() { var m = document.getElementById('tq-modal-overlay'); if (m) m.remove(); }

  // ─── BulkManager Init ──────────────────────────────────────
  if (typeof BulkManager !== 'undefined') {
    BulkManager.init({ tab: 'tire-quotes', endpoint: 'tire-quotes.php', onDelete: function() { loadTireQuotes(); }, superAdminOnly: false, deleteWarning: 'tqBulkDeleteWarn' });
  }

  // ─── Load ───────────────────────────────────────────────────
  async function loadTireQuotes() {
    var url = currentFilter !== 'all' ? API + '?status=' + encodeURIComponent(currentFilter) : API;
    try {
      var res = await fetch(url, { credentials: 'include', headers: { 'X-CSRF-Token': getCsrf() } });
      var json = await res.json();
      quotes = json.success ? (json.data || []) : [];
      render();
    } catch (err) {
      console.error('loadTireQuotes:', err);
      toast(t('tqLoadFail', 'Failed to load tire quotes'), true);
    }
  }

  // ─── Render ─────────────────────────────────────────────────
  function render() {
    var c = document.getElementById('tirequotes-container');
    if (!c) return;
    c.textContent = '';
    if (typeof BulkManager !== 'undefined') BulkManager.reset();

    // Stats bar
    var newCount = quotes.filter(function(q) { return q.status === 'new'; }).length;
    var quotedCount = quotes.filter(function(q) { return q.status === 'quoted'; }).length;
    var acceptedCount = quotes.filter(function(q) { return q.status === 'accepted' || q.status === 'ordered'; }).length;
    if (quotes.length > 0) {
      var stats = el('div', 'grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4');
      [[newCount, t('tqNewRequests', 'New Requests'), 'text-blue-600 dark:text-blue-400'],
       [quotedCount, t('tqAwaitingResponse', 'Awaiting Response'), 'text-yellow-600 dark:text-yellow-400'],
       [acceptedCount, t('tqAcceptedOrdered', 'Accepted / Ordered'), 'text-green-600 dark:text-green-400']
      ].forEach(function(s) {
        var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg p-3 text-center border dark:border-gray-700');
        card.appendChild(el('div', 'text-2xl font-bold ' + s[2], String(s[0])));
        card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400', s[1]));
        stats.appendChild(card);
      });
      c.appendChild(stats);
    }

    // Filter bar
    var bar = el('div', 'flex flex-wrap items-center gap-3 mb-4');
    var filterLabel = el('label', 'text-sm font-medium text-gray-600 dark:text-gray-300', t('tqFilterStatus', 'Filter:'));
    bar.appendChild(filterLabel);
    var allStatuses = [['all','All','Todos'],['new','New','Nuevo'],['quoted','Quoted','Cotizado'],['accepted','Accepted','Aceptado'],['ordered','Ordered','Ordenado'],['installed','Installed','Instalado'],['cancelled','Cancelled','Cancelado']];
    allStatuses.forEach(function(o) {
      var btn = el('button', 'px-3 py-1 rounded-full text-xs font-medium border transition ' +
        (o[0] === currentFilter ? 'bg-brand text-white border-brand' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'),
        isEs() ? o[2] : o[1]);
      btn.addEventListener('click', function() { currentFilter = o[0]; loadTireQuotes(); });
      bar.appendChild(btn);
    });
    c.appendChild(bar);

    if (!quotes.length) {
      c.appendChild(el('p', 'text-center py-8 text-gray-400 dark:text-gray-500', t('tqNoQuotes', 'No tire quote requests found.')));
      return;
    }

    // Select all checkbox
    if (typeof BulkManager !== 'undefined') {
      var selectAllWrap = el('div', 'flex items-center gap-2 mb-3');
      selectAllWrap.innerHTML = BulkManager.selectAllHtml();
      var selectLabel = el('span', 'text-sm text-gray-500 dark:text-gray-400', t('tqSelectAll', 'Select All'));
      selectAllWrap.appendChild(selectLabel);
      c.appendChild(selectAllWrap);
    }

    // Cards layout (mobile-friendly)
    var grid = el('div', 'space-y-3');
    quotes.forEach(function(q) { grid.appendChild(buildCard(q)); });
    c.appendChild(grid);

    // Bulk toolbar
    if (typeof BulkManager !== 'undefined') {
      var toolbarDiv = el('div');
      toolbarDiv.innerHTML = BulkManager.toolbarHtml();
      c.appendChild(toolbarDiv);
      BulkManager.bind();
    }
  }

  function buildCard(q) {
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 hover:shadow-md transition');

    // Checkbox row
    if (typeof BulkManager !== 'undefined') {
      var cbWrap = el('div', 'mb-2');
      cbWrap.innerHTML = BulkManager.checkboxHtml(q.id);
      card.appendChild(cbWrap);
    }

    // Header row: customer + status
    var header = el('div', 'flex justify-between items-start mb-2');
    var left = el('div');
    left.appendChild(el('div', 'font-semibold text-gray-800 dark:text-gray-200', customerName(q)));
    if (q.email) left.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400', q.email));
    if (q.phone) left.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400', q.phone));
    header.appendChild(left);
    var info = statusMap[q.status] || statusMap['new'];
    header.appendChild(el('span', 'text-xs px-2.5 py-1 rounded-full font-medium ' + info.cls, isEs() ? info.es : info.en));
    card.appendChild(header);

    // Details grid
    var details = el('div', 'grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm mb-3');
    [[t('tqVehicle','Vehicle'), vehicleStr(q)],
     [t('tqTireSize','Tire Size'), q.tire_size || '-'],
     [t('tqQty','Qty'), String(q.tire_count || '-')],
     [t('tqDate','Date'), fmtDate(q.created_at)]
    ].forEach(function(f) {
      var d = el('div');
      d.appendChild(el('div', 'text-[10px] text-gray-400 dark:text-gray-500 uppercase', f[0]));
      d.appendChild(el('div', 'text-gray-700 dark:text-gray-300 font-medium', f[1]));
      details.appendChild(d);
    });
    card.appendChild(details);

    // Extra info
    if (q.tire_preference) {
      card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mb-1', t('tqPreference','Preference') + ': ' + q.tire_preference));
    }
    if (q.budget_range) {
      card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mb-1', t('tqBudget','Budget') + ': ' + q.budget_range));
    }
    if (q.notes) {
      card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mb-1 italic', '"' + q.notes + '"'));
    }
    if (q.quote_amount) {
      card.appendChild(el('div', 'text-sm font-semibold text-green-600 dark:text-green-400 mb-1', t('tqQuoted','Quoted') + ': $' + Number(q.quote_amount).toFixed(2)));
    }
    if (q.admin_notes) {
      card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mb-1', t('tqAdminNotes','Admin Notes') + ': ' + q.admin_notes));
    }

    // Action buttons
    var actions = el('div', 'flex flex-wrap gap-2 mt-2 pt-2 border-t dark:border-gray-700');
    if (q.status === 'new') {
      var qb = el('button', 'px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition', t('tqRespond', 'Send Quote'));
      qb.addEventListener('click', function() { openQuoteForm(q); });
      actions.appendChild(qb);
      var cb = el('button', 'px-3 py-1.5 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded-lg text-xs font-medium hover:bg-red-200 transition', t('tqCancel', 'Cancel'));
      cb.addEventListener('click', function() { updateStatus(q.id, 'cancelled'); });
      actions.appendChild(cb);
    }
    if (q.status === 'quoted') {
      var ab = el('button', 'px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 transition', t('tqMarkAccepted', 'Mark Accepted'));
      ab.addEventListener('click', function() { updateStatus(q.id, 'accepted'); });
      actions.appendChild(ab);
    }
    if (q.status === 'accepted') {
      // Book appointment pre-filled with customer + vehicle + tire info
      var bookBtn = el('button', 'px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition', t('tqBookAppt', 'Book Appointment'));
      bookBtn.addEventListener('click', function() {
        // Open the new appointment modal pre-filled if available
        if (typeof openNewApptModal === 'function') {
          openNewApptModal();
          setTimeout(function() {
            var form = document.getElementById('new-appt-form');
            if (!form) return;
            var fill = function(name, val) { var inp = form.querySelector('[name="' + name + '"]'); if (inp && val) inp.value = val; };
            fill('appt-first-name', q.first_name);
            fill('appt-last-name', q.last_name);
            fill('appt-email', q.email);
            fill('appt-phone', q.phone);
            fill('appt-vehicle-year', q.vehicle_year);
            fill('appt-vehicle-make', q.vehicle_make);
            fill('appt-vehicle-model', q.vehicle_model);
            var svcSel = form.querySelector('[name="appt-service"]');
            if (svcSel) svcSel.value = 'tire-installation';
            fill('appt-notes', 'Tire Quote: ' + (q.tire_size || '') + ' x' + (q.tire_count || '') + (q.quote_amount ? ' — Quoted $' + Number(q.quote_amount).toFixed(2) : ''));
          }, 200);
        } else {
          toast(t('tqApptModalNotFound', 'Please use the Appointments tab to book'), true);
        }
      });
      actions.appendChild(bookBtn);
      var ob = el('button', 'px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-medium hover:bg-purple-700 transition', t('tqMarkOrdered', 'Mark Ordered'));
      ob.addEventListener('click', function() { updateStatus(q.id, 'ordered'); });
      actions.appendChild(ob);
    }
    if (q.status === 'ordered') {
      var ib = el('button', 'px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition', t('tqMarkInstalled', 'Mark Installed'));
      ib.addEventListener('click', function() { updateStatus(q.id, 'installed'); });
      actions.appendChild(ib);
    }
    // Edit notes button for all statuses
    var nb = el('button', 'px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition', t('tqEditNotes', 'Notes'));
    nb.addEventListener('click', function() { openNotesForm(q); });
    actions.appendChild(nb);
    // Delete button
    if (typeof BulkManager !== 'undefined') {
      var delB = el('button', 'px-3 py-1.5 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded-lg text-xs font-medium hover:bg-red-200 transition', t('actionDelete', 'Delete'));
      delB.addEventListener('click', function() { BulkManager.deleteSingle(q.id, customerName(q)); });
      actions.appendChild(delB);
    }
    card.appendChild(actions);

    return card;
  }

  // ─── Quote Response Form ────────────────────────────────────
  function openQuoteForm(q) {
    removeModal();
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'tq-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) removeModal(); });
    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('tqRespondTitle', 'Send Quote')));
    pn.appendChild(el('p', 'text-sm text-gray-500 dark:text-gray-400', customerName(q) + ' — ' + (q.tire_size || '') + ' x' + (q.tire_count || '')));

    function addField(id, label, type, ph, val) {
      pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300', label));
      var inp = type === 'textarea' ? document.createElement('textarea') : document.createElement('input');
      if (type === 'textarea') inp.rows = 3; else inp.type = type;
      inp.id = id;
      inp.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
      if (ph) inp.placeholder = ph;
      if (val) inp.value = val;
      pn.appendChild(inp);
    }
    addField('tq-amount', t('tqQuoteAmount','Quote Amount ($)'), 'number', '0.00', '');
    addField('tq-notes', t('tqAdminNotes','Notes for customer'), 'textarea', 'e.g. Brand, availability, includes mounting + balancing...', '');

    var br = el('div', 'flex gap-3 pt-2');
    var canBtn = el('button', 'flex-1 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('tqCancel','Cancel'));
    canBtn.addEventListener('click', removeModal);
    br.appendChild(canBtn);
    var sendBtn = el('button', 'flex-1 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition', t('tqSendQuote','Send Quote'));
    sendBtn.addEventListener('click', function() { submitQuote(q.id, sendBtn); });
    br.appendChild(sendBtn);
    pn.appendChild(br); ov.appendChild(pn); document.body.appendChild(ov);
  }

  async function submitQuote(id, btn) {
    var amount = document.getElementById('tq-amount').value.trim();
    if (!amount) { toast(t('tqAmountReq','Quote amount is required'), true); return; }
    btn.disabled = true;
    try {
      var res = await fetch(API, {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({
          id: id,
          status: 'quoted',
          quote_amount: amount,
          admin_notes: document.getElementById('tq-notes').value.trim()
        })
      });
      var json = await res.json();
      if (json.success) { toast(t('tqQuoteSent','Quote sent')); removeModal(); loadTireQuotes(); }
      else { toast(json.error || 'Failed', true); btn.disabled = false; }
    } catch (err) { console.error('submitQuote:', err); toast(t('tqNetworkError', 'Network error'), true); btn.disabled = false; }
  }

  // ─── Notes Form ────────────────────────────────────────────
  function openNotesForm(q) {
    removeModal();
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'tq-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) removeModal(); });
    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('tqEditNotes', 'Edit Notes')));
    pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300', t('tqAdminNotes','Admin Notes')));
    var ta = document.createElement('textarea');
    ta.id = 'tq-edit-notes';
    ta.rows = 4;
    ta.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    ta.value = q.admin_notes || '';
    pn.appendChild(ta);
    var br = el('div', 'flex gap-3');
    var canBtn = el('button', 'flex-1 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('tqCancel','Cancel'));
    canBtn.addEventListener('click', removeModal);
    br.appendChild(canBtn);
    var saveBtn = el('button', 'flex-1 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:opacity-90 transition', t('save','Save'));
    saveBtn.addEventListener('click', async function() {
      saveBtn.disabled = true;
      try {
        var res = await fetch(API, {
          method: 'PUT', credentials: 'include', headers: hdrs(true),
          body: JSON.stringify({ id: q.id, admin_notes: ta.value.trim() })
        });
        var json = await res.json();
        if (json.success) { toast(t('saved','Saved')); removeModal(); loadTireQuotes(); }
        else { toast(json.error || 'Failed', true); saveBtn.disabled = false; }
      } catch(e) { toast('Network error', true); saveBtn.disabled = false; }
    });
    br.appendChild(saveBtn);
    pn.appendChild(br); ov.appendChild(pn); document.body.appendChild(ov);
  }

  // ─── Update Status ──────────────────────────────────────────
  async function updateStatus(id, status) {
    try {
      var res = await fetch(API, {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({ id: id, status: status })
      });
      var json = await res.json();
      if (json.success) {
        var info = statusMap[status] || {};
        toast(t('tqStatusUpdated','Status updated') + ': ' + (isEs() ? info.es : info.en));
        loadTireQuotes();
      } else { toast(json.error || 'Update failed', true); }
    } catch (err) { console.error('updateStatus:', err); toast(t('tqNetworkError', 'Network error'), true); }
  }

  // ─── Expose ─────────────────────────────────────────────────
  window.loadTireQuotes = loadTireQuotes;
})();
