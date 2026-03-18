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
    new:      { en: 'New',      es: 'Nuevo',    cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' },
    quoted:   { en: 'Quoted',   es: 'Cotizado', cls: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' },
    accepted: { en: 'Accepted', es: 'Aceptado', cls: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' },
    declined: { en: 'Declined', es: 'Rechazado', cls: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }
  };

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function isEs() { return typeof currentLang !== 'undefined' && currentLang === 'es'; }
  function getCsrf() { return (typeof csrfToken !== 'undefined' && csrfToken) ? csrfToken : ''; }
  function hdrs(json) { var h = { 'X-CSRF-Token': getCsrf() }; if (json) h['Content-Type'] = 'application/json'; return h; }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, !!err); }

  function fmtDate(s) {
    if (!s) return '-';
    return new Date(s).toLocaleDateString(isEs() ? 'es-MX' : 'en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  function removeModal() { var m = document.getElementById('tq-modal-overlay'); if (m) m.remove(); }

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

    // Filter bar
    var bar = el('div', 'flex flex-wrap items-center gap-3 mb-4');
    bar.appendChild(el('label', 'text-sm font-medium text-gray-600 dark:text-gray-300', t('tqFilterStatus', 'Status:')));
    var sel = el('select', 'border rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200');
    [['all','All','Todos'],['new','New','Nuevo'],['quoted','Quoted','Cotizado'],['accepted','Accepted','Aceptado'],['declined','Declined','Rechazado']].forEach(function(o) {
      var opt = el('option', null, isEs() ? o[2] : o[1]);
      opt.value = o[0];
      if (o[0] === currentFilter) opt.selected = true;
      sel.appendChild(opt);
    });
    sel.addEventListener('change', function() { currentFilter = sel.value; loadTireQuotes(); });
    bar.appendChild(sel);
    c.appendChild(bar);

    if (!quotes.length) { c.appendChild(el('p', 'text-center py-8 text-gray-400 dark:text-gray-500', t('tqNoQuotes', 'No tire quote requests found.'))); return; }

    var wrap = el('div', 'overflow-x-auto');
    var tbl = el('table', 'w-full text-sm');
    var thead = el('thead', 'bg-gray-50 dark:bg-gray-700');
    var hr = el('tr');
    [t('tqDate','Date'), t('tqCustomer','Customer'), t('tqVehicle','Vehicle'), t('tqTireSize','Tire Size'), t('tqQty','Qty'), t('tqStatus','Status'), t('tqActions','Actions')].forEach(function(txt) {
      hr.appendChild(el('th', 'text-left p-3 font-medium text-gray-600 dark:text-gray-300', txt));
    });
    thead.appendChild(hr); tbl.appendChild(thead);
    var tbody = el('tbody', 'divide-y divide-gray-200 dark:divide-gray-700');
    quotes.forEach(function(q) { tbody.appendChild(buildRow(q)); });
    tbl.appendChild(tbody); wrap.appendChild(tbl); c.appendChild(wrap);
  }

  function buildRow(q) {
    var tr = el('tr', 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition');
    tr.appendChild(el('td', 'p-3 text-gray-600 dark:text-gray-300', fmtDate(q.created_at)));
    tr.appendChild(el('td', 'p-3 font-medium text-gray-800 dark:text-gray-200', q.customer_name || q.customer_email || '-'));
    tr.appendChild(el('td', 'p-3 text-gray-600 dark:text-gray-300', [q.vehicle_year, q.vehicle_make, q.vehicle_model].filter(Boolean).join(' ') || '-'));
    tr.appendChild(el('td', 'p-3 text-gray-600 dark:text-gray-300', q.tire_size || '-'));
    tr.appendChild(el('td', 'p-3 text-gray-600 dark:text-gray-300 text-center', String(q.quantity || '-')));

    var tdS = el('td', 'p-3');
    var info = statusMap[q.status] || statusMap['new'];
    tdS.appendChild(el('span', 'text-xs px-2 py-1 rounded-full ' + info.cls, isEs() ? info.es : info.en));
    tr.appendChild(tdS);

    var tdA = el('td', 'p-3');
    var aw = el('div', 'flex flex-wrap gap-2');
    var viewBtn = el('button', 'text-blue-600 hover:text-blue-800 text-xs font-medium dark:text-blue-400', t('tqView', 'View'));
    viewBtn.addEventListener('click', function() { showDetails(q); });
    aw.appendChild(viewBtn);

    if (q.status === 'new') {
      var qb = el('button', 'text-emerald-600 hover:text-emerald-800 text-xs font-medium dark:text-emerald-400', t('tqRespond', 'Quote'));
      qb.addEventListener('click', function() { openQuoteForm(q); });
      aw.appendChild(qb);
    }
    if (q.status === 'quoted') {
      var ab = el('button', 'text-green-600 hover:text-green-800 text-xs font-medium dark:text-green-400', t('tqAccept', 'Accept'));
      ab.addEventListener('click', function() { updateStatus(q.id, 'accepted'); });
      aw.appendChild(ab);
      var db = el('button', 'text-red-600 hover:text-red-800 text-xs font-medium dark:text-red-400', t('tqDecline', 'Decline'));
      db.addEventListener('click', function() { updateStatus(q.id, 'declined'); });
      aw.appendChild(db);
    }
    tdA.appendChild(aw); tr.appendChild(tdA);
    return tr;
  }

  // ─── Detail Modal ───────────────────────────────────────────
  function showDetails(q) {
    removeModal();
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'tq-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) removeModal(); });
    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 space-y-3 max-h-[90vh] overflow-y-auto');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('tqDetails', 'Quote Request Details')));

    var fields = [
      [t('tqCustomer','Customer'), q.customer_name || '-'],
      [t('tqEmail','Email'), q.customer_email || '-'],
      [t('tqPhone','Phone'), q.customer_phone || '-'],
      [t('tqVehicle','Vehicle'), [q.vehicle_year, q.vehicle_make, q.vehicle_model].filter(Boolean).join(' ') || '-'],
      [t('tqTireSize','Tire Size'), q.tire_size || '-'],
      [t('tqQty','Quantity'), String(q.quantity || '-')],
      [t('tqNotes','Notes'), q.notes || '-'],
      [t('tqDate','Submitted'), fmtDate(q.created_at)]
    ];
    if (q.quote_price) fields.push([t('tqPrice','Price/Tire'), '$' + q.quote_price]);
    if (q.quote_brand) fields.push([t('tqBrand','Brand/Model'), q.quote_brand]);
    if (q.quote_availability) fields.push([t('tqAvail','Availability'), q.quote_availability]);
    if (q.quote_notes) fields.push([t('tqQuoteNotes','Quote Notes'), q.quote_notes]);

    fields.forEach(function(f) {
      var row = el('div');
      row.appendChild(el('span', 'text-xs font-semibold text-gray-500 dark:text-gray-400 block', f[0]));
      row.appendChild(el('span', 'text-sm text-gray-800 dark:text-gray-200 block', f[1]));
      pn.appendChild(row);
    });
    var cb = el('button', 'mt-4 w-full py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('tqClose','Close'));
    cb.addEventListener('click', removeModal);
    pn.appendChild(cb); ov.appendChild(pn); document.body.appendChild(ov);
  }

  // ─── Quote Response Form ────────────────────────────────────
  function openQuoteForm(q) {
    removeModal();
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'tq-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) removeModal(); });
    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('tqRespondTitle', 'Respond with Quote')));
    pn.appendChild(el('p', 'text-sm text-gray-500 dark:text-gray-400', (q.customer_name || '') + ' — ' + (q.tire_size || '') + ' x' + (q.quantity || '')));

    function addField(id, label, type, ph) {
      pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300', label));
      var inp = type === 'textarea' ? document.createElement('textarea') : document.createElement('input');
      if (type === 'textarea') inp.rows = 3; else inp.type = type;
      inp.id = id;
      inp.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
      if (ph) inp.placeholder = ph;
      pn.appendChild(inp);
    }
    addField('tq-price', t('tqPricePerTire','Price per Tire ($)'), 'number', '0.00');
    addField('tq-brand', t('tqBrandModel','Brand / Model'), 'text', 'e.g. Michelin Defender');
    addField('tq-avail', t('tqAvailability','Availability'), 'text', 'e.g. In stock, 2-3 days');
    addField('tq-notes', t('tqQuoteNotes','Notes'), 'textarea', '');

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
    var price = document.getElementById('tq-price').value.trim();
    if (!price) { toast(t('tqPriceReq','Price is required'), true); return; }
    btn.disabled = true;
    try {
      var res = await fetch(API, {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({
          id: id, action: 'quote', quote_price: price,
          quote_brand: document.getElementById('tq-brand').value.trim(),
          quote_availability: document.getElementById('tq-avail').value.trim(),
          quote_notes: document.getElementById('tq-notes').value.trim()
        })
      });
      var json = await res.json();
      if (json.success) { toast(t('tqQuoteSent','Quote sent')); removeModal(); loadTireQuotes(); }
      else { toast(json.error || 'Failed', true); btn.disabled = false; }
    } catch (err) { console.error('submitQuote:', err); toast(t('tqNetworkError', 'Network error'), true); btn.disabled = false; }
  }

  // ─── Update Status ──────────────────────────────────────────
  async function updateStatus(id, status) {
    try {
      var res = await fetch(API, {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({ id: id, action: 'status', status: status })
      });
      var json = await res.json();
      if (json.success) {
        toast(status === 'accepted' ? t('tqAccepted','Marked as accepted') : t('tqDeclined','Marked as declined'));
        loadTireQuotes();
      } else { toast(json.error || 'Update failed', true); }
    } catch (err) { console.error('updateStatus:', err); toast(t('tqNetworkError', 'Network error'), true); }
  }

  // ─── Expose ─────────────────────────────────────────────────
  window.loadTireQuotes = loadTireQuotes;
})();
