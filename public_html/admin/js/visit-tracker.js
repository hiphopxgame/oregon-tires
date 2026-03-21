/**
 * Oregon Tires — Shop Floor Visit Tracker
 * Real-time view of customer visits, bay occupancy, and service timers.
 */
(function() {
  'use strict';

  var refreshInterval = null;
  var BAY_COUNT = 4; // configurable bay count

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function getCsrf() { return (typeof csrfToken !== 'undefined' && csrfToken) ? csrfToken : ''; }
  function hdrs(json) { var h = { 'X-CSRF-Token': getCsrf() }; if (json) h['Content-Type'] = 'application/json'; return h; }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, !!err); }

  function formatDuration(startStr, endStr) {
    if (!startStr) return '--';
    var start = new Date(startStr.replace(' ', 'T'));
    var end = endStr ? new Date(endStr.replace(' ', 'T')) : new Date();
    var diff = Math.floor((end.getTime() - start.getTime()) / 1000 / 60);
    if (diff < 0) return '0m';
    if (diff < 60) return diff + 'm';
    return Math.floor(diff / 60) + 'h ' + (diff % 60) + 'm';
  }

  function fmtClock(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr.replace(' ', 'T'));
    var h = d.getHours(); var m = d.getMinutes();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return h + ':' + (m < 10 ? '0' : '') + m + ' ' + ampm;
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  function removeModal() { var m = document.getElementById('visit-modal-overlay'); if (m) m.remove(); }

  // ─── Load (for shopFloorWidget on dashboard) ─────────────────
  async function loadVisits() {
    try {
      var res = await fetch('/api/admin/visit-log.php?filter=active', { credentials: 'include' });
      var json = await res.json();
      if (!json.success) return;
      renderShopFloor(json.data);
    } catch (err) {
      console.error('Visit tracker error:', err);
    }
  }

  function renderShopFloor(data) {
    var container = document.getElementById('shopFloorWidget');
    if (!container) return;
    while (container.firstChild) container.removeChild(container.firstChild);

    var visits = data.visits || [];
    var activeCount = data.active_count || 0;
    var baysInUse = (data.bays_in_use || []).map(function(b) { return parseInt(b.bay_number); });

    // Header
    var header = el('div', 'flex items-center justify-between mb-4');
    var counterWrap = el('div', 'flex items-center gap-2');
    var badge = el('span', 'inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold text-sm', String(activeCount));
    counterWrap.appendChild(badge);
    counterWrap.appendChild(el('span', 'text-sm text-gray-600 dark:text-gray-400', t('visitVehiclesInShop', 'vehicles in shop')));
    header.appendChild(counterWrap);
    var checkInBtn = el('button', 'text-sm px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition', t('visitCheckIn', '+ Check In'));
    checkInBtn.addEventListener('click', showCheckIn);
    header.appendChild(checkInBtn);
    container.appendChild(header);

    // Bay status
    var bayRow = el('div', 'flex gap-2 mb-4');
    for (var i = 1; i <= BAY_COUNT; i++) {
      var inUse = baysInUse.indexOf(i) !== -1;
      bayRow.appendChild(el('div', 'flex-1 text-center py-2 rounded-lg border text-xs font-medium ' +
        (inUse ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'
               : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600'),
        t('visitBayLabel', 'Bay') + ' ' + i + ' ' + (inUse ? t('visitInUse', '(In Use)') : t('visitOpen', '(Open)'))));
    }
    container.appendChild(bayRow);

    if (!visits.length) {
      container.appendChild(el('p', 'text-center text-gray-400 dark:text-gray-500 py-8 text-sm', t('visitNoActive', 'No active visits')));
      return;
    }

    var list = el('div', 'space-y-3');
    visits.forEach(function(v) {
      var name = ((v.first_name || '') + ' ' + (v.last_name || '')).trim();
      var isInService = v.service_start_at && !v.service_end_at;
      var isDone = !!v.service_end_at;
      var statusText = isDone ? t('visitDone', 'Done') : isInService ? t('visitInService', 'In Service') + ' (' + formatDuration(v.service_start_at) + ')' : t('visitWaiting', 'Waiting') + ' (' + formatDuration(v.check_in_at) + ')';
      var statusCls = isDone ? 'text-gray-500' : isInService ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400';

      var row = el('div', 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg');
      var info = el('div');
      info.appendChild(el('p', 'font-medium text-sm text-gray-900 dark:text-white', name));
      var detailParts = [v.ro_number ? 'RO: ' + v.ro_number : '', v.bay_number ? t('visitBayLabel', 'Bay') + ' ' + v.bay_number : '', v.service ? v.service.replace(/-/g, ' ') : '', v.employee_name ? '\u2192 ' + v.employee_name : ''].filter(Boolean);
      info.appendChild(el('p', 'text-xs text-gray-500 dark:text-gray-400', detailParts.join(' \u2022 ')));
      info.appendChild(el('p', 'text-xs font-medium ' + statusCls, statusText));
      row.appendChild(info);

      var actions = el('div', 'flex gap-1');
      if (!v.service_start_at) {
        var sb = el('button', 'text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700', t('visitStart', 'Start'));
        sb.addEventListener('click', function() { updateVisit(v.id, { service_start_at: 'now' }); });
        actions.appendChild(sb);
      } else if (!v.service_end_at) {
        var db = el('button', 'text-xs px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700', t('visitDone', 'Done'));
        db.addEventListener('click', function() { updateVisit(v.id, { service_end_at: 'now' }); });
        actions.appendChild(db);
      }
      if (!v.check_out_at) {
        var ob = el('button', 'text-xs px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700', t('visitOut', 'Out'));
        ob.addEventListener('click', function() { updateVisit(v.id, { check_out_at: 'now' }); });
        actions.appendChild(ob);
      }
      row.appendChild(actions);
      list.appendChild(row);
    });
    container.appendChild(list);
  }

  // ─── Full Visit Tracker (dedicated Visits tab) ───────────────
  async function loadFullVisitTracker() {
    var container = document.getElementById('visits-container');
    if (!container) return;
    container.textContent = '';

    try {
      var res = await fetch('/api/admin/visit-log.php?filter=active', { credentials: 'include' });
      var json = await res.json();
      if (!json.success) return;

      var data = json.data || {};
      var visits = data.visits || [];
      var activeCount = data.active_count || 0;
      var baysInUse = (data.bays_in_use || []).map(function(b) { return parseInt(b.bay_number); });
      pendingAppointments = data.pending_appointments || [];

      // Stats cards
      var stats = el('div', 'grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6');
      [[String(activeCount), t('visitVehiclesInShop', 'In Shop'), 'text-green-600 dark:text-green-400'],
       [baysInUse.length + '/' + BAY_COUNT, t('visitBaysInUse', 'Bays In Use'), 'text-blue-600 dark:text-blue-400'],
       [String(BAY_COUNT - baysInUse.length), t('visitBaysOpen', 'Bays Open'), 'text-gray-500 dark:text-gray-400'],
       [visits.filter(function(v) { return !v.service_start_at; }).length + '', t('visitWaiting', 'Waiting'), 'text-amber-600 dark:text-amber-400']
      ].forEach(function(s) {
        var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 shadow border dark:border-gray-700 text-center');
        card.appendChild(el('div', 'text-2xl font-bold ' + s[2], s[0]));
        card.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400 mt-1', s[1]));
        stats.appendChild(card);
      });
      container.appendChild(stats);

      // Bay status bar
      var bayRow = el('div', 'flex gap-3 mb-6');
      for (var i = 1; i <= BAY_COUNT; i++) {
        var inUse = baysInUse.indexOf(i) !== -1;
        bayRow.appendChild(el('div', 'flex-1 text-center py-3 rounded-xl border-2 text-sm font-semibold ' +
          (inUse ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700'
                 : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700'),
          t('visitBayLabel', 'Bay') + ' ' + i + ' — ' + (inUse ? t('visitOccupied', 'Occupied') : t('visitAvailable', 'Available'))));
      }
      container.appendChild(bayRow);

      // Check In button
      var btnRow = el('div', 'flex justify-end mb-4');
      var checkInBtn = el('button', 'px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition', t('visitCheckIn', '+ Check In'));
      checkInBtn.addEventListener('click', showCheckIn);
      btnRow.appendChild(checkInBtn);
      container.appendChild(btnRow);

      if (!visits.length) {
        var emptyCard = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow p-8 text-center');
        emptyCard.appendChild(el('div', 'text-4xl mb-3', '🏪'));
        emptyCard.appendChild(el('p', 'text-gray-500 dark:text-gray-400 mb-2', t('visitNoActive', 'No active visits')));
        emptyCard.appendChild(el('p', 'text-sm text-gray-400 dark:text-gray-500', t('visitCheckInFirst', 'Check in a customer to get started')));
        container.appendChild(emptyCard);
        return;
      }

      // Visit cards
      var grid = el('div', 'space-y-3');
      visits.forEach(function(v) {
        var name = ((v.first_name || '') + ' ' + (v.last_name || '')).trim() || 'Customer #' + v.customer_id;
        var isInService = v.service_start_at && !v.service_end_at;
        var isDone = !!v.service_end_at;
        var statusText = isDone ? t('visitServiceComplete', 'Service Complete') : isInService ? t('visitInService', 'In Service') : t('visitWaiting', 'Waiting');
        var statusCls = isDone ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
          : isInService ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
          : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';

        var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 hover:shadow-md transition');

        // ── Top row: name, badges, actions ──
        var topRow = el('div', 'flex items-center justify-between gap-2');
        var nameWrap = el('div', 'flex items-center gap-2 flex-wrap min-w-0');
        nameWrap.appendChild(el('span', 'font-semibold text-gray-800 dark:text-gray-200', name));
        if (v.ro_number) nameWrap.appendChild(el('span', 'text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded', 'RO: ' + v.ro_number));
        nameWrap.appendChild(el('span', 'text-xs px-2 py-0.5 rounded-full font-medium ' + statusCls, statusText));
        topRow.appendChild(nameWrap);

        // Actions
        var actions = el('div', 'flex gap-2 shrink-0');
        if (!v.service_start_at) {
          var sb = el('button', 'px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition', t('visitStartService', 'Start Service'));
          sb.addEventListener('click', function() { updateVisit(v.id, { service_start_at: 'now' }); setTimeout(loadFullVisitTracker, 500); });
          actions.appendChild(sb);
        } else if (!v.service_end_at) {
          var fb = el('button', 'px-3 py-1.5 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 transition', t('visitFinishService', 'Finish'));
          fb.addEventListener('click', function() { updateVisit(v.id, { service_end_at: 'now' }); setTimeout(loadFullVisitTracker, 500); });
          actions.appendChild(fb);
        }
        if (!v.check_out_at) {
          var ob = el('button', 'px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('visitCheckOut', 'Check Out'));
          ob.addEventListener('click', function() { updateVisit(v.id, { check_out_at: 'now' }); setTimeout(loadFullVisitTracker, 500); });
          actions.appendChild(ob);
        }
        topRow.appendChild(actions);
        card.appendChild(topRow);

        // ── Detail row: service, employee, bay, phone ──
        var details = [];
        if (v.service || v.appt_service) details.push((v.service || v.appt_service).replace(/-/g, ' '));
        if (v.employee_name) details.push('\u2192 ' + v.employee_name);
        if (v.bay_number) details.push(t('visitBayLabel', 'Bay') + ' ' + v.bay_number);
        if (v.phone) details.push(v.phone);
        if (details.length) {
          card.appendChild(el('p', 'text-xs text-gray-500 dark:text-gray-400 mt-2', details.join(' \u2022 ')));
        }

        // ── Timing row: visual timeline ──
        var timeRow = el('div', 'mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center');

        // Check-in time
        var t1 = el('div', 'rounded-lg p-2 ' + (v.check_in_at ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700'));
        t1.appendChild(el('div', 'text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 font-semibold', t('visitArrival', 'Arrival')));
        t1.appendChild(el('div', 'text-sm font-bold ' + (v.check_in_at ? 'text-green-700 dark:text-green-400' : 'text-gray-300'), v.check_in_at ? fmtClock(v.check_in_at) : '--'));
        timeRow.appendChild(t1);

        // Service start
        var t2 = el('div', 'rounded-lg p-2 ' + (v.service_start_at ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700'));
        t2.appendChild(el('div', 'text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 font-semibold', t('visitServiceStart', 'Start')));
        t2.appendChild(el('div', 'text-sm font-bold ' + (v.service_start_at ? 'text-blue-700 dark:text-blue-400' : 'text-gray-300'), v.service_start_at ? fmtClock(v.service_start_at) : '--'));
        if (v.check_in_at && v.service_start_at) {
          t2.appendChild(el('div', 'text-[10px] text-gray-400', t('visitWaitLabel', 'wait') + ' ' + formatDuration(v.check_in_at, v.service_start_at)));
        }
        timeRow.appendChild(t2);

        // Service end
        var t3 = el('div', 'rounded-lg p-2 ' + (v.service_end_at ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700'));
        t3.appendChild(el('div', 'text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 font-semibold', t('visitServiceEnd', 'Done')));
        t3.appendChild(el('div', 'text-sm font-bold ' + (v.service_end_at ? 'text-amber-700 dark:text-amber-400' : 'text-gray-300'), v.service_end_at ? fmtClock(v.service_end_at) : '--'));
        if (v.service_start_at && v.service_end_at) {
          t3.appendChild(el('div', 'text-[10px] text-gray-400', t('visitSvcTime', 'service') + ' ' + formatDuration(v.service_start_at, v.service_end_at)));
        } else if (v.service_start_at && !v.service_end_at) {
          t3.appendChild(el('div', 'text-[10px] text-blue-500 font-medium', formatDuration(v.service_start_at) + '...'));
        }
        timeRow.appendChild(t3);

        // Check-out / total
        var t4 = el('div', 'rounded-lg p-2 ' + (v.check_out_at ? 'bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700'));
        t4.appendChild(el('div', 'text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 font-semibold', t('visitCheckOut', 'Check Out')));
        t4.appendChild(el('div', 'text-sm font-bold ' + (v.check_out_at ? 'text-gray-700 dark:text-gray-300' : 'text-gray-300'), v.check_out_at ? fmtClock(v.check_out_at) : '--'));
        if (v.check_in_at) {
          var totalTime = formatDuration(v.check_in_at, v.check_out_at || undefined);
          t4.appendChild(el('div', 'text-[10px] font-semibold ' + (v.check_out_at ? 'text-gray-500' : 'text-green-500'), t('visitTotal', 'total') + ' ' + totalTime));
        }
        timeRow.appendChild(t4);

        card.appendChild(timeRow);
        grid.appendChild(card);
      });
      container.appendChild(grid);

    } catch (err) {
      console.error('Visit tracker error:', err);
      container.appendChild(el('p', 'text-red-500 text-center py-8', t('visitLoadError', 'Error loading visit data')));
    }
  }

  // ─── Update Visit ────────────────────────────────────────────
  async function updateVisit(id, data) {
    try {
      var res = await fetch('/api/admin/visit-log.php', {
        method: 'PUT', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify(Object.assign({ id: id }, data))
      });
      var json = await res.json();
      if (json.success) {
        loadVisits(); // refresh dashboard widget
        toast(t('visitUpdated', 'Visit updated'));
      } else { toast(json.error || 'Update failed', true); }
    } catch (err) {
      console.error('Visit update error:', err);
      toast(t('visitNetworkError', 'Network error'), true);
    }
  }

  // Store pending appointments from last API call
  var pendingAppointments = [];

  // ─── Check In Modal (replaces prompt) ────────────────────────
  function showCheckIn() {
    removeModal();
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4');
    ov.id = 'visit-modal-overlay';
    ov.addEventListener('click', function(e) { if (e.target === ov) removeModal(); });

    var pn = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto');
    pn.appendChild(el('h3', 'text-lg font-bold dark:text-white', t('visitCheckInTitle', 'Check In Customer')));

    // Quick check-in from today's appointments
    if (pendingAppointments.length > 0) {
      var apptSection = el('div', 'border dark:border-gray-700 rounded-lg p-3 bg-green-50 dark:bg-green-900/20');
      apptSection.appendChild(el('p', 'text-xs font-semibold text-green-700 dark:text-green-400 mb-2 uppercase', t('visitTodayAppts', "Today's Appointments")));
      pendingAppointments.forEach(function(a) {
        var name = ((a.first_name || '') + ' ' + (a.last_name || '')).trim();
        var vehicle = [a.vehicle_year, a.vehicle_make, a.vehicle_model].filter(Boolean).join(' ');
        var row = el('div', 'flex items-center justify-between py-1.5 border-b dark:border-gray-700 last:border-b-0');
        var info = el('div', 'min-w-0');
        info.appendChild(el('div', 'text-sm font-medium text-gray-800 dark:text-gray-200 truncate', name + (vehicle ? ' — ' + vehicle : '')));
        info.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400', (a.preferred_time || '') + ' \u2022 ' + (a.service || '')));
        row.appendChild(info);
        var qBtn = el('button', 'shrink-0 ml-2 text-xs px-2.5 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium', t('visitQuickCheckIn', 'Check In'));
        qBtn.addEventListener('click', (function(appt) {
          return function() { doQuickCheckIn(appt.cust_id || appt.customer_id, appt.id); };
        })(a));
        row.appendChild(qBtn);
        apptSection.appendChild(row);
      });
      pn.appendChild(apptSection);
      pn.appendChild(el('div', 'text-center text-xs text-gray-400 dark:text-gray-500', '— ' + t('visitOrSearch', 'or search manually') + ' —'));
    }

    // Customer search
    pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300', t('visitSearchCustomer', 'Search Customer')));
    var searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.id = 'visit-search';
    searchInput.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    searchInput.placeholder = t('visitSearchPlaceholder', 'Name, email, or phone...');
    pn.appendChild(searchInput);

    var resultsDiv = el('div', 'max-h-40 overflow-y-auto border rounded-lg hidden dark:border-gray-600');
    resultsDiv.id = 'visit-search-results';
    pn.appendChild(resultsDiv);

    var selectedDiv = el('div', 'hidden');
    selectedDiv.id = 'visit-selected';
    pn.appendChild(selectedDiv);

    // Bay selector
    pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2', t('visitBayNumber', 'Bay Number')));
    var baySelect = document.createElement('select');
    baySelect.id = 'visit-bay';
    baySelect.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    baySelect.appendChild(el('option', null, t('visitNoBay', 'No bay assigned')));
    for (var i = 1; i <= BAY_COUNT; i++) {
      var opt = el('option', null, t('visitBayLabel', 'Bay') + ' ' + i);
      opt.value = i;
      baySelect.appendChild(opt);
    }
    pn.appendChild(baySelect);

    // Notes
    pn.appendChild(el('label', 'block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2', t('visitNotes', 'Notes (optional)')));
    var notesInput = document.createElement('input');
    notesInput.type = 'text';
    notesInput.id = 'visit-notes';
    notesInput.className = 'w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    pn.appendChild(notesInput);

    // Buttons
    var btnRow = el('div', 'flex gap-3 pt-2');
    var canBtn = el('button', 'flex-1 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition', t('cancel', 'Cancel'));
    canBtn.addEventListener('click', removeModal);
    btnRow.appendChild(canBtn);
    var submitBtn = el('button', 'flex-1 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition', t('visitCheckIn', 'Check In'));
    submitBtn.id = 'visit-submit';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.5';
    submitBtn.addEventListener('click', doCheckIn);
    btnRow.appendChild(submitBtn);
    pn.appendChild(btnRow);

    ov.appendChild(pn);
    document.body.appendChild(ov);
    searchInput.focus();

    // Search handler
    var searchTimer;
    var selectedCustomerId = null;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimer);
      var q = searchInput.value.trim();
      if (q.length < 2) { resultsDiv.classList.add('hidden'); return; }
      searchTimer = setTimeout(function() {
        fetch('/api/admin/customers.php?search=' + encodeURIComponent(q) + '&limit=5', { credentials: 'include', headers: hdrs() })
          .then(function(r) { return r.json(); })
          .then(function(json) {
            resultsDiv.textContent = '';
            var custs = json.data || [];
            if (!custs.length) {
              resultsDiv.appendChild(el('div', 'p-3 text-sm text-gray-400 dark:text-gray-500', t('visitNoResults', 'No customers found')));
              resultsDiv.classList.remove('hidden');
              return;
            }
            custs.forEach(function(c) {
              var row = el('div', 'p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-700 last:border-b-0');
              row.appendChild(el('div', 'font-medium text-sm text-gray-800 dark:text-gray-200', c.first_name + ' ' + c.last_name));
              row.appendChild(el('div', 'text-xs text-gray-500 dark:text-gray-400', [c.email, c.phone].filter(Boolean).join(' \u2022 ')));
              row.addEventListener('click', function() {
                selectedCustomerId = c.id;
                searchInput.value = c.first_name + ' ' + c.last_name;
                resultsDiv.classList.add('hidden');
                selectedDiv.className = 'p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm text-green-700 dark:text-green-400';
                selectedDiv.textContent = t('visitSelected', 'Selected') + ': ' + c.first_name + ' ' + c.last_name + ' (' + (c.email || c.phone || '#' + c.id) + ')';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
              });
              resultsDiv.appendChild(row);
            });
            resultsDiv.classList.remove('hidden');
          })
          .catch(function() {});
      }, 300);
    });

    function doCheckIn() {
      if (!selectedCustomerId) return;
      submitBtn.disabled = true;
      submitBtn.textContent = t('visitCheckingIn', 'Checking in...');
      fetch('/api/admin/visit-log.php', {
        method: 'POST', credentials: 'include', headers: hdrs(true),
        body: JSON.stringify({
          customer_id: selectedCustomerId,
          bay_number: baySelect.value ? parseInt(baySelect.value) : null,
          notes: notesInput.value.trim() || null
        })
      })
      .then(function(r) { return r.json(); })
      .then(function(json) {
        if (json.success) {
          toast(t('visitCheckedInSuccess', 'Customer checked in'));
          removeModal();
          loadVisits();
          loadFullVisitTracker();
        } else {
          toast(json.error || 'Check-in failed', true);
          submitBtn.disabled = false;
          submitBtn.textContent = t('visitCheckIn', 'Check In');
        }
      })
      .catch(function() {
        toast(t('visitNetworkError', 'Network error'), true);
        submitBtn.disabled = false;
        submitBtn.textContent = t('visitCheckIn', 'Check In');
      });
    }
  }

  // Quick check-in from appointment (no search needed)
  function doQuickCheckIn(customerId, appointmentId) {
    removeModal();
    fetch('/api/admin/visit-log.php', {
      method: 'POST', credentials: 'include', headers: hdrs(true),
      body: JSON.stringify({ customer_id: customerId, appointment_id: appointmentId })
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
      if (json.success) {
        toast(t('visitCheckedInSuccess', 'Customer checked in'));
        loadVisits();
        loadFullVisitTracker();
      } else { toast(json.error || 'Check-in failed', true); }
    })
    .catch(function() { toast(t('visitNetworkError', 'Network error'), true); });
  }

  // Public API
  window.ShopFloor = {
    init: function() { loadVisits(); refreshInterval = setInterval(loadVisits, 30000); },
    refresh: loadVisits,
    startService: function(id) { updateVisit(id, { service_start_at: 'now' }); },
    endService: function(id) { updateVisit(id, { service_end_at: 'now' }); },
    checkOut: function(id) { updateVisit(id, { check_out_at: 'now' }); },
    showCheckIn: showCheckIn,
    destroy: function() { if (refreshInterval) clearInterval(refreshInterval); }
  };

  window.loadVisitTracker = loadFullVisitTracker;
})();
