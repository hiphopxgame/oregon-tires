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
  { key: 'diagnosis',        label: 'Diagnosis',   color: '#8b5cf6' },
  { key: 'estimate_pending', label: 'Estimate',    color: '#f59e0b' },
  { key: 'pending_approval', label: 'Approval',    color: '#f59e0b' },
  { key: 'approved',         label: 'Approved',    color: '#22c55e' },
  { key: 'in_progress',      label: 'In Progress', color: '#16a34a' },
  { key: 'waiting_parts',    label: 'Parts',       color: '#f97316' },
  { key: 'ready',            label: 'Ready',       color: '#14b8a6' },
  { key: 'completed',        label: 'Done',        color: '#6b7280' }
];

var kanbanActive = false;

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

  if (days > 0)    return days + 'd ago';
  if (hours > 0)   return hours + 'h ago';
  if (minutes > 0) return minutes + 'm ago';
  return 'just now';
}

function abbreviateVehicle(year, make, model) {
  var parts = [year, make, model].filter(Boolean);
  if (parts.length === 0) return '-';
  // Abbreviate long model names
  var str = parts.join(' ');
  if (str.length > 22) {
    str = str.substring(0, 20) + '...';
  }
  return str;
}

// ─── Build a kanban card ─────────────────────────────────────────────────────

function createCard(ro) {
  var dark = isDark();

  var card = document.createElement('div');
  card.setAttribute('draggable', 'true');
  card.setAttribute('data-ro-id', String(ro.id));
  card.setAttribute('data-ro-status', ro.status || 'intake');
  card.style.cssText =
    'background:' + (dark ? '#374151' : '#ffffff') + ';' +
    'border-radius:6px;' +
    'padding:10px;' +
    'margin-bottom:8px;' +
    'cursor:grab;' +
    'box-shadow:0 1px 3px rgba(0,0,0,0.1);' +
    'transition:box-shadow 0.15s, transform 0.15s;' +
    'border:1px solid ' + (dark ? '#4b5563' : '#e5e7eb') + ';';

  // Hover effect
  card.addEventListener('mouseenter', function() {
    card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    card.style.transform = 'translateY(-1px)';
  });
  card.addEventListener('mouseleave', function() {
    card.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
    card.style.transform = 'translateY(0)';
  });

  // Click → detail
  card.addEventListener('click', function() {
    if (typeof viewRoDetail === 'function') {
      viewRoDetail(ro.id);
    }
  });

  // Drag events
  card.addEventListener('dragstart', function(e) {
    e.dataTransfer.setData('text/plain', String(ro.id));
    e.dataTransfer.effectAllowed = 'move';
    card.style.opacity = '0.5';
    card.style.cursor = 'grabbing';
  });
  card.addEventListener('dragend', function() {
    card.style.opacity = '1';
    card.style.cursor = 'grab';
  });

  // RO number
  var roNum = document.createElement('div');
  roNum.style.cssText =
    'font-weight:700;' +
    'font-size:13px;' +
    'color:' + (dark ? '#4ade80' : '#15803d') + ';' +
    'margin-bottom:4px;';
  roNum.textContent = ro.ro_number || 'RO-???';
  card.appendChild(roNum);

  // Customer name
  var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();
  if (custName) {
    var custEl = document.createElement('div');
    custEl.style.cssText =
      'font-size:12px;' +
      'color:' + (dark ? '#d1d5db' : '#374151') + ';' +
      'margin-bottom:2px;' +
      'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
    custEl.textContent = custName;
    card.appendChild(custEl);
  }

  // Vehicle
  var vehicle = abbreviateVehicle(ro.vehicle_year, ro.vehicle_make, ro.vehicle_model);
  var vehEl = document.createElement('div');
  vehEl.style.cssText =
    'font-size:11px;' +
    'color:' + (dark ? '#9ca3af' : '#6b7280') + ';' +
    'margin-bottom:4px;' +
    'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
  vehEl.textContent = vehicle;
  card.appendChild(vehEl);

  // Time in status
  var timeEl = document.createElement('div');
  timeEl.style.cssText =
    'font-size:10px;' +
    'color:' + (dark ? '#6b7280' : '#9ca3af') + ';';
  timeEl.textContent = timeAgo(ro.updated_at);
  card.appendChild(timeEl);

  return card;
}

// ─── Build a kanban column ───────────────────────────────────────────────────

function createColumn(colDef, cards) {
  var dark = isDark();

  var col = document.createElement('div');
  col.setAttribute('data-status', colDef.key);
  col.style.cssText =
    'min-width:180px;' +
    'max-width:220px;' +
    'flex:1 0 180px;' +
    'background:' + (dark ? '#1f2937' : '#f9fafb') + ';' +
    'border-radius:8px;' +
    'padding:8px;' +
    'display:flex;' +
    'flex-direction:column;' +
    'border-top:3px solid ' + colDef.color + ';' +
    'transition:border-color 0.2s, box-shadow 0.2s;';

  // Header
  var header = document.createElement('div');
  header.style.cssText =
    'display:flex;' +
    'align-items:center;' +
    'justify-content:space-between;' +
    'margin-bottom:8px;' +
    'padding:4px 2px;';

  var label = document.createElement('span');
  label.style.cssText =
    'font-size:12px;' +
    'font-weight:700;' +
    'color:' + (dark ? '#e5e7eb' : '#374151') + ';' +
    'text-transform:uppercase;' +
    'letter-spacing:0.5px;';
  label.textContent = colDef.label;

  var badge = document.createElement('span');
  badge.style.cssText =
    'font-size:11px;' +
    'font-weight:700;' +
    'color:' + (dark ? '#d1d5db' : '#ffffff') + ';' +
    'background:' + colDef.color + ';' +
    'border-radius:9999px;' +
    'min-width:20px;' +
    'height:20px;' +
    'display:inline-flex;' +
    'align-items:center;' +
    'justify-content:center;' +
    'padding:0 6px;';
  badge.textContent = String(cards.length);

  header.appendChild(label);
  header.appendChild(badge);
  col.appendChild(header);

  // Card list (scrollable)
  var cardList = document.createElement('div');
  cardList.setAttribute('data-drop-zone', colDef.key);
  cardList.style.cssText =
    'flex:1;' +
    'overflow-y:auto;' +
    'min-height:60px;' +
    'padding:2px;';

  cards.forEach(function(ro) {
    cardList.appendChild(createCard(ro));
  });

  // Empty state
  if (cards.length === 0) {
    var empty = document.createElement('div');
    empty.style.cssText =
      'text-align:center;' +
      'padding:16px 8px;' +
      'font-size:11px;' +
      'color:' + (dark ? '#4b5563' : '#d1d5db') + ';';
    empty.textContent = 'No orders';
    cardList.appendChild(empty);
  }

  col.appendChild(cardList);

  // ─── Drop zone handlers ───────────────────────────────────────────────────

  col.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    col.style.boxShadow = '0 0 0 2px ' + colDef.color;
    col.style.background = dark ? '#283548' : '#f0fdf4';
  });

  col.addEventListener('dragleave', function(e) {
    // Only fire when actually leaving the column (not entering a child)
    if (col.contains(e.relatedTarget)) return;
    col.style.boxShadow = 'none';
    col.style.background = dark ? '#1f2937' : '#f9fafb';
  });

  col.addEventListener('drop', function(e) {
    e.preventDefault();
    col.style.boxShadow = 'none';
    col.style.background = dark ? '#1f2937' : '#f9fafb';

    var roId = e.dataTransfer.getData('text/plain');
    if (!roId) return;

    var newStatus = colDef.key;

    // Find the card's current status
    var draggedCard = document.querySelector('[data-ro-id="' + roId + '"]');
    var oldStatus = draggedCard ? draggedCard.getAttribute('data-ro-status') : null;

    if (oldStatus === newStatus) return;

    // Update via API
    handleStatusDrop(parseInt(roId, 10), newStatus);
  });

  return col;
}

// ─── Handle drag-and-drop status change ──────────────────────────────────────

async function handleStatusDrop(roId, newStatus) {
  try {
    await api('repair-orders.php', {
      method: 'PUT',
      body: { id: roId, status: newStatus }
    });
    var friendlyStatus = newStatus.replace(/_/g, ' ');
    showToast('Moved to ' + friendlyStatus.charAt(0).toUpperCase() + friendlyStatus.slice(1));
    // Reload the kanban board
    loadKanban();
    // Also refresh the table data in the background so switching views stays in sync
    if (typeof loadRepairOrders === 'function') {
      loadRepairOrders();
    }
  } catch (err) {
    showToast('Failed to update status: ' + (err.message || 'Unknown error'), true);
  }
}

// ─── Render the kanban board ─────────────────────────────────────────────────

function renderKanban(orders) {
  var container = document.getElementById('ro-kanban-view');
  if (!container) return;
  container.textContent = '';

  var dark = isDark();

  // Board wrapper
  var board = document.createElement('div');
  board.style.cssText =
    'display:flex;' +
    'gap:12px;' +
    'overflow-x:auto;' +
    'padding:16px 0;' +
    'min-height:400px;' +
    '-webkit-overflow-scrolling:touch;';

  // Group orders by status
  var buckets = {};
  COLUMNS.forEach(function(col) {
    buckets[col.key] = [];
  });
  orders.forEach(function(ro) {
    var status = ro.status || 'intake';
    if (buckets[status]) {
      buckets[status].push(ro);
    }
    // Skip cancelled and invoiced — they don't have columns
  });

  // Build columns
  COLUMNS.forEach(function(colDef) {
    board.appendChild(createColumn(colDef, buckets[colDef.key]));
  });

  container.appendChild(board);
}

// ─── loadKanban (exposed globally) ───────────────────────────────────────────

window.loadKanban = async function() {
  var container = document.getElementById('ro-kanban-view');
  if (!container) return;

  // Show loading state
  container.textContent = '';
  var loadingMsg = document.createElement('div');
  loadingMsg.style.cssText =
    'text-align:center;' +
    'padding:40px;' +
    'color:' + (isDark() ? '#9ca3af' : '#6b7280') + ';' +
    'font-size:14px;';
  loadingMsg.textContent = 'Loading kanban board...';
  container.appendChild(loadingMsg);

  try {
    // Fetch all non-cancelled ROs (up to 100)
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
    errMsg.style.cssText =
      'text-align:center;' +
      'padding:40px;' +
      'color:#ef4444;' +
      'font-size:14px;';
    errMsg.textContent = 'Failed to load kanban: ' + (err.message || 'Unknown error');
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
    // Show kanban, hide table
    tableView.style.display = 'none';
    kanbanView.style.display = 'block';
    if (toggleBtn) {
      toggleBtn.textContent = '';
      var tableIcon = document.createTextNode('Table View');
      toggleBtn.appendChild(tableIcon);
    }
    loadKanban();
  } else {
    // Show table, hide kanban
    tableView.style.display = '';
    kanbanView.style.display = 'none';
    if (toggleBtn) {
      toggleBtn.textContent = '';
      var kanbanIcon = document.createTextNode('Kanban View');
      toggleBtn.appendChild(kanbanIcon);
    }
  }
};

// ─── Inject the toggle button and kanban container into the DOM ──────────────

function injectKanbanElements() {
  // Find the RO tab's existing container
  var roTab = document.getElementById('tab-repairorders');
  if (!roTab) return;

  // Find the header area to add the toggle button
  var headerDiv = roTab.querySelector('.bg-brand-light');
  if (headerDiv) {
    var btnContainer = headerDiv.querySelector('.flex.items-center.gap-3');
    if (btnContainer) {
      // Check if button already exists
      if (!document.getElementById('ro-view-toggle')) {
        var toggleBtn = document.createElement('button');
        toggleBtn.id = 'ro-view-toggle';
        toggleBtn.className = 'bg-white/20 text-white px-4 py-2 rounded-lg text-sm hover:bg-white/30 flex items-center gap-1';
        toggleBtn.textContent = 'Kanban View';
        toggleBtn.addEventListener('click', function() {
          toggleKanbanView();
        });
        // Insert as the first button
        btnContainer.insertBefore(toggleBtn, btnContainer.firstChild);
      }
    }
  }

  // Wrap existing table + pagination in a container div if not already done
  var contentArea = roTab.querySelector('.bg-white.rounded-b-xl, .dark\\:bg-gray-800.rounded-b-xl');
  if (!contentArea) {
    // Fallback: find by structure
    var allDivs = roTab.querySelectorAll(':scope > div');
    if (allDivs.length >= 2) contentArea = allDivs[1];
  }
  if (!contentArea) return;

  if (!document.getElementById('ro-table-view')) {
    // Find the overflow-x-auto div (table wrapper) and pagination
    var tableWrapper = contentArea.querySelector('.overflow-x-auto');
    var pagination   = document.getElementById('ro-pagination');

    if (tableWrapper) {
      var tableViewDiv = document.createElement('div');
      tableViewDiv.id = 'ro-table-view';

      // Move table wrapper and pagination into the new container
      tableWrapper.parentNode.insertBefore(tableViewDiv, tableWrapper);
      tableViewDiv.appendChild(tableWrapper);
      if (pagination) {
        tableViewDiv.appendChild(pagination);
      }
    }
  }

  // Create kanban container if not present
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
