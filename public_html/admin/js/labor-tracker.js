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

  init: function(roId) {
    this.roId = roId;
    this.entries = [];
    this.totalHours = 0;
    this.billableHours = 0;
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

// Intervals for live elapsed timers
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

window.loadLaborSummary = async function() {
  var container = document.getElementById('labor-container');
  if (!container) return;
  _clearLaborTimers();
  container.textContent = '';

  var loading = _el('div', 'text-gray-400 dark:text-gray-500 text-center py-12', t('laborSummaryLoading', 'Loading labor data...'));
  container.appendChild(loading);

  try {
    var res = await fetch('/api/admin/labor.php?summary=1', { credentials: 'include' });
    var json = await res.json();
    container.textContent = '';

    if (!json.success) {
      container.appendChild(_el('p', 'text-red-500 text-center py-8', json.error || 'Error'));
      return;
    }

    var d = json.data;
    var totals = d.totals || {};
    var active = d.active || [];
    var recent = d.recent || [];
    var employees = d.employees || [];
    var availEmps = d.available_employees || [];
    var availROs = d.available_ros || [];

    // ── Stat Cards ──────────────────────────────────────────────────────
    var stats = _el('div', 'grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6');

    var cards = [
      { label: t('laborStatActive', 'Active Clocks'), value: active.length, color: 'green', pulse: active.length > 0 },
      { label: t('laborStatTotalHrs', 'Total Hours'), value: totals.total_hours ? totals.total_hours.toFixed(1) + 'h' : '0h', color: 'sky' },
      { label: t('laborStatBillable', 'Billable Hours'), value: totals.billable_hours ? totals.billable_hours.toFixed(1) + 'h' : '0h', color: 'emerald' },
      { label: t('laborStatEntries', 'Total Entries'), value: totals.total_entries || 0, color: 'gray' }
    ];

    cards.forEach(function(c) {
      var card = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');

      var labelRow = _el('div', 'flex items-center gap-2 mb-1');
      if (c.pulse) {
        labelRow.appendChild(_el('span', 'w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse'));
      }
      labelRow.appendChild(_el('span', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider', c.label));
      card.appendChild(labelRow);

      card.appendChild(_el('div', 'text-2xl font-bold text-gray-900 dark:text-white', String(c.value)));
      stats.appendChild(card);
    });
    container.appendChild(stats);

    // ── Quick Clock-In ──────────────────────────────────────────────────
    if (availEmps.length > 0 && availROs.length > 0) {
      var clockSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 mb-6');

      var clockHeader = _el('div', 'flex items-center justify-between mb-4');
      clockHeader.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm', '\u23F1 ' + t('laborQuickClockIn', 'Quick Clock In')));
      clockSection.appendChild(clockHeader);

      var form = _el('div', 'grid grid-cols-1 sm:grid-cols-4 gap-3 items-end');

      // Employee select
      var empGroup = _el('div', '');
      empGroup.appendChild(_el('label', 'block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1', t('laborEmployee', 'Employee')));
      var empSel = _el('select', 'w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm');
      var defOpt = _el('option', '', t('laborSelectEmployee', 'Select employee...'));
      defOpt.value = '';
      empSel.appendChild(defOpt);
      availEmps.forEach(function(emp) {
        var opt = _el('option', '', emp.name);
        opt.value = emp.id;
        empSel.appendChild(opt);
      });
      empGroup.appendChild(empSel);
      form.appendChild(empGroup);

      // RO select
      var roGroup = _el('div', '');
      roGroup.appendChild(_el('label', 'block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1', t('laborRo', 'Repair Order')));
      var roSel = _el('select', 'w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm');
      var roDefOpt = _el('option', '', t('laborSelectRo', 'Select RO...'));
      roDefOpt.value = '';
      roSel.appendChild(roDefOpt);
      availROs.forEach(function(ro) {
        var opt = _el('option', '', ro.ro_number + ' (' + ro.status + ')');
        opt.value = ro.id;
        roSel.appendChild(opt);
      });
      roGroup.appendChild(roSel);
      form.appendChild(roGroup);

      // Task input
      var taskGroup = _el('div', '');
      taskGroup.appendChild(_el('label', 'block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1', t('laborTask', 'Task') + ' (' + t('laborOptional', 'optional') + ')'));
      var taskInput = _el('input', 'w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm');
      taskInput.type = 'text';
      taskInput.placeholder = t('laborTaskPlaceholder', 'e.g. Brake pad replacement');
      taskInput.maxLength = 500;
      taskGroup.appendChild(taskInput);
      form.appendChild(taskGroup);

      // Submit button
      var btnGroup = _el('div', '');
      var clockBtn = _el('button', 'w-full px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center justify-center gap-2');
      clockBtn.appendChild(_el('span', '', '\u25B6'));
      clockBtn.appendChild(_el('span', '', t('laborClockIn', 'Clock In')));
      clockBtn.addEventListener('click', async function() {
        var eId = parseInt(empSel.value, 10);
        var rId = parseInt(roSel.value, 10);
        if (!eId) { showToast(t('laborSelectEmployeeFirst', 'Select an employee'), true); return; }
        if (!rId) { showToast(t('laborSelectRoFirst', 'Select a repair order'), true); return; }
        clockBtn.disabled = true;
        clockBtn.style.opacity = '0.5';
        try {
          await api('labor.php', { method: 'POST', body: {
            repair_order_id: rId, employee_id: eId,
            task_description: taskInput.value.trim(), is_billable: 1
          }});
          showToast(t('laborClockedIn', 'Clocked in successfully'));
          loadLaborSummary();
        } catch (err) {
          showToast(t('laborClockInFail', 'Failed to clock in') + ': ' + (err.message || ''), true);
          clockBtn.disabled = false;
          clockBtn.style.opacity = '1';
        }
      });
      btnGroup.appendChild(clockBtn);
      form.appendChild(btnGroup);

      clockSection.appendChild(form);
      container.appendChild(clockSection);
    }

    // ── Active Clocks ───────────────────────────────────────────────────
    if (active.length > 0) {
      var activeSection = _el('div', 'mb-6');
      activeSection.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm mb-3', '\uD83D\uDFE2 ' + t('laborActiveClocks', 'Active Clocks') + ' (' + active.length + ')'));

      var activeGrid = _el('div', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3');

      active.forEach(function(entry) {
        var card = _el('div', 'border-2 border-green-400 dark:border-green-600 bg-green-50 dark:bg-green-900/20 rounded-xl p-4');

        // Top row: name + RO
        var top = _el('div', 'flex items-center justify-between mb-2');
        var nameWrap = _el('div', 'flex items-center gap-2');
        nameWrap.appendChild(_el('span', 'w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse'));
        nameWrap.appendChild(_el('span', 'font-bold text-gray-900 dark:text-white text-sm', entry.employee_name));
        top.appendChild(nameWrap);
        top.appendChild(_el('span', 'text-xs font-mono bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded', entry.ro_number));
        card.appendChild(top);

        // Task
        if (entry.task_description) {
          card.appendChild(_el('p', 'text-sm text-gray-600 dark:text-gray-400 mb-2', entry.task_description));
        }

        // Elapsed time + clock out
        var bottom = _el('div', 'flex items-center justify-between');
        var elapsed = _el('span', 'text-sm font-medium text-green-700 dark:text-green-400');
        var updateElapsed = function() {
          var now = new Date();
          var start = new Date(entry.clock_in_at);
          var diffMin = Math.floor((now - start) / 60000);
          var h = Math.floor(diffMin / 60);
          var m = diffMin % 60;
          elapsed.textContent = (h > 0 ? h + 'h ' : '') + m + 'm' + ' \u2014 ' + t('laborStarted', 'Started') + ' ' + formatDateTime(entry.clock_in_at);
        };
        updateElapsed();
        _laborTimers.push(setInterval(updateElapsed, 30000));
        bottom.appendChild(elapsed);

        var outBtn = _el('button', 'px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700 transition', t('laborClockOut', 'Clock Out'));
        outBtn.addEventListener('click', async function() {
          outBtn.disabled = true;
          outBtn.textContent = '...';
          try {
            await api('labor.php', { method: 'PUT', body: { id: entry.id, clock_out: true } });
            showToast(t('laborClockedOut', 'Clocked out successfully'));
            loadLaborSummary();
          } catch (err) {
            showToast(t('laborClockOutFail', 'Failed to clock out') + ': ' + (err.message || ''), true);
            outBtn.disabled = false;
            outBtn.textContent = t('laborClockOut', 'Clock Out');
          }
        });
        bottom.appendChild(outBtn);
        card.appendChild(bottom);

        activeGrid.appendChild(card);
      });

      activeSection.appendChild(activeGrid);
      container.appendChild(activeSection);
    }

    // ── Employee Summary Table ──────────────────────────────────────────
    if (employees.length > 0) {
      var summarySection = _el('div', 'mb-6');
      summarySection.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm mb-3', '\uD83D\uDCCA ' + t('laborEmployeeSummary', 'Employee Summary')));

      var table = _el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700');

      var thead = _el('div', 'grid grid-cols-5 gap-2 bg-gray-50 dark:bg-gray-900/50 px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider');
      [t('laborSummaryEmployee', 'Employee'), t('laborSummaryTotalHrs', 'Total Hours'), t('laborSummaryBillableHrs', 'Billable'), t('laborSummaryActiveClocks', 'Active'), t('laborSummaryRoCount', 'ROs')].forEach(function(text) {
        thead.appendChild(_el('div', '', text));
      });
      table.appendChild(thead);

      employees.forEach(function(row) {
        var tr = _el('div', 'grid grid-cols-5 gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 text-sm items-center hover:bg-gray-50 dark:hover:bg-gray-800/50 transition');

        tr.appendChild(_el('div', 'font-medium text-gray-900 dark:text-white', row.employee_name || 'Unknown'));
        tr.appendChild(_el('div', 'text-gray-700 dark:text-gray-300', (row.total_hours || 0).toFixed(1) + 'h'));
        tr.appendChild(_el('div', 'text-green-600 dark:text-green-400 font-medium', (row.billable_hours || 0).toFixed(1) + 'h'));

        var activeCell = _el('div', '');
        if (row.active_count > 0) {
          activeCell.appendChild(_el('span', 'inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1'));
          var actText = _el('span', 'text-green-700 dark:text-green-400 font-medium');
          actText.textContent = row.active_count;
          activeCell.appendChild(actText);
        } else {
          activeCell.className = 'text-gray-400';
          activeCell.textContent = '-';
        }
        tr.appendChild(activeCell);

        tr.appendChild(_el('div', 'text-gray-600 dark:text-gray-400', String(row.ro_count || 0)));

        table.appendChild(tr);
      });

      summarySection.appendChild(table);
      container.appendChild(summarySection);
    }

    // ── Recent Entries ──────────────────────────────────────────────────
    if (recent.length > 0) {
      var recentSection = _el('div', 'mb-6');
      recentSection.appendChild(_el('h3', 'font-semibold text-gray-900 dark:text-white text-sm mb-3', '\uD83D\uDD52 ' + t('laborRecentEntries', 'Recent Entries')));

      var rTable = _el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700');

      var rHead = _el('div', 'grid grid-cols-12 gap-1 bg-gray-50 dark:bg-gray-900/50 px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider');
      var rCols = [
        { text: t('laborEmployee', 'Employee'), span: 2 },
        { text: t('laborRo', 'RO'), span: 2 },
        { text: t('laborTask', 'Task'), span: 3 },
        { text: t('laborClockInTime', 'In'), span: 2 },
        { text: t('laborClockOutTime', 'Out'), span: 2 },
        { text: t('laborDuration', 'Dur.'), span: 1 }
      ];
      rCols.forEach(function(col) {
        rHead.appendChild(_el('div', 'col-span-' + col.span, col.text));
      });
      rTable.appendChild(rHead);

      recent.forEach(function(entry) {
        var row = _el('div', 'grid grid-cols-12 gap-1 px-5 py-2.5 border-t border-gray-100 dark:border-gray-700/50 text-sm items-center hover:bg-gray-50 dark:hover:bg-gray-800/50 transition');

        var empCell = _el('div', 'col-span-2 font-medium text-gray-900 dark:text-white truncate', entry.employee_name);
        empCell.title = entry.employee_name;
        row.appendChild(empCell);

        row.appendChild(_el('div', 'col-span-2 text-xs font-mono text-gray-500 dark:text-gray-400', entry.ro_number));

        var taskCell = _el('div', 'col-span-3 text-gray-600 dark:text-gray-400 truncate', entry.task_description || '-');
        taskCell.title = entry.task_description || '';
        row.appendChild(taskCell);

        row.appendChild(_el('div', 'col-span-2 text-gray-500 dark:text-gray-400 text-xs', formatDateTime(entry.clock_in_at)));
        row.appendChild(_el('div', 'col-span-2 text-gray-500 dark:text-gray-400 text-xs', formatDateTime(entry.clock_out_at)));
        row.appendChild(_el('div', 'col-span-1 font-medium text-gray-700 dark:text-gray-300', formatDuration(entry.duration_minutes)));

        rTable.appendChild(row);
      });

      recentSection.appendChild(rTable);
      container.appendChild(recentSection);
    }

    // ── Empty State (no entries AND no active clocks) ───────────────────
    if (employees.length === 0 && active.length === 0 && recent.length === 0) {
      var emptyWrap = _el('div', 'text-center py-12');
      emptyWrap.appendChild(_el('div', 'text-4xl mb-3', '\u23F1'));
      emptyWrap.appendChild(_el('h3', 'text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2', t('laborEmptyTitle', 'No labor entries yet')));
      emptyWrap.appendChild(_el('p', 'text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto', t('laborEmptyDesc', 'Use the clock-in form above to start tracking technician time on repair orders. You can also clock in from inside any Repair Order.')));
      container.appendChild(emptyWrap);
    }

  } catch (err) {
    container.textContent = '';
    container.appendChild(_el('p', 'text-red-500 text-center py-8', t('laborSummaryError', 'Error loading labor data')));
    console.error('Labor summary error:', err);
  }
};

})();
