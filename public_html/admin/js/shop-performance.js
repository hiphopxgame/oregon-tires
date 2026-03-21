/**
 * Oregon Tires Admin — Shop Performance Analytics
 * Fetches /api/admin/analytics.php?type=shop_performance and renders:
 *   - 4 stat cards (avg time in shop, wait, service, completed count)
 *   - Service duration table
 *   - Technician productivity table
 *   - RO status distribution pills
 *
 * Uses createElement/appendChild only (no innerHTML per security rules).
 * All text bilingual via adminT[currentLang] with safe fallbacks.
 */

(function () {
  'use strict';

  // ── Helpers ──────────────────────────────────────────────────────────
  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined && text !== null) e.textContent = String(text);
    return e;
  }

  function T(key) {
    if (typeof adminT !== 'undefined' && typeof currentLang !== 'undefined' && adminT[currentLang]) {
      return adminT[currentLang][key] || key;
    }
    return key;
  }

  function fmtMinutes(mins) {
    if (mins === null || mins === undefined) return '\u2014';
    var n = parseInt(mins, 10);
    if (isNaN(n)) return '\u2014';
    if (n < 60) return n + ' ' + T('spMinutes');
    var h = Math.floor(n / 60);
    var m = n % 60;
    return h + T('spHours') + (m > 0 ? ' ' + m + T('spMinutes') : '');
  }

  // RO status → color mapping (Tailwind classes)
  var STATUS_COLORS = {
    intake:           { bg: 'bg-blue-100 dark:bg-blue-900/30',   text: 'text-blue-800 dark:text-blue-300' },
    diagnosis:        { bg: 'bg-indigo-100 dark:bg-indigo-900/30', text: 'text-indigo-800 dark:text-indigo-300' },
    estimate_pending: { bg: 'bg-yellow-100 dark:bg-yellow-900/30', text: 'text-yellow-800 dark:text-yellow-300' },
    pending_approval: { bg: 'bg-amber-100 dark:bg-amber-900/30',  text: 'text-amber-800 dark:text-amber-300' },
    approved:         { bg: 'bg-green-100 dark:bg-green-900/30',   text: 'text-green-800 dark:text-green-300' },
    in_progress:      { bg: 'bg-teal-100 dark:bg-teal-900/30',    text: 'text-teal-800 dark:text-teal-300' },
    waiting_parts:    { bg: 'bg-orange-100 dark:bg-orange-900/30', text: 'text-orange-800 dark:text-orange-300' },
    ready:            { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-800 dark:text-emerald-300' },
    completed:        { bg: 'bg-gray-100 dark:bg-gray-700',       text: 'text-gray-800 dark:text-gray-300' },
    invoiced:         { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-800 dark:text-purple-300' },
    cancelled:        { bg: 'bg-red-100 dark:bg-red-900/30',      text: 'text-red-800 dark:text-red-300' },
  };

  function statusColor(status) {
    return STATUS_COLORS[status] || { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-700 dark:text-gray-300' };
  }

  // ── Main render function ─────────────────────────────────────────────
  function renderShopPerformance(data) {
    var frag = document.createDocumentFragment();

    // Section header
    var header = el('div', 'flex items-center justify-between mb-4');
    header.appendChild(el('h3', 'text-lg font-bold text-gray-800 dark:text-gray-200', T('spTitle')));
    var refreshBtn = el('button', 'text-sm text-brand hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition', T('analyticsRefresh'));
    refreshBtn.addEventListener('click', loadShopPerformance);
    header.appendChild(refreshBtn);
    frag.appendChild(header);

    var vt = data.vehicle_time || {};

    // ── 4 Stat Cards ─────────────────────────────────────────────────
    var statsRow = el('div', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6');

    // Avg Time in Shop
    var c1 = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 text-center');
    c1.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', T('spAvgTimeInShop')));
    c1.appendChild(el('div', 'text-2xl font-bold text-brand dark:text-green-400', fmtMinutes(vt.avg_total_minutes)));
    statsRow.appendChild(c1);

    // Avg Wait Time
    var c2 = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 text-center');
    c2.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', T('spAvgWaitTime')));
    c2.appendChild(el('div', 'text-2xl font-bold text-amber-600 dark:text-amber-400', fmtMinutes(vt.avg_wait_minutes)));
    statsRow.appendChild(c2);

    // Avg Service Time
    var c3 = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 text-center');
    c3.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', T('spAvgServiceTime')));
    c3.appendChild(el('div', 'text-2xl font-bold text-blue-600 dark:text-blue-400', fmtMinutes(vt.avg_service_minutes)));
    statsRow.appendChild(c3);

    // Completed (30d)
    var c4 = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 text-center');
    c4.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', T('spCompleted30d')));
    var countVal = (vt.completed_count !== null && vt.completed_count !== undefined) ? String(vt.completed_count) : '0';
    c4.appendChild(el('div', 'text-2xl font-bold text-gray-800 dark:text-gray-200', countVal));
    c4.appendChild(el('p', 'text-xs text-gray-400 dark:text-gray-500 mt-1', T('spVehicles')));
    statsRow.appendChild(c4);

    frag.appendChild(statsRow);

    // ── Two-column layout: Service Duration + Tech Productivity ──────
    var twoCol = el('div', 'grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6');

    // Service Duration Table
    var sdCard = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6');
    sdCard.appendChild(el('h4', 'text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3', T('spServiceDuration')));

    var sdList = data.service_duration || [];
    if (sdList.length) {
      var sdWrap = el('div', 'overflow-x-auto');
      var sdTable = el('table', 'w-full text-sm');
      var sdThead = document.createElement('thead');
      var sdHeadRow = document.createElement('tr');
      sdHeadRow.className = 'border-b dark:border-gray-700';
      [T('spService'), T('spAvgDuration'), T('spJobCount')].forEach(function (label, i) {
        var th = document.createElement('th');
        th.className = i === 0 ? 'text-left p-2 text-gray-600 dark:text-gray-400' : 'p-2 text-center text-gray-600 dark:text-gray-400';
        th.textContent = label;
        sdHeadRow.appendChild(th);
      });
      sdThead.appendChild(sdHeadRow);
      sdTable.appendChild(sdThead);

      var sdTbody = document.createElement('tbody');
      sdList.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';
        tr.appendChild(el('td', 'p-2 font-medium text-gray-800 dark:text-gray-200', row.service || '\u2014'));
        tr.appendChild(el('td', 'p-2 text-center text-gray-700 dark:text-gray-300', fmtMinutes(row.avg_minutes)));
        tr.appendChild(el('td', 'p-2 text-center text-gray-700 dark:text-gray-300', String(row.job_count || 0)));
        sdTbody.appendChild(tr);
      });
      sdTable.appendChild(sdTbody);
      sdWrap.appendChild(sdTable);
      sdCard.appendChild(sdWrap);
    } else {
      sdCard.appendChild(el('p', 'text-gray-400 dark:text-gray-500 text-center py-6', T('spNoData')));
    }
    twoCol.appendChild(sdCard);

    // Technician Productivity Table
    var tpCard = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6');
    tpCard.appendChild(el('h4', 'text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3', T('spTechProductivity')));

    var tpList = data.tech_productivity || [];
    if (tpList.length) {
      var tpWrap = el('div', 'overflow-x-auto');
      var tpTable = el('table', 'w-full text-sm');
      var tpThead = document.createElement('thead');
      var tpHeadRow = document.createElement('tr');
      tpHeadRow.className = 'border-b dark:border-gray-700';
      [T('spEmployee'), T('spTotalHours'), T('spBillable'), T('spEfficiency'), T('spJobs')].forEach(function (label, i) {
        var th = document.createElement('th');
        th.className = i === 0 ? 'text-left p-2 text-gray-600 dark:text-gray-400' : 'p-2 text-center text-gray-600 dark:text-gray-400';
        th.textContent = label;
        tpHeadRow.appendChild(th);
      });
      tpThead.appendChild(tpHeadRow);
      tpTable.appendChild(tpThead);

      var tpTbody = document.createElement('tbody');
      tpList.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';
        tr.appendChild(el('td', 'p-2 font-medium text-gray-800 dark:text-gray-200', row.name || '\u2014'));
        tr.appendChild(el('td', 'p-2 text-center text-gray-700 dark:text-gray-300', parseFloat(row.total_hours || 0).toFixed(1) + T('spHours')));
        tr.appendChild(el('td', 'p-2 text-center text-gray-700 dark:text-gray-300', parseFloat(row.billable_hours || 0).toFixed(1) + T('spHours')));

        var totalH = parseFloat(row.total_hours || 0);
        var billH = parseFloat(row.billable_hours || 0);
        var eff = totalH > 0 ? Math.round((billH / totalH) * 100) : 0;
        var effTd = el('td', 'p-2 text-center font-semibold', eff + '%');
        effTd.className += eff >= 80 ? ' text-green-600 dark:text-green-400' : eff >= 60 ? ' text-amber-600 dark:text-amber-400' : ' text-red-600 dark:text-red-400';
        tr.appendChild(effTd);

        tr.appendChild(el('td', 'p-2 text-center text-gray-700 dark:text-gray-300', String(row.jobs_completed || 0)));
        tpTbody.appendChild(tr);
      });
      tpTable.appendChild(tpTbody);
      tpWrap.appendChild(tpTable);
      tpCard.appendChild(tpWrap);
    } else {
      tpCard.appendChild(el('p', 'text-gray-400 dark:text-gray-500 text-center py-6', T('spNoData')));
    }
    twoCol.appendChild(tpCard);

    frag.appendChild(twoCol);

    // ── RO Status Distribution (colored pills) ──────────────────────
    var roCard = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6');
    roCard.appendChild(el('h4', 'text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3', T('spRoStatus')));

    var roList = data.ro_status_distribution || [];
    if (roList.length) {
      var pillsWrap = el('div', 'flex flex-wrap gap-3');
      roList.forEach(function (item) {
        var sc = statusColor(item.status);
        var pill = el('div', sc.bg + ' ' + sc.text + ' px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2');
        pill.appendChild(el('span', '', item.status.replace(/_/g, ' ')));
        var badge = el('span', 'bg-white/60 dark:bg-black/20 px-2 py-0.5 rounded-full text-xs font-bold', String(item.count));
        pill.appendChild(badge);
        pillsWrap.appendChild(pill);
      });
      roCard.appendChild(pillsWrap);
    } else {
      roCard.appendChild(el('p', 'text-gray-400 dark:text-gray-500 text-center py-6', T('spNoData')));
    }
    frag.appendChild(roCard);

    return frag;
  }

  // ── Load function ────────────────────────────────────────────────────
  var shopPerfLoaded = false;

  async function loadShopPerformance() {
    var container = document.getElementById('shop-performance-container');
    if (!container) return;

    // Show loading spinner
    container.textContent = '';
    var loadDiv = el('div', 'flex items-center justify-center py-12');
    var spinWrap = el('div', 'text-center');
    var spinner = el('div', 'inline-block w-8 h-8 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4');
    spinWrap.appendChild(spinner);
    spinWrap.appendChild(el('p', 'text-gray-500 dark:text-gray-400', T('analyticsLoading')));
    loadDiv.appendChild(spinWrap);
    container.appendChild(loadDiv);

    try {
      var resp = await api('analytics.php?type=shop_performance');
      var data = resp.data || resp;

      container.textContent = '';
      container.appendChild(renderShopPerformance(data));
      shopPerfLoaded = true;
    } catch (err) {
      console.error('loadShopPerformance error:', err);
      container.textContent = '';
      var errDiv = el('div', 'text-center py-12');
      errDiv.appendChild(el('p', 'text-red-500 text-lg mb-4', T('analyticsError')));
      var retryBtn = el('button', 'bg-brand text-white px-6 py-2 rounded-lg hover:bg-green-700 transition', T('analyticsRefresh'));
      retryBtn.addEventListener('click', loadShopPerformance);
      errDiv.appendChild(retryBtn);
      container.appendChild(errDiv);
    }
  }

  // Expose globally
  window.renderShopPerformance = renderShopPerformance;
  window.loadShopPerformance = loadShopPerformance;
})();
