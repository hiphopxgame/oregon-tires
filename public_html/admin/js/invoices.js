/**
 * Oregon Tires — Invoice Management
 * Admin panel CRUD for invoices: list, create from RO, send, mark paid, void.
 *
 * Depends on: api(), showToast(), csrfToken, currentLang, adminT from admin/index.html
 */
(function() {
  'use strict';

  var page = 1;
  var perPage = 20;
  var statusFilter = '';
  var searchQuery = '';

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }

  function statusLabel(s) {
    var map = { draft: t('invDraft', 'Draft'), sent: t('invSent', 'Sent'), paid: t('invPaid', 'Paid'), void: t('invVoid', 'Void') };
    return map[s] || s;
  }

  function statusClass(s) {
    var map = {
      draft: 'bg-gray-100 text-gray-700',
      sent: 'bg-blue-100 text-blue-700',
      paid: 'bg-green-100 text-green-700',
      void: 'bg-red-100 text-red-700',
    };
    return map[s] || 'bg-gray-100 text-gray-700';
  }

  function formatCurrency(n) {
    return '$' + parseFloat(n || 0).toFixed(2);
  }

  function formatDate(str) {
    if (!str) return '-';
    var d = new Date(str);
    var lang = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
    return d.toLocaleDateString(lang, { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function apiHeaders() {
    return { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken };
  }

  function apiOpts(method, body) {
    var opts = { method: method, credentials: 'include', headers: apiHeaders() };
    if (body) opts.body = JSON.stringify(body);
    return opts;
  }

  // ─── Filter Bar ────────────────────────────────────────────────────────────
  function renderFilterBar(container) {
    var bar = document.createElement('div');
    bar.className = 'flex flex-wrap items-center gap-3 mb-4';

    var select = document.createElement('select');
    select.className = 'border rounded px-3 py-2 text-sm';
    var statuses = ['', 'draft', 'sent', 'paid', 'void'];
    var labels = [t('invAllStatuses', 'All Statuses'), t('invDraft', 'Draft'), t('invSent', 'Sent'), t('invPaid', 'Paid'), t('invVoid', 'Void')];
    statuses.forEach(function(val, i) {
      var opt = document.createElement('option');
      opt.value = val;
      opt.textContent = labels[i];
      if (val === statusFilter) opt.selected = true;
      select.appendChild(opt);
    });
    select.addEventListener('change', function() {
      statusFilter = this.value;
      page = 1;
      loadInvoices();
    });

    var input = document.createElement('input');
    input.type = 'text';
    input.placeholder = t('invSearchPlaceholder', 'Search invoices…');
    input.className = 'border rounded px-3 py-2 text-sm flex-1 min-w-[180px]';
    input.value = searchQuery;
    var debounce = null;
    input.addEventListener('input', function() {
      var self = this;
      clearTimeout(debounce);
      debounce = setTimeout(function() {
        searchQuery = self.value.trim();
        page = 1;
        loadInvoices();
      }, 350);
    });

    var createBtn = document.createElement('button');
    createBtn.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium ml-auto';
    createBtn.textContent = t('invCreateFromRo', '+ Create from RO');
    createBtn.addEventListener('click', function() {
      var roNum = prompt(t('invEnterRoId', 'Enter Repair Order number (e.g. RO-00000001):'));
      if (roNum) createInvoice(roNum.trim());
    });

    bar.appendChild(select);
    bar.appendChild(input);
    bar.appendChild(createBtn);
    container.appendChild(bar);
  }

  // ─── Table Render ──────────────────────────────────────────────────────────
  function renderTable(container, invoices) {
    var table = document.createElement('table');
    table.className = 'w-full text-sm border-collapse';

    var thead = document.createElement('thead');
    var headRow = document.createElement('tr');
    headRow.className = 'border-b bg-gray-50 text-left';
    var colKeys = [
      ['invInvoiceNum', 'Invoice #'], ['invCustomer', 'Customer'], ['invRoNum', 'RO #'],
      ['invTotal', 'Total'], ['invStatus', 'Status'], ['invDate', 'Date'], ['invActions', 'Actions']
    ];
    colKeys.forEach(function(pair) {
      var th = document.createElement('th');
      th.className = 'px-3 py-2 font-medium';
      th.textContent = t(pair[0], pair[1]);
      headRow.appendChild(th);
    });
    thead.appendChild(headRow);
    table.appendChild(thead);

    var tbody = document.createElement('tbody');
    if (!invoices || invoices.length === 0) {
      var emptyRow = document.createElement('tr');
      var emptyTd = document.createElement('td');
      emptyTd.colSpan = 7;
      emptyTd.className = 'px-3 py-8 text-center text-gray-400';
      emptyTd.textContent = t('invNoInvoices', 'No invoices found.');
      emptyRow.appendChild(emptyTd);
      tbody.appendChild(emptyRow);
    } else {
      invoices.forEach(function(inv) {
        var tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';

        var tdNum = document.createElement('td');
        tdNum.className = 'px-3 py-2 font-mono';
        tdNum.textContent = inv.invoice_number || '-';
        tr.appendChild(tdNum);

        var tdCust = document.createElement('td');
        tdCust.className = 'px-3 py-2';
        tdCust.textContent = inv.customer_name || '-';
        tr.appendChild(tdCust);

        var tdRo = document.createElement('td');
        tdRo.className = 'px-3 py-2 font-mono';
        tdRo.textContent = inv.ro_number || '-';
        tr.appendChild(tdRo);

        var tdTotal = document.createElement('td');
        tdTotal.className = 'px-3 py-2';
        tdTotal.textContent = formatCurrency(inv.total);
        tr.appendChild(tdTotal);

        var tdStatus = document.createElement('td');
        tdStatus.className = 'px-3 py-2';
        var badge = document.createElement('span');
        badge.className = 'px-2 py-0.5 rounded-full text-xs font-medium ' + statusClass(inv.status);
        badge.textContent = statusLabel(inv.status);
        tdStatus.appendChild(badge);
        tr.appendChild(tdStatus);

        var tdDate = document.createElement('td');
        tdDate.className = 'px-3 py-2';
        tdDate.textContent = formatDate(inv.created_at);
        tr.appendChild(tdDate);

        var tdActions = document.createElement('td');
        tdActions.className = 'px-3 py-2 flex gap-1 flex-wrap';
        appendActionButtons(tdActions, inv);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
      });
    }
    table.appendChild(tbody);
    container.appendChild(table);
  }

  function appendActionButtons(td, inv) {
    var viewBtn = makeBtn(t('invView', 'View'), 'bg-gray-200 hover:bg-gray-300 text-gray-800', function() {
      window.open('/api/admin/invoices.php?id=' + inv.id + '&format=html', '_blank');
    });
    td.appendChild(viewBtn);

    if (inv.status === 'draft') {
      td.appendChild(makeBtn(t('invSend', 'Send'), 'bg-blue-500 hover:bg-blue-600 text-white', function() {
        if (confirm(t('invConfirmSend', 'Send this invoice to the customer?'))) sendInvoice(inv.id);
      }));
    }
    if (inv.status === 'sent') {
      td.appendChild(makeBtn(t('invMarkPaid', 'Mark Paid'), 'bg-green-500 hover:bg-green-600 text-white', function() {
        if (confirm(t('invConfirmPaid', 'Mark this invoice as paid?'))) markInvoicePaid(inv.id);
      }));
    }
    if (inv.status !== 'void' && inv.status !== 'paid') {
      td.appendChild(makeBtn(t('invVoidBtn', 'Void'), 'bg-red-500 hover:bg-red-600 text-white', function() {
        if (confirm(t('invConfirmVoid', 'Void this invoice? This cannot be undone.'))) voidInvoice(inv.id);
      }));
    }
  }

  function makeBtn(label, cls, handler) {
    var btn = document.createElement('button');
    btn.className = 'px-2 py-1 rounded text-xs font-medium ' + cls;
    btn.textContent = label;
    btn.addEventListener('click', handler);
    return btn;
  }

  // ─── Pagination ────────────────────────────────────────────────────────────
  function renderPagination(container, totalPages) {
    if (totalPages <= 1) return;
    var nav = document.createElement('div');
    nav.className = 'flex items-center justify-between mt-4 text-sm';

    var prevBtn = document.createElement('button');
    prevBtn.className = 'px-3 py-1 border rounded disabled:opacity-40';
    prevBtn.textContent = t('invPrev', '← Prev');
    prevBtn.disabled = (page <= 1);
    prevBtn.addEventListener('click', function() { page--; loadInvoices(); });

    var info = document.createElement('span');
    info.className = 'text-gray-500';
    info.textContent = t('invPageOf', 'Page {page} of {total}').replace('{page}', page).replace('{total}', totalPages);

    var nextBtn = document.createElement('button');
    nextBtn.className = 'px-3 py-1 border rounded disabled:opacity-40';
    nextBtn.textContent = t('invNext', 'Next →');
    nextBtn.disabled = (page >= totalPages);
    nextBtn.addEventListener('click', function() { page++; loadInvoices(); });

    nav.appendChild(prevBtn);
    nav.appendChild(info);
    nav.appendChild(nextBtn);
    container.appendChild(nav);
  }

  // ─── Main Load ─────────────────────────────────────────────────────────────
  function loadInvoices() {
    var container = document.getElementById('invoices-container');
    if (!container) return;

    while (container.firstChild) container.removeChild(container.firstChild);

    renderFilterBar(container);

    var url = '/api/admin/invoices.php?page=' + page + '&per_page=' + perPage;
    if (statusFilter) url += '&status=' + encodeURIComponent(statusFilter);
    if (searchQuery) url += '&q=' + encodeURIComponent(searchQuery);

    fetch(url, { credentials: 'include', headers: { 'X-CSRF-Token': csrfToken } })
      .then(function(res) { return res.json(); })
      .then(function(json) {
        if (!json.success) {
          showToast(t('invError', 'Something went wrong. Please try again.'), 'error');
          return;
        }
        renderTable(container, json.data || []);
        var totalPages = Math.ceil((json.total || 0) / perPage) || 1;
        renderPagination(container, totalPages);
      })
      .catch(function() { showToast(t('invError', 'Something went wrong. Please try again.'), 'error'); });
  }

  // ─── Actions ───────────────────────────────────────────────────────────────
  function createInvoice(roNumber) {
    fetch('/api/admin/invoices.php', apiOpts('POST', { action: 'create', ro_number: roNumber }))
      .then(function(res) { return res.json(); })
      .then(function(json) {
        showToast(json.success ? t('invCreateSuccess', 'Invoice created.') : (json.error || t('invError', 'Something went wrong. Please try again.')), json.success ? 'success' : 'error');
        if (json.success) loadInvoices();
      })
      .catch(function() { showToast(t('invError', 'Something went wrong. Please try again.'), 'error'); });
  }

  function sendInvoice(id) {
    fetch('/api/admin/invoices.php', apiOpts('POST', { action: 'send', id: id }))
      .then(function(res) { return res.json(); })
      .then(function(json) {
        showToast(json.success ? t('invSendSuccess', 'Invoice sent to customer.') : (json.error || t('invError', 'Something went wrong. Please try again.')), json.success ? 'success' : 'error');
        if (json.success) loadInvoices();
      })
      .catch(function() { showToast(t('invError', 'Something went wrong. Please try again.'), 'error'); });
  }

  function markInvoicePaid(id) {
    fetch('/api/admin/invoices.php', apiOpts('POST', { action: 'mark_paid', id: id }))
      .then(function(res) { return res.json(); })
      .then(function(json) {
        showToast(json.success ? t('invPaidSuccess', 'Invoice marked as paid.') : (json.error || t('invError', 'Something went wrong. Please try again.')), json.success ? 'success' : 'error');
        if (json.success) loadInvoices();
      })
      .catch(function() { showToast(t('invError', 'Something went wrong. Please try again.'), 'error'); });
  }

  function voidInvoice(id) {
    fetch('/api/admin/invoices.php', apiOpts('POST', { action: 'void', id: id }))
      .then(function(res) { return res.json(); })
      .then(function(json) {
        showToast(json.success ? t('invVoidSuccess', 'Invoice voided.') : (json.error || t('invError', 'Something went wrong. Please try again.')), json.success ? 'success' : 'error');
        if (json.success) loadInvoices();
      })
      .catch(function() { showToast(t('invError', 'Something went wrong. Please try again.'), 'error'); });
  }

  // ─── Expose globally ──────────────────────────────────────────────────────
  window.loadInvoices = loadInvoices;

})();
