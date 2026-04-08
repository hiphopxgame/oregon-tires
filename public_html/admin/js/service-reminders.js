/**
 * Oregon Tires — Admin Service Reminders Management
 * Handles CRUD, filtering, and sending of service reminders.
 */
(function() {
  'use strict';

  const API = '/api/admin/service-reminders.php';
  let reminders = [];
  let editingId = null;
  let filterStatus = '';
  let filterOverdue = false;

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  function getCsrf() {
    return (typeof csrfToken !== 'undefined' && csrfToken) ? csrfToken : '';
  }

  function getHeaders(isJson) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }

  function locale() {
    return (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString(locale(), { month: 'short', day: 'numeric', year: 'numeric' });
  }

  var serviceTypes = [
    { value: 'oil_change', en: 'Oil Change', es: 'Cambio de Aceite' },
    { value: 'tire_rotation', en: 'Tire Rotation', es: 'Rotaci\u00f3n de Llantas' },
    { value: 'brake_inspection', en: 'Brake Inspection', es: 'Inspecci\u00f3n de Frenos' },
    { value: 'alignment', en: 'Alignment', es: 'Alineaci\u00f3n' },
    { value: 'tire_replacement', en: 'Tire Replacement', es: 'Reemplazo de Llantas' },
    { value: 'fluid_check', en: 'Fluid Check', es: 'Revisi\u00f3n de Fluidos' },
    { value: 'battery_check', en: 'Battery Check', es: 'Revisi\u00f3n de Bater\u00eda' },
    { value: 'engine_diagnostic', en: 'Engine Diagnostic', es: 'Diagn\u00f3stico de Motor' }
  ];

  var statusLabels = {
    pending:   { en: 'Pending', es: 'Pendiente', cls: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' },
    sent:      { en: 'Sent', es: 'Enviado', cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
    completed: { en: 'Completed', es: 'Completado', cls: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
    overdue:   { en: 'Overdue', es: 'Vencido', cls: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }
  };

  // ─── BulkManager Init ──────────────────────────────────────
  if (typeof BulkManager !== 'undefined') {
    BulkManager.init({ tab: 'service-reminders', endpoint: 'service-reminders.php', onDelete: function() { loadServiceReminders(); }, superAdminOnly: false, deleteWarning: 'reminderBulkDeleteWarn' });
  }

  // ─── Load Reminders ───────────────────────────────────────────
  window.loadServiceReminders = async function() {
    var params = new URLSearchParams();
    if (filterStatus) params.set('status', filterStatus);
    if (filterOverdue) params.set('overdue', '1');

    try {
      var res = await fetch(API + '?' + params.toString(), { credentials: 'include' });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed to load');
      reminders = json.data || [];
      renderReminders();
    } catch (err) {
      console.error('loadServiceReminders error:', err);
      var c = document.getElementById('reminders-container');
      if (c) {
        c.textContent = '';
        var p = document.createElement('p');
        p.className = 'text-red-600 dark:text-red-400 p-4';
        p.textContent = t('reminderLoadFail', 'Failed to load service reminders.');
        c.appendChild(p);
      }
    }
  };

  // ─── Render ───────────────────────────────────────────────────
  function renderReminders() {
    var container = document.getElementById('reminders-container');
    if (!container) return;
    container.textContent = '';
    if (typeof BulkManager !== 'undefined') BulkManager.reset();

    // Filter bar
    var filterBar = document.createElement('div');
    filterBar.className = 'flex flex-wrap items-center gap-3 mb-4';

    var statusSelect = document.createElement('select');
    statusSelect.className = 'border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    var allOpt = document.createElement('option');
    allOpt.value = '';
    allOpt.textContent = t('reminderAllStatuses', 'All Statuses');
    statusSelect.appendChild(allOpt);
    Object.keys(statusLabels).forEach(function(key) {
      var opt = document.createElement('option');
      opt.value = key;
      opt.textContent = statusLabels[key][locale() === 'es-MX' ? 'es' : 'en'];
      if (key === filterStatus) opt.selected = true;
      statusSelect.appendChild(opt);
    });
    statusSelect.addEventListener('change', function() {
      filterStatus = this.value;
      loadServiceReminders();
    });
    filterBar.appendChild(statusSelect);

    var overdueLabel = document.createElement('label');
    overdueLabel.className = 'flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer';
    var overdueCb = document.createElement('input');
    overdueCb.type = 'checkbox';
    overdueCb.checked = filterOverdue;
    overdueCb.className = 'rounded';
    overdueCb.addEventListener('change', function() {
      filterOverdue = this.checked;
      loadServiceReminders();
    });
    overdueLabel.appendChild(overdueCb);
    var overdueText = document.createElement('span');
    overdueText.textContent = t('reminderOverdueOnly', 'Overdue Only');
    overdueLabel.appendChild(overdueText);
    filterBar.appendChild(overdueLabel);

    var createBtn = document.createElement('button');
    createBtn.className = 'ml-auto px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition';
    createBtn.textContent = t('reminderCreate', 'Create Reminder');
    createBtn.addEventListener('click', function() { toggleForm(); });
    filterBar.appendChild(createBtn);

    container.appendChild(filterBar);

    // Form panel
    container.appendChild(buildFormPanel());

    if (!reminders.length) {
      var emptyP = document.createElement('p');
      emptyP.className = 'text-gray-500 dark:text-gray-400 text-center py-8';
      emptyP.textContent = t('reminderNone', 'No service reminders found.');
      container.appendChild(emptyP);
      return;
    }

    // Table
    var wrap = document.createElement('div');
    wrap.className = 'overflow-x-auto';
    var table = document.createElement('table');
    table.className = 'w-full text-sm';

    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var hRow = document.createElement('tr');
    var lang = locale() === 'es-MX' ? 'es' : 'en';
    // Checkbox header
    if (typeof BulkManager !== 'undefined') {
      var thCb = document.createElement('th');
      thCb.className = 'w-10 p-3';
      thCb.innerHTML = BulkManager.selectAllHtml();
      hRow.appendChild(thCb);
    }
    var headers = [
      { en: 'Customer', es: 'Cliente' }, { en: 'Vehicle', es: 'Veh\u00edculo' },
      { en: 'Service Type', es: 'Tipo de Servicio' }, { en: 'Due Date', es: 'Fecha L\u00edmite' },
      { en: 'Mileage Due', es: 'Millaje L\u00edmite' }, { en: 'Status', es: 'Estado' },
      { en: 'Last Sent', es: '\u00daltimo Env\u00edo' }, { en: 'Actions', es: 'Acciones' }
    ];
    headers.forEach(function(h) {
      var th = document.createElement('th');
      th.className = 'text-left p-3 font-medium text-gray-600 dark:text-gray-300';
      th.textContent = h[lang];
      hRow.appendChild(th);
    });
    thead.appendChild(hRow);
    table.appendChild(thead);

    var tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-200 dark:divide-gray-700';

    var today = new Date().toISOString().split('T')[0];

    reminders.forEach(function(r) {
      var isOverdue = r.status !== 'completed' && r.due_date && r.due_date < today;
      var tr = document.createElement('tr');
      tr.className = isOverdue
        ? 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20 transition'
        : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition';

      // Checkbox cell
      if (typeof BulkManager !== 'undefined') {
        var tdCb = document.createElement('td');
        tdCb.className = 'p-3';
        tdCb.innerHTML = BulkManager.checkboxHtml(r.id);
        tr.appendChild(tdCb);
      }

      var tdCust = document.createElement('td');
      tdCust.className = 'p-3 font-medium text-gray-800 dark:text-gray-200';
      tdCust.textContent = r.customer_name || '-';
      tr.appendChild(tdCust);

      var tdVeh = document.createElement('td');
      tdVeh.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdVeh.textContent = r.vehicle_label || '-';
      tr.appendChild(tdVeh);

      var tdType = document.createElement('td');
      tdType.className = 'p-3 text-gray-600 dark:text-gray-300';
      var sType = serviceTypes.find(function(s) { return s.value === r.service_type; });
      tdType.textContent = sType ? sType[lang] : (r.service_type || '-');
      tr.appendChild(tdType);

      var tdDue = document.createElement('td');
      tdDue.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdDue.textContent = formatDate(r.due_date);
      tr.appendChild(tdDue);

      var tdMile = document.createElement('td');
      tdMile.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdMile.textContent = r.due_mileage ? Number(r.due_mileage).toLocaleString(locale()) : '-';
      tr.appendChild(tdMile);

      var tdStatus = document.createElement('td');
      tdStatus.className = 'p-3';
      var displayStatus = isOverdue ? 'overdue' : (r.status || 'pending');
      var sInfo = statusLabels[displayStatus] || statusLabels.pending;
      var badge = document.createElement('span');
      badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + sInfo.cls;
      badge.textContent = sInfo[lang];
      tdStatus.appendChild(badge);
      tr.appendChild(tdStatus);

      var tdSent = document.createElement('td');
      tdSent.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdSent.textContent = formatDate(r.last_sent_at);
      tr.appendChild(tdSent);

      var tdAct = document.createElement('td');
      tdAct.className = 'p-3';
      var actWrap = document.createElement('div');
      actWrap.className = 'flex gap-2 flex-wrap';

      if (r.status !== 'completed') {
        var sendBtn = document.createElement('button');
        sendBtn.className = 'text-blue-600 hover:text-blue-800 text-xs font-medium dark:text-blue-400';
        sendBtn.textContent = t('reminderSendNow', 'Send Now');
        sendBtn.addEventListener('click', function() { sendReminder(r.id); });
        actWrap.appendChild(sendBtn);

        var editBtn = document.createElement('button');
        editBtn.className = 'text-amber-600 hover:text-amber-800 text-xs font-medium dark:text-amber-400';
        editBtn.textContent = t('actionEdit', 'Edit');
        editBtn.addEventListener('click', function() { openEdit(r); });
        actWrap.appendChild(editBtn);

        var compBtn = document.createElement('button');
        compBtn.className = 'text-green-600 hover:text-green-800 text-xs font-medium dark:text-green-400';
        compBtn.textContent = t('reminderComplete', 'Complete');
        compBtn.addEventListener('click', function() { completeReminder(r.id); });
        actWrap.appendChild(compBtn);
      }

      var delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-xs font-medium dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function() {
        if (typeof BulkManager !== 'undefined') BulkManager.deleteSingle(r.id, r.customer_name || 'this reminder');
        else deleteReminder(r.id);
      });
      actWrap.appendChild(delBtn);

      tdAct.appendChild(actWrap);
      tr.appendChild(tdAct);
      tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    wrap.appendChild(table);

    // Bulk toolbar
    if (typeof BulkManager !== 'undefined') {
      var toolbarDiv = document.createElement('div');
      toolbarDiv.innerHTML = BulkManager.toolbarHtml();
      wrap.appendChild(toolbarDiv);
    }

    container.appendChild(wrap);

    if (typeof BulkManager !== 'undefined') BulkManager.bind();
  }

  // ─── Form Panel ───────────────────────────────────────────────
  function buildFormPanel() {
    var panel = document.createElement('div');
    panel.id = 'reminder-form-panel';
    panel.className = 'hidden bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-4 mb-4';

    var title = document.createElement('h3');
    title.id = 'reminder-form-title';
    title.className = 'text-lg font-semibold mb-3 dark:text-gray-200';
    title.textContent = t('reminderCreate', 'Create Reminder');
    panel.appendChild(title);

    var grid = document.createElement('div');
    grid.className = 'grid grid-cols-1 md:grid-cols-2 gap-3';

    grid.appendChild(makeInput('reminder-customer-search', t('reminderCustomer', 'Customer Search'), 'text'));
    grid.appendChild(makeSelect('reminder-vehicle', t('reminderVehicle', 'Vehicle'), []));
    grid.appendChild(makeSelect('reminder-service-type', t('reminderServiceType', 'Service Type'),
      serviceTypes.map(function(s) { return { value: s.value, label: s[locale() === 'es-MX' ? 'es' : 'en'] }; })
    ));
    grid.appendChild(makeInput('reminder-due-date', t('reminderDueDate', 'Due Date'), 'date'));
    grid.appendChild(makeInput('reminder-due-mileage', t('reminderDueMileage', 'Due Mileage'), 'number'));
    panel.appendChild(grid);

    // Customer search handler
    var searchInput = grid.querySelector('#reminder-customer-search');
    var debounceTimer = null;
    searchInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      var q = this.value.trim();
      if (q.length < 2) return;
      debounceTimer = setTimeout(function() { searchCustomers(q); }, 300);
    });

    var btnRow = document.createElement('div');
    btnRow.className = 'flex gap-2 mt-4';

    var saveBtn = document.createElement('button');
    saveBtn.id = 'reminder-save-btn';
    saveBtn.className = 'px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition';
    saveBtn.textContent = t('reminderSave', 'Save Reminder');
    saveBtn.addEventListener('click', saveReminder);
    btnRow.appendChild(saveBtn);

    var cancelBtn = document.createElement('button');
    cancelBtn.className = 'px-4 py-2 border text-sm rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-gray-300 transition';
    cancelBtn.textContent = t('actionCancel', 'Cancel');
    cancelBtn.addEventListener('click', function() { panel.classList.add('hidden'); editingId = null; });
    btnRow.appendChild(cancelBtn);

    panel.appendChild(btnRow);
    return panel;
  }

  function makeInput(id, label, type) {
    var wrap = document.createElement('div');
    var lbl = document.createElement('label');
    lbl.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
    lbl.textContent = label;
    lbl.setAttribute('for', id);
    wrap.appendChild(lbl);
    var inp = document.createElement('input');
    inp.type = type;
    inp.id = id;
    inp.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    wrap.appendChild(inp);
    return wrap;
  }

  function makeSelect(id, label, options) {
    var wrap = document.createElement('div');
    var lbl = document.createElement('label');
    lbl.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
    lbl.textContent = label;
    lbl.setAttribute('for', id);
    wrap.appendChild(lbl);
    var sel = document.createElement('select');
    sel.id = id;
    sel.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    var def = document.createElement('option');
    def.value = '';
    def.textContent = '— ' + label + ' —';
    sel.appendChild(def);
    options.forEach(function(o) {
      var opt = document.createElement('option');
      opt.value = o.value;
      opt.textContent = o.label;
      sel.appendChild(opt);
    });
    wrap.appendChild(sel);
    return wrap;
  }

  // ─── Customer Search ──────────────────────────────────────────
  var selectedCustomerId = null;

  async function searchCustomers(query) {
    try {
      var res = await fetch('/api/admin/customers.php?search=' + encodeURIComponent(query) + '&limit=5', {
        credentials: 'include', headers: { 'X-CSRF-Token': getCsrf() }
      });
      var json = await res.json();
      if (!json.success) return;
      var customers = (json.data && json.data.customers) || json.data || [];
      if (customers.length && customers[0]) {
        selectedCustomerId = customers[0].id;
        var input = document.getElementById('reminder-customer-search');
        if (input) input.value = customers[0].name || customers[0].email;
        loadVehicles(customers[0].id);
      }
    } catch (err) { console.error('searchCustomers error:', err); }
  }

  async function loadVehicles(customerId) {
    var sel = document.getElementById('reminder-vehicle');
    if (!sel) return;
    sel.textContent = '';
    var def = document.createElement('option');
    def.value = '';
    def.textContent = '— ' + t('reminderVehicle', 'Vehicle') + ' —';
    sel.appendChild(def);
    try {
      var res = await fetch('/api/admin/vehicles.php?customer_id=' + customerId, {
        credentials: 'include', headers: { 'X-CSRF-Token': getCsrf() }
      });
      var json = await res.json();
      var vehicles = (json.success && json.data) ? json.data : [];
      vehicles.forEach(function(v) {
        var opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = [v.year, v.make, v.model].filter(Boolean).join(' ') || ('Vehicle #' + v.id);
        sel.appendChild(opt);
      });
    } catch (err) { console.error('loadVehicles error:', err); }
  }

  // ─── Toggle / Edit ────────────────────────────────────────────
  function toggleForm() {
    var panel = document.getElementById('reminder-form-panel');
    if (!panel) return;
    editingId = null;
    selectedCustomerId = null;
    var inputs = panel.querySelectorAll('input, select');
    inputs.forEach(function(el) { el.value = ''; });
    var titleEl = document.getElementById('reminder-form-title');
    if (titleEl) titleEl.textContent = t('reminderCreate', 'Create Reminder');
    panel.classList.toggle('hidden');
  }

  function openEdit(r) {
    var panel = document.getElementById('reminder-form-panel');
    if (!panel) return;
    editingId = r.id;
    selectedCustomerId = r.customer_id;
    panel.classList.remove('hidden');
    var titleEl = document.getElementById('reminder-form-title');
    if (titleEl) titleEl.textContent = t('reminderEdit', 'Edit Reminder');
    var cs = document.getElementById('reminder-customer-search');
    if (cs) cs.value = r.customer_name || '';
    if (r.customer_id) loadVehicles(r.customer_id).then(function() {
      var vs = document.getElementById('reminder-vehicle');
      if (vs && r.vehicle_id) vs.value = r.vehicle_id;
    });
    var st = document.getElementById('reminder-service-type');
    if (st) st.value = r.service_type || '';
    var dd = document.getElementById('reminder-due-date');
    if (dd) dd.value = r.due_date || '';
    var dm = document.getElementById('reminder-due-mileage');
    if (dm) dm.value = r.due_mileage || '';
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── CRUD Actions ─────────────────────────────────────────────
  async function saveReminder() {
    var serviceType = document.getElementById('reminder-service-type').value;
    if (!selectedCustomerId || !serviceType) {
      if (typeof showToast === 'function') showToast(t('reminderRequired', 'Customer and service type are required.'), true);
      return;
    }
    var payload = {
      customer_id: selectedCustomerId,
      vehicle_id: document.getElementById('reminder-vehicle').value || null,
      service_type: serviceType,
      due_date: document.getElementById('reminder-due-date').value || null,
      due_mileage: document.getElementById('reminder-due-mileage').value || null
    };
    var method = 'POST';
    if (editingId) { payload.id = editingId; method = 'PUT'; }

    try {
      var res = await fetch(API, { method: method, credentials: 'include', headers: getHeaders(true), body: JSON.stringify(payload) });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingId ? t('reminderUpdated', 'Reminder updated') : t('reminderCreated', 'Reminder created'));
        document.getElementById('reminder-form-panel').classList.add('hidden');
        editingId = null;
        loadServiceReminders();
      } else {
        if (typeof showToast === 'function') showToast(json.message || 'Save failed', true);
      }
    } catch (err) {
      console.error('saveReminder error:', err);
      if (typeof showToast === 'function') showToast(t('reminderNetworkError', 'Network error'), true);
    }
  }

  async function sendReminder(id) {
    try {
      var res = await fetch(API, { method: 'POST', credentials: 'include', headers: getHeaders(true), body: JSON.stringify({ id: id, action: 'send' }) });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('reminderSent', 'Reminder sent'));
        loadServiceReminders();
      } else {
        if (typeof showToast === 'function') showToast(json.message || 'Send failed', true);
      }
    } catch (err) { console.error('sendReminder error:', err); }
  }

  async function completeReminder(id) {
    try {
      var res = await fetch(API, { method: 'PUT', credentials: 'include', headers: getHeaders(true), body: JSON.stringify({ id: id, status: 'completed' }) });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('reminderCompleted', 'Marked as completed'));
        loadServiceReminders();
      } else {
        if (typeof showToast === 'function') showToast(json.message || 'Update failed', true);
      }
    } catch (err) { console.error('completeReminder error:', err); }
  }

  async function deleteReminder(id) {
    if (!confirm(t('reminderDeleteConfirm', 'Delete this reminder? This cannot be undone.'))) return;
    try {
      var res = await fetch(API, { method: 'DELETE', credentials: 'include', headers: getHeaders(true), body: JSON.stringify({ id: id }) });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('reminderDeleted', 'Reminder deleted'));
        loadServiceReminders();
      } else {
        if (typeof showToast === 'function') showToast(json.message || 'Delete failed', true);
      }
    } catch (err) { console.error('deleteReminder error:', err); }
  }

})();
