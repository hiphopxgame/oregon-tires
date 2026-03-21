/**
 * TechPicker — Smart technician assignment floating panel
 * Surfaces skill match, workload, and availability inline.
 * Uses global `employees` and `appointments` arrays already loaded by admin panel.
 */
(function() {
  'use strict';

  var panel = null;
  var backdropListener = null;
  var escapeListener = null;

  // 6 avatar colors — hash employee name to pick one
  var AVATAR_COLORS = [
    'bg-blue-500', 'bg-emerald-500', 'bg-purple-500',
    'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'
  ];

  function hashName(name) {
    var h = 0;
    for (var i = 0; i < name.length; i++) {
      h = ((h << 5) - h) + name.charCodeAt(i);
      h = h & h;
    }
    return Math.abs(h);
  }

  function getInitials(name) {
    var parts = (name || '').trim().split(/\s+/);
    if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    return (parts[0] || '?')[0].toUpperCase();
  }

  function T(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  function getGroupName(emp) {
    if (typeof currentLang !== 'undefined' && currentLang === 'es' && emp.group_name_es) return emp.group_name_es;
    return emp.group_name || '';
  }

  function scoreEmployees(service, date) {
    var active = (typeof employees !== 'undefined' ? employees : []).filter(function(e) { return e.is_active; });
    var appts = typeof appointments !== 'undefined' ? appointments : [];

    return active.map(function(emp) {
      var skillMatch = false;
      if (service && emp.skills && emp.skills.length > 0) {
        skillMatch = emp.skills.indexOf(service) !== -1;
      }

      // Count today's non-cancelled appointments for this employee
      var workload = 0;
      if (date) {
        for (var i = 0; i < appts.length; i++) {
          var a = appts[i];
          if (String(a.assigned_employee_id) === String(emp.id) && a.preferred_date === date && a.status !== 'cancelled') {
            workload++;
          }
        }
      }

      var maxDaily = emp.max_daily_appointments || 10;
      var pct = maxDaily > 0 ? workload / maxDaily : 0;

      var score = 0;
      if (service) {
        score += skillMatch ? 3 : -5;
      }
      if (pct < 0.5) score += 2;
      else if (pct < 0.8) score += 1;

      return {
        emp: emp,
        score: score,
        skillMatch: skillMatch,
        workload: workload,
        maxDaily: maxDaily,
        pct: pct
      };
    });
  }

  function sortScored(scored, currentEmployeeId) {
    scored.sort(function(a, b) {
      // Current employee always first
      var aCurrent = currentEmployeeId && String(a.emp.id) === String(currentEmployeeId);
      var bCurrent = currentEmployeeId && String(b.emp.id) === String(currentEmployeeId);
      if (aCurrent && !bCurrent) return -1;
      if (!aCurrent && bCurrent) return 1;
      return b.score - a.score;
    });

    // Mark recommended (highest score, excluding current)
    var bestScore = -Infinity;
    var bestIdx = -1;
    for (var i = 0; i < scored.length; i++) {
      if (currentEmployeeId && String(scored[i].emp.id) === String(currentEmployeeId)) continue;
      if (scored[i].score > bestScore) {
        bestScore = scored[i].score;
        bestIdx = i;
      }
    }
    if (bestIdx !== -1) scored[bestIdx].isRecommended = true;

    return scored;
  }

  function renderPanel(scored, options) {
    var el = document.createElement('div');
    el.id = 'tech-picker-panel';
    el.className = 'fixed z-[100] w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden';
    el.style.maxHeight = '420px';
    el.style.display = 'flex';
    el.style.flexDirection = 'column';

    // Prevent clicks inside panel from closing it
    el.addEventListener('click', function(e) { e.stopPropagation(); });

    // Header
    var header = document.createElement('div');
    header.className = 'px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600';

    var title = document.createElement('div');
    title.className = 'font-semibold text-sm text-gray-900 dark:text-gray-100';
    title.textContent = T('techPickerTitle', 'Assign Technician');
    header.appendChild(title);

    if (options.service || options.date) {
      var sub = document.createElement('div');
      sub.className = 'text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2';
      if (options.service) {
        var svcSpan = document.createElement('span');
        svcSpan.textContent = '\uD83D\uDD27 ' + (options.service || '').replace(/-/g, ' ');
        sub.appendChild(svcSpan);
      }
      if (options.date) {
        var dateSpan = document.createElement('span');
        dateSpan.textContent = '\uD83D\uDCC5 ' + options.date;
        sub.appendChild(dateSpan);
      }
      header.appendChild(sub);
    }
    el.appendChild(header);

    // List
    var list = document.createElement('div');
    list.className = 'overflow-y-auto flex-1';
    list.style.maxHeight = '320px';

    if (scored.length === 0) {
      var empty = document.createElement('div');
      empty.className = 'p-4 text-center text-gray-400 text-sm';
      empty.textContent = T('techPickerNoTechs', 'No active employees');
      list.appendChild(empty);
    } else {
      for (var i = 0; i < scored.length; i++) {
        var rowEl = renderRow(scored[i], options);
        if (i > 0) rowEl.className += ' border-t border-gray-100 dark:border-gray-700/50';
        list.appendChild(rowEl);
      }
    }
    el.appendChild(list);

    // Footer — link to Resource Planner
    var footer = document.createElement('div');
    footer.className = 'px-4 py-2.5 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50';
    var plannerLink = document.createElement('button');
    plannerLink.className = 'text-xs text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium flex items-center gap-1 w-full';
    plannerLink.innerHTML = '\uD83D\uDCCA ';
    var plannerText = document.createTextNode(T('techPickerViewPlanner', 'Open Resource Planner'));
    plannerLink.appendChild(plannerText);
    plannerLink.addEventListener('click', function() {
      closePicker();
      if (typeof switchTab === 'function') switchTab('resourceplanner');
    });
    footer.appendChild(plannerLink);
    el.appendChild(footer);

    return el;
  }

  function renderRow(item, options) {
    var emp = item.emp;
    var isCurrent = options.currentEmployeeId && String(emp.id) === String(options.currentEmployeeId);

    var row = document.createElement('div');
    row.className = 'px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-start gap-3 transition-colors' +
      (isCurrent ? ' bg-green-50/50 dark:bg-green-900/10' : '');


    row.addEventListener('click', function() {
      if (typeof options.onSelect === 'function') {
        options.onSelect(emp.id, emp.name);
      }
      closePicker();
    });

    // Avatar
    var avatar = document.createElement('div');
    var colorClass = AVATAR_COLORS[hashName(emp.name) % AVATAR_COLORS.length];
    avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5 ' + colorClass;
    avatar.textContent = getInitials(emp.name);
    row.appendChild(avatar);

    // Info column
    var info = document.createElement('div');
    info.className = 'flex-1 min-w-0';

    // Name row with badges
    var nameRow = document.createElement('div');
    nameRow.className = 'flex items-center gap-1.5 flex-wrap';

    var nameSpan = document.createElement('span');
    nameSpan.className = 'font-medium text-sm text-gray-900 dark:text-gray-100';
    nameSpan.textContent = emp.name;
    nameRow.appendChild(nameSpan);

    if (isCurrent) {
      var currentBadge = document.createElement('span');
      currentBadge.className = 'text-[10px] px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-medium';
      currentBadge.textContent = T('techPickerCurrent', 'Current');
      nameRow.appendChild(currentBadge);
    }

    if (item.isRecommended) {
      var recBadge = document.createElement('span');
      recBadge.className = 'text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-medium';
      recBadge.textContent = '\u2B50 ' + T('techPickerRecommended', 'Best fit');
      nameRow.appendChild(recBadge);
    }

    info.appendChild(nameRow);

    // Group + Skill line
    var detailRow = document.createElement('div');
    detailRow.className = 'flex items-center gap-2 mt-0.5';

    var groupName = getGroupName(emp);
    if (groupName) {
      var groupSpan = document.createElement('span');
      groupSpan.className = 'text-[11px] text-gray-400 dark:text-gray-500';
      groupSpan.textContent = groupName;
      detailRow.appendChild(groupSpan);
    }

    if (options.service) {
      var skillBadge = document.createElement('span');
      if (item.skillMatch) {
        skillBadge.className = 'text-[11px] text-green-600 dark:text-green-400 font-medium';
        skillBadge.textContent = '\u2705 ' + T('techPickerCertified', 'Certified');
      } else {
        skillBadge.className = 'text-[11px] text-red-500 dark:text-red-400 font-medium';
        skillBadge.textContent = '\u274C ' + T('techPickerNotCertified', 'Not certified');
      }
      detailRow.appendChild(skillBadge);
    }

    info.appendChild(detailRow);

    // Workload bar
    if (options.date) {
      var wlRow = document.createElement('div');
      wlRow.className = 'flex items-center gap-2 mt-1';

      var wlText = document.createElement('span');
      wlText.className = 'text-[11px] text-gray-400 dark:text-gray-500 w-16 flex-shrink-0';
      wlText.textContent = item.workload + '/' + item.maxDaily + ' ' + T('techPickerToday', 'today');
      wlRow.appendChild(wlText);

      var barBg = document.createElement('div');
      barBg.className = 'flex-1 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden';

      var barFill = document.createElement('div');
      var barPct = Math.min(item.pct * 100, 100);
      barFill.style.width = barPct + '%';
      var barColor = item.pct < 0.5 ? 'bg-green-500' : (item.pct < 0.8 ? 'bg-amber-500' : 'bg-red-500');
      barFill.className = 'h-full rounded-full transition-all ' + barColor;
      barBg.appendChild(barFill);
      wlRow.appendChild(barBg);

      info.appendChild(wlRow);
    }

    row.appendChild(info);
    return row;
  }

  function positionPanel(anchor, panelEl) {
    var rect = anchor.getBoundingClientRect();
    var viewW = window.innerWidth;
    var viewH = window.innerHeight;

    // On narrow screens, use bottom-sheet style
    if (viewW < 640) {
      panelEl.style.position = 'fixed';
      panelEl.style.bottom = '0';
      panelEl.style.left = '0';
      panelEl.style.right = '0';
      panelEl.style.top = 'auto';
      panelEl.style.width = '100%';
      panelEl.style.maxHeight = '70vh';
      panelEl.style.borderRadius = '16px 16px 0 0';
      return;
    }

    panelEl.style.position = 'fixed';

    // Prefer below anchor
    var top = rect.bottom + 4;
    var left = rect.left;

    // Flip above if near bottom
    if (top + 420 > viewH && rect.top > 420) {
      top = rect.top - 420 - 4;
    }

    // Keep within viewport horizontally
    if (left + 320 > viewW) {
      left = viewW - 330;
    }
    if (left < 10) left = 10;

    panelEl.style.top = top + 'px';
    panelEl.style.left = left + 'px';
  }

  function closePicker() {
    if (panel) {
      panel.remove();
      panel = null;
    }
    if (backdropListener) {
      document.removeEventListener('click', backdropListener, true);
      backdropListener = null;
    }
    if (escapeListener) {
      document.removeEventListener('keydown', escapeListener);
      escapeListener = null;
    }
  }

  function openPicker(options) {
    // Close any existing panel
    closePicker();

    var scored = scoreEmployees(options.service || null, options.date || null);
    scored = sortScored(scored, options.currentEmployeeId);

    panel = renderPanel(scored, options);
    document.body.appendChild(panel);
    positionPanel(options.anchor, panel);

    // Close on click outside (delayed to avoid immediate close)
    setTimeout(function() {
      backdropListener = function(e) {
        if (panel && !panel.contains(e.target)) {
          closePicker();
        }
      };
      document.addEventListener('click', backdropListener, true);
    }, 10);

    // Close on Escape
    escapeListener = function(e) {
      if (e.key === 'Escape') {
        closePicker();
      }
    };
    document.addEventListener('keydown', escapeListener);
  }

  window.TechPicker = {
    open: openPicker,
    close: closePicker
  };
})();
