/**
 * Oregon Tires — Admin Waitlist (Orders On Hold / Awaiting Parts)
 * Shows repair orders with status 'on_hold' or 'waiting_parts'.
 * Allows quick status changes (resume work, mark ready).
 */
(function() {
  'use strict';

  var refreshTimer = null;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function getCsrf() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, err); }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  function timeAgo(dateStr) {
    if (!dateStr) return '';
    var diff = Date.now() - new Date(dateStr).getTime();
    if (diff < 0) diff = 0;
    var minutes = Math.floor(diff / 60000);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);
    if (days > 0) return days + t('wlDayAgo', 'd ago');
    if (hours > 0) return hours + t('wlHrAgo', 'h ago');
    if (minutes > 0) return minutes + t('wlMinAgo', 'm ago');
    return t('wlJustNow', 'just now');
  }

  var SC = {
    on_hold:       'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    waiting_parts: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
  };

  // ─── BulkManager Init ──────────────────────────────────────
  if (typeof BulkManager !== 'undefined') {
    BulkManager.init({ tab: 'waitlist', endpoint: 'waitlist.php', onDelete: function() { loadWaitlist(); }, superAdminOnly: false, deleteWarning: 'wlBulkDeleteWarn' });
  }

  // ─── Load waitlist (on_hold + waiting_parts ROs) ──────────────
  window.loadWaitlist = async function() {
    var box = document.getElementById('waitlist-container');
    if (!box) return;
    try {
      // Fetch on_hold ROs
      var p1 = fetch('/api/admin/repair-orders.php?status=on_hold&limit=100&sort_by=updated_at&sort_order=ASC', { credentials: 'include' });
      // Fetch waiting_parts ROs
      var p2 = fetch('/api/admin/repair-orders.php?status=waiting_parts&limit=100&sort_by=updated_at&sort_order=ASC', { credentials: 'include' });

      var results = await Promise.all([p1, p2]);
      var json1 = await results[0].json();
      var json2 = await results[1].json();

      var onHold = (json1.data || []);
      var waitingParts = (json2.data || []);
      var allOrders = onHold.concat(waitingParts);

      // Sort by updated_at ascending (longest waiting first)
      allOrders.sort(function(a, b) {
        return new Date(a.updated_at).getTime() - new Date(b.updated_at).getTime();
      });

      render(box, allOrders);
    } catch (err) {
      console.error('loadWaitlist error:', err);
      box.textContent = '';
      box.appendChild(el('p', 'text-red-600 dark:text-red-400 p-4', t('wlFailed', 'Action failed')));
    }
    startAutoRefresh();
  };

  // ─── Render ────────────────────────────────────────────────────
  function render(box, orders) {
    box.textContent = '';
    if (typeof BulkManager !== 'undefined') BulkManager.reset();

    // Summary badges
    var summary = el('div', 'flex gap-4 mb-4 flex-wrap');
    var onHoldCount = orders.filter(function(ro) { return ro.status === 'on_hold'; }).length;
    var partsCount = orders.filter(function(ro) { return ro.status === 'waiting_parts'; }).length;

    var holdBadge = el('div', 'flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg');
    var holdDot = el('span', 'w-3 h-3 rounded-full bg-red-500 inline-block');
    holdBadge.appendChild(holdDot);
    holdBadge.appendChild(el('span', 'text-sm font-semibold text-red-800 dark:text-red-300', t('wlOnHold', 'On Hold') + ': ' + onHoldCount));
    summary.appendChild(holdBadge);

    var partsBadge = el('div', 'flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg');
    var partsDot = el('span', 'w-3 h-3 rounded-full bg-amber-500 inline-block');
    partsBadge.appendChild(partsDot);
    partsBadge.appendChild(el('span', 'text-sm font-semibold text-amber-800 dark:text-amber-300', t('wlAwaitingParts', 'Awaiting Parts') + ': ' + partsCount));
    summary.appendChild(partsBadge);

    box.appendChild(summary);

    if (!orders.length) {
      box.appendChild(el('p', 'text-gray-500 dark:text-gray-400 text-center py-8', t('wlNoEntries', 'No orders on hold.')));
      return;
    }

    box.appendChild(buildTable(orders));
  }

  // ─── Build Table ───────────────────────────────────────────────
  function buildTable(orders) {
    var wrap = el('div', 'overflow-x-auto');
    var tbl = el('table', 'w-full text-sm');
    var thead = el('thead', 'bg-gray-50 dark:bg-gray-700');
    var hr = document.createElement('tr');
    // Checkbox header
    if (typeof BulkManager !== 'undefined') {
      var thCb = el('th', 'w-10 p-3');
      thCb.innerHTML = BulkManager.selectAllHtml();
      hr.appendChild(thCb);
    }
    var cols = [
      t('wlRoNumber', 'RO #'),
      t('wlCustomer', 'Customer'),
      t('wlVehicle', 'Vehicle'),
      t('wlStatus', 'Status'),
      t('wlTimeInStatus', 'Time in Status'),
      t('wlActions', 'Actions')
    ];
    cols.forEach(function(c) {
      hr.appendChild(el('th', 'text-left p-3 font-medium text-gray-600 dark:text-gray-300 text-xs uppercase', c));
    });
    thead.appendChild(hr);
    tbl.appendChild(thead);

    var tbody = el('tbody', 'divide-y divide-gray-200 dark:divide-gray-700');
    orders.forEach(function(ro) {
      var tr = el('tr', 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition');

      // Checkbox cell
      if (typeof BulkManager !== 'undefined') {
        var tdCb = el('td', 'p-3');
        tdCb.innerHTML = BulkManager.checkboxHtml(ro.id);
        tr.appendChild(tdCb);
      }

      // RO Number
      var tdRo = el('td', 'p-3 font-semibold text-green-700 dark:text-green-400', ro.ro_number || '-');
      tr.appendChild(tdRo);

      // Customer
      var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim() || '-';
      tr.appendChild(el('td', 'p-3 text-gray-700 dark:text-gray-300', custName));

      // Vehicle
      var vehicle = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ') || '-';
      tr.appendChild(el('td', 'p-3 text-gray-700 dark:text-gray-300', vehicle));

      // Status badge
      var tdStatus = el('td', 'p-3');
      var statusLabel = ro.status === 'on_hold' ? t('wlOnHold', 'On Hold') : t('wlAwaitingParts', 'Awaiting Parts');
      var badge = el('span', 'inline-flex px-2.5 py-1 rounded-full text-xs font-bold ' + (SC[ro.status] || ''), statusLabel);
      tdStatus.appendChild(badge);
      tr.appendChild(tdStatus);

      // Time in status
      tr.appendChild(el('td', 'p-3 text-gray-500 dark:text-gray-400 text-sm', timeAgo(ro.updated_at)));

      // Actions
      var tdActions = el('td', 'p-3');
      var btnWrap = el('div', 'flex gap-1 flex-wrap');

      // Resume Work → in_progress
      var resumeBtn = el('button', 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 text-xs font-medium px-2 py-1 rounded transition', t('wlResumeWork', 'Resume Work'));
      resumeBtn.addEventListener('click', (function(id) { return function() { updateRoStatus(id, 'in_progress'); }; })(ro.id));
      btnWrap.appendChild(resumeBtn);

      // Mark Ready → ready
      var readyBtn = el('button', 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium px-2 py-1 rounded transition', t('wlMarkReady', 'Mark Ready'));
      readyBtn.addEventListener('click', (function(id) { return function() { updateRoStatus(id, 'ready'); }; })(ro.id));
      btnWrap.appendChild(readyBtn);

      // View RO detail
      var viewBtn = el('button', 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-200 text-xs font-medium px-2 py-1 rounded transition', t('wlViewRo', 'View'));
      viewBtn.addEventListener('click', (function(id) { return function() {
        if (typeof viewRoDetail === 'function') viewRoDetail(id);
      }; })(ro.id));
      btnWrap.appendChild(viewBtn);

      // Delete button
      if (typeof BulkManager !== 'undefined') {
        var delBtn = el('button', 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 text-xs font-medium px-2 py-1 rounded transition', t('actionDelete', 'Delete'));
        delBtn.addEventListener('click', (function(id, num) { return function() { BulkManager.deleteSingle(id, num || 'this entry'); }; })(ro.id, ro.ro_number));
        btnWrap.appendChild(delBtn);
      }

      tdActions.appendChild(btnWrap);
      tr.appendChild(tdActions);

      tbody.appendChild(tr);
    });
    tbl.appendChild(tbody);
    wrap.appendChild(tbl);

    // Bulk toolbar
    if (typeof BulkManager !== 'undefined') {
      var toolbarDiv = el('div');
      toolbarDiv.innerHTML = BulkManager.toolbarHtml();
      wrap.appendChild(toolbarDiv);
      setTimeout(function() { BulkManager.bind(); }, 0);
    }

    return wrap;
  }

  // ─── Update RO Status ─────────────────────────────────────────
  async function updateRoStatus(roId, newStatus) {
    try {
      var res = await fetch('/api/admin/repair-orders.php', {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf() },
        body: JSON.stringify({ id: roId, status: newStatus })
      });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed');
      toast(t('wlUpdated', 'Status updated'));
      loadWaitlist();
      // Also refresh RO table/kanban if available
      if (typeof loadRepairOrders === 'function') loadRepairOrders();
      if (typeof loadKanban === 'function') {
        var kanbanView = document.getElementById('ro-kanban-view');
        if (kanbanView && kanbanView.style.display !== 'none') loadKanban();
      }
    } catch (err) {
      console.error('updateRoStatus:', err);
      toast(t('wlFailed', 'Action failed'), true);
    }
  }

  // ─── Auto-refresh (30s) ────────────────────────────────────────
  function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(function() {
      if (document.getElementById('waitlist-container')) loadWaitlist();
      else clearInterval(refreshTimer);
    }, 30000);
  }

})();
