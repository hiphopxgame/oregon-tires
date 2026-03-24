/**
 * Oregon Tires — Admin Parts & Inventory Manager
 * Sub-tabs: Catalog, Vendors, Orders
 * Uses createElement/appendChild only (no innerHTML per security rules).
 */
(function() {
  'use strict';

  var API_CATALOG = '/api/admin/parts-catalog.php';
  var API_VENDORS = '/api/admin/vendors.php';
  var API_ORDERS  = '/api/admin/parts-orders.php';

  var catalog = [], vendors = [], orders = [];
  var activeSubTab = 'catalog';
  var catalogSearch = '';
  var editingPartId = null;
  var editingVendorId = null;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function getCsrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : (typeof csrfToken !== 'undefined' ? csrfToken : '');
  }
  function hdrs(json) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (json) h['Content-Type'] = 'application/json';
    return h;
  }
  function el(tag, cls, txt) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (txt !== undefined && txt !== null) n.textContent = String(txt);
    return n;
  }
  function badge(text, color) {
    var colors = {
      green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
      red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
      blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
      amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
      gray: 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300',
      purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    };
    return el('span', 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + (colors[color] || colors.gray), text);
  }
  function input(id, type, val, placeholder) {
    var inp = document.createElement('input');
    inp.type = type || 'text'; inp.id = id;
    inp.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    if (val !== undefined && val !== null) inp.value = val;
    if (placeholder) inp.placeholder = placeholder;
    return inp;
  }
  function label(text) {
    return el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', text);
  }
  function select(id, options, selected) {
    var sel = document.createElement('select');
    sel.id = id;
    sel.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    options.forEach(function(o) {
      var opt = document.createElement('option');
      opt.value = o.value; opt.textContent = o.label;
      if (String(o.value) === String(selected)) opt.selected = true;
      sel.appendChild(opt);
    });
    return sel;
  }
  function modal(id) {
    var old = document.getElementById(id);
    if (old) { old.remove(); return null; }
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center');
    ov.id = id;
    ov.addEventListener('click', function(e) { if (e.target === ov) ov.remove(); });
    return ov;
  }
  function formatDate(str) {
    if (!str) return '\u2014';
    var locale = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
    return new Date(str).toLocaleDateString(locale, { month: 'short', day: 'numeric', year: 'numeric' });
  }
  function formatMoney(val) {
    var n = parseFloat(val);
    return isNaN(n) ? '\u2014' : '$' + n.toFixed(2);
  }
  function spinner() {
    var s = el('div', 'flex justify-center py-12');
    s.appendChild(el('div', 'animate-spin rounded-full h-8 w-8 border-b-2 border-brand'));
    return s;
  }

  // ── Main Load ─────────────────────────────────────────────
  async function loadParts() {
    var c = document.getElementById('tab-parts');
    if (!c) return;
    c.textContent = '';
    c.appendChild(renderSubTabs());
    var content = el('div', '');
    content.id = 'parts-content';
    c.appendChild(content);
    await switchSubTab(activeSubTab);
  }

  // ── Sub-tab Navigation ────────────────────────────────────
  function renderSubTabs() {
    var nav = el('div', 'flex gap-1 mb-6 border-b dark:border-gray-700');
    var tabs = [
      { key: 'catalog', label: t('partsCatalog', 'Catalog') },
      { key: 'vendors', label: t('partsVendors', 'Vendors') },
      { key: 'orders',  label: t('partsOrders', 'Orders') },
    ];
    tabs.forEach(function(tab) {
      var btn = el('button', 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition ' +
        (activeSubTab === tab.key
          ? 'border-brand text-brand dark:text-green-400 dark:border-green-400'
          : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'),
        tab.label);
      btn.setAttribute('data-subtab', tab.key);
      btn.addEventListener('click', function() { switchSubTab(tab.key); });
      nav.appendChild(btn);
    });
    return nav;
  }

  async function switchSubTab(key) {
    activeSubTab = key;
    // Update active state on buttons
    var c = document.getElementById('tab-parts');
    if (c) {
      var btns = c.querySelectorAll('[data-subtab]');
      btns.forEach(function(b) {
        var isActive = b.getAttribute('data-subtab') === key;
        b.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition ' +
          (isActive
            ? 'border-brand text-brand dark:text-green-400 dark:border-green-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300');
      });
    }
    var content = document.getElementById('parts-content');
    if (!content) return;
    content.textContent = '';
    content.appendChild(spinner());

    if (key === 'catalog') { await fetchCatalog(); renderCatalog(content); }
    else if (key === 'vendors') { await fetchVendors(); renderVendors(content); }
    else if (key === 'orders') { await fetchOrders(); renderOrders(content); }
  }

  // ── Catalog ───────────────────────────────────────────────
  async function fetchCatalog() {
    try {
      var url = API_CATALOG;
      if (catalogSearch) url += '?search=' + encodeURIComponent(catalogSearch);
      var res = await fetch(url, { credentials: 'include' });
      var json = await res.json();
      catalog = json.success ? (json.data || []) : [];
      // Also refresh vendors for dropdown
      if (!vendors.length) await fetchVendors();
    } catch (err) {
      console.error('fetchCatalog error:', err);
      if (typeof showToast === 'function') showToast(t('partsLoadFail', 'Failed to load parts'), true);
    }
  }

  function renderCatalog(content) {
    content.textContent = '';
    // Header
    var hdr = el('div', 'flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4');
    var searchWrap = el('div', 'flex gap-2 flex-1 w-full sm:w-auto');
    var searchInp = input('parts-search', 'text', catalogSearch, t('partsSearchPlaceholder', 'Search parts...'));
    searchInp.className = 'flex-1 border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    var timer;
    searchInp.addEventListener('input', function() {
      clearTimeout(timer);
      timer = setTimeout(function() { catalogSearch = searchInp.value.trim(); switchSubTab('catalog'); }, 300);
    });
    searchWrap.appendChild(searchInp);
    hdr.appendChild(searchWrap);
    var addBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 whitespace-nowrap', t('partsNewPart', 'New Part'));
    addBtn.addEventListener('click', function() { openPartModal(); });
    hdr.appendChild(addBtn);
    content.appendChild(hdr);

    // Table
    var wrap = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-x-auto');
    var tbl = el('table', 'w-full text-sm');
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var hr = el('tr', '');
    ['Part #', 'Name', 'Category', 'Price', 'Cost', 'Vendor', 'Stock'].forEach(function(h) {
      hr.appendChild(el('th', 'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase', t('parts' + h.replace(/[\s#]/g, ''), h)));
    });
    thead.appendChild(hr); tbl.appendChild(thead);

    var tbody = document.createElement('tbody');
    if (!catalog.length) {
      var tr = el('tr'), td = el('td', 'text-center py-8 text-gray-400 dark:text-gray-500', t('partsNoParts', 'No parts found. Click "New Part" to add inventory.'));
      td.colSpan = 7; tr.appendChild(td); tbody.appendChild(tr);
    } else {
      catalog.forEach(function(part) {
        var tr = el('tr', 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer');
        tr.addEventListener('click', function() { openPartModal(part); });
        tr.appendChild(el('td', 'px-4 py-3 font-mono text-xs dark:text-gray-200', part.part_number || '\u2014'));
        var tdName = el('td', 'px-4 py-3');
        tdName.appendChild(el('div', 'font-medium dark:text-gray-200', currentLang === 'es' ? (part.name_es || part.name) : part.name));
        if (part.name_es && currentLang === 'en') {
          tdName.appendChild(el('div', 'text-xs text-gray-400 dark:text-gray-500', part.name_es));
        }
        tr.appendChild(tdName);
        tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', part.category || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 dark:text-gray-200', formatMoney(part.default_price)));
        tr.appendChild(el('td', 'px-4 py-3 text-gray-500 dark:text-gray-400', formatMoney(part.cost_price)));
        tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', part.vendor_name || '\u2014'));
        var tdStock = el('td', 'px-4 py-3');
        var stockVal = Number(part.in_stock);
        tdStock.appendChild(badge(stockVal ? t('partsInStock', 'In Stock') : t('partsOutOfStock', 'Out'), stockVal ? 'green' : 'red'));
        if (part.min_stock && Number(part.qty || 0) <= Number(part.min_stock)) {
          tdStock.appendChild(el('div', 'text-xs text-amber-600 dark:text-amber-400 mt-0.5', t('partsLowStock', 'Low stock')));
        }
        tr.appendChild(tdStock);
        tbody.appendChild(tr);
      });
    }
    tbl.appendChild(tbody); wrap.appendChild(tbl); content.appendChild(wrap);
  }

  // ── Part Modal ────────────────────────────────────────────
  function openPartModal(part) {
    var ov = modal('parts-part-modal');
    if (!ov) return;
    editingPartId = part ? part.id : null;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', part ? t('partsEditPart', 'Edit Part') : t('partsNewPart', 'New Part')));

    card.appendChild(label(t('partsPartNumber', 'Part Number')));
    card.appendChild(input('pm-part-number', 'text', part ? part.part_number : ''));
    card.appendChild(label(t('partsName', 'Name (EN)')));
    card.appendChild(input('pm-name', 'text', part ? part.name : ''));
    card.appendChild(label(t('partsNameEs', 'Name (ES)')));
    card.appendChild(input('pm-name-es', 'text', part ? part.name_es : ''));
    card.appendChild(label(t('partsCategory', 'Category')));
    card.appendChild(select('pm-category', [
      { value: '', label: t('partsSelectCategory', '-- Select --') },
      { value: 'tires', label: 'Tires / Llantas' },
      { value: 'brakes', label: 'Brakes / Frenos' },
      { value: 'oil', label: 'Oil & Fluids' },
      { value: 'filters', label: 'Filters' },
      { value: 'electrical', label: 'Electrical' },
      { value: 'suspension', label: 'Suspension' },
      { value: 'other', label: 'Other' },
    ], part ? part.category : ''));
    card.appendChild(label(t('partsPrice', 'Default Price')));
    card.appendChild(input('pm-price', 'number', part ? part.default_price : ''));
    card.appendChild(label(t('partsCost', 'Cost Price')));
    card.appendChild(input('pm-cost', 'number', part ? part.cost_price : ''));
    card.appendChild(label(t('partsVendor', 'Vendor')));
    var vendorOpts = [{ value: '', label: t('partsSelectVendor', '-- Select Vendor --') }];
    vendors.forEach(function(v) { vendorOpts.push({ value: v.id, label: v.name }); });
    card.appendChild(select('pm-vendor', vendorOpts, part ? part.vendor_id : ''));

    // In-stock toggle
    var toggleWrap = el('div', 'flex items-center gap-3 mb-3');
    var cb = document.createElement('input');
    cb.type = 'checkbox'; cb.id = 'pm-in-stock';
    cb.className = 'w-4 h-4 accent-brand';
    cb.checked = part ? Number(part.in_stock) === 1 : true;
    toggleWrap.appendChild(cb);
    toggleWrap.appendChild(el('label', 'text-sm dark:text-gray-300', t('partsInStockLabel', 'In Stock')));
    card.appendChild(toggleWrap);

    card.appendChild(label(t('partsMinStock', 'Min Stock Level')));
    card.appendChild(input('pm-min-stock', 'number', part ? part.min_stock : ''));
    card.appendChild(label(t('partsNotes', 'Notes')));
    var ta = document.createElement('textarea');
    ta.id = 'pm-notes'; ta.rows = 2;
    ta.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    ta.value = part ? (part.notes || '') : '';
    card.appendChild(ta);

    // Buttons
    var row = el('div', 'flex gap-3 justify-end mt-2');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    if (part) {
      var del = el('button', 'bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700', t('actionDeactivate', 'Deactivate'));
      del.addEventListener('click', function() { deactivatePart(part.id, ov); });
      row.appendChild(del);
    }
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('actionSave', 'Save'));
    save.addEventListener('click', function() { savePart(ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  async function savePart(ov) {
    var data = {
      _csrf: getCsrf(),
      part_number: document.getElementById('pm-part-number').value.trim(),
      name: document.getElementById('pm-name').value.trim(),
      name_es: document.getElementById('pm-name-es').value.trim(),
      category: document.getElementById('pm-category').value,
      default_price: document.getElementById('pm-price').value,
      cost_price: document.getElementById('pm-cost').value,
      vendor_id: document.getElementById('pm-vendor').value || null,
      in_stock: document.getElementById('pm-in-stock').checked ? 1 : 0,
      min_stock: document.getElementById('pm-min-stock').value || 0,
      notes: document.getElementById('pm-notes').value.trim(),
    };
    if (!data.name) { showToast(t('partsNameRequired', 'Part name is required'), true); return; }
    try {
      var method = editingPartId ? 'PUT' : 'POST';
      if (editingPartId) data.id = editingPartId;
      var res = await fetch(API_CATALOG, { method: method, headers: hdrs(true), credentials: 'include', body: JSON.stringify(data) });
      var json = await res.json();
      if (json.success) {
        showToast(editingPartId ? t('partsUpdated', 'Part updated') : t('partsCreated', 'Part created'));
        ov.remove(); switchSubTab('catalog');
      } else { showToast(json.error || t('partsSaveFail', 'Save failed'), true); }
    } catch (err) {
      console.error('savePart error:', err);
      showToast(t('partsNetworkError', 'Network error'), true);
    }
  }

  async function deactivatePart(id, ov) {
    try {
      var res = await fetch(API_CATALOG, { method: 'DELETE', headers: hdrs(true), credentials: 'include', body: JSON.stringify({ _csrf: getCsrf(), id: id }) });
      var json = await res.json();
      if (json.success) { showToast(t('partsDeactivated', 'Part deactivated')); ov.remove(); switchSubTab('catalog'); }
      else { showToast(json.error || t('partsDeleteFail', 'Deactivate failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  // ── Vendors ───────────────────────────────────────────────
  async function fetchVendors() {
    try {
      var res = await fetch(API_VENDORS, { credentials: 'include' });
      var json = await res.json();
      vendors = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchVendors error:', err);
    }
  }

  function renderVendors(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('partsVendorList', 'Vendors')));
    var addBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('partsNewVendor', 'New Vendor'));
    addBtn.addEventListener('click', function() { openVendorModal(); });
    hdr.appendChild(addBtn);
    content.appendChild(hdr);

    var wrap = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-x-auto');
    var tbl = el('table', 'w-full text-sm');
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var hr = el('tr');
    ['Name', 'Contact', 'Email', 'Phone', 'Account #', 'Actions'].forEach(function(h) {
      hr.appendChild(el('th', 'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase', h));
    });
    thead.appendChild(hr); tbl.appendChild(thead);

    var tbody = document.createElement('tbody');
    if (!vendors.length) {
      var tr = el('tr'), td = el('td', 'text-center py-8 text-gray-400 dark:text-gray-500', t('partsNoVendors', 'No vendors yet. Click "New Vendor" to add one.'));
      td.colSpan = 6; tr.appendChild(td); tbody.appendChild(tr);
    } else {
      vendors.forEach(function(v) {
        var tr = el('tr', 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50');
        tr.appendChild(el('td', 'px-4 py-3 font-medium dark:text-gray-200', v.name || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', v.contact_name || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', v.email || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', v.phone || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 font-mono text-xs dark:text-gray-300', v.account_number || '\u2014'));
        var tdA = el('td', 'px-4 py-3');
        var acts = el('div', 'flex gap-2');
        var eB = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('actionEdit', 'Edit'));
        eB.addEventListener('click', function(e) { e.stopPropagation(); openVendorModal(v); });
        acts.appendChild(eB);
        var dB = el('button', 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400', t('actionDeactivate', 'Deactivate'));
        dB.addEventListener('click', function(e) { e.stopPropagation(); deactivateVendor(v.id); });
        acts.appendChild(dB);
        tdA.appendChild(acts); tr.appendChild(tdA);
        tbody.appendChild(tr);
      });
    }
    tbl.appendChild(tbody); wrap.appendChild(tbl); content.appendChild(wrap);
  }

  function openVendorModal(v) {
    var ov = modal('parts-vendor-modal');
    if (!ov) return;
    editingVendorId = v ? v.id : null;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md mx-4');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', v ? t('partsEditVendor', 'Edit Vendor') : t('partsNewVendor', 'New Vendor')));
    card.appendChild(label(t('partsVendorName', 'Vendor Name')));
    card.appendChild(input('vm-name', 'text', v ? v.name : ''));
    card.appendChild(label(t('partsContactName', 'Contact Name')));
    card.appendChild(input('vm-contact', 'text', v ? v.contact_name : ''));
    card.appendChild(label(t('partsEmail', 'Email')));
    card.appendChild(input('vm-email', 'email', v ? v.email : ''));
    card.appendChild(label(t('partsPhone', 'Phone')));
    card.appendChild(input('vm-phone', 'tel', v ? v.phone : ''));
    card.appendChild(label(t('partsAccountNumber', 'Account Number')));
    card.appendChild(input('vm-account', 'text', v ? v.account_number : ''));
    card.appendChild(label(t('partsNotes', 'Notes')));
    var ta = document.createElement('textarea');
    ta.id = 'vm-notes'; ta.rows = 2;
    ta.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    ta.value = v ? (v.notes || '') : '';
    card.appendChild(ta);

    var row = el('div', 'flex gap-3 justify-end mt-2');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('actionSave', 'Save'));
    save.addEventListener('click', function() { saveVendor(ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  async function saveVendor(ov) {
    var data = {
      _csrf: getCsrf(),
      name: document.getElementById('vm-name').value.trim(),
      contact_name: document.getElementById('vm-contact').value.trim(),
      email: document.getElementById('vm-email').value.trim(),
      phone: document.getElementById('vm-phone').value.trim(),
      account_number: document.getElementById('vm-account').value.trim(),
      notes: document.getElementById('vm-notes').value.trim(),
    };
    if (!data.name) { showToast(t('partsVendorNameRequired', 'Vendor name is required'), true); return; }
    try {
      var method = editingVendorId ? 'PUT' : 'POST';
      if (editingVendorId) data.id = editingVendorId;
      var res = await fetch(API_VENDORS, { method: method, headers: hdrs(true), credentials: 'include', body: JSON.stringify(data) });
      var json = await res.json();
      if (json.success) {
        showToast(editingVendorId ? t('partsVendorUpdated', 'Vendor updated') : t('partsVendorCreated', 'Vendor created'));
        ov.remove(); switchSubTab('vendors');
      } else { showToast(json.error || t('partsVendorSaveFail', 'Save failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  async function deactivateVendor(id) {
    try {
      var res = await fetch(API_VENDORS, { method: 'DELETE', headers: hdrs(true), credentials: 'include', body: JSON.stringify({ _csrf: getCsrf(), id: id }) });
      var json = await res.json();
      if (json.success) { showToast(t('partsVendorDeactivated', 'Vendor deactivated')); switchSubTab('vendors'); }
      else { showToast(json.error || t('partsVendorDeleteFail', 'Deactivate failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  // ── Orders ────────────────────────────────────────────────
  var ORDER_STATUSES = ['draft', 'ordered', 'shipped', 'partial', 'received'];
  var ORDER_STATUS_COLORS = {
    draft: 'gray', ordered: 'blue', shipped: 'purple', partial: 'amber', received: 'green', cancelled: 'red',
  };

  async function fetchOrders() {
    try {
      var res = await fetch(API_ORDERS, { credentials: 'include' });
      var json = await res.json();
      orders = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchOrders error:', err);
      if (typeof showToast === 'function') showToast(t('partsOrdersLoadFail', 'Failed to load orders'), true);
    }
  }

  function renderOrders(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('partsOrderPipeline', 'Order Pipeline')));
    var addBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('partsNewOrder', 'New Order'));
    addBtn.addEventListener('click', function() { openOrderModal(); });
    hdr.appendChild(addBtn);
    content.appendChild(hdr);

    // Check for RO param
    var urlParams = new URLSearchParams(window.location.search);
    var fromRo = urlParams.get('from_ro');
    if (fromRo) {
      var notice = el('div', 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4 text-sm text-blue-700 dark:text-blue-300',
        t('partsOrderFromRo', 'Creating order for RO: ') + fromRo);
      content.appendChild(notice);
    }

    // Pipeline columns
    var pipeline = el('div', 'grid grid-cols-1 md:grid-cols-5 gap-4');
    ORDER_STATUSES.forEach(function(status) {
      var col = el('div', 'bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 min-h-[200px]');
      var colHdr = el('div', 'flex items-center justify-between mb-3');
      var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
      colHdr.appendChild(el('span', 'text-sm font-semibold dark:text-gray-200', t('partsStatus_' + status, statusLabel)));
      var statusOrders = orders.filter(function(o) { return o.status === status; });
      colHdr.appendChild(badge(String(statusOrders.length), ORDER_STATUS_COLORS[status]));
      col.appendChild(colHdr);

      if (!statusOrders.length) {
        col.appendChild(el('p', 'text-xs text-gray-400 dark:text-gray-500 text-center py-4', t('partsNoOrdersInStatus', 'No orders')));
      } else {
        statusOrders.forEach(function(order) {
          var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-3 mb-2 cursor-pointer hover:shadow-sm transition');
          card.appendChild(el('div', 'font-mono text-xs font-bold dark:text-gray-200 mb-1', order.order_number || 'PO-' + order.id));
          card.appendChild(el('div', 'text-sm dark:text-gray-300', order.vendor_name || t('partsNoVendor', 'No vendor')));
          var meta = el('div', 'flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-gray-400');
          meta.appendChild(el('span', '', (order.item_count || 0) + ' ' + t('partsItems', 'items')));
          meta.appendChild(el('span', 'font-medium', formatMoney(order.total)));
          card.appendChild(meta);
          if (order.tracking_number) {
            card.appendChild(el('div', 'text-xs text-blue-600 dark:text-blue-400 mt-1 truncate', order.tracking_number));
          }
          card.addEventListener('click', function() { openOrderDetail(order); });
          col.appendChild(card);
        });
      }
      pipeline.appendChild(col);
    });
    content.appendChild(pipeline);
  }

  // ── Order Detail ──────────────────────────────────────────
  function openOrderDetail(order) {
    var ov = modal('parts-order-detail');
    if (!ov) return;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto');
    var titleRow = el('div', 'flex items-center justify-between mb-4');
    titleRow.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', order.order_number || 'Order #' + order.id));
    titleRow.appendChild(badge(order.status.charAt(0).toUpperCase() + order.status.slice(1), ORDER_STATUS_COLORS[order.status]));
    card.appendChild(titleRow);

    // Info grid
    var info = el('div', 'grid grid-cols-2 gap-3 mb-4 text-sm');
    info.appendChild(el('div', 'text-gray-500 dark:text-gray-400', t('partsVendor', 'Vendor')));
    info.appendChild(el('div', 'dark:text-gray-200', order.vendor_name || '\u2014'));
    info.appendChild(el('div', 'text-gray-500 dark:text-gray-400', t('partsOrderDate', 'Order Date')));
    info.appendChild(el('div', 'dark:text-gray-200', formatDate(order.created_at)));
    if (order.tracking_number) {
      info.appendChild(el('div', 'text-gray-500 dark:text-gray-400', t('partsTracking', 'Tracking')));
      info.appendChild(el('div', 'dark:text-gray-200 font-mono text-xs', order.tracking_number));
    }
    if (order.ro_number) {
      info.appendChild(el('div', 'text-gray-500 dark:text-gray-400', t('partsLinkedRO', 'Linked RO')));
      info.appendChild(el('div', 'dark:text-gray-200 font-mono', order.ro_number));
    }
    card.appendChild(info);

    // Items table
    card.appendChild(el('h4', 'text-sm font-semibold mb-2 dark:text-gray-200', t('partsOrderItems', 'Items')));
    var items = order.items || [];
    if (items.length) {
      var tbl = el('table', 'w-full text-sm mb-4');
      var thead = document.createElement('thead');
      thead.className = 'bg-gray-50 dark:bg-gray-700';
      var hr = el('tr');
      ['Part', 'Qty', 'Price', 'Received'].forEach(function(h) {
        hr.appendChild(el('th', 'px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400', h));
      });
      thead.appendChild(hr); tbl.appendChild(thead);
      var tbody = document.createElement('tbody');
      items.forEach(function(item) {
        var tr = el('tr', 'border-b dark:border-gray-700');
        tr.appendChild(el('td', 'px-3 py-2 dark:text-gray-200', item.part_name || item.part_number || '\u2014'));
        tr.appendChild(el('td', 'px-3 py-2 dark:text-gray-300', String(item.qty || 0)));
        tr.appendChild(el('td', 'px-3 py-2 dark:text-gray-300', formatMoney(item.unit_price)));
        var tdR = el('td', 'px-3 py-2');
        if (order.status === 'shipped' || order.status === 'partial') {
          var rb = el('button', 'text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700',
            item.received ? t('partsReceived', 'Received') : t('partsMarkReceived', 'Mark Received'));
          if (!item.received) {
            rb.addEventListener('click', function() { receiveItem(order.id, item.id, ov); });
          } else {
            rb.disabled = true; rb.className += ' opacity-50 cursor-not-allowed';
          }
          tdR.appendChild(rb);
        } else {
          tdR.appendChild(el('span', 'text-xs text-gray-400', item.received ? t('partsReceived', 'Received') : '\u2014'));
        }
        tr.appendChild(tdR);
        tbody.appendChild(tr);
      });
      tbl.appendChild(tbody); card.appendChild(tbl);
    } else {
      card.appendChild(el('p', 'text-sm text-gray-400 dark:text-gray-500 mb-4', t('partsNoItems', 'No items in this order.')));
    }

    // Total
    card.appendChild(el('div', 'text-right font-bold dark:text-gray-100 mb-4', t('partsTotal', 'Total') + ': ' + formatMoney(order.total)));

    // Action buttons
    var actions = el('div', 'flex flex-wrap gap-2 justify-end border-t dark:border-gray-700 pt-4');
    var nextStatus = { draft: 'ordered', ordered: 'shipped', shipped: 'partial', partial: 'received' };
    if (nextStatus[order.status]) {
      var ns = nextStatus[order.status];
      var advBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90',
        t('partsMoveTo', 'Move to ') + ns.charAt(0).toUpperCase() + ns.slice(1));
      advBtn.addEventListener('click', function() { transitionOrder(order.id, ns, ov); });
      actions.appendChild(advBtn);
    }
    if (order.status === 'draft') {
      var delBtn = el('button', 'bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700', t('actionCancel', 'Cancel'));
      delBtn.addEventListener('click', function() { cancelOrder(order.id, ov); });
      actions.appendChild(delBtn);
    }
    var closeBtn = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionClose', 'Close'));
    closeBtn.addEventListener('click', function() { ov.remove(); });
    actions.appendChild(closeBtn);
    card.appendChild(actions);
    ov.appendChild(card); document.body.appendChild(ov);
  }

  async function transitionOrder(id, newStatus, ov) {
    try {
      var res = await fetch(API_ORDERS, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), action: 'transition', id: id, status: newStatus }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('partsOrderUpdated', 'Order updated')); ov.remove(); switchSubTab('orders'); }
      else { showToast(json.error || t('partsTransitionFail', 'Status change failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  async function receiveItem(orderId, itemId, ov) {
    try {
      var res = await fetch(API_ORDERS, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), action: 'receive_item', order_id: orderId, item_id: itemId }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('partsItemReceived', 'Item received')); ov.remove(); switchSubTab('orders'); }
      else { showToast(json.error || t('partsReceiveFail', 'Receive failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  async function cancelOrder(id, ov) {
    try {
      var res = await fetch(API_ORDERS, { method: 'DELETE', headers: hdrs(true), credentials: 'include', body: JSON.stringify({ _csrf: getCsrf(), id: id }) });
      var json = await res.json();
      if (json.success) { showToast(t('partsOrderCancelled', 'Order cancelled')); ov.remove(); switchSubTab('orders'); }
      else { showToast(json.error || t('partsCancelFail', 'Cancel failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  // ── New Order Modal ───────────────────────────────────────
  function openOrderModal(roNumber) {
    var ov = modal('parts-new-order');
    if (!ov) return;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md mx-4');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', t('partsNewOrder', 'New Order')));

    card.appendChild(label(t('partsVendor', 'Vendor')));
    var vendorOpts = [{ value: '', label: t('partsSelectVendor', '-- Select Vendor --') }];
    vendors.forEach(function(v) { vendorOpts.push({ value: v.id, label: v.name }); });
    card.appendChild(select('no-vendor', vendorOpts, ''));

    card.appendChild(label(t('partsLinkedRO', 'Linked RO (optional)')));
    var urlParams = new URLSearchParams(window.location.search);
    card.appendChild(input('no-ro', 'text', roNumber || urlParams.get('from_ro') || '', 'RO-XXXXXXXX'));

    card.appendChild(label(t('partsNotes', 'Notes')));
    var ta = document.createElement('textarea');
    ta.id = 'no-notes'; ta.rows = 2;
    ta.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    card.appendChild(ta);

    var row = el('div', 'flex gap-3 justify-end mt-2');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('partsCreateOrder', 'Create Order'));
    save.addEventListener('click', function() { createOrder(ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  async function createOrder(ov) {
    var vendorId = document.getElementById('no-vendor').value;
    if (!vendorId) { showToast(t('partsSelectVendorRequired', 'Select a vendor'), true); return; }
    var data = {
      _csrf: getCsrf(),
      vendor_id: vendorId,
      ro_number: document.getElementById('no-ro').value.trim() || null,
      notes: document.getElementById('no-notes').value.trim(),
    };
    try {
      var res = await fetch(API_ORDERS, { method: 'POST', headers: hdrs(true), credentials: 'include', body: JSON.stringify(data) });
      var json = await res.json();
      if (json.success) { showToast(t('partsOrderCreated', 'Order created')); ov.remove(); switchSubTab('orders'); }
      else { showToast(json.error || t('partsOrderCreateFail', 'Create failed'), true); }
    } catch (err) { showToast(t('partsNetworkError', 'Network error'), true); }
  }

  // ── Public API ────────────────────────────────────────────
  window.loadParts = loadParts;
  window.createPartsOrderForRO = function(roNumber) {
    activeSubTab = 'orders';
    loadParts().then(function() { openOrderModal(roNumber); });
  };
})();
