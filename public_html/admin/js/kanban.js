/**
 * Oregon Tires — Admin Kanban Board Module
 * Drag-and-drop kanban view for repair order workflow management.
 *
 * Depends on: api(), showToast(), viewRoDetail() from admin/index.html + repair-orders.js
 */

(function() {
'use strict';

// ─── Column definitions ─────────────────────────────────────────────────────
var COLUMNS = [
  { key: 'intake',           label: 'Intake',      color: '#3b82f6' },
  { key: 'check_in',         label: 'Check In',    color: '#06b6d4' },
  { key: 'diagnosis',        label: 'Diagnosis',   color: '#8b5cf6' },
  { key: 'estimate_pending', label: 'Estimate',    color: '#f59e0b' },
  { key: 'pending_approval', label: 'Approval',    color: '#f59e0b' },
  { key: 'approved',         label: 'Approved',    color: '#22c55e' },
  { key: 'in_progress',      label: 'In Progress', color: '#16a34a' },
  { key: 'on_hold',          label: 'On Hold',     color: '#991b1b' },
  { key: 'waiting_parts',    label: 'Parts',       color: '#f97316' },
  { key: 'ready',            label: 'Ready',       color: '#14b8a6' },
  { key: 'completed',        label: 'Done',        color: '#6b7280' },
  { key: 'invoiced',         label: 'Invoiced',    color: '#0d9488' }
];

var kanbanActive = false;
var autoRefreshInterval = null;

function t(key, fallback) {
  return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function isDark() {
  return document.documentElement.classList.contains('dark');
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  var now = Date.now();
  var then = new Date(dateStr).getTime();
  var diff = now - then;
  if (diff < 0) diff = 0;

  var seconds = Math.floor(diff / 1000);
  var minutes = Math.floor(seconds / 60);
  var hours   = Math.floor(minutes / 60);
  var days    = Math.floor(hours / 24);

  if (days > 0)    return days + t('kanbanTimeDayAgo', 'd ago');
  if (hours > 0)   return hours + t('kanbanTimeHrAgo', 'h ago');
  if (minutes > 0) return minutes + t('kanbanTimeMinAgo', 'm ago');
  return t('kanbanTimeJustNow', 'just now');
}

function abbreviateVehicle(year, make, model) {
  var parts = [year, make, model].filter(Boolean);
  if (parts.length === 0) return '-';
  var str = parts.join(' ');
  if (str.length > 22) {
    str = str.substring(0, 20) + '...';
  }
  return str;
}

// ─── Next-action logic ───────────────────────────────────────────────────────
function getNextAction(ro) {
  var s = ro.status || 'intake';
  var hasEstimate = (ro.estimate_count || 0) > 0;
  var dark = isDark();

  if (s === 'intake') return { label: 'Check In', bg: dark ? '#164e63' : '#cffafe', color: dark ? '#67e8f9' : '#0e7490' };
  if (s === 'check_in') return { label: 'Start Diag', bg: dark ? '#3b0764' : '#ede9fe', color: dark ? '#c4b5fd' : '#6d28d9' };
  if (s === 'diagnosis' && !hasEstimate) return { label: 'Needs Est.', bg: dark ? '#451a03' : '#fef3c7', color: dark ? '#fcd34d' : '#92400e' };
  if (s === 'estimate_pending') return { label: 'Send Est.', bg: dark ? '#1e3a5f' : '#dbeafe', color: dark ? '#93c5fd' : '#1d4ed8' };
  if (s === 'pending_approval') return { label: 'Awaiting', bg: dark ? '#431407' : '#ffedd5', color: dark ? '#fdba74' : '#c2410c' };
  if (s === 'approved') return { label: 'Start Work', bg: dark ? '#052e16' : '#dcfce7', color: dark ? '#86efac' : '#166534' };
  if (s === 'ready') return { label: 'Complete', bg: dark ? '#022c22' : '#d1fae5', color: dark ? '#6ee7b7' : '#065f46' };
  if (s === 'completed') return { label: 'Invoice', bg: dark ? '#042f2e' : '#ccfbf1', color: dark ? '#5eead4' : '#0f766e' };
  return null;
}

// ─── Next-status mapping for quick advance ──────────────────────────────────
function getNextStatus(ro) {
  var s = ro.status || 'intake';
  var hasEstimate = (ro.estimate_count || 0) > 0;

  var map = {
    'intake': 'check_in',
    'check_in': 'diagnosis',
    'estimate_pending': 'pending_approval',
    'pending_approval': 'approved',
    'approved': 'in_progress',
    'in_progress': 'ready',
    'ready': 'completed',
    'completed': 'invoiced'
  };

  if (s === 'diagnosis' && hasEstimate) return 'estimate_pending';
  if (s === 'diagnosis') return null;

  return map[s] || null;
}

// ─── Update column badge counts without full re-render ──────────────────────
function updateColumnCounts() {
  COLUMNS.forEach(function(colDef) {
    var zone = document.querySelector('[data-drop-zone="' + colDef.key + '"]');
    if (!zone) return;
    var count = zone.querySelectorAll('[data-ro-id]').length;
    var col = zone.closest('[data-status]');
    if (!col) return;
    // The count badge is the second span in the header
    var spans = col.querySelectorAll(':scope > div:first-child span');
    if (spans.length >= 2) {
      spans[spans.length - 1].textContent = String(count);
    }
  });
}

// ─── Build a kanban card ─────────────────────────────────────────────────────

function createCard(ro) {
  var card = document.createElement('div');
  card.setAttribute('draggable', 'true');
  card.setAttribute('data-ro-id', String(ro.id));
  card.setAttribute('data-ro-status', ro.status || 'intake');
  card.setAttribute('role', 'listitem');
  card.setAttribute('tabindex', '0');
  card.setAttribute('aria-label', (ro.ro_number || 'RO') + ' — ' + ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim());
  card.className = 'bg-white dark:bg-gray-700 rounded-md p-2.5 mb-2 cursor-grab shadow-sm border border-gray-200 dark:border-gray-600 transition-all duration-150 relative overflow-hidden group';

  // Click → detail
  card.addEventListener('click', function() {
    if (typeof viewRoDetail === 'function') {
      viewRoDetail(ro.id);
    }
  });

  // Keyboard → Enter/Space opens detail
  card.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      if (typeof viewRoDetail === 'function') {
        viewRoDetail(ro.id);
      }
    }
  });

  // Drag events
  card.addEventListener('dragstart', function(e) {
    e.dataTransfer.setData('text/plain', String(ro.id));
    e.dataTransfer.effectAllowed = 'move';
    card.classList.add('opacity-50');
  });
  card.addEventListener('dragend', function() {
    card.classList.remove('opacity-50');
  });

  // RO number
  var roNum = document.createElement('div');
  roNum.className = 'font-bold text-[13px] text-green-700 dark:text-green-400 mb-1';
  roNum.textContent = ro.ro_number || 'RO-???';
  card.appendChild(roNum);

  // Customer name
  var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();
  if (custName) {
    var custEl = document.createElement('div');
    custEl.className = 'text-xs text-gray-700 dark:text-gray-300 mb-0.5 truncate';
    custEl.textContent = custName;
    card.appendChild(custEl);
  }

  // Vehicle
  var vehicle = abbreviateVehicle(ro.vehicle_year, ro.vehicle_make, ro.vehicle_model);
  var vehEl = document.createElement('div');
  vehEl.className = 'text-[11px] text-gray-500 dark:text-gray-400 mb-1 truncate';
  vehEl.textContent = vehicle;
  card.appendChild(vehEl);

  // Active labor indicator
  if (ro.active_labor_count > 0) {
    var laborInd = document.createElement('div');
    laborInd.className = 'flex items-center gap-1 mb-0.5';
    var pulseDot = document.createElement('span');
    pulseDot.className = 'w-1.5 h-1.5 rounded-full bg-green-500 inline-block animate-pulse';
    laborInd.appendChild(pulseDot);
    var laborText = document.createElement('span');
    laborText.className = 'text-[10px] font-semibold text-green-600 dark:text-green-400';
    laborText.textContent = ro.active_labor_count + ' ' + (ro.active_labor_count === 1 ? 'tech working' : 'techs working');
    laborInd.appendChild(laborText);
    card.appendChild(laborInd);
  }

  // Bottom row: time + next-action badge
  var bottomRow = document.createElement('div');
  bottomRow.className = 'flex justify-between items-center mt-0.5';

  var timeEl = document.createElement('div');
  timeEl.className = 'text-[10px] text-gray-400 dark:text-gray-500';
  timeEl.textContent = timeAgo(ro.updated_at);
  bottomRow.appendChild(timeEl);

  // Next-action indicator (dynamic colors require inline style)
  var nextAction = getNextAction(ro);
  if (nextAction) {
    var actionBadge = document.createElement('span');
    actionBadge.className = 'text-[9px] font-semibold px-1.5 py-px rounded whitespace-nowrap';
    actionBadge.style.background = nextAction.bg;
    actionBadge.style.color = nextAction.color;
    actionBadge.textContent = nextAction.label;
    bottomRow.appendChild(actionBadge);
  }
  card.appendChild(bottomRow);

  // ─── Hover quick-action overlay (uses group-hover) ────────────────────────
  var overlay = document.createElement('div');
  overlay.className = 'absolute bottom-0 left-0 right-0 hidden group-hover:flex justify-center items-center gap-1.5 p-1.5 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 rounded-b-md bg-white/90 dark:bg-gray-900/90';

  // "Open" button
  var openBtn = document.createElement('button');
  openBtn.className = 'text-[11px] font-semibold px-2.5 py-1 rounded border-none cursor-pointer transition-colors duration-150 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500 min-h-[28px]';
  openBtn.textContent = t('kanbanOpen', 'Open');
  openBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    if (typeof viewRoDetail === 'function') {
      viewRoDetail(ro.id);
    }
  });
  overlay.appendChild(openBtn);

  // "Next" button (only if there's a valid next status)
  var nextStatus = getNextStatus(ro);
  if (nextStatus && nextAction) {
    var nextBtn = document.createElement('button');
    nextBtn.className = 'text-[11px] font-semibold px-2.5 py-1 rounded border-none cursor-pointer transition-opacity duration-150 text-white hover:opacity-85 min-h-[28px]';
    nextBtn.style.background = nextAction.color;
    var friendlyNext = nextStatus.replace(/_/g, ' ');
    friendlyNext = friendlyNext.charAt(0).toUpperCase() + friendlyNext.slice(1);
    nextBtn.textContent = t('kanbanNext', 'Next') + ' \u2192';
    nextBtn.title = friendlyNext;
    nextBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      handleStatusDrop(ro.id, nextStatus);
    });
    overlay.appendChild(nextBtn);
  }

  card.appendChild(overlay);

  return card;
}

// ─── Build a kanban column ───────────────────────────────────────────────────

function createColumn(colDef, cards) {
  var col = document.createElement('div');
  col.setAttribute('data-status', colDef.key);
  col.setAttribute('role', 'region');
  col.setAttribute('aria-label', colDef.label + ' column');
  col.className = 'min-w-[180px] max-w-[220px] flex-[1_0_180px] bg-gray-50 dark:bg-gray-800 rounded-lg p-2 flex flex-col transition-all duration-200';
  col.style.borderTop = '3px solid ' + colDef.color;

  // Header
  var header = document.createElement('div');
  header.className = 'flex items-center justify-between mb-2 px-0.5 py-1';

  var label = document.createElement('span');
  label.className = 'text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wide';
  var labelKeys = {
    intake: 'roStatusIntake',
    check_in: 'roStatusCheckIn',
    diagnosis: 'roStatusDiagnosis',
    estimate_pending: 'roTimelineEst',
    pending_approval: 'roTimelineApproval',
    approved: 'roStatusApproved',
    in_progress: 'roStatusInProgress',
    on_hold: 'roStatusOnHold',
    waiting_parts: 'roTimelineParts',
    ready: 'roStatusReady',
    completed: 'roTimelineDone',
    invoiced: 'roStatusInvoiced'
  };
  label.textContent = t(labelKeys[colDef.key], colDef.label);

  var badge = document.createElement('span');
  badge.className = 'text-[11px] font-bold text-white rounded-full min-w-[20px] h-5 inline-flex items-center justify-center px-1.5';
  badge.style.background = colDef.color;
  badge.textContent = String(cards.length);

  header.appendChild(label);
  header.appendChild(badge);
  col.appendChild(header);

  // Card list (scrollable)
  var cardList = document.createElement('div');
  cardList.setAttribute('data-drop-zone', colDef.key);
  cardList.setAttribute('role', 'list');
  cardList.setAttribute('aria-label', colDef.label + ' orders');
  cardList.className = 'flex-1 overflow-y-auto min-h-[60px] p-0.5';

  cards.forEach(function(ro) {
    cardList.appendChild(createCard(ro));
  });

  // Empty state
  if (cards.length === 0) {
    var empty = document.createElement('div');
    empty.className = 'text-center py-4 px-2 text-[11px] text-gray-300 dark:text-gray-600';
    empty.textContent = t('kanbanNoOrders', 'No orders');
    cardList.appendChild(empty);
  }

  col.appendChild(cardList);

  // ─── Drop zone handlers ───────────────────────────────────────────────────

  col.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    col.classList.add('ring-2', 'ring-offset-0');
    col.style.setProperty('--tw-ring-color', colDef.color);
  });

  col.addEventListener('dragleave', function(e) {
    if (col.contains(e.relatedTarget)) return;
    col.classList.remove('ring-2', 'ring-offset-0');
    col.style.removeProperty('--tw-ring-color');
  });

  col.addEventListener('drop', function(e) {
    e.preventDefault();
    col.classList.remove('ring-2', 'ring-offset-0');
    col.style.removeProperty('--tw-ring-color');

    var roId = e.dataTransfer.getData('text/plain');
    if (!roId) return;

    var newStatus = colDef.key;

    // Find the card's current status
    var draggedCard = document.querySelector('[data-ro-id="' + roId + '"]');
    var oldStatus = draggedCard ? draggedCard.getAttribute('data-ro-status') : null;

    if (oldStatus === newStatus) return;

    // Optimistic update: move card DOM immediately
    if (draggedCard) {
      draggedCard.setAttribute('data-ro-status', newStatus);
      cardList.appendChild(draggedCard);
      // Remove empty state from target if present
      var emptyEl = cardList.querySelector('.text-center.py-4');
      if (emptyEl && cardList.querySelectorAll('[data-ro-id]').length > 0) {
        emptyEl.remove();
      }
      updateColumnCounts();
    }

    // Update via API (rollback on failure)
    handleStatusDrop(parseInt(roId, 10), newStatus, draggedCard, oldStatus);
  });

  return col;
}

// ─── Handle drag-and-drop status change ──────────────────────────────────────

async function handleStatusDrop(roId, newStatus, card, oldStatus) {
  try {
    await api('repair-orders.php', {
      method: 'PUT',
      body: { id: roId, status: newStatus }
    });
    var friendlyStatus = newStatus.replace(/_/g, ' ');
    showToast(t('roMovedTo', 'Moved to') + ' ' + friendlyStatus.charAt(0).toUpperCase() + friendlyStatus.slice(1));
    // Refresh table data in background so switching views stays in sync
    if (typeof loadRepairOrders === 'function') {
      loadRepairOrders();
    }
  } catch (err) {
    showToast(t('kanbanFailedUpdate', 'Failed to update status') + ': ' + (err.message || 'Unknown error'), true);
    // Rollback: move card back to original column
    if (card && oldStatus) {
      card.setAttribute('data-ro-status', oldStatus);
      var origZone = document.querySelector('[data-drop-zone="' + oldStatus + '"]');
      if (origZone) {
        origZone.appendChild(card);
        updateColumnCounts();
      }
    }
  }
}

// ─── Render the kanban board ─────────────────────────────────────────────────

var _lastOrders = [];

function isMobileView() {
  return typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(max-width: 640px)').matches;
}

// MOBILE_LIST_VIEW
// Below the sm breakpoint we render a stacked single-column list with a
// <select> per card for status changes. Drag-and-drop stays desktop-only.
function renderMobileList(orders) {
  var container = document.getElementById('ro-kanban-view');
  if (!container) return;
  container.textContent = '';

  var list = document.createElement('div');
  list.className = 'flex flex-col gap-3 py-4';

  if (!orders || orders.length === 0) {
    var empty = document.createElement('div');
    empty.className = 'text-center text-gray-500 dark:text-gray-400 py-8';
    empty.textContent = t('kanbanNoOrders', 'No repair orders');
    list.appendChild(empty);
    container.appendChild(list);
    return;
  }

  orders.forEach(function(ro) {
    var card = document.createElement('div');
    card.className = 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm';

    var header = document.createElement('div');
    header.className = 'flex items-start justify-between gap-2 mb-2';

    var titleBtn = document.createElement('button');
    titleBtn.className = 'text-left font-semibold text-brand dark:text-green-400 underline min-h-[44px] flex-1';
    titleBtn.textContent = (ro.ro_number || '#' + ro.id);
    titleBtn.addEventListener('click', function() {
      if (typeof viewRoDetail === 'function') viewRoDetail(ro.id);
    });
    header.appendChild(titleBtn);

    var ago = document.createElement('span');
    ago.className = 'text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap';
    ago.textContent = timeAgo(ro.updated_at || ro.created_at);
    header.appendChild(ago);

    card.appendChild(header);

    var meta = document.createElement('div');
    meta.className = 'text-sm text-gray-700 dark:text-gray-300 mb-1';
    meta.textContent = (ro.customer_name || '') + ' — ' + abbreviateVehicle(ro.vehicle_year, ro.vehicle_make, ro.vehicle_model);
    card.appendChild(meta);

    if (ro.customer_concern) {
      var concern = document.createElement('div');
      concern.className = 'text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2';
      concern.textContent = ro.customer_concern;
      card.appendChild(concern);
    }

    var label = document.createElement('label');
    label.className = 'block text-xs text-gray-500 dark:text-gray-400 mb-1';
    label.textContent = t('kanbanStatus', 'Status');
    card.appendChild(label);

    var sel = document.createElement('select');
    sel.className = 'w-full min-h-[44px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100';
    COLUMNS.forEach(function(col) {
      var opt = document.createElement('option');
      opt.value = col.key;
      opt.textContent = col.label;
      if ((ro.status || 'intake') === col.key) opt.selected = true;
      sel.appendChild(opt);
    });
    var currentStatus = ro.status || 'intake';
    sel.addEventListener('change', function() {
      var newStatus = sel.value;
      if (newStatus === currentStatus) return;
      handleStatusDrop(ro.id, newStatus, null, currentStatus).then(function() {
        currentStatus = newStatus;
        ro.status = newStatus;
      }).catch(function() {
        sel.value = currentStatus;
      });
    });
    card.appendChild(sel);

    list.appendChild(card);
  });

  container.appendChild(list);
}

function renderKanban(orders) {
  _lastOrders = orders || [];
  var container = document.getElementById('ro-kanban-view');
  if (!container) return;

  if (isMobileView()) {
    renderMobileList(_lastOrders);
    return;
  }

  container.textContent = '';

  // Board wrapper
  var board = document.createElement('div');
  board.className = 'flex gap-3 overflow-x-auto py-4 min-h-[400px] touch-pan-x';

  // Group orders by status
  var buckets = {};
  COLUMNS.forEach(function(col) {
    buckets[col.key] = [];
  });
  _lastOrders.forEach(function(ro) {
    var status = ro.status || 'intake';
    if (buckets[status]) {
      buckets[status].push(ro);
    }
  });

  // Build columns
  COLUMNS.forEach(function(colDef) {
    board.appendChild(createColumn(colDef, buckets[colDef.key]));
  });

  container.appendChild(board);
}

// Re-render on breakpoint change (debounced)
(function() {
  var rerenderTimer = null;
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', function() {
      if (rerenderTimer) clearTimeout(rerenderTimer);
      rerenderTimer = setTimeout(function() {
        var container = document.getElementById('ro-kanban-view');
        if (container && container.style.display !== 'none' && _lastOrders.length) {
          renderKanban(_lastOrders);
        }
      }, 200);
    });
  }
})();

// ─── loadKanban (exposed globally) ───────────────────────────────────────────

window.loadKanban = async function() {
  var container = document.getElementById('ro-kanban-view');
  if (!container) return;

  // Show loading skeleton
  container.textContent = '';
  var skeleton = document.createElement('div');
  skeleton.className = 'flex gap-3 py-4';
  for (var i = 0; i < 6; i++) {
    var shimmer = document.createElement('div');
    shimmer.className = 'skeleton min-w-[180px] h-[300px] rounded-lg';
    skeleton.appendChild(shimmer);
  }
  container.appendChild(skeleton);

  try {
    var params = new URLSearchParams({
      limit: 100,
      offset: 0,
      sort_by: 'updated_at',
      sort_order: 'DESC'
    });

    var json = await api('repair-orders.php?' + params.toString());
    var allOrders = json.data || [];

    // Filter out cancelled orders
    var activeOrders = allOrders.filter(function(ro) {
      return ro.status !== 'cancelled';
    });

    renderKanban(activeOrders);
  } catch (err) {
    container.textContent = '';
    var errMsg = document.createElement('div');
    errMsg.className = 'text-center py-10 text-sm text-red-500';
    errMsg.textContent = t('kanbanFailedLoad', 'Failed to load kanban') + ': ' + (err.message || 'Unknown error');
    container.appendChild(errMsg);
    console.error('loadKanban error:', err);
  }
};

// ─── toggleKanbanView (exposed globally) ─────────────────────────────────────

window.toggleKanbanView = function() {
  var tableView  = document.getElementById('ro-table-view');
  var kanbanView = document.getElementById('ro-kanban-view');
  var toggleBtn  = document.getElementById('ro-view-toggle');

  if (!tableView || !kanbanView) return;

  kanbanActive = !kanbanActive;

  if (kanbanActive) {
    tableView.style.display = 'none';
    kanbanView.style.display = 'block';
    if (toggleBtn) {
      toggleBtn.textContent = '';
      var tableIcon = document.createTextNode(t('kanbanTableView', 'Table View'));
      toggleBtn.appendChild(tableIcon);
    }
    loadKanban();
    startAutoRefresh();
  } else {
    tableView.style.display = '';
    kanbanView.style.display = 'none';
    if (toggleBtn) {
      toggleBtn.textContent = '';
      var kanbanIcon = document.createTextNode(t('kanbanView', 'Kanban View'));
      toggleBtn.appendChild(kanbanIcon);
    }
    stopAutoRefresh();
  }
};

// ─── Auto-refresh when kanban is visible ─────────────────────────────────────

function startAutoRefresh() {
  stopAutoRefresh();
  autoRefreshInterval = setInterval(function() {
    if (document.visibilityState === 'visible' && kanbanActive) {
      loadKanban();
    }
  }, 60000);
}

function stopAutoRefresh() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
    autoRefreshInterval = null;
  }
}

// Stop auto-refresh when page is hidden
if (typeof document !== 'undefined') {
  document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
      stopAutoRefresh();
    } else if (kanbanActive) {
      startAutoRefresh();
    }
  });
}

// ─── Inject the toggle button and kanban container into the DOM ──────────────

function injectKanbanElements() {
  var roTab = document.getElementById('tab-repairorders');
  if (!roTab) return;

  var headerDiv = roTab.querySelector('.bg-brand-light');
  if (headerDiv) {
    var btnContainer = headerDiv.querySelector('.flex.items-center.gap-3');
    if (btnContainer) {
      if (!document.getElementById('ro-view-toggle')) {
        var toggleBtn = document.createElement('button');
        toggleBtn.id = 'ro-view-toggle';
        toggleBtn.className = 'bg-white/20 text-white px-4 py-2 rounded-lg text-sm hover:bg-white/30 flex items-center gap-1 min-h-[44px]';
        toggleBtn.textContent = t('kanbanView', 'Kanban View');
        toggleBtn.addEventListener('click', function() {
          toggleKanbanView();
        });
        btnContainer.insertBefore(toggleBtn, btnContainer.firstChild);
      }
    }
  }

  var contentArea = roTab.querySelector('.bg-white.rounded-b-xl, .dark\\:bg-gray-800.rounded-b-xl');
  if (!contentArea) {
    var allDivs = roTab.querySelectorAll(':scope > div');
    if (allDivs.length >= 2) contentArea = allDivs[1];
  }
  if (!contentArea) return;

  if (!document.getElementById('ro-table-view')) {
    var tableWrapper = contentArea.querySelector('.overflow-x-auto');
    var pagination   = document.getElementById('ro-pagination');

    if (tableWrapper) {
      var tableViewDiv = document.createElement('div');
      tableViewDiv.id = 'ro-table-view';
      tableWrapper.parentNode.insertBefore(tableViewDiv, tableWrapper);
      tableViewDiv.appendChild(tableWrapper);
      if (pagination) {
        tableViewDiv.appendChild(pagination);
      }
    }
  }

  if (!document.getElementById('ro-kanban-view')) {
    var kanbanDiv = document.createElement('div');
    kanbanDiv.id = 'ro-kanban-view';
    kanbanDiv.style.display = 'none';

    var tableViewEl = document.getElementById('ro-table-view');
    if (tableViewEl) {
      tableViewEl.parentNode.insertBefore(kanbanDiv, tableViewEl.nextSibling);
    } else {
      contentArea.appendChild(kanbanDiv);
    }
  }
}

// ─── Init on DOM ready ───────────────────────────────────────────────────────

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectKanbanElements);
} else {
  injectKanbanElements();
}

})();
