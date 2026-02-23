/**
 * Oregon Tires — Admin Repair Orders Module
 * Handles: RO list, detail view, creation, status updates,
 *          inspection management, estimate management
 *
 * Depends on: api(), showToast(), csrfToken from admin/index.html
 */

(function() {
'use strict';

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
  waiting_parts:    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
  ready:            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
  completed:        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  invoiced:         'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300',
  cancelled:        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

function createStatusBadge(status) {
  var label = (status || 'intake').replace(/_/g, ' ');
  label = label.charAt(0).toUpperCase() + label.slice(1);
  var cls = statusColors[status] || statusColors.intake;
  var span = document.createElement('span');
  span.className = 'px-2.5 py-1 rounded-full text-xs font-bold ' + cls;
  span.textContent = label;
  return span;
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  var d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ─── Status Timeline / Stepper ──────────────────────────────────────────────
var timelineStatuses = ['intake', 'diagnosis', 'estimate_pending', 'pending_approval', 'approved', 'in_progress', 'waiting_parts', 'ready', 'completed', 'invoiced'];
var timelineLabels  = { intake: 'Intake', diagnosis: 'Diag', estimate_pending: 'Est.', pending_approval: 'Approval', approved: 'Approved', in_progress: 'In Prog', waiting_parts: 'Parts', ready: 'Ready', completed: 'Done', invoiced: 'Invoiced' };

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
    cancelTxt.textContent = 'Cancelled';
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
    label.textContent = timelineLabels[status] || status;
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
    showToast('Failed to load repair orders', true);
  }
};

function renderRoTable() {
  var tbody = document.getElementById('ro-table');
  if (!tbody) return;
  tbody.textContent = '';

  if (roList.length === 0) {
    var row = document.createElement('tr');
    var cell = document.createElement('td');
    cell.colSpan = 6;
    cell.className = 'p-8 text-center text-gray-400';
    cell.textContent = 'No repair orders found';
    row.appendChild(cell);
    tbody.appendChild(row);
    return;
  }

  roList.forEach(function(ro) {
    var tr = document.createElement('tr');
    tr.className = 'border-b hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition';
    tr.addEventListener('click', function() { viewRoDetail(ro.id); });

    // RO Number
    var tdNum = document.createElement('td');
    tdNum.className = 'p-3 text-sm font-bold text-green-700 dark:text-green-400';
    tdNum.textContent = ro.ro_number;
    tr.appendChild(tdNum);

    // Customer
    var tdCust = document.createElement('td');
    tdCust.className = 'p-3 text-sm';
    tdCust.textContent = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim() || '-';
    tr.appendChild(tdCust);

    // Vehicle
    var tdVeh = document.createElement('td');
    tdVeh.className = 'p-3 text-sm';
    tdVeh.textContent = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ') || '-';
    tr.appendChild(tdVeh);

    // Status
    var tdStatus = document.createElement('td');
    tdStatus.className = 'p-3 text-sm';
    tdStatus.appendChild(createStatusBadge(ro.status));
    tr.appendChild(tdStatus);

    // Created
    var tdDate = document.createElement('td');
    tdDate.className = 'p-3 text-sm';
    tdDate.textContent = formatDate(ro.created_at);
    tr.appendChild(tdDate);

    // Actions
    var tdAct = document.createElement('td');
    tdAct.className = 'p-3 text-sm';
    var viewBtn = document.createElement('button');
    viewBtn.className = 'text-green-600 hover:text-green-800 text-sm font-medium';
    viewBtn.textContent = 'View';
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
    showToast('Failed to load repair order: ' + err.message, true);
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
  ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','waiting_parts','ready','completed','invoiced','cancelled'].forEach(function(s) {
    var opt = document.createElement('option');
    opt.value = s;
    opt.textContent = s.replace(/_/g, ' ');
    opt.className = 'text-gray-900';
    if (s === ro.status) opt.selected = true;
    statusSelect.appendChild(opt);
  });

  var updateBtn = document.createElement('button');
  updateBtn.className = 'bg-white/20 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-white/30 font-medium';
  updateBtn.textContent = 'Update Status';
  updateBtn.addEventListener('click', async function() {
    var newStatus = statusSelect.value;
    if (newStatus === ro.status) return;
    try {
      await api('repair-orders.php', { method: 'PUT', body: { id: ro.id, status: newStatus } });
      showToast('Status updated to ' + newStatus.replace(/_/g, ' '));
      modal.remove();
      loadRepairOrders();
    } catch (err) {
      showToast('Failed: ' + err.message, true);
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

  [
    ['Customer', customer],
    ['Email', ro.customer_email || '-'],
    ['Phone', ro.customer_phone || '-'],
    ['Vehicle', vehicle],
    ['VIN', ro.vin || '-'],
    ['Mileage In', ro.mileage_in ? Number(ro.mileage_in).toLocaleString() : '-'],
    ['Promised Date', ro.promised_date || '-'],
    ['Created', formatDate(ro.created_at)],
    ['Updated', formatDate(ro.updated_at)],
  ].forEach(function(pair) {
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
    concernLbl.textContent = 'Customer Concern';
    var concernTxt = document.createElement('p');
    concernTxt.className = 'text-sm text-gray-700 dark:text-gray-300';
    concernTxt.textContent = ro.customer_concern;
    concernDiv.appendChild(concernLbl);
    concernDiv.appendChild(concernTxt);
    body.appendChild(concernDiv);
  }

  // Action buttons
  var actions = document.createElement('div');
  actions.className = 'flex flex-wrap gap-3';

  var inspBtn = document.createElement('button');
  inspBtn.className = 'px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition';
  inspBtn.textContent = 'New Inspection';
  inspBtn.addEventListener('click', async function() {
    try {
      await api('inspections.php', { method: 'POST', body: { repair_order_id: ro.id } });
      showToast('Inspection created with template items');
      viewRoDetail(ro.id);
    } catch(err) { showToast('Failed: ' + err.message, true); }
  });
  actions.appendChild(inspBtn);

  var estBtn = document.createElement('button');
  estBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition';
  estBtn.textContent = 'New Estimate';
  estBtn.addEventListener('click', async function() {
    var inspId = null;
    if (ro.inspections && ro.inspections.length > 0) inspId = ro.inspections[0].id;
    try {
      var payload = { repair_order_id: ro.id, tax_rate: 0.0 };
      if (inspId) payload.from_inspection_id = inspId;
      await api('estimates.php', { method: 'POST', body: payload });
      showToast('Estimate created');
      viewRoDetail(ro.id);
    } catch(err) { showToast('Failed: ' + err.message, true); }
  });
  actions.appendChild(estBtn);
  body.appendChild(actions);

  // Inspections
  if (ro.inspections && ro.inspections.length > 0) {
    var inspSection = document.createElement('div');
    var inspH = document.createElement('h3');
    inspH.className = 'font-bold text-gray-900 dark:text-white mb-3';
    inspH.textContent = 'Inspections (' + ro.inspections.length + ')';
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
        compBtn.textContent = 'Complete';
        compBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try { await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'complete' } }); showToast('Inspection completed'); viewRoDetail(ro.id); }
          catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(compBtn);
      }

      if (insp.status === 'completed') {
        var sendBtn = document.createElement('button');
        sendBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium';
        sendBtn.textContent = 'Send to Customer';
        sendBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try { await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } }); showToast('Inspection sent to customer'); viewRoDetail(ro.id); }
          catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(sendBtn);

        var resendInspBtn = document.createElement('button');
        resendInspBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendInspBtn.textContent = 'Resend to Customer';
        resendInspBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } });
            showToast('Inspection re-sent to customer');
          } catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(resendInspBtn);
      }

      if (insp.status === 'sent') {
        var resendSentInspBtn = document.createElement('button');
        resendSentInspBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendSentInspBtn.textContent = 'Resend to Customer';
        resendSentInspBtn.addEventListener('click', (function(iid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('inspections.php', { method: 'PUT', body: { id: iid, action: 'send' } });
            showToast('Inspection re-sent to customer');
          } catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(insp.id));
        iActions.appendChild(resendSentInspBtn);
      }

      iCard.appendChild(iActions);
      inspSection.appendChild(iCard);
    });
    body.appendChild(inspSection);
  }

  // Estimates
  if (ro.estimates && ro.estimates.length > 0) {
    var estSection = document.createElement('div');
    var estH = document.createElement('h3');
    estH.className = 'font-bold text-gray-900 dark:text-white mb-3';
    estH.textContent = 'Estimates (' + ro.estimates.length + ')';
    estSection.appendChild(estH);

    ro.estimates.forEach(function(est) {
      var eCard = document.createElement('div');
      eCard.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-2 flex justify-between items-center';

      var eLeft = document.createElement('div');
      eLeft.className = 'flex items-center gap-3';

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
      eTotal.className = 'text-sm text-gray-500';
      eTotal.textContent = '$' + parseFloat(est.total || 0).toFixed(2);
      eLeft.appendChild(eTotal);

      var eVer = document.createElement('span');
      eVer.className = 'text-xs text-gray-400';
      eVer.textContent = 'v' + est.version;
      eLeft.appendChild(eVer);

      eCard.appendChild(eLeft);

      var eActions = document.createElement('div');
      eActions.className = 'flex gap-2';

      if (est.status === 'draft') {
        var sendEstBtn = document.createElement('button');
        sendEstBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium';
        sendEstBtn.textContent = 'Send to Customer';
        sendEstBtn.addEventListener('click', (function(eid) { return async function(e) {
          e.stopPropagation();
          try { await api('estimates.php', { method: 'PUT', body: { id: eid, action: 'send' } }); showToast('Estimate sent to customer'); viewRoDetail(ro.id); }
          catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(est.id));
        eActions.appendChild(sendEstBtn);
      }

      if (est.status === 'sent' || est.status === 'viewed' || est.status === 'approved' || est.status === 'partial') {
        var resendEstBtn = document.createElement('button');
        resendEstBtn.className = 'text-orange-600 hover:text-orange-800 text-sm font-medium';
        resendEstBtn.textContent = 'Resend to Customer';
        resendEstBtn.addEventListener('click', (function(eid) { return async function(e) {
          e.stopPropagation();
          try {
            await api('estimates.php', { method: 'PUT', body: { id: eid, action: 'send' } });
            showToast('Estimate re-sent to customer');
          } catch(err) { showToast('Failed: ' + err.message, true); }
        }; })(est.id));
        eActions.appendChild(resendEstBtn);
      }

      eCard.appendChild(eActions);
      estSection.appendChild(eCard);
    });
    body.appendChild(estSection);
  }

  // Linked appointment
  if (ro.appointment) {
    var apptDiv = document.createElement('div');
    apptDiv.className = 'bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4';
    var apptH = document.createElement('h3');
    apptH.className = 'font-bold text-gray-900 dark:text-white text-sm mb-2';
    apptH.textContent = 'Linked Appointment';
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

// ─── Create RO Modal ─────────────────────────────────────────────────────────
window.roShowCreateModal = function() {
  var existing = document.getElementById('ro-create-modal');
  if (existing) existing.remove();

  var modal = document.createElement('div');
  modal.id = 'ro-create-modal';
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay';

  var card = document.createElement('div');
  card.className = 'bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6';

  var title = document.createElement('h2');
  title.className = 'text-xl font-bold text-gray-900 dark:text-white mb-4';
  title.textContent = 'Create Repair Order';
  card.appendChild(title);

  var optWrap = document.createElement('div');
  optWrap.className = 'space-y-4';

  // From Appointment
  var fromApptDiv = document.createElement('div');
  fromApptDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4';
  var fromApptH = document.createElement('h3');
  fromApptH.className = 'font-bold text-gray-900 dark:text-white mb-2';
  fromApptH.textContent = 'From Appointment';
  fromApptDiv.appendChild(fromApptH);

  var apptIdInput = document.createElement('input');
  apptIdInput.type = 'number';
  apptIdInput.id = 'ro-appt-id';
  apptIdInput.placeholder = 'Appointment ID';
  apptIdInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  fromApptDiv.appendChild(apptIdInput);

  var apptBtn = document.createElement('button');
  apptBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 w-full';
  apptBtn.textContent = 'Create from Appointment';
  apptBtn.addEventListener('click', async function() {
    var apptId = parseInt(document.getElementById('ro-appt-id').value);
    if (!apptId) { showToast('Enter an appointment ID', true); return; }
    try {
      var json = await api('repair-orders.php', { method: 'POST', body: { appointment_id: apptId } });
      showToast('Repair order ' + json.data.ro_number + ' created!');
      modal.remove();
      loadRepairOrders();
    } catch(err) { showToast(err.message, true); }
  });
  fromApptDiv.appendChild(apptBtn);
  optWrap.appendChild(fromApptDiv);

  // Walk-in
  var walkDiv = document.createElement('div');
  walkDiv.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4';
  var walkH = document.createElement('h3');
  walkH.className = 'font-bold text-gray-900 dark:text-white mb-2';
  walkH.textContent = 'Walk-in (No Appointment)';
  walkDiv.appendChild(walkH);

  var custInput = document.createElement('input');
  custInput.type = 'number'; custInput.id = 'ro-cust-id'; custInput.placeholder = 'Customer ID';
  custInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(custInput);

  var vehInput = document.createElement('input');
  vehInput.type = 'number'; vehInput.id = 'ro-veh-id'; vehInput.placeholder = 'Vehicle ID (optional)';
  vehInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-2 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(vehInput);

  var concernInput = document.createElement('textarea');
  concernInput.id = 'ro-concern'; concernInput.placeholder = 'Customer concern (optional)'; concernInput.rows = 2;
  concernInput.className = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600';
  walkDiv.appendChild(concernInput);

  var walkBtn = document.createElement('button');
  walkBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 w-full';
  walkBtn.textContent = 'Create Walk-in RO';
  walkBtn.addEventListener('click', async function() {
    var custId = parseInt(document.getElementById('ro-cust-id').value);
    if (!custId) { showToast('Customer ID is required', true); return; }
    var payload = { customer_id: custId };
    var vehId = parseInt(document.getElementById('ro-veh-id').value);
    if (vehId) payload.vehicle_id = vehId;
    var concern = document.getElementById('ro-concern').value.trim();
    if (concern) payload.customer_concern = concern;
    try {
      var json = await api('repair-orders.php', { method: 'POST', body: payload });
      showToast('Repair order ' + json.data.ro_number + ' created!');
      modal.remove();
      loadRepairOrders();
    } catch(err) { showToast(err.message, true); }
  });
  walkDiv.appendChild(walkBtn);
  optWrap.appendChild(walkDiv);
  card.appendChild(optWrap);

  var cancelBtn = document.createElement('button');
  cancelBtn.className = 'mt-4 w-full text-center text-gray-500 hover:text-gray-700 text-sm';
  cancelBtn.textContent = 'Cancel';
  cancelBtn.addEventListener('click', function() { modal.remove(); });
  card.appendChild(cancelBtn);

  modal.appendChild(card);
  modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
  document.body.appendChild(modal);
};

})();
