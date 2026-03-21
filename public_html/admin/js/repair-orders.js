/**
 * Oregon Tires — Admin Repair Orders Module
 * Handles: RO list, detail view, creation, status updates,
 *          inspection management, estimate management
 *
 * Depends on: api(), showToast(), csrfToken from admin/index.html
 */

(function() {
'use strict';

function t(key, fallback) {
  return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
}

// ─── State ───────────────────────────────────────────────────────────────────
var roList = [];
var roPage = 1;
var roTotal = 0;
var roLimit = 25;
var currentRo = null;

// ─── Status badge colors ─────────────────────────────────────────────────────
var statusColors = {
  intake:           'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
  check_in:         'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
  diagnosis:        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
  estimate_pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
  pending_approval: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
  approved:         'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
  in_progress:      'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
  on_hold:          'bg-red-700 text-white dark:bg-red-800 dark:text-red-100',
  waiting_parts:    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
  ready:            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
  completed:        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  invoiced:         'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300',
  cancelled:        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

function createStatusBadge(status) {
  var statusKey = 'roStatus' + (status || 'intake').replace(/_([a-z])/g, function(m,c){ return c.toUpperCase(); }).replace(/^[a-z]/, function(c){ return c.toUpperCase(); });
  var label = t(statusKey, (status || 'intake').replace(/_/g, ' ').replace(/^[a-z]/, function(c){ return c.toUpperCase(); }));
  var cls = statusColors[status] || statusColors.intake;
  var span = document.createElement('span');
  span.className = 'px-2.5 py-1 rounded-full text-xs font-bold ' + cls;
  span.textContent = label;
  return span;
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  var d = new Date(dateStr);
  return d.toLocaleDateString(currentLang === 'es' ? 'es-MX' : 'en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ─── Status Timeline / Stepper ──────────────────────────────────────────────
var timelineStatuses = ['intake', 'check_in', 'diagnosis', 'estimate_pending', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'waiting_parts', 'ready', 'completed', 'invoiced'];
function getTimelineLabels() {
  return {
    intake: t('roStatusIntake', 'Intake'),
    check_in: t('roStatusCheckIn', 'Check In'),
    diagnosis: t('roTimelineDiag', 'Diag'),
    estimate_pending: t('roTimelineEst', 'Est.'),
    pending_approval: t('roTimelineApproval', 'Approval'),
    approved: t('roStatusApproved', 'Approved'),
    in_progress: t('roTimelineInProg', 'In Prog'),
    on_hold: t('roTimelineOnHold', 'On Hold'),
    waiting_parts: t('roTimelineParts', 'Parts'),
    ready: t('roStatusReady', 'Ready'),
    completed: t('roTimelineDone', 'Done'),
    invoiced: t('roStatusInvoiced', 'Invoiced')
  };
}

function renderStatusTimeline(currentStatus) {
  var isCancelled = currentStatus === 'cancelled';
  var currentIdx  = timelineStatuses.indexOf(currentStatus);

  var wrapper = document.createElement('div');
  wrapper.style.cssText = 'display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:center;gap:0;margin-bottom:8px;padding:12px 8px 4px;background:#f9fafb;border-radius:12px;';

  // Cancelled banner
  if (isCancelled) {
    var cancelBanner = document.createElement('div');
    cancelBanner.style.cssText = 'width:100%;text-align:center;margin-bottom:8px;';
    var cancelBadge = document.createElement('span');
    cancelBadge.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#dc2626;font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;';
    var xMark = document.createElement('span');
    xMark.textContent = '\u2716';
    xMark.style.fontSize = '14px';
    cancelBadge.appendChild(xMark);
    var cancelTxt = document.createElement('span');
    cancelTxt.textContent = t('roStatusCancelled', 'Cancelled');
    cancelBadge.appendChild(cancelTxt);
    cancelBanner.appendChild(cancelBadge);
    wrapper.appendChild(cancelBanner);
  }

  timelineStatuses.forEach(function(status, idx) {
    var isPast    = !isCancelled && currentIdx >= 0 && idx < currentIdx;
    var isCurrent = !isCancelled && idx === currentIdx;

    // Step container (dot + label)
    var step = document.createElement('div');
    step.style.cssText = 'display:flex;flex-direction:column;align-items:center;position:relative;flex:0 0 auto;';

    // Row: connector line + dot
    var dotRow = document.createElement('div');
    dotRow.style.cssText = 'display:flex;align-items:center;';

    // Leading connector line (skip for first)
    if (idx > 0) {
      var lineBefore = document.createElement('div');
      lineBefore.style.cssText = 'width:18px;height:2px;' + (isPast || isCurrent ? 'background:#16a34a;' : 'background:#d1d5db;');
      if (isCancelled) lineBefore.style.background = '#d1d5db';
      dotRow.appendChild(lineBefore);
    }

    // Dot
    var dot = document.createElement('div');
    if (isCurrent) {
      dot.style.cssText = 'width:18px;height:18px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 4px rgba(22,163,106,0.25);flex-shrink:0;';
    } else if (isPast) {
      dot.style.cssText = 'width:12px;height:12px;border-radius:50%;background:#16a34a;flex-shrink:0;';
    } else {
      dot.style.cssText = 'width:12px;height:12px;border-radius:50%;background:#fff;border:2px solid #d1d5db;flex-shrink:0;';
    }
    if (isCancelled) {
      dot.style.cssText = 'width:12px;height:12px;border-radius:50%;background:#fff;border:2px solid #d1d5db;flex-shrink:0;opacity:0.5;';
    }
    dotRow.appendChild(dot);

    // Trailing connector line (skip for last)
    if (idx < timelineStatuses.length - 1) {
      var lineAfter = document.createElement('div');
      lineAfter.style.cssText = 'width:18px;height:2px;' + (isPast ? 'background:#16a34a;' : 'background:#d1d5db;');
      if (isCancelled) lineAfter.style.background = '#d1d5db';
      dotRow.appendChild(lineAfter);
    }

    step.appendChild(dotRow);

    // Label
    var label = document.createElement('div');
    label.textContent = getTimelineLabels()[status] || status;
    var labelColor = isCurrent ? '#16a34a' : isPast ? '#4b5563' : '#9ca3af';
    var labelWeight = isCurrent ? '700' : isPast ? '600' : '400';
    if (isCancelled) { labelColor = '#9ca3af'; labelWeight = '400'; }
    label.style.cssText = 'font-size:10px;margin-top:4px;color:' + labelColor + ';font-weight:' + labelWeight + ';white-space:nowrap;text-align:center;';
    step.appendChild(label);

    wrapper.appendChild(step);
  });

  return wrapper;
}

// ─── Load RO List ────────────────────────────────────────────────────────────
window.loadRepairOrders = async function() {
  var search = (document.getElementById('ro-search') || {}).value || '';
  var status = (document.getElementById('ro-status-filter') || {}).value || '';

  try {
    var params = new URLSearchParams({
      limit: roLimit,
      offset: (roPage - 1) * roLimit,
      sort_by: 'created_at',
      sort_order: 'DESC'
    });
    if (search) params.set('search', search);
    if (status) params.set('status', status);

    var json = await api('repair-orders.php?' + params.toString());
    roList = json.data || [];
    roTotal = json.total || 0;
    renderRoTable();
  } catch (err) {
    console.error('loadRepairOrders error:', err);
    showToast(t('roFailedLoad', 'Failed to load repair orders'), true);
  }
};

function renderRoTable() {
  var tbody = document.getElementById('ro-table');
  if (!tbody) return;
  tbody.textContent = '';

  if (roList.length === 0) {
    var row = document.createElement('tr');
    var cell = document.createElement('td');
    cell.colSpan = 7;
    cell.className = 'p-8 text-center text-gray-400';
    cell.textContent = t('roNoOrders', 'No repair orders found');
    row.appendChild(cell);
    tbody.appendChild(row);
    return;
  }

  function timeAgo(dateStr) {
    if (!dateStr) return '';
    var diff = Date.now() - new Date(dateStr).getTime();
    var mins = Math.floor(diff / 60000);
    if (mins < 60) return mins + 'm';
    var hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + 'h';
    var days = Math.floor(hrs / 24);
    return days + 'd';
  }

  roList.forEach(function(ro) {
    var tr = document.createElement('tr');
    tr.className = 'border-b hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition';
    tr.addEventListener('click', function() { viewRoDetail(ro.id); });

    // Column 1: RO# + Customer + Phone
    var td1 = document.createElement('td');
    td1.className = 'p-3 text-sm';
    var roLink = document.createElement('div');
    roLink.className = 'font-bold text-green-700 dark:text-green-400';
    roLink.textContent = ro.ro_number;
    td1.appendChild(roLink);
    var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();
    if (custName) {
      var nameEl = document.createElement('div');
      nameEl.className = 'font-medium text-gray-800 dark:text-gray-200';
      nameEl.textContent = custName;
      td1.appendChild(nameEl);
    }
    if (ro.customer_phone) {
      var phoneEl = document.createElement('div');
      phoneEl.className = 'text-xs text-gray-400';
      phoneEl.textContent = ro.customer_phone;
      td1.appendChild(phoneEl);
    }
    tr.appendChild(td1);

    // Column 2: Vehicle + Service
    var td2 = document.createElement('td');
    td2.className = 'p-3 text-sm';
    var vehStr = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ');
    if (vehStr) {
      var vehEl = document.createElement('div');
      vehEl.className = 'font-medium text-gray-800 dark:text-gray-200';
      vehEl.textContent = vehStr;
      td2.appendChild(vehEl);
    }
    if (ro.appt_service) {
      var svcEl = document.createElement('div');
      svcEl.className = 'text-xs text-green-600 dark:text-green-400';
      svcEl.textContent = ro.appt_service.replace(/-/g, ' ');
      td2.appendChild(svcEl);
    }
    if (ro.license_plate) {
      var plateEl = document.createElement('div');
      plateEl.className = 'text-xs text-gray-400';
      plateEl.textContent = ro.license_plate;
      td2.appendChild(plateEl);
    }
    tr.appendChild(td2);

    // Column 3: Appointment Date + Tech
    var td3 = document.createElement('td');
    td3.className = 'p-3 text-sm';
    if (ro.appt_date) {
      var dateEl = document.createElement('div');
      dateEl.className = 'font-medium text-gray-800 dark:text-gray-200';
      dateEl.textContent = fmtDate(ro.appt_date);
      td3.appendChild(dateEl);
      if (ro.appt_time) {
        var timeEl = document.createElement('div');
        timeEl.className = 'text-xs text-gray-500';
        timeEl.textContent = fmtTime(ro.appt_time);
        td3.appendChild(timeEl);
      }
    } else {
      td3.appendChild(document.createTextNode(formatDate(ro.created_at)));
    }
    if (ro.assigned_employee_name) {
      var techEl = document.createElement('div');
      techEl.className = 'text-xs text-blue-600 dark:text-blue-400 mt-0.5';
      techEl.textContent = '\u2192 ' + ro.assigned_employee_name;
      td3.appendChild(techEl);
    }
    tr.appendChild(td3);

    // Column 4: Status badge + step + time in status
    var td4 = document.createElement('td');
    td4.className = 'p-3 text-sm';
    td4.appendChild(createStatusBadge(ro.status));
    var updatedAge = timeAgo(ro.updated_at);
    if (updatedAge) {
      var timeLabel = document.createElement('div');
      timeLabel.className = 'text-xs text-gray-400 mt-1';
      timeLabel.textContent = updatedAge + ' ' + t('roInStatus', 'in status');
      td4.appendChild(timeLabel);
    }
    // Active labor indicator
    if (ro.active_labor_count > 0) {
      var laborInd = document.createElement('div');
      laborInd.className = 'flex items-center gap-1 mt-1';
      var pDot = document.createElement('span');
      pDot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
      laborInd.appendChild(pDot);
      var labText = document.createElement('span');
      labText.className = 'text-xs text-green-600 dark:text-green-400 font-medium';
      labText.textContent = ro.active_labor_count + ' ' + t('roTechWorking', 'working');
      laborInd.appendChild(labText);
      td4.appendChild(laborInd);
    }
    tr.appendChild(td4);

    // Column 5: DVI/Est counts + action
    var td5 = document.createElement('td');
    td5.className = 'p-3 text-sm';
    var badges = document.createElement('div');
    badges.className = 'flex flex-wrap gap-1';
    if (ro.inspection_count > 0) {
      var dviBadge = document.createElement('span');
      dviBadge.className = 'text-xs px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-medium';
      dviBadge.textContent = ro.inspection_count + ' DVI';
      badges.appendChild(dviBadge);
    }
    if (ro.estimate_count > 0) {
      var estBadge = document.createElement('span');
      estBadge.className = 'text-xs px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-medium';
      estBadge.textContent = ro.estimate_count + ' Est';
      badges.appendChild(estBadge);
    }
    td5.appendChild(badges);
    var viewBtn = document.createElement('button');
    viewBtn.className = 'text-green-600 hover:text-green-800 dark:text-green-400 text-xs font-bold mt-1 block';
    viewBtn.textContent = t('actionView', 'Open') + ' \u2192';
    viewBtn.addEventListener('click', function(e) { e.stopPropagation(); viewRoDetail(ro.id); });
    td5.appendChild(viewBtn);
    tr.appendChild(td5);

    tbody.appendChild(tr);
  });

  // Pagination
  var pagDiv = document.getElementById('ro-pagination');
  if (pagDiv) {
    pagDiv.textContent = '';
    var totalPages = Math.ceil(roTotal / roLimit);
    if (totalPages > 1) {
      for (var i = 1; i <= totalPages; i++) {
        var btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === roPage
          ? 'px-3 py-1 rounded bg-green-600 text-white text-sm font-bold'
          : 'px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-300';
        btn.addEventListener('click', (function(page) {
          return function() { roPage = page; loadRepairOrders(); };
        })(i));
        pagDiv.appendChild(btn);
      }
    }
  }
}

// ─── View RO Detail ──────────────────────────────────────────────────────────
window.viewRoDetail = async function(id) {
  try {
    var json = await api('repair-orders.php?id=' + id);
    currentRo = json.data;
    renderRoDetailModal();
  } catch (err) {
    showToast(t('roFailedLoad', 'Failed to load repair order') + ': ' + err.message, true);
  }
};

function renderRoDetailModal() {
  var ro = currentRo;
  if (!ro) return;

  var existing = document.getElementById('ro-detail-modal');
  if (existing) {
    if (existing._roTimerInterval) clearInterval(existing._roTimerInterval);
    existing.remove();
  }

  var vehicle = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ') || 'No vehicle';
  var customer = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();

  var modal = document.createElement('div');
  modal.id = 'ro-detail-modal';
  modal.className = 'fixed inset-0 z-50 flex items-start justify-center p-4 pt-12 modal-overlay overflow-y-auto';

  var card = document.createElement('div');
  card.className = 'bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-y-auto';

  // ═══════════════════════════════════════════════════════════════════════════
  // HEADER — Clean: RO#, customer, vehicle, status badge, close
  // ═══════════════════════════════════════════════════════════════════════════
  var header = document.createElement('div');
  header.className = 'bg-gradient-to-r from-green-700 to-green-900 text-white p-5 rounded-t-2xl';

  var headerTop = document.createElement('div');
  headerTop.className = 'flex justify-between items-start';

  var headerLeft = document.createElement('div');
  var h2 = document.createElement('h2');
  h2.className = 'text-xl font-bold flex items-center gap-3';
  h2.textContent = ro.ro_number;
  h2.appendChild(createStatusBadge(ro.status));
  headerLeft.appendChild(h2);

  var custP = document.createElement('p');
  custP.className = 'text-green-200 mt-1 text-sm';
  custP.textContent = customer + ' \u2022 ' + vehicle;
  if (ro.customer_phone) { custP.textContent += ' \u2022 ' + ro.customer_phone; }
  headerLeft.appendChild(custP);

  // Appointment info in header
  if (ro.appointment) {
    var apptP = document.createElement('p');
    apptP.className = 'text-green-300/70 text-xs mt-0.5';
    apptP.textContent = ro.appointment.reference_number + ' \u2022 ' + (ro.appointment.service || '').replace(/-/g, ' ') + ' \u2022 ' + ro.appointment.preferred_date;
    headerLeft.appendChild(apptP);
  }

  // Assigned tech row
  var techRow = document.createElement('div');
  techRow.className = 'flex items-center gap-2 mt-1.5';

  if (ro.assigned_employee_id) {
    var assignedEmp = (typeof employees !== 'undefined' ? employees : []).find(function(e) { return e.id === ro.assigned_employee_id; });
    var techName = assignedEmp ? assignedEmp.name : (ro.assigned_employee_name || 'Unknown');

    var techBadge = document.createElement('span');
    techBadge.className = 'inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-500/20 text-green-200 font-medium';
    techBadge.textContent = '\uD83D\uDD27 ' + techName;
    techRow.appendChild(techBadge);

    var changeBtn = document.createElement('button');
    changeBtn.className = 'text-[10px] px-1.5 py-0.5 rounded border border-white/30 text-white/70 hover:text-white hover:bg-white/10 transition';
    changeBtn.textContent = t('techPickerChange', 'Change');
    changeBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      if (typeof TechPicker === 'undefined') return;
      TechPicker.open({
        anchor: changeBtn,
        service: ro.appointment ? ro.appointment.service : null,
        date: ro.appointment ? ro.appointment.preferred_date : null,
        currentEmployeeId: ro.assigned_employee_id,
        onSelect: function(empId) {
          api('repair-orders.php', { method: 'PUT', body: { id: ro.id, assigned_employee_id: empId } })
            .then(function() { viewRoDetail(ro.id); })
            .catch(function(err) { showToast(err.message, true); });
        }
      });
    });
    techRow.appendChild(changeBtn);
  } else {
    var assignBtn = document.createElement('button');
    assignBtn.className = 'inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-200 hover:bg-amber-500/30 transition font-medium cursor-pointer';
    assignBtn.textContent = '\uD83D\uDD27 ' + t('techPickerAssign', 'Assign Tech');
    assignBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      if (typeof TechPicker === 'undefined') return;
      TechPicker.open({
        anchor: assignBtn,
        service: ro.appointment ? ro.appointment.service : null,
        date: ro.appointment ? ro.appointment.preferred_date : null,
        currentEmployeeId: null,
        onSelect: function(empId) {
          api('repair-orders.php', { method: 'PUT', body: { id: ro.id, assigned_employee_id: empId } })
            .then(function() { viewRoDetail(ro.id); })
            .catch(function(err) { showToast(err.message, true); });
        }
      });
    });
    techRow.appendChild(assignBtn);
  }
  headerLeft.appendChild(techRow);

  headerTop.appendChild(headerLeft);

  var headerBtns = document.createElement('div');
  headerBtns.className = 'flex items-center gap-2';

  var printBtn = document.createElement('button');
  printBtn.className = 'text-white/80 hover:text-white text-sm font-medium px-3 py-1.5 rounded-lg border border-white/30 hover:bg-white/10 transition flex items-center gap-1.5';
  printBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>';
  var printLabel = document.createTextNode(t('roPrintWorkOrder', 'Print'));
  printBtn.appendChild(printLabel);
  printBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    window.open('/work-order?ro_id=' + ro.id, '_blank');
  });
  headerBtns.appendChild(printBtn);

  var closeBtn = document.createElement('button');
  closeBtn.className = 'text-white/80 hover:text-white text-2xl font-bold leading-none';
  closeBtn.textContent = '\u00D7';
  function cleanupAndClose() {
    if (modal._roTimerInterval) clearInterval(modal._roTimerInterval);
    if (typeof LaborTracker !== 'undefined' && LaborTracker.cleanup) LaborTracker.cleanup();
    modal.remove();
  }
  closeBtn.addEventListener('click', cleanupAndClose);
  headerBtns.appendChild(closeBtn);
  headerTop.appendChild(headerBtns);
  header.appendChild(headerTop);
  card.appendChild(header);

  // ═══════════════════════════════════════════════════════════════════════════
  // BODY
  // ═══════════════════════════════════════════════════════════════════════════
  var body = document.createElement('div');
  body.className = 'p-5 space-y-4';

  // Status timeline stepper (compact)
  body.appendChild(renderStatusTimeline(ro.status));

  // ═══════════════════════════════════════════════════════════════════════════
  // TIME TRACKING — Visit + Labor unified view
  // ═══════════════════════════════════════════════════════════════════════════
  var hasTimeData = ro.checked_in_at || ro.service_started_at || (ro.active_labor && ro.active_labor.length > 0) || (ro.labor_entries && ro.labor_entries.length > 0);

  if (hasTimeData) {
    var timeCard = document.createElement('div');
    timeCard.className = 'bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden';

    // Header with live timers
    var timeHeader = document.createElement('div');
    timeHeader.className = 'px-4 py-3 bg-gray-100 dark:bg-gray-800 flex items-center justify-between flex-wrap gap-2';
    var timeTitle = document.createElement('span');
    timeTitle.className = 'text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400';
    timeTitle.textContent = t('roTimeTracking', 'Time Tracking');
    timeHeader.appendChild(timeTitle);

    // Live timer badges (visit + repair)
    var timerWrap = document.createElement('div');
    timerWrap.className = 'flex items-center gap-3';

    function makeLiveBadge(label, startTime, endTime, colorCls, dotColor) {
      if (!startTime) return null;
      var isLive = !endTime;
      var badge = document.createElement('span');
      badge.className = 'inline-flex items-center gap-1.5 text-xs font-bold px-2 py-1 rounded-full ' + colorCls;
      if (isLive) {
        var dot = document.createElement('span');
        dot.className = 'w-2 h-2 rounded-full animate-pulse ' + dotColor;
        badge.appendChild(dot);
      }
      var txt = document.createElement('span');
      txt.className = 'ro-live-timer';
      txt.setAttribute('data-start', startTime);
      if (endTime) txt.setAttribute('data-end', endTime);
      badge.appendChild(document.createTextNode(label + ' '));
      badge.appendChild(txt);
      return badge;
    }

    var vBadge = makeLiveBadge(t('roTimerVehicle', 'Vehicle In Shop'), ro.checked_in_at, ro.checked_out_at, 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300', 'bg-green-500');
    if (vBadge) timerWrap.appendChild(vBadge);

    var repairStart = ro.service_started_at;
    if (ro.active_labor && ro.active_labor.length > 0) repairStart = ro.active_labor[0].clock_in_at;
    var rBadge = makeLiveBadge(t('roTimerRepair', 'Repair'), repairStart, ro.service_ended_at, 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300', 'bg-orange-500');
    if (rBadge) timerWrap.appendChild(rBadge);

    timeHeader.appendChild(timerWrap);
    timeCard.appendChild(timeHeader);

    // ── 4-column timeline: Vehicle In → Service Start → Service Done → Vehicle Out ──
    var tlRow = document.createElement('div');
    tlRow.className = 'grid grid-cols-4 gap-2 p-4 text-center';

    function fmtClock(ts) {
      if (!ts) return '--';
      var d = new Date(ts.replace(' ', 'T'));
      var h = d.getHours(); var m = d.getMinutes();
      var ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return h + ':' + (m < 10 ? '0' : '') + m + ' ' + ampm;
    }
    function minutesBetween(a, b) {
      if (!a || !b) return null;
      return Math.floor((new Date(b.replace(' ', 'T')).getTime() - new Date(a.replace(' ', 'T')).getTime()) / 60000);
    }
    function fmtMin(m) {
      if (m === null || m === undefined) return '';
      if (m < 60) return m + 'm';
      return Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
    }

    var steps = [
      { label: t('roTimeVehicleIn', 'Vehicle In'), ts: ro.checked_in_at, color: 'green', active: !!ro.checked_in_at },
      { label: t('roTimeServiceStart', 'Service Start'), ts: ro.service_started_at, color: 'blue', active: !!ro.service_started_at, dur: minutesBetween(ro.checked_in_at, ro.service_started_at), durLabel: t('roTimeWait', 'wait') },
      { label: t('roTimeServiceEnd', 'Service Done'), ts: ro.service_ended_at, color: 'amber', active: !!ro.service_ended_at, dur: minutesBetween(ro.service_started_at, ro.service_ended_at), durLabel: t('roTimeService', 'service') },
      { label: t('roTimeVehicleOut', 'Vehicle Out'), ts: ro.checked_out_at, color: 'gray', active: !!ro.checked_out_at, dur: minutesBetween(ro.checked_in_at, ro.checked_out_at), durLabel: t('roTimeTotal', 'total') },
    ];

    steps.forEach(function(s) {
      var colorMap = { green: 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20', blue: 'border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20', amber: 'border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20', gray: 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800' };
      var textMap = { green: 'text-green-700 dark:text-green-400', blue: 'text-blue-700 dark:text-blue-400', amber: 'text-amber-700 dark:text-amber-400', gray: 'text-gray-600 dark:text-gray-400' };
      var cls = s.active ? (colorMap[s.color] || colorMap.gray) : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800';
      var cell = document.createElement('div');
      cell.className = 'rounded-lg p-2 border ' + cls;
      cell.appendChild(function() { var l = document.createElement('div'); l.className = 'text-[10px] font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500'; l.textContent = s.label; return l; }());
      cell.appendChild(function() { var v = document.createElement('div'); v.className = 'text-sm font-bold ' + (s.active ? (textMap[s.color] || '') : 'text-gray-300 dark:text-gray-600'); v.textContent = fmtClock(s.ts); return v; }());
      if (s.dur !== null && s.dur !== undefined) {
        cell.appendChild(function() { var d = document.createElement('div'); d.className = 'text-[10px] text-gray-400 mt-0.5'; d.textContent = s.durLabel + ' ' + fmtMin(s.dur); return d; }());
      }
      tlRow.appendChild(cell);
    });
    timeCard.appendChild(tlRow);

    // ── Active labor entries (currently clocked in) ──
    if (ro.active_labor && ro.active_labor.length > 0) {
      var activeDiv = document.createElement('div');
      activeDiv.className = 'px-4 pb-3';
      ro.active_labor.forEach(function(le) {
        var row = document.createElement('div');
        row.className = 'flex items-center gap-2 py-1.5 border-t border-gray-200/50 dark:border-gray-700/50';
        row.appendChild(function() { var d = document.createElement('span'); d.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse shrink-0'; return d; }());
        row.appendChild(function() { var n = document.createElement('span'); n.className = 'text-xs font-bold text-gray-800 dark:text-gray-200'; n.textContent = le.employee_name; return n; }());
        if (le.task_description) {
          row.appendChild(function() { var t = document.createElement('span'); t.className = 'text-xs text-gray-400 truncate'; t.textContent = le.task_description; return t; }());
        }
        row.appendChild(function() { var e = document.createElement('span'); e.className = 'text-xs font-medium text-green-600 dark:text-green-400 ml-auto ro-live-timer'; e.setAttribute('data-start', le.clock_in_at); return e; }());
        activeDiv.appendChild(row);
      });
      timeCard.appendChild(activeDiv);
    }

    // ── Completed labor summary (if entries exist) ──
    var completedLabor = (ro.labor_entries || []).filter(function(le) { return le.clock_out_at; });
    if (completedLabor.length > 0 || ro.labor_total_hours > 0) {
      var laborSummary = document.createElement('div');
      laborSummary.className = 'px-4 pb-3 flex items-center gap-3 text-xs';
      laborSummary.appendChild(function() { var s = document.createElement('span'); s.className = 'font-bold text-gray-500 dark:text-gray-400'; s.textContent = completedLabor.length + ' ' + t('roLaborEntries', 'labor entries'); return s; }());
      laborSummary.appendChild(function() { var s = document.createElement('span'); s.className = 'text-gray-400'; s.textContent = '\u2022'; return s; }());
      laborSummary.appendChild(function() { var s = document.createElement('span'); s.className = 'font-bold text-gray-700 dark:text-gray-300'; s.textContent = (ro.labor_total_hours || 0).toFixed(1) + 'h ' + t('roLaborTotal', 'total'); return s; }());
      laborSummary.appendChild(function() { var s = document.createElement('span'); s.className = 'text-gray-400'; s.textContent = '\u2022'; return s; }());
      laborSummary.appendChild(function() { var s = document.createElement('span'); s.className = 'font-bold text-green-600 dark:text-green-400'; s.textContent = (ro.labor_billable_hours || 0).toFixed(1) + 'h ' + t('roLaborBillable', 'billable'); return s; }());
      timeCard.appendChild(laborSummary);
    }

    body.appendChild(timeCard);

    // Live timer updater
    function updateRoTimers() {
      var timers = timeCard.querySelectorAll('.ro-live-timer');
      timers.forEach(function(el) {
        var start = new Date(el.getAttribute('data-start'));
        var endAttr = el.getAttribute('data-end');
        var end = endAttr ? new Date(endAttr) : new Date();
        var diff = Math.floor((end.getTime() - start.getTime()) / 60000);
        if (diff < 0) diff = 0;
        var h = Math.floor(diff / 60); var m = diff % 60;
        el.textContent = (h > 0 ? h + 'h ' : '') + m + 'm';
      });
    }
    updateRoTimers();
    modal._roTimerInterval = setInterval(updateRoTimers, 30000);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // GUIDED WORKFLOW — THE primary interface. One system, one flow.
  // ═══════════════════════════════════════════════════════════════════════════
  var hasInspections = ro.inspections && ro.inspections.length > 0;
  var hasEstimates = ro.estimates && ro.estimates.length > 0;
  var latestEstimate = hasEstimates ? ro.estimates[0] : null;
  var hasInvoices = ro.invoices && ro.invoices.length > 0;
  var status = ro.status || 'intake';

  // 11-step workflow: intake → check_in → diagnosis → estimate → approval → approved → work → ready → complete → invoiced → done
  var TOTAL_STEPS = 11;
  var WORKFLOW_STEPS = [
    { status: 'intake',           step: 1 },
    { status: 'check_in',        step: 2 },
    { status: 'diagnosis',        step: 3 },
    { status: 'estimate_pending', step: 4 },
    { status: 'pending_approval', step: 5 },
    { status: 'approved',         step: 6 },
    { status: 'in_progress',      step: 7 },
    { status: 'on_hold',          step: 7 },
    { status: 'waiting_parts',    step: 7 },
    { status: 'ready',            step: 8 },
    { status: 'completed',        step: 9 },
    { status: 'invoiced',         step: 10 },
  ];
  var currentStep = WORKFLOW_STEPS.find(function(s) { return s.status === status; });
  var stepNum = currentStep ? currentStep.step : 0;

  var guide = null;
  if (status === 'intake') guide = { text: t('roGuideCheckIn', 'Vehicle has arrived \u2014 check it in to start the process'), btn: t('roCheckInVehicle', 'Check In Vehicle'), color: 'blue', icon: '\uD83D\uDE97', action: 'check_in' };
  else if (status === 'check_in' && !hasInspections) guide = { text: t('roGuideInspect', 'Vehicle checked in \u2014 inspect it and start diagnosis'), btn: t('roStartDiagnosis', 'Start Diagnosis'), color: 'purple', icon: '\uD83D\uDD0D', action: 'start_diagnosis', sub: t('roGuideStartDiagSub', 'This will clock in the assigned technician and start the repair timer') };
  else if (status === 'check_in') guide = { text: t('roGuideDiag', 'Inspection complete \u2014 review findings and diagnose'), btn: t('roStartDiagnosis', 'Start Diagnosis'), color: 'purple', icon: '\u2699\uFE0F', action: 'start_diagnosis', sub: t('roGuideStartDiagSub', 'This will clock in the assigned technician and start the repair timer') };
  else if (status === 'diagnosis' && !hasEstimates) guide = { text: t('roGuideEstimate', 'Build an estimate from inspection findings'), btn: t('roCreateEstimate', 'Create Estimate'), color: 'blue', icon: '\uD83D\uDCCB', action: 'estimate' };
  else if (status === 'diagnosis') guide = { text: t('roGuideSendEst', 'Estimate ready \u2014 email it to the customer'), btn: t('roSendEstimate', 'Send Estimate'), color: 'blue', icon: '\uD83D\uDCE7', action: 'send_estimate' };
  else if (status === 'estimate_pending') guide = { text: t('roGuideSendPending', 'Send the estimate to the customer for review'), btn: t('roSendToCustomer', 'Send to Customer'), color: 'amber', icon: '\uD83D\uDCE7', action: 'send_estimate' };
  else if (status === 'pending_approval') guide = { text: t('roGuideApproval', 'Customer is reviewing the estimate. Approve when confirmed.'), btn: t('roMarkApproved', 'Mark Approved'), color: 'amber', icon: '\u23F3', action: 'approve' };
  else if (status === 'approved') guide = { text: t('roGuideStart', 'Customer approved! Start the repairs.'), btn: t('roStartRepairs', 'Start Repairs'), color: 'green', icon: '\uD83D\uDE80', action: 'start_work', sub: t('roGuideStartSub', 'This will clock in the tech and start the repair timer') };
  else if (status === 'in_progress') guide = {
    text: t('roGuideInProgress', 'Work in progress. Update the status as the job evolves.'),
    color: 'indigo', icon: '\uD83D\uDD27',
    multiAction: [
      { btn: t('roWaitingParts', 'Waiting on Parts'), action: 'waiting_parts', color: 'orange', icon: '\uD83D\uDCE6' },
      { btn: t('roPutOnHold', 'On Hold'), action: 'on_hold', color: 'red', icon: '\u23F8' },
      { btn: t('roMarkReady', 'Mark Ready'), action: 'ready', color: 'teal', icon: '\u2705' }
    ]
  };
  else if (status === 'on_hold') guide = { text: t('roGuideOnHold', 'Job is on hold. Resume when ready to continue.'), btn: t('roResumeWork', 'Resume Work'), color: 'red', icon: '\u23F8', action: 'resume' };
  else if (status === 'waiting_parts') guide = { text: t('roGuideWaitParts', 'Waiting for parts. Resume when parts arrive.'), btn: t('roPartsArrived', 'Parts Arrived \u2014 Resume'), color: 'orange', icon: '\uD83D\uDCE6', action: 'resume' };
  else if (status === 'ready') guide = { text: t('roGuidePickup', 'Vehicle is ready. Mark complete when approved by manager.'), btn: t('roMarkComplete', 'Mark Complete'), color: 'green', icon: '\u2705', action: 'complete', sub: t('roGuideCompleteSub', 'Manager gate \u2014 no invoice generated yet') };
  else if (status === 'completed') guide = { text: t('roGuideInvoice', 'Approved by manager \u2014 generate invoice and release vehicle'), btn: t('roInvoiceRelease', 'Invoice & Release Vehicle'), color: 'teal', icon: '\uD83D\uDCB0', action: 'invoice', sub: t('roGuideInvoiceSub', 'This will generate the invoice, email it, and mark the vehicle as out') };

  // Guided action handler (shared by bar button)
  function executeGuideAction(a) {
    function refreshAfterStatus() {
      if (typeof loadRepairOrders === 'function') loadRepairOrders();
      if (typeof loadKanban === 'function') loadKanban();
      if (typeof loadLaborSummary === 'function') loadLaborSummary();
    }
    if (a === 'check_in') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'check_in' } }).then(function() {
        showToast(t('roCheckedIn', 'Vehicle checked in')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'start_diagnosis') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'diagnosis' } }).then(function() {
        showToast(t('roDiagnosisStarted', 'Diagnosis started \u2014 tech clocked in')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'estimate') {
      var inspId = hasInspections ? ro.inspections[0].id : null;
      var payload = { repair_order_id: ro.id, tax_rate: 0.0 };
      if (inspId) payload.from_inspection_id = inspId;
      api('estimates.php', { method: 'POST', body: payload }).then(function() {
        showToast(t('roEstimateCreated', 'Estimate created')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'send_estimate') {
      if (latestEstimate) {
        api('estimates.php', { method: 'PUT', body: { id: latestEstimate.id, action: 'send' } }).then(function() {
          return api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'pending_approval' } });
        }).then(function() {
          showToast(t('roEstimateSent', 'Estimate sent \u2014 awaiting approval')); refreshAfterStatus(); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else {
        showToast(t('roNoEstimate', 'No estimate to send \u2014 create one first'), true);
      }
    } else if (a === 'approve') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'approved' } }).then(function() {
        showToast(t('roStatusAdvanced', 'Approved!')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'start_work') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'in_progress' } }).then(function() {
        showToast(t('roStatusAdvanced', 'Work started!')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'ready') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'ready' } }).then(function() {
        showToast(t('roMarkedReady', 'Marked Ready \u2014 customer notified')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'waiting_parts') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'waiting_parts' } }).then(function() {
        showToast(t('roWaitingPartsSet', 'Status set to Waiting on Parts')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'on_hold') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'on_hold' } }).then(function() {
        showToast(t('roOnHoldSet', 'Job put on hold')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'resume') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'in_progress' } }).then(function() {
        showToast(t('roStatusAdvanced', 'Resumed \u2014 back in progress')); refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'complete') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'completed' } }).then(function() {
        showToast(t('roMarkedComplete', 'Marked complete \u2014 ready for invoicing'));
        refreshAfterStatus(); viewRoDetail(ro.id);
      }).catch(function(err) { showToast(err.message, true); });
    } else if (a === 'invoice') {
      api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'invoiced' } }).then(function() {
        showToast(t('roInvoicedCheckOut', 'Invoice generated \u2014 vehicle released'));
        cleanupAndClose(); refreshAfterStatus();
      }).catch(function(err) { showToast(err.message, true); });
    }
  }

  // ── Render the guided action bar ──
  if (guide && currentStep) {
    var colorMap = { purple: 'from-purple-600 to-purple-700', blue: 'from-blue-600 to-blue-700', amber: 'from-amber-500 to-amber-600', green: 'from-green-600 to-green-700', teal: 'from-teal-600 to-teal-700', orange: 'from-orange-500 to-orange-600', red: 'from-red-800 to-red-900', indigo: 'from-indigo-600 to-indigo-700' };
    var guideBar = document.createElement('div');
    guideBar.className = 'bg-gradient-to-r ' + (colorMap[guide.color] || colorMap.blue) + ' rounded-xl p-4 text-white';

    var stepRow = document.createElement('div');
    stepRow.className = 'flex items-center justify-between mb-3';
    stepRow.appendChild(function() { var s = document.createElement('span'); s.className = 'text-xs font-bold uppercase tracking-wider opacity-80'; s.textContent = t('roStep', 'Step') + ' ' + stepNum + ' ' + t('roStepOf', 'of') + ' ' + TOTAL_STEPS; return s; }());
    var dots = document.createElement('div'); dots.className = 'flex gap-1.5';
    for (var di = 1; di <= TOTAL_STEPS; di++) {
      var dot = document.createElement('div');
      dot.className = 'w-2 h-2 rounded-full transition ' + (di < stepNum ? 'bg-white' : di === stepNum ? 'bg-white ring-2 ring-white/40 scale-110' : 'bg-white/25');
      dots.appendChild(dot);
    }
    stepRow.appendChild(dots);
    guideBar.appendChild(stepRow);

    var mainRow = document.createElement('div');
    mainRow.className = 'flex items-center gap-3';
    mainRow.appendChild(function() { var i = document.createElement('span'); i.className = 'text-3xl shrink-0'; i.textContent = guide.icon; return i; }());
    var textCol = document.createElement('div');
    textCol.className = 'flex-1';
    textCol.appendChild(function() { var p = document.createElement('p'); p.className = 'font-medium'; p.textContent = guide.text; return p; }());
    if (guide.sub) {
      textCol.appendChild(function() { var s = document.createElement('p'); s.className = 'text-xs opacity-70 mt-0.5'; s.textContent = guide.sub; return s; }());
    }
    mainRow.appendChild(textCol);

    if (guide.multiAction) {
      // Multiple action buttons (e.g. in_progress → waiting_parts / on_hold / ready)
      var btnGroup = document.createElement('div');
      btnGroup.className = 'flex flex-wrap gap-2 shrink-0';
      guide.multiAction.forEach(function(ma) {
        var btnColorMap = { teal: 'bg-white text-gray-900 hover:bg-gray-100', orange: 'bg-orange-400/30 text-white hover:bg-orange-400/50 border border-orange-300/40', red: 'bg-red-400/30 text-white hover:bg-red-400/50 border border-red-300/40' };
        var maBtn = document.createElement('button');
        maBtn.className = 'px-4 py-2 rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-1.5 ' + (btnColorMap[ma.color] || 'bg-white/20 text-white hover:bg-white/30');
        var maIcon = document.createElement('span');
        maIcon.textContent = ma.icon;
        maBtn.appendChild(maIcon);
        var maTxt = document.createElement('span');
        maTxt.textContent = ma.btn;
        maBtn.appendChild(maTxt);
        maBtn.addEventListener('click', function() { executeGuideAction(ma.action); });
        btnGroup.appendChild(maBtn);
      });
      mainRow.appendChild(btnGroup);
    } else {
      var gBtn = document.createElement('button');
      gBtn.className = 'shrink-0 px-5 py-2.5 bg-white text-gray-900 rounded-lg font-bold text-sm hover:bg-gray-100 transition shadow-lg';
      gBtn.textContent = guide.btn;
      gBtn.addEventListener('click', function() { executeGuideAction(guide.action); });
      mainRow.appendChild(gBtn);
    }
    guideBar.appendChild(mainRow);
    body.appendChild(guideBar);
  } else if (status === 'invoiced') {
    var doneBar = document.createElement('div');
    doneBar.className = 'bg-green-50 dark:bg-green-900/20 border-2 border-green-300 dark:border-green-700 rounded-xl p-4 flex items-center gap-3';
    doneBar.appendChild(function() { var i = document.createElement('span'); i.className = 'text-3xl'; i.textContent = '\u2705'; return i; }());
    doneBar.appendChild(function() { var p = document.createElement('p'); p.className = 'font-semibold text-green-800 dark:text-green-300'; p.textContent = t('roInvoicedDone', 'This repair order is complete and invoiced.'); return p; }());
    body.appendChild(doneBar);
  } else if (status === 'cancelled') {
    var cancelBar = document.createElement('div');
    cancelBar.className = 'bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-xl p-4 flex items-center gap-3';
    cancelBar.appendChild(function() { var i = document.createElement('span'); i.className = 'text-3xl'; i.textContent = '\u274C'; return i; }());
    cancelBar.appendChild(function() { var p = document.createElement('p'); p.className = 'font-semibold text-red-800 dark:text-red-300'; p.textContent = t('roCancelledDone', 'This repair order has been cancelled.'); return p; }());
    body.appendChild(cancelBar);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CONTEXT-AWARE SECTIONS — ordered by relevance to the current step
  // ═══════════════════════════════════════════════════════════════════════════

  // Pre-build all sections into named containers so we can order them by status
  var _sections = {};

  // ── Customer concern ──
  if (ro.customer_concern) {
    var concernDiv = document.createElement('div');
    // Highlight concern prominently during intake/check_in when it's the primary info
    var isEarlyStage = ['intake', 'check_in', 'diagnosis'].indexOf(status) !== -1;
    concernDiv.className = isEarlyStage
      ? 'bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-xl p-4 flex items-start gap-3'
      : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 flex items-start gap-2';
    concernDiv.appendChild(function() { var i = document.createElement('span'); i.className = isEarlyStage ? 'text-2xl shrink-0' : 'text-lg shrink-0'; i.textContent = '\u26A0\uFE0F'; return i; }());
    var concernContent = document.createElement('div');
    concernContent.appendChild(function() { var h = document.createElement('h4'); h.className = 'text-xs font-bold text-amber-800 dark:text-amber-300 uppercase'; h.textContent = t('roCustomerConcern', 'Customer Concern'); return h; }());
    concernContent.appendChild(function() { var p = document.createElement('p'); p.className = (isEarlyStage ? 'text-base' : 'text-sm') + ' text-gray-700 dark:text-gray-300 mt-0.5'; p.textContent = ro.customer_concern; return p; }());
    concernDiv.appendChild(concernContent);
    _sections.concern = concernDiv;
  }

  // ── Collapsible Details (vehicle specs, dates, VIN — not the primary focus) ──
  var detailsToggle = document.createElement('details');
  detailsToggle.className = 'border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden';
  var detailsSummary = document.createElement('summary');
  detailsSummary.className = 'px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300 select-none hover:bg-gray-100 dark:hover:bg-gray-800 transition';
  detailsSummary.textContent = t('roShowDetails', 'Vehicle & Order Details');
  detailsToggle.appendChild(detailsSummary);
  var detailsGrid = document.createElement('div');
  detailsGrid.className = 'grid grid-cols-2 md:grid-cols-3 gap-3 p-4';
  var vehicleSpecs = [ro.engine, ro.transmission, ro.drive_type].filter(Boolean).join(' | ');
  var detailItems = [
    [t('roThVehicle', 'Vehicle'), vehicle + (ro.trim_level ? ' ' + ro.trim_level : '')],
    ['VIN', ro.vin || '-'],
    [t('roPlate', 'Plate'), ro.license_plate || '-'],
    [t('roMileageIn', 'Mileage In'), ro.mileage_in ? Number(ro.mileage_in).toLocaleString() : '-'],
  ];
  if (vehicleSpecs) detailItems.push([t('roSpecs', 'Specs'), vehicleSpecs]);
  if (ro.fuel_type) detailItems.push([t('roFuel', 'Fuel'), ro.fuel_type]);
  detailItems.push(
    [t('roPromisedDate', 'Promised'), ro.promised_date || '-'],
    [t('roThCreated', 'Created'), formatDate(ro.created_at)],
    [t('roUpdated', 'Updated'), formatDate(ro.updated_at)]
  );
  detailItems.forEach(function(pair) {
    var div = document.createElement('div');
    div.appendChild(function() { var l = document.createElement('p'); l.className = 'text-[10px] text-gray-400 uppercase font-bold'; l.textContent = pair[0]; return l; }());
    div.appendChild(function() { var v = document.createElement('p'); v.className = 'text-sm font-medium text-gray-800 dark:text-gray-200'; v.textContent = pair[1]; return v; }());
    detailsGrid.appendChild(div);
  });

  // Advanced status override (inside details)
  var advRow = document.createElement('div');
  advRow.className = 'col-span-full border-t border-gray-200 dark:border-gray-700 pt-3 mt-2 flex items-center gap-2';
  advRow.appendChild(function() { var l = document.createElement('span'); l.className = 'text-[10px] text-gray-400 uppercase font-bold'; l.textContent = t('roManualStatus', 'Manual Override'); return l; }());
  var statusSelect = document.createElement('select');
  statusSelect.className = 'border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-2 py-1 text-xs';
  ['intake','check_in','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'].forEach(function(s) {
    var opt = document.createElement('option'); opt.value = s; opt.textContent = s.replace(/_/g, ' '); opt.className = 'text-gray-900'; if (s === ro.status) opt.selected = true; statusSelect.appendChild(opt);
  });
  advRow.appendChild(statusSelect);
  var advBtn = document.createElement('button');
  advBtn.className = 'px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs hover:bg-gray-300 dark:hover:bg-gray-500 transition';
  advBtn.textContent = t('roUpdateStatus', 'Update');
  advBtn.addEventListener('click', async function() {
    if (statusSelect.value === ro.status) return;
    try {
      await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: statusSelect.value } });
      showToast(t('roStatusUpdatedTo', 'Status updated to') + ' ' + statusSelect.value.replace(/_/g, ' '));
      if (typeof loadRepairOrders === 'function') loadRepairOrders();
      if (typeof loadKanban === 'function') loadKanban();
      if (typeof loadLaborSummary === 'function') loadLaborSummary();
      viewRoDetail(ro.id);
    } catch (err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
  });
  advRow.appendChild(advBtn);
  detailsGrid.appendChild(advRow);

  detailsToggle.appendChild(detailsGrid);
  // Auto-open details during intake (user needs to verify vehicle info)
  if (status === 'intake') detailsToggle.open = true;
  _sections.details = detailsToggle;

  // ── Appointment Origin (if linked) ──
  if (ro.appointment && (ro.appointment.notes || ro.appointment.admin_notes)) {
    var apptOrigin = document.createElement('details');
    apptOrigin.className = 'border border-green-200 dark:border-green-800 rounded-xl overflow-hidden';
    apptOrigin.open = true;
    var apptSummary = document.createElement('summary');
    apptSummary.className = 'px-4 py-2.5 bg-green-50 dark:bg-green-900/20 cursor-pointer text-sm font-medium text-green-800 dark:text-green-300 select-none hover:bg-green-100 dark:hover:bg-green-900/30 transition';
    apptSummary.textContent = t('roApptOrigin', 'Appointment Notes') + (ro.appointment.reference_number ? ' (' + ro.appointment.reference_number + ')' : '');
    apptOrigin.appendChild(apptSummary);
    var apptBody = document.createElement('div');
    apptBody.className = 'p-4 space-y-2';
    if (ro.appointment.notes) {
      var custNoteDiv = document.createElement('div');
      custNoteDiv.className = 'p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700';
      var custLabel = document.createElement('div'); custLabel.className = 'text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider mb-1'; custLabel.textContent = t('roBookingNotes', 'Customer Booking Notes');
      custNoteDiv.appendChild(custLabel);
      var custText = document.createElement('p'); custText.className = 'text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap'; custText.textContent = ro.appointment.notes;
      custNoteDiv.appendChild(custText);
      apptBody.appendChild(custNoteDiv);
    }
    if (ro.appointment.admin_notes) {
      var admNoteDiv = document.createElement('div');
      admNoteDiv.className = 'p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600';
      var admLabel = document.createElement('div'); admLabel.className = 'text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1'; admLabel.textContent = t('roApptAdminNotes', 'Admin Notes (from Appointment)');
      admNoteDiv.appendChild(admLabel);
      var admText = document.createElement('div'); admText.className = 'text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap'; admText.textContent = ro.appointment.admin_notes;
      admNoteDiv.appendChild(admText);
      apptBody.appendChild(admNoteDiv);
    }
    apptOrigin.appendChild(apptBody);
    _sections.apptOrigin = apptOrigin;
  }

  // ── Notes (collapsible) ──
  var hasNotes = (ro.technician_notes && ro.technician_notes.trim()) || (ro.admin_notes && ro.admin_notes.trim());
  var notesDetails = document.createElement('details');
  notesDetails.className = 'border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden';
  if (hasNotes) notesDetails.open = true;
  var notesSummary = document.createElement('summary');
  notesSummary.className = 'px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300 select-none hover:bg-gray-100 dark:hover:bg-gray-800 transition';
  notesSummary.textContent = t('roNotes', 'Notes') + (hasNotes ? '' : ' (' + t('roNoNotes', 'none') + ')');
  notesDetails.appendChild(notesSummary);
  var notesBody = document.createElement('div');
  notesBody.className = 'p-4 space-y-3';
  if (ro.technician_notes && ro.technician_notes.trim()) {
    var techLog = document.createElement('div'); techLog.className = 'p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700';
    techLog.appendChild(function() { var l = document.createElement('div'); l.className = 'text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider mb-1'; l.textContent = t('roTechNotes', 'Tech Notes'); return l; }());
    techLog.appendChild(function() { var d = document.createElement('div'); d.className = 'text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap'; d.textContent = ro.technician_notes; return d; }());
    notesBody.appendChild(techLog);
  }
  if (ro.admin_notes && ro.admin_notes.trim()) {
    var adminLog = document.createElement('div'); adminLog.className = 'p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600';
    adminLog.appendChild(function() { var l = document.createElement('div'); l.className = 'text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1'; l.textContent = t('roAdminNotes', 'Admin Notes'); return l; }());
    adminLog.appendChild(function() { var d = document.createElement('div'); d.className = 'text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap'; d.textContent = ro.admin_notes; return d; }());
    notesBody.appendChild(adminLog);
  }
  // Add note form
  var noteForm = document.createElement('div'); noteForm.className = 'border-t border-gray-200 dark:border-gray-700 pt-3';
  var noteTextarea = document.createElement('textarea');
  noteTextarea.className = 'w-full p-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 resize-none';
  noteTextarea.rows = 2; noteTextarea.maxLength = 2000; noteTextarea.placeholder = t('roNotePlaceholder', 'Add a note...');
  noteForm.appendChild(noteTextarea);
  var noteActions = document.createElement('div'); noteActions.className = 'flex gap-2 mt-2';
  ['technician_notes', 'admin_notes'].forEach(function(field) {
    var btn = document.createElement('button');
    btn.className = 'px-3 py-1 ' + (field === 'technician_notes' ? 'bg-blue-600' : 'bg-gray-600') + ' text-white rounded-lg text-xs font-medium hover:opacity-90 transition';
    btn.textContent = field === 'technician_notes' ? t('roSaveTechNote', 'Tech Note') : t('roSaveAdminNote', 'Admin Note');
    btn.addEventListener('click', async function() {
      var txt = noteTextarea.value.trim(); if (!txt) return;
      try { var b = { id: ro.id, note_append: true }; b[field] = txt; await api('repair-orders.php', { method: 'PUT', body: b }); showToast(t('roNoteSaved', 'Saved')); viewRoDetail(ro.id); } catch(e) { showToast(e.message, true); }
    });
    noteActions.appendChild(btn);
  });
  noteForm.appendChild(noteActions);
  notesBody.appendChild(noteForm);
  notesDetails.appendChild(notesBody);
  _sections.notes = notesDetails;

  // Inspections
  if (ro.inspections && ro.inspections.length > 0) {
    var inspSection = document.createElement('div');
    var inspH = document.createElement('h3');
    inspH.className = 'font-bold text-gray-900 dark:text-white mb-3';
    inspH.textContent = t('roInspections', 'Inspections') + ' (' + ro.inspections.length + ')';
    inspSection.appendChild(inspH);

    ro.inspections.forEach(function(insp) {
      var iCard = document.createElement('div');
      iCard.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-2';

      // Top row: status, date, condition, actions
      var iTopRow = document.createElement('div');
      iTopRow.className = 'flex justify-between items-center';

      var iLeft = document.createElement('div');
      iLeft.className = 'flex items-center gap-3';

      var iStatusBadge = document.createElement('span');
      var iStatusCls = insp.status === 'completed' || insp.status === 'sent' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
        insp.status === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
      iStatusBadge.className = 'text-xs font-bold px-2 py-1 rounded ' + iStatusCls;
      iStatusBadge.textContent = insp.status;
      iLeft.appendChild(iStatusBadge);

      var iDate = document.createElement('span');
      iDate.className = 'text-sm text-gray-500';
      iDate.textContent = formatDate(insp.created_at);
      iLeft.appendChild(iDate);

      if (insp.overall_condition) {
        var condColors = { green: 'bg-green-500', yellow: 'bg-yellow-500', red: 'bg-red-500' };
        var condDot = document.createElement('span');
        condDot.className = 'w-3 h-3 rounded-full ' + (condColors[insp.overall_condition] || 'bg-gray-400');
        iLeft.appendChild(condDot);
      }

      // Photo count badge
      if (insp.photo_count && insp.photo_count > 0) {
        var photoBadge = document.createElement('span');
        photoBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        photoBadge.textContent = insp.photo_count + ' photo' + (insp.photo_count > 1 ? 's' : '');
        iLeft.appendChild(photoBadge);
      }

      iTopRow.appendChild(iLeft);

      var iActions = document.createElement('div');
      iActions.className = 'flex gap-2';

      if (insp.status !== 'sent' && insp.status !== 'completed') {
        var compBtn = document.createElement('button');
        compBtn.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
        compBtn.textContent = t('roComplete', 'Complete');
        compBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try { await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'complete' } }); showToast(t('roInspectionCompleted', 'Inspection completed')); viewRoDetail(ro.id); }
          catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(compBtn);
      }

      if (insp.status === 'completed') {
        var sendBtn = document.createElement('button');
        sendBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium';
        sendBtn.textContent = t('roSendToCustomer', 'Send to Customer');
        sendBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try { await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } }); showToast(t('roInspectionSent', 'Inspection sent to customer')); viewRoDetail(ro.id); }
          catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(sendBtn);

        var resendInspBtn = document.createElement('button');
        resendInspBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendInspBtn.textContent = t('roResendToCustomer', 'Resend to Customer');
        resendInspBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } });
            showToast(t('roInspectionResent', 'Inspection re-sent to customer'));
          } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(resendInspBtn);
      }

      if (insp.status === 'sent') {
        var resendSentInspBtn = document.createElement('button');
        resendSentInspBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendSentInspBtn.textContent = t('roResendToCustomer', 'Resend to Customer');
        resendSentInspBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } });
            showToast(t('roInspectionResent', 'Inspection re-sent to customer'));
          } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(resendSentInspBtn);
      }

      iTopRow.appendChild(iActions);
      iCard.appendChild(iTopRow);

      // Inspection photos — collect all photos from items with non-green ratings first, then all
      var allPhotos = [];
      if (insp.items && insp.items.length > 0) {
        insp.items.forEach(function(item) {
          if (item.photos && item.photos.length > 0) {
            item.photos.forEach(function(photo) {
              allPhotos.push({
                url: photo.image_url,
                caption: photo.caption || '',
                itemLabel: item.label || '',
                itemCategory: item.category || '',
                itemRating: item.condition_rating || 'green'
              });
            });
          }
        });
      }

      if (allPhotos.length > 0) {
        var photoGrid = document.createElement('div');
        photoGrid.className = 'flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/50';

        allPhotos.forEach(function(photoData, idx) {
          var thumbWrap = document.createElement('button');
          thumbWrap.className = 'relative group rounded-lg overflow-hidden border-2 transition hover:border-green-500 focus:border-green-500 focus:outline-none';
          // Rating-colored border
          var ratingBorderCls = photoData.itemRating === 'red' ? 'border-red-400 dark:border-red-600' :
            photoData.itemRating === 'yellow' ? 'border-yellow-400 dark:border-yellow-600' : 'border-gray-200 dark:border-gray-600';
          thumbWrap.className += ' ' + ratingBorderCls;

          var thumbImg = document.createElement('img');
          thumbImg.src = photoData.url;
          thumbImg.alt = photoData.itemLabel + (photoData.caption ? ' - ' + photoData.caption : '');
          thumbImg.className = 'w-16 h-16 object-cover';
          thumbImg.loading = 'lazy';
          thumbWrap.appendChild(thumbImg);

          // Hover overlay with item label
          var overlay = document.createElement('div');
          overlay.className = 'absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-end p-1';
          var overlayText = document.createElement('span');
          overlayText.className = 'text-[9px] text-white font-medium leading-tight truncate';
          overlayText.textContent = photoData.itemLabel;
          overlay.appendChild(overlayText);
          thumbWrap.appendChild(overlay);

          // Click to open lightbox
          thumbWrap.addEventListener('click', function(e) {
            e.stopPropagation();
            _showInspectionPhotoLightbox(allPhotos, idx);
          });

          photoGrid.appendChild(thumbWrap);
        });

        iCard.appendChild(photoGrid);
      }

      inspSection.appendChild(iCard);
    });
    // Highlight inspection section during check_in/diagnosis when it's the focus
    if (status === 'check_in' || status === 'diagnosis') {
      inspSection.className = 'ring-2 ring-purple-300 dark:ring-purple-700 rounded-xl p-1';
    }
    _sections.inspections = inspSection;
  } else {
    // No inspections — show prompt during check_in
    if (status === 'check_in') {
      var noInspDiv = document.createElement('div');
      noInspDiv.className = 'border-2 border-dashed border-purple-300 dark:border-purple-700 rounded-xl p-4 text-center';
      noInspDiv.appendChild(function() { var p = document.createElement('p'); p.className = 'text-sm text-purple-600 dark:text-purple-400 font-medium'; p.textContent = t('roNoInspYet', 'No inspection yet \u2014 the "Start Diagnosis" button above will create one and begin the inspection process.'); return p; }());
      _sections.inspections = noInspDiv;
    }
  }

  // Estimates
  var estSection = document.createElement('div');
  var estH = document.createElement('h3');
  estH.className = 'font-bold text-gray-900 dark:text-white mb-3';
  estH.textContent = t('roEstimates', 'Estimates') + (ro.estimates && ro.estimates.length ? ' (' + ro.estimates.length + ')' : '');
  estSection.appendChild(estH);

  if (ro.estimates && ro.estimates.length > 0) {
    ro.estimates.forEach(function(est) {
      var eCard = document.createElement('div');
      eCard.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-3';

      // Header row
      var eHeader = document.createElement('div');
      eHeader.className = 'flex justify-between items-center mb-3';

      var eLeft = document.createElement('div');
      eLeft.className = 'flex items-center gap-3 flex-wrap';

      var eNum = document.createElement('span');
      eNum.className = 'font-bold text-sm text-gray-900 dark:text-white';
      eNum.textContent = est.estimate_number;
      eLeft.appendChild(eNum);

      var eStatusBadge = document.createElement('span');
      var esCls = est.status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
        est.status === 'partial' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
        (est.status === 'sent' || est.status === 'viewed') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
        est.status === 'declined' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
      eStatusBadge.className = 'text-xs font-bold px-2 py-1 rounded ' + esCls;
      eStatusBadge.textContent = est.status;
      eLeft.appendChild(eStatusBadge);

      var eTotal = document.createElement('span');
      eTotal.className = 'text-sm font-semibold text-gray-700 dark:text-gray-300';
      eTotal.textContent = '$' + parseFloat(est.total || 0).toFixed(2);
      eLeft.appendChild(eTotal);

      var eVer = document.createElement('span');
      eVer.className = 'text-xs text-gray-400';
      eVer.textContent = 'v' + est.version;
      eLeft.appendChild(eVer);
      eHeader.appendChild(eLeft);

      var eActions = document.createElement('div');
      eActions.className = 'flex gap-2 flex-wrap';

      // Edit items button (expand/collapse)
      var editItemsBtn = document.createElement('button');
      editItemsBtn.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
      editItemsBtn.textContent = t('roEditItems', 'Edit Items');
      editItemsBtn.addEventListener('click', (function(estId, card) { return function(e) {
        e.stopPropagation();
        var existing = card.querySelector('.est-items-editor');
        if (existing) { existing.remove(); editItemsBtn.textContent = t('roEditItems', 'Edit Items'); return; }
        editItemsBtn.textContent = t('roHideItems', 'Hide Items');
        loadEstimateItems(estId, card, ro.id);
      }; })(est.id, eCard));
      eActions.appendChild(editItemsBtn);

      if (est.status === 'draft') {
        var sendEstBtn = document.createElement('button');
        sendEstBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium';
        sendEstBtn.textContent = t('roSendToCustomer', 'Send');
        sendEstBtn.addEventListener('click', (function(eid) { return async function(e) {
          e.stopPropagation();
          try { await api('estimates.php', { method: 'PUT', body: { id: eid, action: 'send' } }); showToast(t('roEstimateSent', 'Estimate sent to customer')); viewRoDetail(ro.id); }
          catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(est.id));
        eActions.appendChild(sendEstBtn);
      }

      if (est.status === 'sent' || est.status === 'viewed' || est.status === 'approved' || est.status === 'partial') {
        var resendEstBtn = document.createElement('button');
        resendEstBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendEstBtn.textContent = t('roResendToCustomer', 'Resend');
        resendEstBtn.addEventListener('click', (function(eid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('estimates.php', { method: 'PUT', body: { id: eid, action: 'send' } });
            showToast(t('roEstimateResent', 'Estimate re-sent to customer'));
          } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(est.id));
        eActions.appendChild(resendEstBtn);
      }

      eHeader.appendChild(eActions);
      eCard.appendChild(eHeader);
      estSection.appendChild(eCard);
    });
  } else {
    var noEst = document.createElement('p');
    noEst.className = 'text-sm text-gray-400 italic';
    noEst.textContent = t('roNoEstimates', 'No estimates yet. Click "New Estimate" above to create one.');
    estSection.appendChild(noEst);
  }
  // Highlight estimate section when it's the focus
  if (['diagnosis', 'estimate_pending', 'pending_approval'].indexOf(status) !== -1) {
    estSection.className = 'ring-2 ring-blue-300 dark:ring-blue-700 rounded-xl p-1';
  }
  _sections.estimates = estSection;

  // Invoices
  if (ro.invoices && ro.invoices.length > 0) {
    var invSection = document.createElement('div');
    var invH = document.createElement('h3');
    invH.className = 'font-bold text-gray-900 dark:text-white mb-3';
    invH.textContent = t('roInvoices', 'Invoices') + ' (' + ro.invoices.length + ')';
    invSection.appendChild(invH);

    ro.invoices.forEach(function(inv) {
      var iCard = document.createElement('div');
      iCard.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-2 flex justify-between items-center';

      var iLeft = document.createElement('div');
      iLeft.className = 'flex items-center gap-3 flex-wrap';

      var iNum = document.createElement('span');
      iNum.className = 'font-bold text-sm text-gray-900 dark:text-white';
      iNum.textContent = inv.invoice_number;
      iLeft.appendChild(iNum);

      var invStatusCls = inv.status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
        inv.status === 'sent' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
        inv.status === 'overdue' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
        inv.status === 'void' ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' :
        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
      var iStatusBadge = document.createElement('span');
      iStatusBadge.className = 'text-xs font-bold px-2 py-1 rounded ' + invStatusCls;
      iStatusBadge.textContent = inv.status;
      iLeft.appendChild(iStatusBadge);

      var iTotal = document.createElement('span');
      iTotal.className = 'text-sm font-semibold text-gray-700 dark:text-gray-300';
      iTotal.textContent = '$' + parseFloat(inv.total || 0).toFixed(2);
      iLeft.appendChild(iTotal);

      iCard.appendChild(iLeft);

      var iActions = document.createElement('div');
      iActions.className = 'flex gap-2';

      // View invoice link
      if (inv.customer_view_token) {
        var viewLink = document.createElement('a');
        viewLink.href = '/invoice/' + inv.customer_view_token;
        viewLink.target = '_blank';
        viewLink.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
        viewLink.textContent = t('roViewInvoice', 'View');
        iActions.appendChild(viewLink);
      }

      // Mark paid button
      if (inv.status !== 'paid' && inv.status !== 'void') {
        var paidBtn = document.createElement('button');
        paidBtn.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
        paidBtn.textContent = t('roMarkPaid', 'Mark Paid');
        paidBtn.addEventListener('click', (function(invId) { return async function(e) {
          e.stopPropagation();
          try {
            await api('invoices.php', { method: 'PUT', body: { id: invId, status: 'paid' } });
            showToast(t('roInvoicePaid', 'Invoice marked as paid'));
            viewRoDetail(ro.id);
          } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
        }; })(inv.id));
        iActions.appendChild(paidBtn);
      }

      iCard.appendChild(iActions);
      invSection.appendChild(iCard);
    });
    // Highlight invoices during completed/invoiced
    if (status === 'completed' || status === 'invoiced') {
      invSection.className = 'ring-2 ring-teal-300 dark:ring-teal-700 rounded-xl p-1';
    }
    _sections.invoices = invSection;
  }

  // ─── Labor Tracking Section ─────────────────────────────────────────────────
  var laborSection = document.createElement('div');
  laborSection.className = 'bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4';
  var laborH = document.createElement('h3');
  laborH.className = 'font-bold text-gray-900 dark:text-white text-sm mb-3 flex items-center gap-2';
  laborH.textContent = '\u23F1\uFE0F ' + t('laborTime', 'Labor Tracking');
  if (ro.active_labor_count > 0) {
    var laborBadge = document.createElement('span');
    laborBadge.className = 'inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 font-medium';
    var pulseDot = document.createElement('span');
    pulseDot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block';
    laborBadge.appendChild(pulseDot);
    laborBadge.appendChild(document.createTextNode(' ' + ro.active_labor_count + ' ' + t('laborActiveNow', 'active')));
    laborH.appendChild(laborBadge);
  }
  laborSection.appendChild(laborH);
  var laborContainer = document.createElement('div');
  laborContainer.id = 'ro-labor-section';
  laborSection.appendChild(laborContainer);
  // Highlight labor during active work phases
  if (['in_progress', 'on_hold', 'waiting_parts'].indexOf(status) !== -1) {
    laborSection.className = 'bg-green-50 dark:bg-green-900/20 rounded-xl p-4 ring-2 ring-green-300 dark:ring-green-700';
  }
  _sections.labor = laborSection;

  // Initialize LaborTracker for this RO (pass assigned employee for pre-selection)
  if (typeof LaborTracker !== 'undefined') {
    LaborTracker.init(ro.id, ro.assigned_employee_id);
    LaborTracker.render('ro-labor-section');
  }

  // Linked appointment
  if (ro.appointment) {
    var apptDiv = document.createElement('div');
    apptDiv.className = 'bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4';
    var apptH = document.createElement('h3');
    apptH.className = 'font-bold text-gray-900 dark:text-white text-sm mb-2';
    apptH.textContent = t('roLinkedAppointment', 'Linked Appointment');
    apptDiv.appendChild(apptH);
    var apptInfo = document.createElement('p');
    apptInfo.className = 'text-sm text-gray-600 dark:text-gray-300';
    apptInfo.textContent = ro.appointment.reference_number + ' — ' + ro.appointment.service + ' — ' + ro.appointment.preferred_date;
    apptDiv.appendChild(apptInfo);
    _sections.appointment = apptDiv;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRIORITY-BASED SECTION ORDERING — show what matters NOW at the top
  // ═══════════════════════════════════════════════════════════════════════════
  var sectionOrder;
  switch (status) {
    case 'intake':
      // Verify vehicle info, see concern, check appointment
      sectionOrder = ['concern', 'details', 'appointment', 'notes', 'inspections', 'estimates', 'labor', 'invoices'];
      break;
    case 'check_in':
      // Concern is key context, inspections are the next action
      sectionOrder = ['concern', 'inspections', 'details', 'notes', 'labor', 'estimates', 'appointment', 'invoices'];
      break;
    case 'diagnosis':
      // Inspections to review, then build estimate
      sectionOrder = ['concern', 'inspections', 'estimates', 'notes', 'labor', 'details', 'appointment', 'invoices'];
      break;
    case 'estimate_pending':
    case 'pending_approval':
      // Estimate is the focus — review and send/await
      sectionOrder = ['estimates', 'concern', 'inspections', 'notes', 'labor', 'details', 'appointment', 'invoices'];
      break;
    case 'approved':
      // About to start work — labor section is next, show estimate for reference
      sectionOrder = ['estimates', 'labor', 'concern', 'notes', 'inspections', 'details', 'appointment', 'invoices'];
      break;
    case 'in_progress':
    case 'on_hold':
    case 'waiting_parts':
      // Active work — labor is primary, notes for tech communication
      sectionOrder = ['labor', 'notes', 'concern', 'estimates', 'inspections', 'details', 'appointment', 'invoices'];
      break;
    case 'ready':
      // Waiting for pickup — show labor summary, estimate for final review
      sectionOrder = ['labor', 'estimates', 'notes', 'concern', 'inspections', 'invoices', 'details', 'appointment'];
      break;
    case 'completed':
      // Manager review — invoices will be created, show estimates + labor for verification
      sectionOrder = ['estimates', 'labor', 'invoices', 'notes', 'concern', 'inspections', 'details', 'appointment'];
      break;
    case 'invoiced':
      // Done — invoices are primary, everything else is reference
      sectionOrder = ['invoices', 'labor', 'estimates', 'notes', 'inspections', 'concern', 'details', 'appointment'];
      break;
    default:
      sectionOrder = ['concern', 'details', 'inspections', 'estimates', 'notes', 'labor', 'invoices', 'appointment'];
  }

  // Insert apptOrigin after concern in the display order (if it exists)
  if (_sections.apptOrigin) {
    var concernIdx = sectionOrder.indexOf('concern');
    if (concernIdx !== -1) sectionOrder.splice(concernIdx + 1, 0, 'apptOrigin');
    else sectionOrder.unshift('apptOrigin');
  }
  sectionOrder.forEach(function(key) {
    if (_sections[key]) body.appendChild(_sections[key]);
  });

  card.appendChild(body);
  modal.appendChild(card);
  document.body.appendChild(modal);
}

// ─── Inspection Photo Lightbox ────────────────────────────────────────────────
function _showInspectionPhotoLightbox(photos, startIdx) {
  var currentIdx = startIdx;

  // Backdrop
  var backdrop = document.createElement('div');
  backdrop.className = 'fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center';
  backdrop.style.cssText = 'backdrop-filter:blur(4px);';

  // Close on backdrop click
  backdrop.addEventListener('click', function(e) {
    if (e.target === backdrop) backdrop.remove();
  });

  // Container
  var lbContainer = document.createElement('div');
  lbContainer.className = 'relative max-w-4xl w-full mx-4';

  // Close button
  var closeBtn = document.createElement('button');
  closeBtn.className = 'absolute -top-10 right-0 text-white/80 hover:text-white text-3xl font-bold leading-none z-10';
  closeBtn.textContent = '\u00D7';
  closeBtn.addEventListener('click', function() { backdrop.remove(); });
  lbContainer.appendChild(closeBtn);

  // Image wrapper
  var imgWrap = document.createElement('div');
  imgWrap.className = 'relative flex items-center justify-center';

  var mainImg = document.createElement('img');
  mainImg.className = 'max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl';
  imgWrap.appendChild(mainImg);

  lbContainer.appendChild(imgWrap);

  // Caption area
  var captionArea = document.createElement('div');
  captionArea.className = 'text-center mt-3';
  lbContainer.appendChild(captionArea);

  // Navigation buttons
  if (photos.length > 1) {
    var prevBtn = document.createElement('button');
    prevBtn.className = 'absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center text-xl transition';
    prevBtn.textContent = '\u2039';
    prevBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      currentIdx = (currentIdx - 1 + photos.length) % photos.length;
      updateLbImage();
    });
    imgWrap.appendChild(prevBtn);

    var nextBtn = document.createElement('button');
    nextBtn.className = 'absolute right-0 top-1/2 -translate-y-1/2 -mr-2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center text-xl transition';
    nextBtn.textContent = '\u203A';
    nextBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      currentIdx = (currentIdx + 1) % photos.length;
      updateLbImage();
    });
    imgWrap.appendChild(nextBtn);
  }

  function updateLbImage() {
    var photo = photos[currentIdx];
    mainImg.src = photo.url;
    mainImg.alt = photo.itemLabel + (photo.caption ? ' - ' + photo.caption : '');

    // Rating dot color
    var ratingColors = { red: '#ef4444', yellow: '#eab308', green: '#22c55e' };

    captionArea.textContent = '';

    var labelRow = document.createElement('div');
    labelRow.className = 'flex items-center justify-center gap-2 mb-1';

    var ratingDot = document.createElement('span');
    ratingDot.className = 'w-3 h-3 rounded-full inline-block';
    ratingDot.style.backgroundColor = ratingColors[photo.itemRating] || '#9ca3af';
    labelRow.appendChild(ratingDot);

    var labelText = document.createElement('span');
    labelText.className = 'text-white font-medium text-sm';
    labelText.textContent = photo.itemLabel;
    if (photo.itemCategory) {
      labelText.textContent += ' (' + photo.itemCategory + ')';
    }
    labelRow.appendChild(labelText);

    captionArea.appendChild(labelRow);

    if (photo.caption) {
      var captionText = document.createElement('p');
      captionText.className = 'text-white/70 text-xs';
      captionText.textContent = photo.caption;
      captionArea.appendChild(captionText);
    }

    if (photos.length > 1) {
      var counter = document.createElement('p');
      counter.className = 'text-white/50 text-xs mt-1';
      counter.textContent = (currentIdx + 1) + ' / ' + photos.length;
      captionArea.appendChild(counter);
    }
  }

  // Keyboard navigation
  function handleKey(e) {
    if (e.key === 'Escape') { backdrop.remove(); document.removeEventListener('keydown', handleKey); }
    else if (e.key === 'ArrowLeft' && photos.length > 1) { currentIdx = (currentIdx - 1 + photos.length) % photos.length; updateLbImage(); }
    else if (e.key === 'ArrowRight' && photos.length > 1) { currentIdx = (currentIdx + 1) % photos.length; updateLbImage(); }
  }
  document.addEventListener('keydown', handleKey);

  // Clean up key listener when backdrop is removed
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(m) {
      m.removedNodes.forEach(function(n) {
        if (n === backdrop) { document.removeEventListener('keydown', handleKey); observer.disconnect(); }
      });
    });
  });
  observer.observe(document.body, { childList: true });

  updateLbImage();
  backdrop.appendChild(lbContainer);
  document.body.appendChild(backdrop);
}

// ─── Inline Estimate Item Editor ─────────────────────────────────────────────
async function loadEstimateItems(estId, container, roId) {
  try {
    var json = await api('estimates.php?id=' + estId);
    var est = json.data;
    renderEstimateItemEditor(est, container, roId);
  } catch(err) {
    showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true);
  }
}

function renderEstimateItemEditor(est, container, roId) {
  var editor = document.createElement('div');
  editor.className = 'est-items-editor mt-3 border-t border-gray-200 dark:border-gray-700 pt-3';

  var items = est.items || [];
  var itemTypes = ['labor', 'parts', 'tire', 'fee', 'discount', 'sublet'];

  // Items table
  var table = document.createElement('div');
  table.className = 'space-y-2 mb-3';

  function buildItemRow(item, idx) {
    var row = document.createElement('div');
    row.className = 'flex gap-2 items-start bg-gray-50 dark:bg-gray-900/30 rounded-lg p-2';
    row.dataset.itemId = item.id || '';

    // Type select
    var typeSelect = document.createElement('select');
    typeSelect.className = 'border rounded px-2 py-1.5 text-xs w-20 shrink-0 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    typeSelect.name = 'item_type';
    itemTypes.forEach(function(tp) {
      var opt = document.createElement('option');
      opt.value = tp; opt.textContent = tp.charAt(0).toUpperCase() + tp.slice(1);
      if (tp === item.item_type) opt.selected = true;
      typeSelect.appendChild(opt);
    });
    row.appendChild(typeSelect);

    // Description
    var descInput = document.createElement('input');
    descInput.type = 'text';
    descInput.className = 'border rounded px-2 py-1.5 text-sm flex-1 min-w-0 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    descInput.placeholder = 'Description';
    descInput.value = item.description || '';
    descInput.name = 'description';
    row.appendChild(descInput);

    // Qty
    var qtyInput = document.createElement('input');
    qtyInput.type = 'number';
    qtyInput.className = 'border rounded px-2 py-1.5 text-sm w-16 text-center dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    qtyInput.placeholder = 'Qty';
    qtyInput.value = item.quantity || 1;
    qtyInput.min = '0.01'; qtyInput.step = '0.01';
    qtyInput.name = 'quantity';
    qtyInput.addEventListener('input', function() { updateLineTotal(row); });
    row.appendChild(qtyInput);

    // Price
    var priceInput = document.createElement('input');
    priceInput.type = 'number';
    priceInput.className = 'border rounded px-2 py-1.5 text-sm w-24 text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    priceInput.placeholder = 'Price';
    priceInput.value = parseFloat(item.unit_price || 0).toFixed(2);
    priceInput.min = '0'; priceInput.step = '0.01';
    priceInput.name = 'unit_price';
    priceInput.addEventListener('input', function() { updateLineTotal(row); });
    row.appendChild(priceInput);

    // Line total (read-only)
    var totalSpan = document.createElement('span');
    totalSpan.className = 'text-sm font-semibold text-gray-700 dark:text-gray-300 w-20 text-right shrink-0 pt-1.5 line-total';
    var lineTotal = (parseFloat(item.quantity || 1) * parseFloat(item.unit_price || 0));
    totalSpan.textContent = '$' + lineTotal.toFixed(2);
    row.appendChild(totalSpan);

    // Delete button
    var delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'text-red-400 hover:text-red-600 text-lg font-bold shrink-0 pt-0.5';
    delBtn.textContent = '\u00d7';
    delBtn.title = 'Remove item';
    delBtn.addEventListener('click', function() {
      row.remove();
      updateEstimateTotal(editor);
    });
    row.appendChild(delBtn);

    return row;
  }

  function updateLineTotal(row) {
    var qty = parseFloat(row.querySelector('[name="quantity"]').value) || 0;
    var price = parseFloat(row.querySelector('[name="unit_price"]').value) || 0;
    var total = qty * price;
    row.querySelector('.line-total').textContent = '$' + total.toFixed(2);
    updateEstimateTotal(editor);
  }

  function updateEstimateTotal(editorEl) {
    var rows = editorEl.querySelectorAll('[data-item-id]');
    var subtotal = 0;
    rows.forEach(function(r) {
      var qty = parseFloat(r.querySelector('[name="quantity"]').value) || 0;
      var price = parseFloat(r.querySelector('[name="unit_price"]').value) || 0;
      var type = r.querySelector('[name="item_type"]').value;
      var line = qty * price;
      if (type === 'discount') subtotal -= Math.abs(line);
      else subtotal += line;
    });
    var taxRate = parseFloat(editorEl.querySelector('[name="tax_rate"]').value) || 0;
    var tax = subtotal * (taxRate / 100);
    var total = subtotal + tax;
    var totalEl = editorEl.querySelector('.est-grand-total');
    if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
  }

  items.forEach(function(item, idx) {
    table.appendChild(buildItemRow(item, idx));
  });
  editor.appendChild(table);

  // Add item button + Use Template dropdown
  var addRow = document.createElement('div');
  addRow.className = 'flex gap-2 mb-3 flex-wrap items-center';
  var addBtn = document.createElement('button');
  addBtn.type = 'button';
  addBtn.className = 'text-sm text-green-600 hover:text-green-800 font-medium flex items-center gap-1';
  addBtn.textContent = '+ ' + t('roAddItem', 'Add Item');
  addBtn.addEventListener('click', function() {
    var newItem = { id: 'new_' + Date.now(), item_type: 'labor', description: '', quantity: 1, unit_price: 0 };
    table.appendChild(buildItemRow(newItem, table.children.length));
    updateEstimateTotal(editor);
  });
  addRow.appendChild(addBtn);

  // "Use Template" dropdown
  var tplSelect = document.createElement('select');
  tplSelect.className = 'border rounded px-2 py-1.5 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
  var defaultOpt = document.createElement('option');
  defaultOpt.value = '';
  defaultOpt.textContent = t('roUseTemplate', 'Use Template...');
  tplSelect.appendChild(defaultOpt);

  // Load templates asynchronously
  (function loadTemplates() {
    api('estimate-templates.php').then(function(json) {
      var templates = json.data || [];
      templates.forEach(function(tpl) {
        var opt = document.createElement('option');
        opt.value = JSON.stringify(tpl.items);
        opt.textContent = tpl.name_en;
        tplSelect.appendChild(opt);
      });
    }).catch(function() { /* templates table may not exist yet — silently ignore */ });
  })();

  tplSelect.addEventListener('change', function() {
    if (!tplSelect.value) return;
    try {
      var tplItems = JSON.parse(tplSelect.value);
      tplItems.forEach(function(ti) {
        var newItem = {
          id: 'new_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6),
          item_type: ti.type || 'labor',
          description: ti.description_en || '',
          quantity: ti.quantity || 1,
          unit_price: ti.unit_price || 0
        };
        table.appendChild(buildItemRow(newItem, table.children.length));
      });
      updateEstimateTotal(editor);
      showToast(t('roTemplateApplied', 'Template items added'));
    } catch(e) { /* ignore parse errors */ }
    tplSelect.selectedIndex = 0;
  });
  addRow.appendChild(tplSelect);

  editor.appendChild(addRow);

  // Tax rate + totals
  var totalsRow = document.createElement('div');
  totalsRow.className = 'flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-3';

  var taxWrap = document.createElement('div');
  taxWrap.className = 'flex items-center gap-2';
  var taxLabel = document.createElement('label');
  taxLabel.className = 'text-sm text-gray-600 dark:text-gray-400';
  taxLabel.textContent = 'Tax %:';
  var taxInput = document.createElement('input');
  taxInput.type = 'number';
  taxInput.name = 'tax_rate';
  taxInput.className = 'border rounded px-2 py-1 text-sm w-20 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
  taxInput.value = ((parseFloat(est.tax_rate || 0)) * 100).toFixed(2);
  taxInput.step = '0.01'; taxInput.min = '0';
  taxInput.addEventListener('input', function() { updateEstimateTotal(editor); });
  taxWrap.appendChild(taxLabel);
  taxWrap.appendChild(taxInput);
  totalsRow.appendChild(taxWrap);

  var totalWrap = document.createElement('div');
  totalWrap.className = 'text-right';
  var totalLabel = document.createElement('span');
  totalLabel.className = 'text-sm text-gray-500 mr-2';
  totalLabel.textContent = 'Total:';
  var totalVal = document.createElement('span');
  totalVal.className = 'est-grand-total text-lg font-bold text-gray-900 dark:text-white';
  totalVal.textContent = '$' + parseFloat(est.total || 0).toFixed(2);
  totalWrap.appendChild(totalLabel);
  totalWrap.appendChild(totalVal);
  totalsRow.appendChild(totalWrap);
  editor.appendChild(totalsRow);

  // Save button
  var saveRow = document.createElement('div');
  saveRow.className = 'flex justify-end gap-2 mt-3';
  var saveBtn = document.createElement('button');
  saveBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition';
  saveBtn.textContent = t('roSaveEstimate', 'Save Estimate');
  saveBtn.addEventListener('click', async function() {
    var rows = editor.querySelectorAll('[data-item-id]');
    var updatedItems = [];
    rows.forEach(function(r) {
      updatedItems.push({
        id: r.dataset.itemId.indexOf('new_') === 0 ? null : parseInt(r.dataset.itemId),
        item_type: r.querySelector('[name="item_type"]').value,
        description: r.querySelector('[name="description"]').value,
        quantity: parseFloat(r.querySelector('[name="quantity"]').value) || 1,
        unit_price: parseFloat(r.querySelector('[name="unit_price"]').value) || 0,
      });
    });
    var taxPct = parseFloat(editor.querySelector('[name="tax_rate"]').value) || 0;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    try {
      await api('estimates.php', {
        method: 'PUT',
        body: {
          id: est.id,
          tax_rate: taxPct / 100,
          replace_items: updatedItems
        }
      });
      showToast(t('roEstimateSaved', 'Estimate saved'));
      viewRoDetail(roId);
    } catch(err) {
      showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true);
      saveBtn.disabled = false;
      saveBtn.textContent = t('roSaveEstimate', 'Save Estimate');
    }
  });
  saveRow.appendChild(saveBtn);
  editor.appendChild(saveRow);

  container.appendChild(editor);
  updateEstimateTotal(editor);
}

// ─── Create RO Modal ─────────────────────────────────────────────────────────
window.roShowCreateModal = function(appointmentId) {
  var existing = document.getElementById('ro-create-modal');
  if (existing) existing.remove();

  var selectedApptId = null;

  var modal = document.createElement('div');
  modal.id = 'ro-create-modal';
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay';

  var card = document.createElement('div');
  card.className = 'bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6';

  var title = document.createElement('h2');
  title.className = 'text-xl font-bold text-gray-900 dark:text-white mb-4';
  title.textContent = t('roCreateRepairOrder', 'Create Repair Order');
  card.appendChild(title);

  var optWrap = document.createElement('div');
  optWrap.className = 'space-y-4';

  // From Appointment
  var fromApptDiv = document.createElement('div');
  fromApptDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4';
  var fromApptH = document.createElement('h3');
  fromApptH.className = 'font-bold text-gray-900 dark:text-white mb-2';
  fromApptH.textContent = t('roFromAppointment', 'From Appointment');
  fromApptDiv.appendChild(fromApptH);

  // Search input
  var searchInput = document.createElement('input');
  searchInput.type = 'text';
  searchInput.placeholder = t('roSearchApptPlaceholder', 'Search by name, phone, service, reference...');
  searchInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  fromApptDiv.appendChild(searchInput);

  // Scrollable appointment list
  var listWrap = document.createElement('div');
  listWrap.style.cssText = 'max-height:250px;overflow-y:auto';
  listWrap.className = 'border border-gray-200 dark:border-gray-700 rounded-lg mb-3';
  var listLoading = document.createElement('div');
  listLoading.className = 'p-4 text-center text-sm text-gray-500 dark:text-gray-400';
  listLoading.textContent = t('loading', 'Loading appointments...');
  listWrap.appendChild(listLoading);
  fromApptDiv.appendChild(listWrap);

  // Selected appointment confirmation area
  var confirmArea = document.createElement('div');
  confirmArea.className = 'hidden bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 rounded-lg p-3 mb-3 text-sm text-green-800 dark:text-green-300';
  fromApptDiv.appendChild(confirmArea);

  var apptBtn = document.createElement('button');
  apptBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 w-full opacity-50 cursor-not-allowed';
  apptBtn.textContent = t('roCreateFromAppt', 'Create from Appointment');
  apptBtn.disabled = true;
  apptBtn.addEventListener('click', async function() {
    if (!selectedApptId) { showToast(t('roSelectApptFirst', 'Select an appointment first'), true); return; }
    try {
      var json = await api('repair-orders.php', { method: 'POST', body: { appointment_id: selectedApptId } });
      if (json.data && json.data.existing) {
        showToast(t('roAlreadyExists', 'Repair order already exists') + ': ' + (json.data.ro_number || ''));
      } else {
        showToast(t('roCreatedMsg', 'Repair order created!'));
      }
      modal.remove();
      loadRepairOrders();
    } catch(err) { showToast(err.message, true); }
  });
  fromApptDiv.appendChild(apptBtn);

  // Collect appointment IDs that already have ROs
  var usedApptIds = {};
  roList.forEach(function(ro) { if (ro.appointment_id) usedApptIds[ro.appointment_id] = ro.ro_number; });

  // Fetch and render appointments
  var allAppts = [];

  function clearChildren(el) { while (el.firstChild) el.removeChild(el.firstChild); }

  function renderApptList(filter) {
    var lc = (filter || '').toLowerCase();
    var filtered = allAppts.filter(function(a) {
      if (usedApptIds[a.id]) return false;
      if (a.status === 'cancelled') return false;
      if (!lc) return true;
      var hay = [a.reference_number, a.first_name, a.last_name, a.phone, a.email, a.service,
                 a.vehicle_year, a.vehicle_make, a.vehicle_model].filter(Boolean).join(' ').toLowerCase();
      return hay.indexOf(lc) !== -1;
    });
    clearChildren(listWrap);
    if (filtered.length === 0) {
      var empty = document.createElement('div');
      empty.className = 'p-4 text-center text-sm text-gray-500 dark:text-gray-400';
      empty.textContent = filter ? t('roNoMatchingAppts', 'No matching appointments') : t('roNoAvailableAppts', 'No available appointments');
      listWrap.appendChild(empty);
      return;
    }
    filtered.forEach(function(a) {
      var row = document.createElement('div');
      row.className = 'flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition';
      if (selectedApptId === a.id) {
        row.className += ' bg-green-50 dark:bg-green-900/20 ring-1 ring-green-400';
      }
      var info = document.createElement('div');
      info.className = 'flex-1 min-w-0';
      var line1 = document.createElement('div');
      line1.className = 'flex items-center gap-2 text-sm';
      var ref = document.createElement('span');
      ref.className = 'font-mono text-xs text-green-700 dark:text-green-400 font-bold';
      ref.textContent = a.reference_number || '#' + a.id;
      line1.appendChild(ref);
      var name = document.createElement('span');
      name.className = 'font-medium text-gray-900 dark:text-white truncate';
      name.textContent = (a.first_name || '') + ' ' + (a.last_name || '');
      line1.appendChild(name);
      info.appendChild(line1);
      var line2 = document.createElement('div');
      line2.className = 'text-xs text-gray-500 dark:text-gray-400 mt-0.5';
      var parts = [a.service];
      if (a.preferred_date) parts.push(fmtDate(a.preferred_date));
      if (a.preferred_time) parts.push(fmtTime(a.preferred_time));
      var veh = [a.vehicle_year, a.vehicle_make, a.vehicle_model].filter(Boolean).join(' ');
      if (veh) parts.push(veh);
      line2.textContent = parts.join(' · ');
      info.appendChild(line2);
      row.appendChild(info);
      var selectBtn = document.createElement('button');
      selectBtn.className = selectedApptId === a.id
        ? 'ml-2 px-2 py-1 rounded text-xs font-medium bg-green-600 text-white shrink-0'
        : 'ml-2 px-2 py-1 rounded text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shrink-0';
      selectBtn.textContent = selectedApptId === a.id ? 'Selected' : 'Select';
      row.appendChild(selectBtn);
      row.addEventListener('click', function() {
        selectedApptId = a.id;
        apptBtn.disabled = false;
        apptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        confirmArea.classList.remove('hidden');
        var cName = ((a.first_name || '') + ' ' + (a.last_name || '')).trim();
        var cVeh = [a.vehicle_year, a.vehicle_make, a.vehicle_model].filter(Boolean).join(' ') || 'No vehicle';
        var cDate = a.preferred_date ? fmtDate(a.preferred_date) : '';
        confirmArea.textContent = t('roCreatingFor', 'Creating RO for') + ' ' + cName + ' \u2014 ' + cVeh + ' \u2014 ' + cDate;
        renderApptList(searchInput.value);
      });
      listWrap.appendChild(row);
    });
  }

  searchInput.addEventListener('input', function() { renderApptList(this.value); });

  // Fetch appointments
  api('appointments.php?limit=50&sort_by=preferred_date&sort_order=DESC').then(function(json) {
    allAppts = json.data || [];
    // If pre-filled appointmentId, auto-select it
    if (appointmentId) {
      var preId = parseInt(appointmentId);
      if (preId) {
        selectedApptId = preId;
        apptBtn.disabled = false;
        apptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        var match = allAppts.find(function(a) { return a.id == preId; });
        if (match) {
          var cName = ((match.first_name || '') + ' ' + (match.last_name || '')).trim();
          var cVeh = [match.vehicle_year, match.vehicle_make, match.vehicle_model].filter(Boolean).join(' ') || 'No vehicle';
          var cDate = match.preferred_date ? fmtDate(match.preferred_date) : '';
          confirmArea.classList.remove('hidden');
          confirmArea.textContent = t('roCreatingFor', 'Creating RO for') + ' ' + cName + ' \u2014 ' + cVeh + ' \u2014 ' + cDate;
        }
      }
    }
    renderApptList('');
  }).catch(function() {
    clearChildren(listWrap);
    var errDiv = document.createElement('div');
    errDiv.className = 'p-4 text-center text-sm text-red-500';
    errDiv.textContent = t('roFailedLoadAppts', 'Failed to load appointments');
    listWrap.appendChild(errDiv);
  });

  optWrap.appendChild(fromApptDiv);

  // Walk-in
  var walkDiv = document.createElement('div');
  walkDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4';
  var walkH = document.createElement('h3');
  walkH.className = 'font-bold text-gray-900 dark:text-white mb-2';
  walkH.textContent = t('roWalkIn', 'Walk-in (No Appointment)');
  walkDiv.appendChild(walkH);

  var custInput = document.createElement('input');
  custInput.type = 'number'; custInput.id = 'ro-cust-id'; custInput.placeholder = t('roCustomerId', 'Customer ID');
  custInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(custInput);

  var vehInput = document.createElement('input');
  vehInput.type = 'number'; vehInput.id = 'ro-veh-id'; vehInput.placeholder = t('roVehicleId', 'Vehicle ID (optional)');
  vehInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(vehInput);

  var concernInput = document.createElement('textarea');
  concernInput.id = 'ro-concern'; concernInput.placeholder = t('roConcernPlaceholder', 'Customer concern (optional)'); concernInput.rows = 2;
  concernInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(concernInput);

  var walkBtn = document.createElement('button');
  walkBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 w-full';
  walkBtn.textContent = t('roCreateWalkIn', 'Create Walk-in RO');
  walkBtn.addEventListener('click', async function() {
    var custId = parseInt(document.getElementById('ro-cust-id').value);
    if (!custId) { showToast(t('roCustomerIdRequired', 'Customer ID is required'), true); return; }
    var payload = { customer_id: custId };
    var vehId = parseInt(document.getElementById('ro-veh-id').value);
    if (vehId) payload.vehicle_id = vehId;
    var concern = document.getElementById('ro-concern').value.trim();
    if (concern) payload.customer_concern = concern;
    try {
      var json = await api('repair-orders.php', { method: 'POST', body: payload });
      showToast(t('roCreatedMsg', 'Repair order created!'));
      modal.remove();
      loadRepairOrders();
    } catch(err) { showToast(err.message, true); }
  });
  walkDiv.appendChild(walkBtn);
  optWrap.appendChild(walkDiv);
  card.appendChild(optWrap);

  var cancelBtn = document.createElement('button');
  cancelBtn.className = 'mt-4 w-full text-center text-gray-500 hover:text-gray-700 text-sm';
  cancelBtn.textContent = t('cancel', 'Cancel');
  cancelBtn.addEventListener('click', function() { modal.remove(); });
  card.appendChild(cancelBtn);

  modal.appendChild(card);
  modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
  document.body.appendChild(modal);
};

})();
