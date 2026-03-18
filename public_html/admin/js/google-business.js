/**
 * Oregon Tires — Admin Google Business Profile Sync Management
 * Displays sync status, log history, manual sync trigger, and settings.
 */
(function() {
  'use strict';

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }

  function getCsrf() {
    return (typeof csrfToken !== 'undefined') ? csrfToken : '';
  }

  function apiHeaders(method) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (method !== 'GET') h['Content-Type'] = 'application/json';
    return h;
  }

  function formatDate(str) {
    if (!str) return '-';
    var lang = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
    return new Date(str).toLocaleString(lang, { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  function statusBadge(status) {
    var span = document.createElement('span');
    span.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ';
    if (status === 'success') {
      span.className += 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
      span.textContent = t('gbSuccess', 'Success');
    } else if (status === 'pending') {
      span.className += 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
      span.textContent = t('gbPending', 'Pending');
    } else {
      span.className += 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
      span.textContent = t('gbError', 'Error');
    }
    return span;
  }

  // ─── Load & Render ────────────────────────────────────────────
  window.loadGoogleBusinessSync = async function() {
    var container = document.getElementById('gbsync-container');
    if (!container) return;
    container.textContent = '';

    try {
      var res = await fetch('/api/admin/google-business-sync.php', { credentials: 'include', headers: apiHeaders('GET') });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Load failed');
      render(container, json.data);
    } catch (err) {
      console.error('loadGoogleBusinessSync error:', err);
      var errP = document.createElement('p');
      errP.className = 'text-red-600 dark:text-red-400 p-4';
      errP.textContent = err.message;
      container.appendChild(errP);
    }
  };

  function render(container, data) {
    // ── Status Card ──
    var card = document.createElement('div');
    card.className = 'grid grid-cols-2 md:grid-cols-4 gap-4 mb-6';
    var stats = [
      { label: t('gbLastSync', 'Last Sync'), value: formatDate(data.last_sync) },
      { label: t('gbNextSync', 'Next Scheduled Sync'), value: formatDate(data.next_sync) },
      { label: t('gbStatus', 'Status'), value: null, badge: data.status },
      { label: t('gbReviewsSynced', 'Reviews Synced'), value: String(data.reviews_synced || 0) }
    ];
    stats.forEach(function(s) {
      var box = document.createElement('div');
      box.className = 'bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-4';
      var lbl = document.createElement('p');
      lbl.className = 'text-xs text-gray-500 dark:text-gray-400 mb-1';
      lbl.textContent = s.label;
      box.appendChild(lbl);
      if (s.badge) {
        box.appendChild(statusBadge(s.badge));
      } else {
        var val = document.createElement('p');
        val.className = 'text-sm font-semibold text-gray-800 dark:text-gray-200';
        val.textContent = s.value;
        box.appendChild(val);
      }
      card.appendChild(box);
    });
    container.appendChild(card);

    // ── Sync Now Button ──
    var btnWrap = document.createElement('div');
    btnWrap.className = 'mb-6';
    var syncBtn = document.createElement('button');
    syncBtn.className = 'px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition';
    syncBtn.textContent = t('gbSyncNow', 'Sync Now');
    syncBtn.addEventListener('click', function() { triggerSync(syncBtn); });
    btnWrap.appendChild(syncBtn);
    container.appendChild(btnWrap);

    // ── Sync Log Table ──
    var logTitle = document.createElement('h3');
    logTitle.className = 'text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3';
    logTitle.textContent = t('gbLogTitle', 'Recent Sync Log');
    container.appendChild(logTitle);

    var logs = data.logs || [];
    if (logs.length === 0) {
      var emptyP = document.createElement('p');
      emptyP.className = 'text-gray-500 dark:text-gray-400 text-sm mb-6';
      emptyP.textContent = t('gbNoLogs', 'No sync logs yet.');
      container.appendChild(emptyP);
    } else {
      var tableWrap = document.createElement('div');
      tableWrap.className = 'overflow-x-auto mb-6';
      var table = document.createElement('table');
      table.className = 'w-full text-sm';
      var thead = document.createElement('thead');
      thead.className = 'bg-gray-50 dark:bg-gray-700';
      var hRow = document.createElement('tr');
      [t('gbDate', 'Date'), t('gbType', 'Type'), t('gbStatus', 'Status'), t('gbDetails', 'Details')].forEach(function(h) {
        var th = document.createElement('th');
        th.className = 'text-left p-3 font-medium text-gray-600 dark:text-gray-300';
        th.textContent = h;
        hRow.appendChild(th);
      });
      thead.appendChild(hRow);
      table.appendChild(thead);

      var tbody = document.createElement('tbody');
      tbody.className = 'divide-y divide-gray-200 dark:divide-gray-700';
      logs.forEach(function(log) {
        var tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition';
        var tdDate = document.createElement('td');
        tdDate.className = 'p-3 text-gray-600 dark:text-gray-300';
        tdDate.textContent = formatDate(log.date);
        tr.appendChild(tdDate);
        var tdType = document.createElement('td');
        tdType.className = 'p-3 text-gray-600 dark:text-gray-300';
        tdType.textContent = log.type;
        tr.appendChild(tdType);
        var tdStatus = document.createElement('td');
        tdStatus.className = 'p-3';
        tdStatus.appendChild(statusBadge(log.status));
        tr.appendChild(tdStatus);
        var tdDetails = document.createElement('td');
        tdDetails.className = 'p-3 text-gray-500 dark:text-gray-400';
        tdDetails.textContent = log.details || '-';
        tr.appendChild(tdDetails);
        tbody.appendChild(tr);
      });
      table.appendChild(tbody);
      tableWrap.appendChild(table);
      container.appendChild(tableWrap);
    }

    // ── Settings Section ──
    var setTitle = document.createElement('h3');
    setTitle.className = 'text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3';
    setTitle.textContent = t('gbSettingsTitle', 'Sync Settings');
    container.appendChild(setTitle);

    var setWrap = document.createElement('div');
    setWrap.className = 'bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-4 flex flex-wrap gap-4 items-end';

    // Frequency select
    var freqDiv = document.createElement('div');
    var freqLabel = document.createElement('label');
    freqLabel.className = 'block text-xs text-gray-500 dark:text-gray-400 mb-1';
    freqLabel.textContent = t('gbFrequency', 'Frequency');
    var freqSelect = document.createElement('select');
    freqSelect.id = 'gbsync-frequency';
    freqSelect.className = 'border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-1.5 text-sm';
    var freqLabels = { daily: t('gbDaily','Daily'), weekly: t('gbWeekly','Weekly'), monthly: t('gbMonthly','Monthly') };
    ['daily', 'weekly', 'monthly'].forEach(function(v) {
      var opt = document.createElement('option');
      opt.value = v;
      opt.textContent = freqLabels[v];
      if (v === (data.settings && data.settings.frequency || 'weekly')) opt.selected = true;
      freqSelect.appendChild(opt);
    });
    freqDiv.appendChild(freqLabel);
    freqDiv.appendChild(freqSelect);
    setWrap.appendChild(freqDiv);

    // Auto-sync toggle
    var toggleDiv = document.createElement('div');
    var toggleLabel = document.createElement('label');
    toggleLabel.className = 'flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer';
    var toggleCb = document.createElement('input');
    toggleCb.type = 'checkbox';
    toggleCb.id = 'gbsync-auto';
    toggleCb.className = 'rounded border-gray-300 dark:border-gray-600';
    if (data.settings && data.settings.auto_sync) toggleCb.checked = true;
    var toggleText = document.createTextNode(' ' + t('gbAutoSync', 'Auto-Sync'));
    toggleLabel.appendChild(toggleCb);
    toggleLabel.appendChild(toggleText);
    toggleDiv.appendChild(toggleLabel);
    setWrap.appendChild(toggleDiv);

    // Save button
    var saveBtn = document.createElement('button');
    saveBtn.className = 'px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition';
    saveBtn.textContent = t('gbSave', 'Save Settings');
    saveBtn.addEventListener('click', function() { saveSettings(saveBtn); });
    setWrap.appendChild(saveBtn);
    container.appendChild(setWrap);
  }

  // ─── Trigger Manual Sync ──────────────────────────────────────
  async function triggerSync(btn) {
    btn.disabled = true;
    btn.textContent = t('gbSyncing', 'Syncing...');
    try {
      var res = await fetch('/api/admin/google-business-sync.php', {
        method: 'POST', credentials: 'include',
        headers: apiHeaders('POST'),
        body: JSON.stringify({ action: 'sync' })
      });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Sync failed');
      if (typeof showToast === 'function') showToast(t('gbSyncSuccess', 'Sync completed'));
      loadGoogleBusinessSync();
    } catch (err) {
      console.error('triggerSync error:', err);
      if (typeof showToast === 'function') showToast(t('gbSyncFailed', 'Sync failed'), true);
      btn.disabled = false;
      btn.textContent = t('gbSyncNow', 'Sync Now');
    }
  }

  // ─── Save Settings ────────────────────────────────────────────
  async function saveSettings(btn) {
    btn.disabled = true;
    try {
      var res = await fetch('/api/admin/google-business-sync.php', {
        method: 'PUT', credentials: 'include',
        headers: apiHeaders('PUT'),
        body: JSON.stringify({
          action: 'settings',
          frequency: document.getElementById('gbsync-frequency').value,
          auto_sync: document.getElementById('gbsync-auto').checked
        })
      });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Save failed');
      if (typeof showToast === 'function') showToast(t('gbSaved', 'Settings saved'));
    } catch (err) {
      console.error('saveSettings error:', err);
      if (typeof showToast === 'function') showToast(t('gbSaveFailed', 'Failed to save settings'), true);
    }
    btn.disabled = false;
  }

})();
