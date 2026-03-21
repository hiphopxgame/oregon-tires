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
  diagnosis:        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
  estimate_pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
  pending_approval: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
  approved:         'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
  in_progress:      'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
  on_hold:          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
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
var timelineStatuses = ['intake', 'diagnosis', 'estimate_pending', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'waiting_parts', 'ready', 'completed', 'invoiced'];
function getTimelineLabels() {
  return {
    intake: t('roStatusIntake', 'Intake'),
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

    // RO Number + age
    var tdNum = document.createElement('td');
    tdNum.className = 'p-3 text-sm';
    var roLink = document.createElement('span');
    roLink.className = 'font-bold text-green-700 dark:text-green-400';
    roLink.textContent = ro.ro_number;
    tdNum.appendChild(roLink);
    var age = timeAgo(ro.created_at);
    if (age) {
      var ageBadge = document.createElement('span');
      ageBadge.className = 'ml-1.5 text-xs text-gray-400';
      ageBadge.textContent = age;
      tdNum.appendChild(ageBadge);
    }
    tr.appendChild(tdNum);

    // Customer + contact
    var tdCust = document.createElement('td');
    tdCust.className = 'p-3 text-sm';
    var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim() || '-';
    var nameEl = document.createElement('div');
    nameEl.className = 'font-medium';
    nameEl.textContent = custName;
    tdCust.appendChild(nameEl);
    if (ro.customer_phone) {
      var phoneEl = document.createElement('div');
      phoneEl.className = 'text-xs text-gray-400';
      phoneEl.textContent = ro.customer_phone;
      tdCust.appendChild(phoneEl);
    }
    tr.appendChild(tdCust);

    // Vehicle + VIN
    var tdVeh = document.createElement('td');
    tdVeh.className = 'p-3 text-sm';
    var vehStr = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ');
    var vehEl = document.createElement('div');
    vehEl.className = 'font-medium';
    vehEl.textContent = vehStr || '-';
    tdVeh.appendChild(vehEl);
    if (ro.vin) {
      var vinEl = document.createElement('div');
      vinEl.className = 'text-xs text-gray-400 font-mono';
      vinEl.textContent = ro.vin;
      tdVeh.appendChild(vinEl);
    }
    if (ro.license_plate) {
      var plateEl = document.createElement('div');
      plateEl.className = 'text-xs text-gray-400';
      plateEl.textContent = 'Plate: ' + ro.license_plate;
      tdVeh.appendChild(plateEl);
    }
    tr.appendChild(tdVeh);

    // Status — inline dropdown
    var tdStatus = document.createElement('td');
    tdStatus.className = 'p-3 text-sm';
    var statusSelect = document.createElement('select');
    statusSelect.className = 'text-xs border rounded-lg px-2 py-1.5 font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 cursor-pointer';
    var allStatuses = ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'];
    allStatuses.forEach(function(s) {
      var opt = document.createElement('option');
      opt.value = s;
      var sKey = 'roStatus' + s.replace(/_([a-z])/g, function(m,c){ return c.toUpperCase(); }).replace(/^[a-z]/, function(c){ return c.toUpperCase(); });
      opt.textContent = t(sKey, s.replace(/_/g, ' '));
      if (s === ro.status) opt.selected = true;
      statusSelect.appendChild(opt);
    });
    // Color the select based on current status
    var colorMap = { intake:'#dbeafe', diagnosis:'#ede9fe', estimate_pending:'#fef3c7', pending_approval:'#ffedd5', approved:'#dcfce7', in_progress:'#e0e7ff', on_hold:'#fee2e2', waiting_parts:'#fef3c7', ready:'#d1fae5', completed:'#f3f4f6', invoiced:'#ccfbf1', cancelled:'#fee2e2' };
    statusSelect.style.backgroundColor = colorMap[ro.status] || '';
    statusSelect.addEventListener('click', function(e) { e.stopPropagation(); });
    statusSelect.addEventListener('change', (function(roId, sel) { return async function(e) {
      e.stopPropagation();
      var newStatus = sel.value;
      try {
        await api('repair-orders.php', { method: 'PUT', body: { id: roId, status: newStatus } });
        showToast(t('roStatusUpdatedTo', 'Status updated to') + ' ' + newStatus.replace(/_/g, ' '));
        loadRepairOrders();
      } catch(err) {
        showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true);
        loadRepairOrders();
      }
    }; })(ro.id, statusSelect));
    tdStatus.appendChild(statusSelect);
    // Time in current status
    var updatedAge = timeAgo(ro.updated_at);
    if (updatedAge) {
      var timeLabel = document.createElement('div');
      timeLabel.className = 'text-xs text-gray-400 mt-0.5';
      timeLabel.textContent = updatedAge + ' in status';
      tdStatus.appendChild(timeLabel);
    }
    tr.appendChild(tdStatus);

    // Created
    var tdDate = document.createElement('td');
    tdDate.className = 'p-3 text-sm text-gray-500';
    tdDate.textContent = formatDate(ro.created_at);
    tr.appendChild(tdDate);

    // Inspections + Estimates counts
    var tdCounts = document.createElement('td');
    tdCounts.className = 'p-3 text-sm';
    var counts = [];
    if (ro.inspection_count > 0) counts.push(ro.inspection_count + ' DVI');
    if (ro.estimate_count > 0) counts.push(ro.estimate_count + ' Est');
    tdCounts.textContent = counts.join(', ') || '-';
    tr.appendChild(tdCounts);

    // Actions
    var tdAct = document.createElement('td');
    tdAct.className = 'p-3 text-sm';
    var viewBtn = document.createElement('button');
    viewBtn.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
    viewBtn.textContent = t('actionView', 'View');
    viewBtn.addEventListener('click', function(e) { e.stopPropagation(); viewRoDetail(ro.id); });
    tdAct.appendChild(viewBtn);
    tr.appendChild(tdAct);

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
  if (existing) existing.remove();

  var vehicle = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ') || 'No vehicle';
  var customer = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();

  var modal = document.createElement('div');
  modal.id = 'ro-detail-modal';
  modal.className = 'fixed inset-0 z-50 flex items-start justify-center p-4 pt-12 modal-overlay overflow-y-auto';

  var card = document.createElement('div');
  card.className = 'bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-y-auto';

  // Header
  var header = document.createElement('div');
  header.className = 'bg-gradient-to-r from-green-700 to-green-900 text-white p-6 rounded-t-2xl';

  var headerTop = document.createElement('div');
  headerTop.className = 'flex justify-between items-start';

  var headerLeft = document.createElement('div');
  var h2 = document.createElement('h2');
  h2.className = 'text-2xl font-bold';
  h2.textContent = ro.ro_number;
  headerLeft.appendChild(h2);

  var custP = document.createElement('p');
  custP.className = 'text-green-200 mt-1';
  custP.textContent = customer + ' — ' + vehicle;
  headerLeft.appendChild(custP);
  headerTop.appendChild(headerLeft);

  var closeBtn = document.createElement('button');
  closeBtn.className = 'text-white/80 hover:text-white text-2xl font-bold';
  closeBtn.textContent = '\u00D7';
  closeBtn.addEventListener('click', function() { modal.remove(); });
  headerTop.appendChild(closeBtn);
  header.appendChild(headerTop);

  // Status + actions
  var statusRow = document.createElement('div');
  statusRow.className = 'flex items-center gap-3 mt-4 flex-wrap';
  statusRow.appendChild(createStatusBadge(ro.status));

  var statusSelect = document.createElement('select');
  statusSelect.className = 'bg-white/20 text-white border border-white/30 rounded-lg px-3 py-1.5 text-sm';
  ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'].forEach(function(s) {
    var opt = document.createElement('option');
    opt.value = s;
    var sKey = 'roStatus' + s.replace(/_([a-z])/g, function(m,c){ return c.toUpperCase(); }).replace(/^[a-z]/, function(c){ return c.toUpperCase(); });
    opt.textContent = t(sKey, s.replace(/_/g, ' '));
    opt.className = 'text-gray-900';
    if (s === ro.status) opt.selected = true;
    statusSelect.appendChild(opt);
  });

  var updateBtn = document.createElement('button');
  updateBtn.className = 'bg-white/20 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-white/30 font-medium';
  updateBtn.textContent = t('roUpdateStatus', 'Update Status');
  updateBtn.addEventListener('click', async function() {
    var newStatus = statusSelect.value;
    if (newStatus === ro.status) return;
    try {
      await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: newStatus } });
      showToast(t('roStatusUpdatedTo', 'Status updated to') + ' ' + newStatus.replace(/_/g, ' '));
      modal.remove();
      loadRepairOrders();
    } catch (err) {
      showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true);
    }
  });

  statusRow.appendChild(statusSelect);
  statusRow.appendChild(updateBtn);
  header.appendChild(statusRow);
  card.appendChild(header);

  // Body
  var body = document.createElement('div');
  body.className = 'p-6 space-y-6';

  // Status timeline stepper
  body.appendChild(renderStatusTimeline(ro.status));

  // Info grid
  var grid = document.createElement('div');
  grid.className = 'grid grid-cols-2 md:grid-cols-3 gap-4';

  var vehicleSpecs = [ro.engine, ro.transmission, ro.drive_type].filter(Boolean).join(' | ');
  var infoItems = [
    [t('roThCustomer', 'Customer'), customer],
    [t('emailLabel2', 'Email'), ro.customer_email || '-'],
    [t('phone', 'Phone'), ro.customer_phone || '-'],
    [t('roThVehicle', 'Vehicle'), vehicle + (ro.trim_level ? ' ' + ro.trim_level : '')],
    ['VIN', ro.vin || '-'],
    ['Plate', ro.license_plate || '-'],
    [t('roMileageIn', 'Mileage In'), ro.mileage_in ? Number(ro.mileage_in).toLocaleString() : '-'],
  ];
  if (vehicleSpecs) infoItems.push(['Specs', vehicleSpecs]);
  if (ro.fuel_type) infoItems.push(['Fuel', ro.fuel_type]);
  infoItems.push(
    [t('roPromisedDate', 'Promised Date'), ro.promised_date || '-'],
    [t('roThCreated', 'Created'), formatDate(ro.created_at)],
    [t('roUpdated', 'Updated'), formatDate(ro.updated_at)]
  );
  infoItems.forEach(function(pair) {
    var div = document.createElement('div');
    var lbl = document.createElement('p');
    lbl.className = 'text-xs text-gray-400 uppercase font-medium';
    lbl.textContent = pair[0];
    var val = document.createElement('p');
    val.className = 'font-semibold text-gray-900 dark:text-white text-sm';
    val.textContent = pair[1];
    div.appendChild(lbl);
    div.appendChild(val);
    grid.appendChild(div);
  });
  body.appendChild(grid);

  // Customer concern
  if (ro.customer_concern) {
    var concernDiv = document.createElement('div');
    concernDiv.className = 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4';
    var concernLbl = document.createElement('h3');
    concernLbl.className = 'font-bold text-amber-800 dark:text-amber-300 text-sm mb-1';
    concernLbl.textContent = t('roCustomerConcern', 'Customer Concern');
    var concernTxt = document.createElement('p');
    concernTxt.className = 'text-sm text-gray-700 dark:text-gray-300';
    concernTxt.textContent = ro.customer_concern;
    concernDiv.appendChild(concernLbl);
    concernDiv.appendChild(concernTxt);
    body.appendChild(concernDiv);
  }

  // ─── Notes section ───────────────────────────────────────────────────────
  var notesSection = document.createElement('div');
  notesSection.className = 'border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden';

  var notesHeader = document.createElement('div');
  notesHeader.className = 'bg-gray-50 dark:bg-gray-900/50 px-4 py-3 flex justify-between items-center';
  var notesH = document.createElement('h3');
  notesH.className = 'font-bold text-gray-900 dark:text-white text-sm';
  notesH.textContent = t('roNotes', 'Notes');
  notesHeader.appendChild(notesH);
  notesSection.appendChild(notesHeader);

  var notesBody = document.createElement('div');
  notesBody.className = 'p-4 space-y-3';

  // Existing technician notes log (read-only)
  if (ro.technician_notes && ro.technician_notes.trim()) {
    var techLog = document.createElement('div');
    techLog.className = 'p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700';
    var techLbl = document.createElement('div');
    techLbl.className = 'text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider mb-1';
    techLbl.textContent = t('roTechNotes', 'Technician Notes');
    techLog.appendChild(techLbl);
    var techTxt = document.createElement('div');
    techTxt.className = 'text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap';
    techTxt.textContent = ro.technician_notes;
    techLog.appendChild(techTxt);
    notesBody.appendChild(techLog);
  }

  // Existing admin notes log (read-only)
  if (ro.admin_notes && ro.admin_notes.trim()) {
    var adminLog = document.createElement('div');
    adminLog.className = 'p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600';
    var adminLbl = document.createElement('div');
    adminLbl.className = 'text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1';
    adminLbl.textContent = t('roAdminNotes', 'Admin Notes');
    adminLog.appendChild(adminLbl);
    var adminTxt = document.createElement('div');
    adminTxt.className = 'text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap';
    adminTxt.textContent = ro.admin_notes;
    adminLog.appendChild(adminTxt);
    notesBody.appendChild(adminLog);
  }

  // Add new note form
  var noteForm = document.createElement('div');
  noteForm.className = 'border-t border-gray-200 dark:border-gray-700 pt-3';

  var noteLabel = document.createElement('label');
  noteLabel.className = 'block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1';
  noteLabel.textContent = t('roAddNote', 'Add Note');
  noteForm.appendChild(noteLabel);

  var noteTextarea = document.createElement('textarea');
  noteTextarea.className = 'w-full p-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 resize-none';
  noteTextarea.rows = 3;
  noteTextarea.maxLength = 2000;
  noteTextarea.placeholder = t('roNotePlaceholder', 'Type a note...');
  noteForm.appendChild(noteTextarea);

  var noteActions = document.createElement('div');
  noteActions.className = 'flex gap-2 mt-2';

  var saveTechBtn = document.createElement('button');
  saveTechBtn.className = 'px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition';
  saveTechBtn.textContent = t('roSaveTechNote', 'Save as Tech Note');
  saveTechBtn.addEventListener('click', async function() {
    var txt = noteTextarea.value.trim();
    if (!txt) { showToast(t('roNoteEmpty', 'Please enter a note.'), true); return; }
    try {
      await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, technician_notes: txt, note_append: true } });
      showToast(t('roNoteSaved', 'Note saved'));
      modal.remove();
      viewRoDetail(ro.id);
    } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
  });
  noteActions.appendChild(saveTechBtn);

  var saveAdminBtn = document.createElement('button');
  saveAdminBtn.className = 'px-3 py-1.5 bg-gray-600 text-white rounded-lg text-xs font-medium hover:bg-gray-700 transition';
  saveAdminBtn.textContent = t('roSaveAdminNote', 'Save as Admin Note');
  saveAdminBtn.addEventListener('click', async function() {
    var txt = noteTextarea.value.trim();
    if (!txt) { showToast(t('roNoteEmpty', 'Please enter a note.'), true); return; }
    try {
      await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, admin_notes: txt, note_append: true } });
      showToast(t('roNoteSaved', 'Note saved'));
      modal.remove();
      viewRoDetail(ro.id);
    } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
  });
  noteActions.appendChild(saveAdminBtn);

  noteForm.appendChild(noteActions);
  notesBody.appendChild(noteForm);
  notesSection.appendChild(notesBody);
  body.appendChild(notesSection);

  // ─── Smart Action Bar (context-aware) ──────────────────────────────────────
  var hasInspections = ro.inspections && ro.inspections.length > 0;
  var hasEstimates = ro.estimates && ro.estimates.length > 0;
  var latestEstimate = hasEstimates ? ro.estimates[0] : null;
  var hasInvoices = ro.invoices && ro.invoices.length > 0;
  var status = ro.status || 'intake';

  // ─── Guided Workflow Action Bar ─────────────────────────────────────────────
  var WORKFLOW_STEPS = [
    { status: 'intake',            step: 1, total: 8 },
    { status: 'diagnosis',         step: 2, total: 8 },
    { status: 'estimate_pending',  step: 3, total: 8 },
    { status: 'pending_approval',  step: 4, total: 8 },
    { status: 'approved',          step: 5, total: 8 },
    { status: 'in_progress',       step: 6, total: 8 },
    { status: 'ready',             step: 7, total: 8 },
    { status: 'completed',         step: 8, total: 8 },
  ];
  var currentStep = WORKFLOW_STEPS.find(function(s) { return s.status === status; });

  var guide = null;
  if (status === 'intake' && !hasInspections) guide = { text: t('roSuggestInspect', 'Create an inspection for this vehicle'), btn: t('roStartInspection', 'Start Inspection'), color: 'purple', icon: '\uD83D\uDD0D', action: 'inspect' };
  else if (status === 'intake') guide = { text: t('roSuggestDiag', 'Inspection done \u2014 advance to diagnosis'), btn: t('roBeginDiagnosis', 'Begin Diagnosis'), color: 'purple', icon: '\u2699\uFE0F', action: 'diagnosis' };
  else if (status === 'diagnosis' && !hasEstimates) guide = { text: t('roSuggestEstimate', 'Create an estimate for the customer'), btn: t('roCreateEstimate', 'Create Estimate'), color: 'blue', icon: '\uD83D\uDCCB', action: 'estimate' };
  else if (status === 'diagnosis') guide = { text: t('roSuggestSendEst', 'Estimate ready \u2014 send to customer'), btn: t('roSendEstimate', 'Send Estimate'), color: 'blue', icon: '\uD83D\uDCE7', action: 'send_estimate' };
  else if (status === 'estimate_pending') guide = { text: t('roSuggestSendEst', 'Send the estimate to the customer'), btn: t('roSendToCustomer', 'Send to Customer'), color: 'amber', icon: '\uD83D\uDCE7', action: 'send_estimate' };
  else if (status === 'pending_approval') guide = { text: t('roSuggestWait', 'Waiting for customer approval'), btn: t('roMarkApproved', 'Mark Approved'), color: 'green', icon: '\u23F3', action: 'approve' };
  else if (status === 'approved') guide = { text: t('roSuggestStart', 'Customer approved \u2014 begin work'), btn: t('roStartWorkClockIn', 'Start Work & Clock In'), color: 'green', icon: '\uD83D\uDE80', action: 'start_work' };
  else if (status === 'in_progress') guide = { text: t('roSuggestReady', 'Mark ready when the job is done'), btn: t('roMarkReady', 'Mark Ready'), color: 'teal', icon: '\uD83D\uDD27', action: 'ready' };
  else if (status === 'waiting_parts') guide = { text: t('roPartsArrivedHint', 'Parts arrived? Resume work.'), btn: t('roPartsArrived', 'Parts Arrived \u2014 Resume'), color: 'orange', icon: '\uD83D\uDCE6', action: 'resume' };
  else if (status === 'ready') guide = { text: t('roSuggestComplete', 'Customer notified. Complete when picked up.'), btn: t('roCompleteInvoice', 'Complete & Invoice'), color: 'green', icon: '\u2705', action: 'complete' };
  else if (status === 'completed' && !hasInvoices) guide = { text: t('roSuggestInvoice', 'Generate invoice from estimate'), btn: t('roGenerateInvoice', 'Generate Invoice'), color: 'teal', icon: '\uD83D\uDCB0', action: 'invoice' };

  if (guide && currentStep) {
    var colorMap = { purple: 'from-purple-600 to-purple-700', blue: 'from-blue-600 to-blue-700', amber: 'from-amber-500 to-amber-600', green: 'from-green-600 to-green-700', teal: 'from-teal-600 to-teal-700', orange: 'from-orange-500 to-orange-600' };
    var gradCls = colorMap[guide.color] || colorMap.blue;

    var guideBar = document.createElement('div');
    guideBar.className = 'bg-gradient-to-r ' + gradCls + ' rounded-xl p-4 text-white';

    // Step indicator + progress dots
    var stepRow = document.createElement('div');
    stepRow.className = 'flex items-center justify-between mb-2';
    var stepLabel = document.createElement('span');
    stepLabel.className = 'text-xs font-bold uppercase tracking-wider opacity-80';
    stepLabel.textContent = t('roStep', 'Step') + ' ' + currentStep.step + ' ' + t('roStepOf', 'of') + ' ' + currentStep.total;
    stepRow.appendChild(stepLabel);

    var dots = document.createElement('div');
    dots.className = 'flex gap-1';
    for (var di = 1; di <= currentStep.total; di++) {
      var dot = document.createElement('div');
      dot.className = 'w-2 h-2 rounded-full ' + (di < currentStep.step ? 'bg-white' : di === currentStep.step ? 'bg-white ring-2 ring-white/50 scale-125' : 'bg-white/30');
      dots.appendChild(dot);
    }
    stepRow.appendChild(dots);
    guideBar.appendChild(stepRow);

    // Main row: icon + text + button
    var mainRow = document.createElement('div');
    mainRow.className = 'flex items-center gap-3';
    var gIcon = document.createElement('span');
    gIcon.className = 'text-2xl shrink-0';
    gIcon.textContent = guide.icon;
    mainRow.appendChild(gIcon);
    var gText = document.createElement('p');
    gText.className = 'flex-1 text-sm font-medium opacity-90';
    gText.textContent = guide.text;
    mainRow.appendChild(gText);

    var gBtn = document.createElement('button');
    gBtn.className = 'shrink-0 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-bold transition backdrop-blur-sm border border-white/20';
    gBtn.textContent = guide.btn;
    gBtn.addEventListener('click', function() {
      var a = guide.action;
      if (a === 'inspect') {
        api('inspections.php', { method: 'POST', body: { repair_order_id: ro.id } }).then(function() {
          api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'diagnosis' } }).then(function() {
            showToast(t('roInspectionCreated', 'Inspection created \u2014 moved to Diagnosis'));
            viewRoDetail(ro.id);
          });
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'diagnosis') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'diagnosis' } }).then(function() {
          showToast(t('roStatusAdvanced', 'Advanced to Diagnosis')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'estimate') {
        var inspId = hasInspections ? ro.inspections[0].id : null;
        var payload = { repair_order_id: ro.id, tax_rate: 0.0 };
        if (inspId) payload.from_inspection_id = inspId;
        api('estimates.php', { method: 'POST', body: payload }).then(function() {
          showToast(t('roEstimateCreated', 'Estimate created')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'send_estimate') {
        if (latestEstimate) {
          api('estimates.php', { method: 'PUT', body: { id: latestEstimate.id, action: 'send' } }).then(function() {
            api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'pending_approval' } }).then(function() {
              showToast(t('roEstimateSent', 'Estimate sent \u2014 awaiting approval')); viewRoDetail(ro.id);
            });
          }).catch(function(err) { showToast(err.message, true); });
        }
      } else if (a === 'approve') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'approved' } }).then(function() {
          showToast(t('roStatusAdvanced', 'Approved!')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'start_work') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'in_progress' } }).then(function() {
          showToast(t('roStatusAdvanced', 'Work started!'));
          viewRoDetail(ro.id);
          // After modal reloads, the labor section will be visible for clock-in
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'ready') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'ready' } }).then(function() {
          showToast(t('roMarkedReady', 'Marked Ready \u2014 customer notified')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'resume') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'in_progress' } }).then(function() {
          showToast(t('roStatusAdvanced', 'Resumed \u2014 back in progress')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'complete') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'completed' } }).then(function() {
          showToast(t('roCompletedInvoiced', 'Completed \u2014 invoice generated'));
          modal.remove(); loadRepairOrders(); if (typeof loadKanban === 'function') loadKanban();
        }).catch(function(err) { showToast(err.message, true); });
      } else if (a === 'invoice') {
        api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'completed' } }).then(function() {
          showToast(t('roCompletedInvoiced', 'Invoice generated')); viewRoDetail(ro.id);
        }).catch(function(err) { showToast(err.message, true); });
      }
    });
    mainRow.appendChild(gBtn);
    guideBar.appendChild(mainRow);
    body.appendChild(guideBar);
  } else if (status === 'invoiced' || status === 'completed') {
    // Completed state - no action needed
    var doneBar = document.createElement('div');
    doneBar.className = 'bg-gray-100 dark:bg-gray-700 rounded-xl p-3 flex items-center gap-3';
    doneBar.appendChild(document.createTextNode('\u2705 '));
    var doneText = document.createElement('span');
    doneText.className = 'text-sm text-gray-600 dark:text-gray-300 font-medium';
    doneText.textContent = status === 'invoiced' ? t('roInvoicedDone', 'This repair order is complete and invoiced.') : t('roCompletedDone', 'This repair order is complete.');
    doneBar.appendChild(doneText);
    body.appendChild(doneBar);
  }

  var actions = document.createElement('div');
  actions.className = 'flex flex-wrap gap-3';

  // Always show core action buttons
  var inspBtn = document.createElement('button');
  inspBtn.className = 'px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition';
  inspBtn.textContent = t('roNewInspection', 'New Inspection');
  inspBtn.addEventListener('click', async function() {
    try {
      await api('inspections.php', { method: 'POST', body: { repair_order_id: ro.id } });
      showToast(t('roInspectionCreated', 'Inspection created with template items'));
      viewRoDetail(ro.id);
    } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
  });
  actions.appendChild(inspBtn);

  var estBtn = document.createElement('button');
  estBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition';
  estBtn.textContent = t('roNewEstimate', 'New Estimate');
  estBtn.addEventListener('click', async function() {
    var inspId = null;
    if (ro.inspections && ro.inspections.length > 0) inspId = ro.inspections[0].id;
    try {
      var payload = { repair_order_id: ro.id, tax_rate: 0.0 };
      if (inspId) payload.from_inspection_id = inspId;
      await api('estimates.php', { method: 'POST', body: payload });
      showToast(t('roEstimateCreated', 'Estimate created'));
      viewRoDetail(ro.id);
    } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
  });
  actions.appendChild(estBtn);

  // Quick-send button if draft estimate exists
  if (latestEstimate && latestEstimate.status === 'draft' && parseFloat(latestEstimate.total || 0) > 0) {
    var quickSendBtn = document.createElement('button');
    quickSendBtn.className = 'px-4 py-2 bg-amber-500 text-black rounded-lg text-sm font-bold hover:bg-amber-600 transition';
    quickSendBtn.textContent = '\uD83D\uDCE7 ' + t('roQuickSendEstimate', 'Send Estimate ($' + parseFloat(latestEstimate.total).toFixed(2) + ')');
    quickSendBtn.addEventListener('click', async function() {
      try {
        await api('estimates.php', { method: 'PUT', body: { id: latestEstimate.id, action: 'send' } });
        showToast(t('roEstimateSent', 'Estimate sent to customer'));
        viewRoDetail(ro.id);
      } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
    });
    actions.appendChild(quickSendBtn);
  }

  // Quick complete button if status is ready
  if (status === 'ready') {
    var completeBtn = document.createElement('button');
    completeBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 transition';
    completeBtn.textContent = '\u2705 ' + t('roMarkCompleted', 'Mark Completed & Invoice');
    completeBtn.addEventListener('click', async function() {
      try {
        await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: 'completed' } });
        showToast(t('roCompletedInvoiced', 'Completed \u2014 invoice generated & sent'));
        modal.remove();
        loadRepairOrders();
        if (typeof loadKanban === 'function') loadKanban();
      } catch(err) { showToast(t('roFailedMsg', 'Failed') + ': ' + err.message, true); }
    });
    actions.appendChild(completeBtn);
  }

  body.appendChild(actions);

  // Inspections
  if (ro.inspections && ro.inspections.length > 0) {
    var inspSection = document.createElement('div');
    var inspH = document.createElement('h3');
    inspH.className = 'font-bold text-gray-900 dark:text-white mb-3';
    inspH.textContent = t('roInspections', 'Inspections') + ' (' + ro.inspections.length + ')';
    inspSection.appendChild(inspH);

    ro.inspections.forEach(function(insp) {
      var iCard = document.createElement('div');
      iCard.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-2 flex justify-between items-center';

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
      iCard.appendChild(iLeft);

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

      iCard.appendChild(iActions);
      inspSection.appendChild(iCard);
    });
    body.appendChild(inspSection);
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
  body.appendChild(estSection);

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
    body.appendChild(invSection);
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
  body.appendChild(laborSection);

  // Initialize LaborTracker for this RO
  if (typeof LaborTracker !== 'undefined') {
    LaborTracker.init(ro.id);
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
    body.appendChild(apptDiv);
  }

  card.appendChild(body);
  modal.appendChild(card);

  modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
  document.body.appendChild(modal);
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

  // Add item button
  var addRow = document.createElement('div');
  addRow.className = 'flex gap-2 mb-3';
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
