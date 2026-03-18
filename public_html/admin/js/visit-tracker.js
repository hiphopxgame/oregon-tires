/**
 * Oregon Tires — Shop Floor Visit Tracker
 * Real-time view of customer visits, bay occupancy, and service timers.
 */
(function() {
  'use strict';

  let refreshInterval = null;

  async function loadVisits() {
    try {
      const res = await fetch('/api/admin/visit-log.php?filter=active', { credentials: 'include' });
      const json = await res.json();
      if (!json.success) return;

      renderShopFloor(json.data);
    } catch (err) {
      console.error('Visit tracker error:', err);
    }
  }

  function formatDuration(startStr) {
    if (!startStr) return '--';
    const start = new Date(startStr);
    const now = new Date();
    const diff = Math.floor((now - start) / 1000 / 60);
    if (diff < 60) return diff + 'm';
    const h = Math.floor(diff / 60);
    const m = diff % 60;
    return h + 'h ' + m + 'm';
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.textContent;
  }

  function renderShopFloor(data) {
    const container = document.getElementById('shopFloorWidget');
    if (!container) return;

    // Clear existing content safely
    while (container.firstChild) container.removeChild(container.firstChild);

    const visits = data.visits || [];
    const activeCount = data.active_count || 0;
    const baysInUse = (data.bays_in_use || []).map(b => parseInt(b.bay_number));

    // Header row
    const header = document.createElement('div');
    header.className = 'flex items-center justify-between mb-4';

    const counterWrap = document.createElement('div');
    counterWrap.className = 'flex items-center gap-2';

    const badge = document.createElement('span');
    badge.className = 'inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold text-sm';
    badge.textContent = String(activeCount);
    counterWrap.appendChild(badge);

    const label = document.createElement('span');
    label.className = 'text-sm text-gray-600 dark:text-gray-400';
    label.textContent = 'vehicles in shop';
    counterWrap.appendChild(label);
    header.appendChild(counterWrap);

    const checkInBtn = document.createElement('button');
    checkInBtn.className = 'text-sm px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors';
    checkInBtn.textContent = '+ Check In';
    checkInBtn.addEventListener('click', showCheckIn);
    header.appendChild(checkInBtn);
    container.appendChild(header);

    // Bay status
    const bayRow = document.createElement('div');
    bayRow.className = 'flex gap-2 mb-4';
    for (let i = 1; i <= 4; i++) {
      const inUse = baysInUse.includes(i);
      const bayEl = document.createElement('div');
      bayEl.className = 'flex-1 text-center py-2 rounded-lg border text-xs font-medium ' +
        (inUse
          ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'
          : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600');
      bayEl.textContent = 'Bay ' + i + (inUse ? ' (In Use)' : ' (Open)');
      bayRow.appendChild(bayEl);
    }
    container.appendChild(bayRow);

    if (visits.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'text-center text-gray-400 dark:text-gray-500 py-8 text-sm';
      empty.textContent = 'No active visits';
      container.appendChild(empty);
      return;
    }

    const list = document.createElement('div');
    list.className = 'space-y-3';

    visits.forEach(function(v) {
      const name = ((v.first_name || '') + ' ' + (v.last_name || '')).trim();
      const roLabel = v.ro_number ? 'RO: ' + v.ro_number : '';
      const bay = v.bay_number ? 'Bay ' + v.bay_number : 'No bay';
      const waitTime = formatDuration(v.check_in_at);
      const svcTime = v.service_start_at ? formatDuration(v.service_start_at) : null;

      const isInService = v.service_start_at && !v.service_end_at;
      const statusCls = isInService ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400';
      const statusText = isInService ? 'In Service (' + svcTime + ')' : 'Waiting (' + waitTime + ')';

      const row = document.createElement('div');
      row.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg';

      const info = document.createElement('div');

      const nameEl = document.createElement('p');
      nameEl.className = 'font-medium text-sm text-gray-900 dark:text-white';
      nameEl.textContent = name;
      info.appendChild(nameEl);

      const detailEl = document.createElement('p');
      detailEl.className = 'text-xs text-gray-500 dark:text-gray-400';
      detailEl.textContent = [roLabel, bay].filter(Boolean).join(' \u2022 ');
      info.appendChild(detailEl);

      const statusEl = document.createElement('p');
      statusEl.className = 'text-xs font-medium ' + statusCls;
      statusEl.textContent = statusText;
      info.appendChild(statusEl);

      row.appendChild(info);

      const actions = document.createElement('div');
      actions.className = 'flex gap-1';

      if (!v.service_start_at) {
        const startBtn = document.createElement('button');
        startBtn.className = 'text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700';
        startBtn.title = 'Start Service';
        startBtn.textContent = 'Start';
        startBtn.addEventListener('click', function() { updateVisit(v.id, { service_start_at: 'now' }); });
        actions.appendChild(startBtn);
      } else if (!v.service_end_at) {
        const doneBtn = document.createElement('button');
        doneBtn.className = 'text-xs px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700';
        doneBtn.title = 'End Service';
        doneBtn.textContent = 'Done';
        doneBtn.addEventListener('click', function() { updateVisit(v.id, { service_end_at: 'now' }); });
        actions.appendChild(doneBtn);
      }
      if (!v.check_out_at) {
        const outBtn = document.createElement('button');
        outBtn.className = 'text-xs px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700';
        outBtn.title = 'Check Out';
        outBtn.textContent = 'Out';
        outBtn.addEventListener('click', function() { updateVisit(v.id, { check_out_at: 'now' }); });
        actions.appendChild(outBtn);
      }

      row.appendChild(actions);
      list.appendChild(row);
    });

    container.appendChild(list);
  }

  async function updateVisit(id, data) {
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]');
      const headers = { 'Content-Type': 'application/json' };
      if (csrf) headers['X-CSRF-Token'] = csrf.content;

      const res = await fetch('/api/admin/visit-log.php', {
        method: 'PUT',
        credentials: 'include',
        headers: headers,
        body: JSON.stringify(Object.assign({ id: id }, data)),
      });
      const json = await res.json();
      if (json.success) loadVisits();
    } catch (err) {
      console.error('Visit update error:', err);
    }
  }

  function showCheckIn() {
    // Simple prompt — in production this would be a modal with customer search
    const customerId = prompt('Enter Customer ID:');
    if (!customerId) return;
    const bay = prompt('Bay number (1-4, or leave blank):');

    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Content-Type': 'application/json' };
    if (csrf) headers['X-CSRF-Token'] = csrf.content;

    fetch('/api/admin/visit-log.php', {
      method: 'POST',
      credentials: 'include',
      headers: headers,
      body: JSON.stringify({
        customer_id: parseInt(customerId),
        bay_number: bay ? parseInt(bay) : null,
      }),
    })
    .then(r => r.json())
    .then(json => { if (json.success) loadVisits(); })
    .catch(err => console.error('Check-in error:', err));
  }

  // Public API
  window.ShopFloor = {
    init: function() {
      loadVisits();
      refreshInterval = setInterval(loadVisits, 30000); // refresh every 30s
    },
    refresh: loadVisits,
    startService: function(id) { updateVisit(id, { service_start_at: 'now' }); },
    endService: function(id) { updateVisit(id, { service_end_at: 'now' }); },
    checkOut: function(id) { updateVisit(id, { check_out_at: 'now' }); },
    showCheckIn: showCheckIn,
    destroy: function() { if (refreshInterval) clearInterval(refreshInterval); },
  };
})();
