/**
 * Oregon Tires — Labor Time Tracker
 * Renders a labor tracking section inside the RO detail modal.
 *
 * Depends on: api(), showToast(), csrfToken from admin/index.html
 */

(function() {
'use strict';

function t(key, fallback) {
  return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
}

function formatDuration(minutes) {
  if (minutes === null || minutes === undefined) return '-';
  var m = parseInt(minutes, 10);
  var h = Math.floor(m / 60);
  var r = m % 60;
  if (h > 0) return h + 'h ' + r + 'm';
  return r + 'm';
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-';
  var d = new Date(dateStr);
  var lang = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
  return d.toLocaleDateString(lang, { month: 'short', day: 'numeric' }) + ' ' +
    d.toLocaleTimeString(lang, { hour: 'numeric', minute: '2-digit' });
}

function createTrashIcon() {
  var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('class', 'w-4 h-4');
  svg.setAttribute('fill', 'none');
  svg.setAttribute('stroke', 'currentColor');
  svg.setAttribute('viewBox', '0 0 24 24');
  var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  path.setAttribute('stroke-linecap', 'round');
  path.setAttribute('stroke-linejoin', 'round');
  path.setAttribute('stroke-width', '2');
  path.setAttribute('d', 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16');
  svg.appendChild(path);
  return svg;
}

// ─── LaborTracker ────────────────────────────────────────────────────────────

var LaborTracker = {
  roId: null,
  entries: [],
  totalHours: 0,
  billableHours: 0,
  employees: [],
  _intervals: [],

  init: function(roId) {
    this.cleanup();
    this.roId = roId;
    this.entries = [];
    this.totalHours = 0;
    this.billableHours = 0;
  },

  cleanup: function() {
    this._intervals.forEach(function(id) { clearInterval(id); });
    this._intervals = [];
  },

  loadEmployees: async function() {
    if (this.employees.length > 0) return;
    try {
      var json = await api('employees.php');
      this.employees = (json.data || []).filter(function(e) { return e.is_active == 1; });
    } catch (err) {
      this.employees = [];
    }
  },

  loadEntries: async function() {
    try {
      var json = await api('labor.php?ro_id=' + this.roId);
      var data = json.data || json;
      this.entries = data.entries || [];
      this.totalHours = data.total_hours || 0;
      this.billableHours = data.billable_hours || 0;
    } catch (err) {
      this.entries = [];
      this.totalHours = 0;
      this.billableHours = 0;
    }
  },

  clockIn: async function(employeeId, taskDescription, isBillable) {
    var payload = {
      repair_order_id: this.roId,
      employee_id: employeeId
    };
    if (taskDescription) payload.task_description = taskDescription;
    if (typeof isBillable !== 'undefined') payload.is_billable = isBillable ? 1 : 0;
    return await api('labor.php', { method: 'POST', body: payload });
  },

  clockOut: async function(entryId) {
    return await api('labor.php', { method: 'PUT', body: { id: entryId, clock_out: true } });
  },

  deleteEntry: async function(entryId) {
    return await api('labor.php?id=' + entryId, { method: 'DELETE' });
  },

  render: async function(containerId) {
    var self = this;
    await Promise.all([this.loadEntries(), this.loadEmployees()]);

    var container = document.getElementById(containerId);
    if (!container) return;
    container.textContent = '';

    // Section wrapper
    var section = document.createElement('div');
    section.className = 'mt-4';

    // Header row
    var headerRow = document.createElement('div');
    headerRow.className = 'flex items-center justify-between mb-3';

    var headerLeft = document.createElement('div');
    headerLeft.className = 'flex items-center gap-3';

    var title = document.createElement('h3');
    title.className = 'font-bold text-gray-900 dark:text-white';
    title.textContent = t('laborTime', 'Labor Time');
    headerLeft.appendChild(title);

    // Summary badges
    if (this.entries.length > 0) {
      var totalBadge = document.createElement('span');
      totalBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
      totalBadge.textContent = t('laborTotal', 'Total') + ': ' + this.totalHours.toFixed(1) + 'h';
      headerLeft.appendChild(totalBadge);

      var billBadge = document.createElement('span');
      billBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
      billBadge.textContent = t('laborBillable', 'Billable') + ': ' + this.billableHours.toFixed(1) + 'h';
      headerLeft.appendChild(billBadge);
    }

    headerRow.appendChild(headerLeft);

    // Clock In button
    var clockInBtn = document.createElement('button');
    clockInBtn.className = 'px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center gap-1.5';
    var clockIcon = document.createElement('span');
    clockIcon.textContent = '\u23F1';
    clockInBtn.appendChild(clockIcon);
    var clockText = document.createElement('span');
    clockText.textContent = t('laborClockIn', 'Clock In');
    clockInBtn.appendChild(clockText);
    clockInBtn.addEventListener('click', function() {
      self._showClockInForm(container, containerId);
    });
    headerRow.appendChild(clockInBtn);

    section.appendChild(headerRow);

    // Active entries (clocked in, not yet out) — highlight
    var activeEntries = this.entries.filter(function(e) { return !e.clock_out_at; });
    var completedEntries = this.entries.filter(function(e) { return e.clock_out_at; });

    if (activeEntries.length > 0) {
      var activeSection = document.createElement('div');
      activeSection.className = 'mb-3';

      activeEntries.forEach(function(entry) {
        activeSection.appendChild(self._renderActiveEntry(entry, containerId));
      });

      section.appendChild(activeSection);
    }

    // Completed entries table
    if (completedEntries.length > 0) {
      var table = document.createElement('div');
      table.className = 'border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden';

      // Table header
      var thead = document.createElement('div');
      thead.className = 'grid grid-cols-12 gap-1 bg-gray-50 dark:bg-gray-900/50 px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider';

      var cols = [
        { text: t('laborEmployee', 'Employee'), span: 2 },
        { text: t('laborTask', 'Task'), span: 3 },
        { text: t('laborClockInTime', 'Clock In'), span: 2 },
        { text: t('laborClockOutTime', 'Clock Out'), span: 2 },
        { text: t('laborDuration', 'Duration'), span: 1 },
        { text: t('laborBillableCol', 'Bill.'), span: 1 },
        { text: '', span: 1 }
      ];
      cols.forEach(function(col) {
        var th = document.createElement('div');
        th.className = 'col-span-' + col.span;
        th.textContent = col.text;
        thead.appendChild(th);
      });
      table.appendChild(thead);

      // Table body
      completedEntries.forEach(function(entry) {
        table.appendChild(self._renderCompletedRow(entry, containerId));
      });

      section.appendChild(table);
    }

    // Empty state
    if (this.entries.length === 0) {
      var empty = document.createElement('div');
      empty.className = 'text-center py-6 text-sm text-gray-400 dark:text-gray-500';
      empty.textContent = t('laborNoEntries', 'No labor entries yet. Click "Clock In" to start tracking time.');
      section.appendChild(empty);
    }

    container.appendChild(section);
  },

  _renderActiveEntry: function(entry, containerId) {
    var self = this;
    var card = document.createElement('div');
    card.className = 'border-2 border-green-400 dark:border-green-600 bg-green-50 dark:bg-green-900/20 rounded-xl p-4 mb-2 flex items-center justify-between';

    var left = document.createElement('div');
    left.className = 'flex items-center gap-3';

    // Pulsing dot
    var dot = document.createElement('span');
    dot.className = 'w-3 h-3 bg-green-500 rounded-full animate-pulse';
    left.appendChild(dot);

    var info = document.createElement('div');

    var empName = document.createElement('span');
    empName.className = 'font-bold text-gray-900 dark:text-white text-sm';
    empName.textContent = entry.employee_name || (t('laborEmployeeNum', 'Employee #') + entry.employee_id);
    info.appendChild(empName);

    if (entry.task_description) {
      var taskSpan = document.createElement('span');
      taskSpan.className = 'text-gray-500 dark:text-gray-400 text-sm ml-2';
      taskSpan.textContent = '\u2014 ' + entry.task_description;
      info.appendChild(taskSpan);
    }

    var timeInfo = document.createElement('div');
    timeInfo.className = 'text-xs text-gray-500 dark:text-gray-400 mt-0.5';
    timeInfo.textContent = t('laborStarted', 'Started') + ': ' + formatDateTime(entry.clock_in_at);

    // Live elapsed time
    var elapsed = document.createElement('span');
    elapsed.className = 'ml-2 font-medium text-green-700 dark:text-green-400';
    var updateElapsed = function() {
      var now = new Date();
      var start = new Date(entry.clock_in_at);
      var diffMs = now - start;
      var diffMin = Math.floor(diffMs / 60000);
      elapsed.textContent = '(' + formatDuration(diffMin) + ')';
    };
    updateElapsed();
    var elapsedInterval = setInterval(updateElapsed, 30000);
    self._intervals.push(elapsedInterval);
    card._elapsedInterval = elapsedInterval;
    timeInfo.appendChild(elapsed);

    info.appendChild(timeInfo);
    left.appendChild(info);
    card.appendChild(left);

    // Actions
    var actions = document.createElement('div');
    actions.className = 'flex items-center gap-2';

    if (entry.is_billable == 1) {
      var billTag = document.createElement('span');
      billTag.className = 'text-xs px-1.5 py-0.5 rounded bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200 font-medium';
      billTag.textContent = t('laborBillableTag', 'Billable');
      actions.appendChild(billTag);
    }

    var clockOutBtn = document.createElement('button');
    clockOutBtn.className = 'px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition';
    clockOutBtn.textContent = t('laborClockOut', 'Clock Out');
    clockOutBtn.addEventListener('click', async function() {
      try {
        await self.clockOut(entry.id);
        if (card._elapsedInterval) clearInterval(card._elapsedInterval);
        showToast(t('laborClockedOut', 'Clocked out successfully'));
        await self.render(containerId);
      } catch (err) {
        showToast(t('laborClockOutFail', 'Failed to clock out') + ': ' + (err.message || ''), true);
      }
    });
    actions.appendChild(clockOutBtn);

    card.appendChild(actions);
    return card;
  },

  _renderCompletedRow: function(entry, containerId) {
    var self = this;
    var row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-1 px-4 py-2.5 border-t border-gray-100 dark:border-gray-700/50 text-sm items-center hover:bg-gray-50 dark:hover:bg-gray-800/50 transition';

    // Employee (col-span-2)
    var empCell = document.createElement('div');
    empCell.className = 'col-span-2 font-medium text-gray-900 dark:text-white truncate';
    empCell.textContent = entry.employee_name || (t('laborEmployeeNum', 'Employee #') + entry.employee_id);
    empCell.title = entry.employee_name || '';
    row.appendChild(empCell);

    // Task (col-span-3)
    var taskCell = document.createElement('div');
    taskCell.className = 'col-span-3 text-gray-600 dark:text-gray-400 truncate';
    taskCell.textContent = entry.task_description || '-';
    taskCell.title = entry.task_description || '';
    row.appendChild(taskCell);

    // Clock In (col-span-2)
    var inCell = document.createElement('div');
    inCell.className = 'col-span-2 text-gray-500 dark:text-gray-400 text-xs';
    inCell.textContent = formatDateTime(entry.clock_in_at);
    row.appendChild(inCell);

    // Clock Out (col-span-2)
    var outCell = document.createElement('div');
    outCell.className = 'col-span-2 text-gray-500 dark:text-gray-400 text-xs';
    outCell.textContent = formatDateTime(entry.clock_out_at);
    row.appendChild(outCell);

    // Duration (col-span-1)
    var durCell = document.createElement('div');
    durCell.className = 'col-span-1 font-medium text-gray-700 dark:text-gray-300';
    durCell.textContent = formatDuration(entry.duration_minutes);
    row.appendChild(durCell);

    // Billable (col-span-1)
    var billCell = document.createElement('div');
    billCell.className = 'col-span-1';
    var billDot = document.createElement('span');
    if (entry.is_billable == 1) {
      billDot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-green-500';
      billDot.title = t('laborBillableTag', 'Billable');
    } else {
      billDot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600';
      billDot.title = t('laborNotBillable', 'Not billable');
    }
    billCell.appendChild(billDot);
    row.appendChild(billCell);

    // Actions (col-span-1)
    var actCell = document.createElement('div');
    actCell.className = 'col-span-1 flex justify-end';

    var delBtn = document.createElement('button');
    delBtn.className = 'text-red-400 hover:text-red-600 dark:text-red-500 dark:hover:text-red-400 transition p-1';
    delBtn.title = t('laborDelete', 'Delete');
    delBtn.appendChild(createTrashIcon());
    delBtn.addEventListener('click', async function() {
      if (!confirm(t('laborDeleteConfirm', 'Delete this labor entry?'))) return;
      try {
        await self.deleteEntry(entry.id);
        showToast(t('laborDeleted', 'Labor entry deleted'));
        await self.render(containerId);
      } catch (err) {
        showToast(t('laborDeleteFail', 'Failed to delete entry') + ': ' + (err.message || ''), true);
      }
    });
    actCell.appendChild(delBtn);
    row.appendChild(actCell);

    return row;
  },

  _showClockInForm: function(container, containerId) {
    var self = this;

    // Remove existing form if open
    var existing = document.getElementById('labor-clock-in-form');
    if (existing) { existing.remove(); return; }

    var form = document.createElement('div');
    form.id = 'labor-clock-in-form';
    form.className = 'border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 rounded-xl p-4 mb-4';

    var formTitle = document.createElement('h4');
    formTitle.className = 'font-bold text-gray-900 dark:text-white text-sm mb-3';
    formTitle.textContent = t('laborClockInTitle', 'Clock In Technician');
    form.appendChild(formTitle);

    var grid = document.createElement('div');
    grid.className = 'grid grid-cols-1 sm:grid-cols-3 gap-3';

    // Employee dropdown
    var empGroup = document.createElement('div');
    var empLabel = document.createElement('label');
    empLabel.className = 'block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1';
    empLabel.textContent = t('laborEmployee', 'Employee');
    empGroup.appendChild(empLabel);

    var empSelect = document.createElement('select');
    empSelect.className = 'w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm';

    var defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = t('laborSelectEmployee', 'Select employee...');
    empSelect.appendChild(defaultOpt);

    this.employees.forEach(function(emp) {
      var opt = document.createElement('option');
      opt.value = emp.id;
      opt.textContent = emp.name + (emp.role ? ' (' + emp.role + ')' : '');
      empSelect.appendChild(opt);
    });
    empGroup.appendChild(empSelect);
    grid.appendChild(empGroup);

    // Task description
    var taskGroup = document.createElement('div');
    var taskLabel = document.createElement('label');
    taskLabel.className = 'block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1';
    taskLabel.textContent = t('laborTask', 'Task') + ' (' + t('laborOptional', 'optional') + ')';
    taskGroup.appendChild(taskLabel);

    var taskInput = document.createElement('input');
    taskInput.type = 'text';
    taskInput.className = 'w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm';
    taskInput.placeholder = t('laborTaskPlaceholder', 'e.g. Brake pad replacement');
    taskInput.maxLength = 500;
    taskGroup.appendChild(taskInput);
    grid.appendChild(taskGroup);

    // Billable checkbox + actions
    var actionGroup = document.createElement('div');
    actionGroup.className = 'flex items-end gap-3';

    var billWrap = document.createElement('label');
    billWrap.className = 'flex items-center gap-2 pb-2 cursor-pointer';
    var billCheck = document.createElement('input');
    billCheck.type = 'checkbox';
    billCheck.checked = true;
    billCheck.className = 'rounded border-gray-300 text-green-600 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700';
    var billText = document.createElement('span');
    billText.className = 'text-sm text-gray-700 dark:text-gray-300';
    billText.textContent = t('laborBillableTag', 'Billable');
    billWrap.appendChild(billCheck);
    billWrap.appendChild(billText);
    actionGroup.appendChild(billWrap);

    var submitBtn = document.createElement('button');
    submitBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition mb-0.5';
    submitBtn.textContent = t('laborStartClock', 'Start');
    submitBtn.addEventListener('click', async function() {
      var empId = parseInt(empSelect.value, 10);
      if (!empId) {
        showToast(t('laborSelectEmployeeFirst', 'Please select an employee'), true);
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = '...';
      try {
        await self.clockIn(empId, taskInput.value.trim(), billCheck.checked);
        showToast(t('laborClockedIn', 'Clocked in successfully'));
        form.remove();
        await self.render(containerId);
      } catch (err) {
        showToast(t('laborClockInFail', 'Failed to clock in') + ': ' + (err.message || ''), true);
        submitBtn.disabled = false;
        submitBtn.textContent = t('laborStartClock', 'Start');
      }
    });
    actionGroup.appendChild(submitBtn);

    var cancelBtn = document.createElement('button');
    cancelBtn.className = 'px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm mb-0.5';
    cancelBtn.textContent = t('laborCancel', 'Cancel');
    cancelBtn.addEventListener('click', function() { form.remove(); });
    actionGroup.appendChild(cancelBtn);

    grid.appendChild(actionGroup);
    form.appendChild(grid);

    // Insert form at the top of the container, after the header
    var firstChild = container.querySelector('.mt-4');
    if (firstChild && firstChild.firstChild && firstChild.firstChild.nextSibling) {
      firstChild.insertBefore(form, firstChild.firstChild.nextSibling);
    } else {
      container.insertBefore(form, container.firstChild);
    }
  }
};

// Expose globally
window.LaborTracker = LaborTracker;

// ─── Cross-RO Labor Dashboard (for dedicated Labor tab) ─────────────────────

var _laborTimers = [];
function _clearLaborTimers() {
  _laborTimers.forEach(function(id) { clearInterval(id); });
  _laborTimers = [];
}

function _el(tag, cls, text) {
  var e = document.createElement(tag);
  if (cls) e.className = cls;
  if (text) e.textContent = text;
  return e;
}

var _laborDateRange = null;
var _laborView = 'job_board'; // 'job_board' or 'reports'
var _jobBoardFilter = '';

// ─── Mini status stepper for job board cards ──────────────────────────────────
var JB_STATUSES = ['intake','check_in','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced'];
var JB_COLORS = { intake:'#3b82f6', check_in:'#06b6d4', diagnosis:'#8b5cf6', estimate_pending:'#f59e0b', pending_approval:'#f59e0b', approved:'#22c55e', in_progress:'#16a34a', on_hold:'#991b1b', waiting_parts:'#f97316', ready:'#14b8a6', completed:'#6b7280', invoiced:'#0d9488' };

function renderMiniStepper(currentStatus) {
  var wrap = _el('div', 'flex items-center gap-0.5 mb-2');
  var idx = JB_STATUSES.indexOf(currentStatus);
  JB_STATUSES.forEach(function(s, i) {
    if (i > 0) {
      var line = _el('div', '');
      line.style.cssText = 'width:8px;height:2px;background:' + (i <= idx ? JB_COLORS[currentStatus] || '#16a34a' : '#d1d5db') + ';';
      wrap.appendChild(line);
    }
    var dot = _el('div', '');
    var isCurrent = i === idx;
    var isPast = i < idx;
    if (isCurrent) {
      dot.style.cssText = 'width:10px;height:10px;border-radius:50%;background:' + (JB_COLORS[s] || '#16a34a') + ';box-shadow:0 0 0 2px rgba(22,163,106,0.3);flex-shrink:0;';
    } else if (isPast) {
      dot.style.cssText = 'width:6px;height:6px;border-radius:50%;background:' + (JB_COLORS[currentStatus] || '#16a34a') + ';flex-shrink:0;';
    } else {
      dot.style.cssText = 'width:6px;height:6px;border-radius:50%;background:#d1d5db;flex-shrink:0;';
    }
    wrap.appendChild(dot);
  });
  return wrap;
}

function formatElapsed(startStr) {
  if (!startStr) return '-';
  var diff = Math.floor((Date.now() - new Date(startStr).getTime()) / 60000);
  if (diff < 0) diff = 0;
  var h = Math.floor(diff / 60); var m = diff % 60;
  return (h > 0 ? h + 'h ' : '') + m + 'm';
}

// ─── JOB BOARD VIEW ─────────────────────────────────────────────────────────

async function loadJobBoard() {
  var container = document.getElementById('labor-container');
  if (!container) return;
  _clearLaborTimers();
  container.textContent = '';
  container.appendChild(_el('div', 'text-gray-400 text-center py-12', t('laborSummaryLoading', 'Loading job board...')));

  try {
    var res = await fetch('/api/admin/labor.php?job_board=1', { credentials: 'include' });
    var json = await res.json();
    container.textContent = '';
    if (!json.success) { container.appendChild(_el('p', 'text-red-500 text-center py-8', json.error || 'Error')); return; }

    var data = json.data;
    var ros = data.ros || [];
    var summary = data.summary || {};
    var completedToday = data.completed_today || [];

    // ── View Toggle ──
    var viewToggle = _el('div', 'flex items-center gap-2 mb-4');
    ['job_board', 'reports'].forEach(function(v) {
      var btn = _el('button', 'px-4 py-2 rounded-lg text-sm font-medium transition ' +
        (v === _laborView ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'),
        v === 'job_board' ? t('laborJobBoard', 'Job Board') : t('laborReports', 'Reports'));
      btn.addEventListener('click', function() {
        _laborView = v;
        if (v === 'job_board') loadJobBoard();
        else loadLaborReports();
      });
      viewToggle.appendChild(btn);
    });
    container.appendChild(viewToggle);

    // ── Summary Stats ──
    var stats = _el('div', 'grid grid-cols-3 gap-4 mb-4');
    [
      { label: t('laborJbActiveRos', 'Active ROs'), value: summary.active_ros || 0, pulse: (summary.active_ros || 0) > 0 },
      { label: t('laborJbTechsWorking', 'Techs Working'), value: summary.techs_working || 0 },
      { label: t('laborJbHoursToday', 'Hours Today'), value: (summary.hours_today || 0) + 'h' }
    ].forEach(function(c) {
      var card = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
      var row = _el('div', 'flex items-center gap-2 mb-1');
      if (c.pulse) row.appendChild(_el('span', 'w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse'));
      row.appendChild(_el('span', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider', c.label));
      card.appendChild(row);
      card.appendChild(_el('div', 'text-2xl font-bold text-gray-900 dark:text-white', String(c.value)));
      stats.appendChild(card);
    });
    container.appendChild(stats);

    // ── Filters ──
    var filterBar = _el('div', 'flex flex-wrap items-center gap-2 mb-4');
    var filterStatuses = [
      { key: '', label: t('laborJbAllActive', 'All Active') },
      { key: 'check_in', label: t('roStatusCheckIn', 'Checked In') },
      { key: 'in_progress', label: t('roStatusInProgress', 'In Progress') },
      { key: 'waiting', label: t('laborJbWaiting', 'Waiting') },
      { key: 'ready', label: t('roStatusReady', 'Ready') },
    ];
    filterStatuses.forEach(function(f) {
      var active = _jobBoardFilter === f.key;
      var btn = _el('button', 'px-3 py-1.5 rounded-lg text-xs font-medium transition ' +
        (active ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'), f.label);
      btn.addEventListener('click', function() { _jobBoardFilter = f.key; loadJobBoard(); });
      filterBar.appendChild(btn);
    });
    container.appendChild(filterBar);

    // ── Filter ROs ──
    var filteredRos = ros;
    if (_jobBoardFilter === 'waiting') {
      filteredRos = ros.filter(function(r) { return r.status === 'waiting_parts' || r.status === 'on_hold'; });
    } else if (_jobBoardFilter) {
      filteredRos = ros.filter(function(r) { return r.status === _jobBoardFilter; });
    }

    // ── Job Cards ──
    if (filteredRos.length > 0) {
      var cardGrid = _el('div', 'grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6');

      filteredRos.forEach(function(ro) {
        var card = _el('div', 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 cursor-pointer hover:shadow-md transition');
        card.addEventListener('click', function() { if (typeof viewRoDetail === 'function') viewRoDetail(ro.id); });

        // Mini stepper
        card.appendChild(renderMiniStepper(ro.status));

        // RO# + Customer
        var topRow = _el('div', 'flex items-center justify-between mb-1');
        topRow.appendChild(_el('span', 'font-bold text-green-700 dark:text-green-400 text-sm', ro.ro_number));
        var custName = ((ro.first_name || '') + ' ' + (ro.last_name || '')).trim();
        topRow.appendChild(_el('span', 'text-sm font-medium text-gray-700 dark:text-gray-300', custName));
        card.appendChild(topRow);

        // Vehicle + Plate
        var vehRow = _el('div', 'flex items-center justify-between mb-2');
        var vehicle = [ro.vehicle_year, ro.vehicle_make, ro.vehicle_model].filter(Boolean).join(' ');
        vehRow.appendChild(_el('span', 'text-xs text-gray-500 dark:text-gray-400', vehicle || '-'));
        if (ro.license_plate) {
          vehRow.appendChild(_el('span', 'text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400', 'Plate: ' + ro.license_plate));
        }
        card.appendChild(vehRow);

        // Timers row
        var timerRow = _el('div', 'flex items-center gap-4 mb-2');
        if (ro.checked_in_at) {
          var visitWrap = _el('div', 'flex items-center gap-1');
          visitWrap.appendChild(_el('span', 'w-2 h-2 rounded-full bg-green-500 animate-pulse'));
          visitWrap.appendChild(_el('span', 'text-xs font-bold text-green-700 dark:text-green-400', t('roTimerVisit', 'VISIT') + ':'));
          var visitVal = _el('span', 'text-xs font-bold text-green-700 dark:text-green-400 jb-timer');
          visitVal.setAttribute('data-start', ro.checked_in_at);
          visitVal.textContent = formatElapsed(ro.checked_in_at);
          visitWrap.appendChild(visitVal);
          timerRow.appendChild(visitWrap);
        }
        var repairStart = null;
        if (ro.active_labor && ro.active_labor.length > 0) {
          repairStart = ro.active_labor[0].clock_in_at;
        } else if (ro.service_started_at && !ro.service_ended_at) {
          repairStart = ro.service_started_at;
        }
        if (repairStart) {
          var repairWrap = _el('div', 'flex items-center gap-1');
          repairWrap.appendChild(_el('span', 'w-2 h-2 rounded-full bg-orange-500 animate-pulse'));
          repairWrap.appendChild(_el('span', 'text-xs font-bold text-orange-600 dark:text-orange-400', t('roTimerRepair', 'REPAIR') + ':'));
          var repairVal = _el('span', 'text-xs font-bold text-orange-600 dark:text-orange-400 jb-timer');
          repairVal.setAttribute('data-start', repairStart);
          repairVal.textContent = formatElapsed(repairStart);
          repairWrap.appendChild(repairVal);
          timerRow.appendChild(repairWrap);
        }
        card.appendChild(timerRow);

        // Tech + Next action row
        var bottomRow = _el('div', 'flex items-center justify-between');
        var techInfo = _el('div', 'flex items-center gap-2');
        if (ro.active_labor && ro.active_labor.length > 0) {
          ro.active_labor.forEach(function(l) {
            var techBadge = _el('span', 'text-xs px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-medium', l.employee_name);
            techInfo.appendChild(techBadge);
          });
        } else if (ro.assigned_employee_name) {
          techInfo.appendChild(_el('span', 'text-xs text-gray-500', t('laborAssigned', 'Assigned') + ': ' + ro.assigned_employee_name));
        }
        bottomRow.appendChild(techInfo);

        if (ro.next_action) {
          var nextBtn = _el('span', 'text-[10px] font-bold px-2 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400', '\u2192 ' + ro.next_action);
          bottomRow.appendChild(nextBtn);
        }
        card.appendChild(bottomRow);

        cardGrid.appendChild(card);
      });
      container.appendChild(cardGrid);
    } else {
      container.appendChild(_el('div', 'text-center py-8 text-gray-400', t('laborJbNoRos', 'No active repair orders')));
    }

    // ── Completed Today (collapsed) ──
    if (completedToday.length > 0) {
      var details = document.createElement('details');
      details.className = 'border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden';
      var summary2 = document.createElement('summary');
      summary2.className = 'px-4 py-3 bg-gray-50 dark:bg-gray-900/50 cursor-pointer text-sm font-medium text-gray-600 dark:text-gray-400';
      summary2.textContent = t('laborJbCompletedToday', 'Completed Today') + ' (' + completedToday.length + ')';
      details.appendChild(summary2);
      var cList = _el('div', 'p-3 space-y-2');
      completedToday.forEach(function(r) {
        var row = _el('div', 'flex items-center justify-between text-sm px-2 py-1');
        row.appendChild(_el('span', 'font-medium text-green-700 dark:text-green-400', r.ro_number));
        var cust = ((r.first_name || '') + ' ' + (r.last_name || '')).trim();
        row.appendChild(_el('span', 'text-gray-600 dark:text-gray-400', cust));
        var veh = [r.vehicle_year, r.vehicle_make, r.vehicle_model].filter(Boolean).join(' ');
        row.appendChild(_el('span', 'text-xs text-gray-400', veh));
        row.appendChild(_el('span', 'text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500', r.status));
        cList.appendChild(row);
      });
      details.appendChild(cList);
      container.appendChild(details);
    }

    // Single timer update interval for all job board timers
    function updateAllJbTimers() {
      var timers = document.querySelectorAll('.jb-timer');
      timers.forEach(function(el) {
        var start = el.getAttribute('data-start');
        if (start) el.textContent = formatElapsed(start);
      });
    }
    _laborTimers.push(setInterval(updateAllJbTimers, 30000));

  } catch (err) {
    container.textContent = '';
    container.appendChild(_el('p', 'text-red-500 text-center py-8', t('laborSummaryError', 'Error loading job board')));
    console.error('Job board error:', err);
  }
}

// ─── REPORTS VIEW (existing summary/report UI) ──────────────────────────────

async function loadLaborReports() {
  var container = document.getElementById('labor-container');
  if (!container) return;
  _clearLaborTimers();
  container.textContent = '';

  // View Toggle
  var viewToggle = _el('div', 'flex items-center gap-2 mb-4');
  ['job_board', 'reports'].forEach(function(v) {
    var btn = _el('button', 'px-4 py-2 rounded-lg text-sm font-medium transition ' +
      (v === _laborView ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'),
      v === 'job_board' ? t('laborJobBoard', 'Job Board') : t('laborReports', 'Reports'));
    btn.addEventListener('click', function() {
      _laborView = v;
      if (v === 'job_board') loadJobBoard();
      else loadLaborReports();
    });
    viewToggle.appendChild(btn);
  });
  container.appendChild(viewToggle);

  container.appendChild(_el('div', 'text-gray-400 text-center py-12', t('laborSummaryLoading', 'Loading reports...')));

  try {
    var url = '/api/admin/labor.php?summary=1';
    if (_laborDateRange) url += '&start_date=' + _laborDateRange.start + '&end_date=' + _laborDateRange.end;
    var res = await fetch(url, { credentials: 'include' });
    var json = await res.json();

    // Remove loading
    var loadingEl = container.querySelector('.text-gray-400');
    if (loadingEl) loadingEl.remove();

    if (!json.success) { container.appendChild(_el('p', 'text-red-500 text-center py-8', json.error || 'Error')); return; }

    var d = json.data;
    var totals = d.totals || {};
    var employees = d.employees || [];
    var recent = d.recent || [];

    // Stats
    var stats = _el('div', 'grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6');
    [
      { label: t('laborStatActive', 'Active Clocks'), value: totals.active_clocks || 0 },
      { label: t('laborStatTotalHrs', 'Total Hours'), value: totals.total_hours ? totals.total_hours.toFixed(1) + 'h' : '0h' },
      { label: t('laborStatBillable', 'Billable Hours'), value: totals.billable_hours ? totals.billable_hours.toFixed(1) + 'h' : '0h' },
      { label: t('laborStatEntries', 'Total Entries'), value: totals.total_entries || 0 }
    ].forEach(function(c) {
      var card = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
      card.appendChild(_el('div', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1', c.label));
      card.appendChild(_el('div', 'text-2xl font-bold text-gray-900 dark:text-white', String(c.value)));
      stats.appendChild(card);
    });
    container.appendChild(stats);

    // Date range filter
    var filterBar = _el('div', 'flex flex-wrap items-center gap-2 mb-6');
    function makePreset(label, range) {
      var isActive = (!range && !_laborDateRange) || (range && _laborDateRange && _laborDateRange.start === range.start && _laborDateRange.end === range.end);
      var btn = _el('button', 'px-3 py-1.5 rounded-lg text-xs font-medium transition ' + (isActive ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'), label);
      btn.addEventListener('click', function() { _laborDateRange = range; loadLaborReports(); });
      return btn;
    }
    var today = new Date().toISOString().slice(0, 10);
    var dow = new Date().getDay();
    var mondayOff = dow === 0 ? -6 : 1 - dow;
    var mon = new Date(); mon.setDate(mon.getDate() + mondayOff);
    var sun = new Date(mon); sun.setDate(sun.getDate() + 6);
    filterBar.appendChild(makePreset(t('laborToday', 'Today'), { start: today, end: today }));
    filterBar.appendChild(makePreset(t('laborThisWeek', 'This Week'), { start: mon.toISOString().slice(0, 10), end: sun.toISOString().slice(0, 10) }));
    filterBar.appendChild(makePreset(t('laborThisMonth', 'This Month'), { start: today.slice(0, 8) + '01', end: today }));
    filterBar.appendChild(makePreset(t('laborAllTime', 'All Time'), null));
    container.appendChild(filterBar);

    // Employee summary table
    if (employees.length > 0) {
      var summarySection = _el('div', 'mb-6');
      summarySection.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm mb-3', t('laborEmployeeSummary', 'Employee Summary')));
      var table = _el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700');
      var thead = _el('div', 'grid grid-cols-6 gap-2 bg-gray-50 dark:bg-gray-900/50 px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider');
      [t('laborSummaryEmployee', 'Employee'), t('laborSummaryTotalHrs', 'Total Hours'), t('laborSummaryBillableHrs', 'Billable'), t('laborEfficiency', 'Efficiency'), t('laborSummaryActiveClocks', 'Active'), t('laborSummaryRoCount', 'ROs')].forEach(function(txt) { thead.appendChild(_el('div', '', txt)); });
      table.appendChild(thead);
      employees.forEach(function(row) {
        var tr = _el('div', 'grid grid-cols-6 gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 text-sm items-center hover:bg-gray-50 dark:hover:bg-gray-800/50 transition');
        tr.appendChild(_el('div', 'font-medium text-gray-900 dark:text-white', row.employee_name || 'Unknown'));
        tr.appendChild(_el('div', 'text-gray-700 dark:text-gray-300', (row.total_hours || 0).toFixed(1) + 'h'));
        tr.appendChild(_el('div', 'text-green-600 dark:text-green-400 font-medium', (row.billable_hours || 0).toFixed(1) + 'h'));
        var eff = row.total_hours > 0 ? Math.round((row.billable_hours / row.total_hours) * 100) : 0;
        var effCls = eff >= 80 ? 'text-green-600' : eff >= 50 ? 'text-amber-600' : 'text-red-600';
        tr.appendChild(_el('div', 'font-bold ' + effCls, eff + '%'));
        tr.appendChild(_el('div', row.active_count > 0 ? 'text-green-700 font-medium' : 'text-gray-400', row.active_count > 0 ? String(row.active_count) : '-'));
        tr.appendChild(_el('div', 'text-gray-600 dark:text-gray-400', String(row.ro_count || 0)));
        table.appendChild(tr);
      });
      summarySection.appendChild(table);
      container.appendChild(summarySection);
    }

    // Recent entries
    if (recent.length > 0) {
      var recentSection = _el('div', 'mb-6');
      recentSection.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm mb-3', t('laborRecentEntries', 'Recent Entries')));
      var rTable = _el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700');
      recent.forEach(function(entry) {
        var row = _el('div', 'border-b border-gray-100 dark:border-gray-700/50 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition');
        var topRow = _el('div', 'flex items-center justify-between mb-1');
        var leftTop = _el('div', 'flex items-center gap-2 flex-wrap');
        leftTop.appendChild(_el('span', 'font-semibold text-gray-900 dark:text-white text-sm', entry.employee_name));
        leftTop.appendChild(_el('span', 'text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-500 px-1.5 py-0.5 rounded', entry.ro_number));
        topRow.appendChild(leftTop);
        topRow.appendChild(_el('span', 'font-bold text-gray-800 dark:text-gray-200 text-sm', formatDuration(entry.duration_minutes)));
        row.appendChild(topRow);
        var details = [];
        var cust = [entry.customer_first, entry.customer_last].filter(Boolean).join(' ');
        var veh = [entry.vehicle_year, entry.vehicle_make, entry.vehicle_model].filter(Boolean).join(' ');
        if (cust) details.push(cust);
        if (veh) details.push(veh);
        if (details.length) row.appendChild(_el('div', 'text-xs text-gray-500 mb-1', details.join(' \u2022 ')));
        var timeRow = _el('div', 'flex items-center gap-2 text-[10px] text-gray-400');
        timeRow.appendChild(_el('span', '', 'In: ' + formatDateTime(entry.clock_in_at)));
        timeRow.appendChild(_el('span', '', '\u2192'));
        timeRow.appendChild(_el('span', '', 'Out: ' + formatDateTime(entry.clock_out_at)));
        if (entry.is_billable) timeRow.appendChild(_el('span', 'px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-medium', t('laborBillableTag', 'Billable')));
        row.appendChild(timeRow);
        rTable.appendChild(row);
      });
      recentSection.appendChild(rTable);
      container.appendChild(recentSection);
    }

    if (employees.length === 0 && recent.length === 0) {
      container.appendChild(_el('div', 'text-center py-12 text-gray-400', t('laborEmptyTitle', 'No labor entries yet')));
    }

  } catch (err) {
    container.appendChild(_el('p', 'text-red-500 text-center py-8', t('laborSummaryError', 'Error loading reports')));
  }
}

// ─── Entry point (called by tab switch + refresh button) ────────────────────
window.loadLaborSummary = function(dateRange) {
  if (typeof dateRange !== 'undefined') _laborDateRange = dateRange;
  if (_laborView === 'job_board') {
    loadJobBoard();
  } else {
    loadLaborReports();
  }
};

})();
