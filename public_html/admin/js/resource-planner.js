/**
 * Oregon Tires — Resource Planner Module
 *
 * Renders the Resource Planner tab: date toggle, command center cards,
 * hourly heatmap, resource grid, unassigned sidebar, skill gap alerts,
 * staffing recommendations, and employee skills matrix.
 */
(function() {
  'use strict';

  var rpData = null;
  var rpDate = 'today'; // 'today' | 'tomorrow' | 'YYYY-MM-DD'

  function T(key) {
    return (adminT[currentLang] || {})[key] || key;
  }

  function getDateStr(which) {
    var d = new Date();
    d.setHours(0, 0, 0, 0);
    if (which === 'tomorrow') d.setDate(d.getDate() + 1);
    else if (which !== 'today') return which;
    return d.toISOString().split('T')[0];
  }

  function fmtHour(h) {
    if (h === 0) return '12am';
    if (h < 12) return h + 'am';
    if (h === 12) return '12pm';
    return (h - 12) + 'pm';
  }

  function svcLabel(svc) {
    if (typeof SERVICE_COLORS !== 'undefined' && SERVICE_COLORS[svc]) {
      return currentLang === 'es' ? SERVICE_COLORS[svc].labelEs : SERVICE_COLORS[svc].label;
    }
    return svc.replace(/-/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
  }

  function svcColor(svc) {
    if (typeof SERVICE_COLORS !== 'undefined' && SERVICE_COLORS[svc]) {
      return SERVICE_COLORS[svc].hex;
    }
    return '#9CA3AF';
  }

  function ce(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function clearEl(id) {
    var el = document.getElementById(id);
    if (!el) return null;
    while (el.firstChild) el.removeChild(el.firstChild);
    return el;
  }

  // ═══════════════════════════════════════════════════════════════
  // DATA LOADING
  // ═══════════════════════════════════════════════════════════════

  window.loadResourcePlanner = function() {
    var todayStr = getDateStr('today');
    var tomorrowStr = getDateStr('tomorrow');
    var dateStr = getDateStr(rpDate);

    // Always fetch today + tomorrow, plus custom if different
    var dates = [todayStr, tomorrowStr];
    if (dateStr !== todayStr && dateStr !== tomorrowStr) {
      dates.push(dateStr);
    }

    fetch('/api/admin/resource-planner.php?dates=' + dates.join(','), {
      credentials: 'include'
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
      if (!resp.success) {
        showToast(resp.error || 'Failed to load resource data', true);
        return;
      }
      rpData = resp.data;
      renderResourcePlanner();
    })
    .catch(function(err) {
      console.error('Resource planner load error:', err);
      showToast('Failed to load resource planner', true);
    });
  };

  // ═══════════════════════════════════════════════════════════════
  // MAIN RENDER
  // ═══════════════════════════════════════════════════════════════

  function renderResourcePlanner() {
    if (!rpData) return;

    renderDateToggle();
    renderCommandCenter();
    renderHeatmap();
    renderResourceGrid();
    renderUnassigned();
    renderSkillGapAlerts();
    renderRecommendations();
    renderSkillsMatrix();
  }

  // ═══════════════════════════════════════════════════════════════
  // DATE TOGGLE
  // ═══════════════════════════════════════════════════════════════

  function renderDateToggle() {
    var container = clearEl('rp-date-toggle');
    if (!container) return;

    var wrap = ce('div', 'flex items-center gap-2 flex-wrap');

    var btnToday = ce('button', 'px-4 py-2 rounded-lg text-sm font-medium transition ' +
      (rpDate === 'today' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'),
      T('rpToday'));
    btnToday.addEventListener('click', function() { rpDate = 'today'; loadResourcePlanner(); });
    wrap.appendChild(btnToday);

    var btnTomorrow = ce('button', 'px-4 py-2 rounded-lg text-sm font-medium transition ' +
      (rpDate === 'tomorrow' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'),
      T('rpTomorrow'));
    btnTomorrow.addEventListener('click', function() { rpDate = 'tomorrow'; loadResourcePlanner(); });
    wrap.appendChild(btnTomorrow);

    var customInput = document.createElement('input');
    customInput.type = 'date';
    customInput.className = 'px-3 py-2 rounded-lg text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white';
    customInput.value = getDateStr(rpDate);
    customInput.addEventListener('change', function() {
      if (this.value) { rpDate = this.value; loadResourcePlanner(); }
    });
    wrap.appendChild(customInput);

    container.appendChild(wrap);
  }

  // ═══════════════════════════════════════════════════════════════
  // COMMAND CENTER CARDS
  // ═══════════════════════════════════════════════════════════════

  function renderCommandCenter() {
    var container = clearEl('rp-command-center');
    if (!container) return;

    var todayStr = getDateStr('today');
    var tomorrowStr = getDateStr('tomorrow');
    var todayData = rpData.dates[todayStr];
    var tomorrowData = rpData.dates[tomorrowStr];

    var grid = ce('div', 'grid grid-cols-2 lg:grid-cols-4 gap-4');

    function addCard(label, value, detail, borderColor) {
      var card = ce('div', 'bg-white rounded-xl shadow p-4 border-l-4 dark:bg-gray-800 ' + borderColor);
      card.appendChild(ce('div', 'text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide', label));
      card.appendChild(ce('div', 'text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1', String(value)));
      if (detail) card.appendChild(ce('div', 'text-xs text-gray-400 dark:text-gray-500 mt-1', detail));
      grid.appendChild(card);
    }

    if (todayData && !todayData.shop_closed) {
      var todayPeak = todayData.peak_hour !== null ? (T('rpPeak') + ': ' + fmtHour(todayData.peak_hour) + ' — ' + todayData.peak_concurrent) : '';
      addCard(T('rpTodayAppts'), todayData.appointments.length, todayPeak, 'border-amber-500');
      addCard(T('rpTodayStaff'), todayData.working_count + '/' + rpData.all_employees.length, todayData.unassigned_count + ' ' + T('rpUnassigned'), 'border-blue-500');
    } else {
      addCard(T('rpTodayAppts'), todayData ? (T('rpShopClosed') || 'Closed') : '—', '', 'border-gray-400');
      addCard(T('rpTodayStaff'), '—', '', 'border-gray-400');
    }

    if (tomorrowData && !tomorrowData.shop_closed) {
      var tmrPeak = tomorrowData.peak_hour !== null ? (T('rpPeak') + ': ' + fmtHour(tomorrowData.peak_hour) + ' — ' + tomorrowData.peak_concurrent) : '';
      addCard(T('rpTomorrowAppts'), tomorrowData.appointments.length, tmrPeak, 'border-purple-500');
      addCard(T('rpTomorrowStaff'), tomorrowData.working_count + '/' + rpData.all_employees.length, tomorrowData.unassigned_count + ' ' + T('rpUnassigned'), 'border-green-500');
    } else {
      addCard(T('rpTomorrowAppts'), tomorrowData ? (T('rpShopClosed') || 'Closed') : '—', '', 'border-gray-400');
      addCard(T('rpTomorrowStaff'), '—', '', 'border-gray-400');
    }

    container.appendChild(grid);
  }

  // ═══════════════════════════════════════════════════════════════
  // HOURLY HEATMAP (stacked bars)
  // ═══════════════════════════════════════════════════════════════

  function renderHeatmap() {
    var container = clearEl('rp-heatmap');
    if (!container) return;

    var dateStr = getDateStr(rpDate);
    var dateData = rpData.dates[dateStr];
    if (!dateData || dateData.shop_closed) {
      container.appendChild(ce('p', 'text-gray-400 dark:text-gray-500 text-center py-8', T('rpNoData')));
      return;
    }

    var chartData = [];
    dateData.hourly_breakdown.forEach(function(hb) {
      var segments = [];
      var svcs = Object.keys(hb.services || {});
      svcs.forEach(function(svc) {
        segments.push({
          key: svc,
          value: hb.services[svc],
          color: svcColor(svc),
          name: svcLabel(svc)
        });
      });
      chartData.push({
        label: fmtHour(hb.hour),
        segments: segments,
        capacity: hb.capacity
      });
    });

    if (chartData.some(function(d) { var t = 0; d.segments.forEach(function(s){ t += s.value; }); return t > 0; })) {
      OTCharts.stackedHorizontalBars(container, chartData, { capacityLabel: T('rpCapacity') });
    } else {
      container.appendChild(ce('p', 'text-gray-400 dark:text-gray-500 text-center py-8', T('rpNoAppts')));
    }
  }

  // ═══════════════════════════════════════════════════════════════
  // RESOURCE GRID (time × employees)
  // ═══════════════════════════════════════════════════════════════

  function renderResourceGrid() {
    var container = clearEl('rp-resource-grid');
    if (!container) return;

    var dateStr = getDateStr(rpDate);
    var dateData = rpData.dates[dateStr];
    if (!dateData || dateData.shop_closed || !dateData.employees || !dateData.employees.length) {
      container.appendChild(ce('p', 'text-gray-400 dark:text-gray-500 text-center py-8', T('rpNoStaff')));
      return;
    }

    var emps = dateData.employees;
    var apts = dateData.appointments;

    // Build assignment map: hour -> empId -> [appointments]
    var assignMap = {};
    apts.forEach(function(apt) {
      if (!apt.assigned_employee_id || !apt.preferred_time) return;
      var h = parseInt(apt.preferred_time.substring(0, 2));
      if (!assignMap[h]) assignMap[h] = {};
      if (!assignMap[h][apt.assigned_employee_id]) assignMap[h][apt.assigned_employee_id] = [];
      assignMap[h][apt.assigned_employee_id].push(apt);
    });

    // Build table
    var table = ce('div', 'overflow-x-auto');
    var tbl = document.createElement('table');
    tbl.className = 'w-full text-xs border-collapse';

    // Header row
    var thead = document.createElement('thead');
    var hRow = document.createElement('tr');
    var thTime = document.createElement('th');
    thTime.className = 'p-2 text-left text-gray-500 dark:text-gray-400 font-medium sticky left-0 bg-white dark:bg-gray-800 z-10';
    thTime.textContent = T('rpTime');
    hRow.appendChild(thTime);

    emps.forEach(function(emp) {
      var th = document.createElement('th');
      th.className = 'p-2 text-center font-medium text-gray-700 dark:text-gray-300 min-w-[120px]';
      var nameSpan = ce('div', 'font-semibold text-xs', emp.name);
      th.appendChild(nameSpan);
      // Skill badges (compact)
      var badges = ce('div', 'flex flex-wrap gap-0.5 justify-center mt-1');
      (emp.skills || []).slice(0, 4).forEach(function(skill) {
        var dot = ce('span', 'w-2 h-2 rounded-full inline-block');
        dot.style.backgroundColor = svcColor(skill);
        dot.title = svcLabel(skill);
        badges.appendChild(dot);
      });
      if (emp.skills && emp.skills.length > 4) {
        badges.appendChild(ce('span', 'text-gray-400 text-[9px]', '+' + (emp.skills.length - 4)));
      }
      th.appendChild(badges);
      hRow.appendChild(th);
    });
    thead.appendChild(hRow);
    tbl.appendChild(thead);

    // Body rows (8am-5pm)
    var tbody = document.createElement('tbody');
    for (var h = 8; h <= 17; h++) {
      var tr = document.createElement('tr');
      tr.className = h % 2 === 0 ? 'bg-gray-50 dark:bg-gray-900/30' : '';

      var tdTime = document.createElement('td');
      tdTime.className = 'p-2 text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap sticky left-0 ' + (h % 2 === 0 ? 'bg-gray-50 dark:bg-gray-900/30' : 'bg-white dark:bg-gray-800');
      tdTime.textContent = fmtHour(h);
      tr.appendChild(tdTime);

      emps.forEach(function(emp) {
        var td = document.createElement('td');
        td.className = 'p-1 border border-gray-100 dark:border-gray-700 min-w-[120px]';

        var startH = parseInt((emp.start_time || '08:00').substring(0, 2));
        var endH = parseInt((emp.end_time || '17:00').substring(0, 2));

        if (h < startH || h >= endH) {
          // Not working this hour
          td.className += ' bg-gray-200/50 dark:bg-gray-700/30';
          td.title = T('rpOffHours');
          tr.appendChild(td);
          return;
        }

        var cellApts = (assignMap[h] && assignMap[h][emp.id]) || [];
        if (cellApts.length === 0) {
          // Available — green tint
          td.className += ' bg-green-50/50 dark:bg-green-900/10';
        }

        cellApts.forEach(function(apt) {
          var card = ce('div', 'rounded px-1.5 py-1 mb-0.5 text-[10px] leading-tight');
          var sc = svcColor(apt.service || 'other');
          card.style.backgroundColor = sc + '22';
          card.style.borderLeft = '3px solid ' + sc;

          // Check skill mismatch
          var hasSkill = emp.skills && emp.skills.indexOf(apt.service) !== -1;
          if (!hasSkill && apt.service) {
            card.style.outline = '2px dashed #EF4444';
            card.title = T('rpSkillMismatch');
          }

          card.appendChild(ce('div', 'font-semibold text-gray-800 dark:text-gray-200 truncate', (apt.first_name || '') + ' ' + (apt.last_name || '').charAt(0) + '.'));
          card.appendChild(ce('div', 'text-gray-500 dark:text-gray-400 truncate', svcLabel(apt.service || 'other')));
          td.appendChild(card);
        });

        tr.appendChild(td);
      });

      tbody.appendChild(tr);
    }
    tbl.appendChild(tbody);
    table.appendChild(tbl);
    container.appendChild(table);
  }

  // ═══════════════════════════════════════════════════════════════
  // UNASSIGNED SIDEBAR
  // ═══════════════════════════════════════════════════════════════

  function renderUnassigned() {
    var container = clearEl('rp-unassigned');
    if (!container) return;

    var dateStr = getDateStr(rpDate);
    var dateData = rpData.dates[dateStr];
    if (!dateData || dateData.shop_closed) return;

    var unassigned = dateData.appointments.filter(function(a) {
      return !a.assigned_employee_id && a.status !== 'completed';
    });

    if (!unassigned.length) {
      container.appendChild(ce('p', 'text-green-600 dark:text-green-400 text-sm text-center py-4', T('rpAllAssigned')));
      return;
    }

    // Group by hour
    var byHour = {};
    unassigned.forEach(function(a) {
      var h = a.preferred_time ? parseInt(a.preferred_time.substring(0, 2)) : 0;
      if (!byHour[h]) byHour[h] = [];
      byHour[h].push(a);
    });

    var hours = Object.keys(byHour).sort(function(a, b) { return a - b; });
    hours.forEach(function(h) {
      var group = ce('div', 'mb-3');
      group.appendChild(ce('div', 'text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1', fmtHour(parseInt(h))));

      byHour[h].forEach(function(apt) {
        var card = ce('div', 'flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded px-2 py-1.5 mb-1');
        var name = ce('span', 'text-xs font-medium text-gray-800 dark:text-gray-200 truncate flex-1', (apt.first_name || '') + ' ' + (apt.last_name || ''));
        card.appendChild(name);
        if (apt.service) {
          var badge = ce('span', 'text-[10px] px-1.5 py-0.5 rounded-full font-medium', svcLabel(apt.service));
          badge.style.backgroundColor = svcColor(apt.service) + '22';
          badge.style.color = svcColor(apt.service);
          card.appendChild(badge);
        }
        group.appendChild(card);
      });

      container.appendChild(group);
    });
  }

  // ═══════════════════════════════════════════════════════════════
  // SKILL GAP ALERTS
  // ═══════════════════════════════════════════════════════════════

  function renderSkillGapAlerts() {
    var container = clearEl('rp-skill-gaps');
    if (!container) return;

    var dateStr = getDateStr(rpDate);
    var dateData = rpData.dates[dateStr];
    if (!dateData || dateData.shop_closed || !dateData.skill_gaps.length) {
      container.appendChild(ce('p', 'text-green-600 dark:text-green-400 text-sm text-center py-4', T('rpNoGaps')));
      return;
    }

    dateData.skill_gaps.forEach(function(gap) {
      var isCritical = gap.severity === 'critical';
      var alertClass = isCritical
        ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
        : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800';
      var textClass = isCritical
        ? 'text-red-700 dark:text-red-300'
        : 'text-amber-700 dark:text-amber-300';

      var alert = ce('div', 'border rounded-lg p-3 mb-2 ' + alertClass);
      var icon = isCritical ? 'CRITICAL' : 'WARNING';
      var msg = icon + ': ' + gap.demand + ' ' + svcLabel(gap.service) + ' ' + T('rpAt') + ' ' + fmtHour(gap.hour) +
        ' — ' + gap.supply + ' ' + T('rpCertifiedAvail');

      alert.appendChild(ce('div', 'text-sm font-medium ' + textClass, msg));
      container.appendChild(alert);
    });
  }

  // ═══════════════════════════════════════════════════════════════
  // STAFFING RECOMMENDATIONS
  // ═══════════════════════════════════════════════════════════════

  function renderRecommendations() {
    var container = clearEl('rp-recommendations');
    if (!container) return;

    var dateStr = getDateStr(rpDate);
    var dateData = rpData.dates[dateStr];
    if (!dateData || dateData.shop_closed) return;

    var recs = [];

    // 1. Off-duty employees with skills matching unmet demand
    var gaps = dateData.skill_gaps || [];
    var neededSkills = {};
    gaps.forEach(function(g) { neededSkills[g.service] = (neededSkills[g.service] || 0) + g.gap; });

    (rpData.off_duty_employees || []).forEach(function(emp) {
      var matchingSkills = (emp.skills || []).filter(function(s) { return neededSkills[s]; });
      if (matchingSkills.length > 0) {
        var skillNames = matchingSkills.map(function(s) { return svcLabel(s); }).join(', ');
        recs.push({
          type: 'call-in',
          text: T('rpCallIn') + ' ' + emp.name + ' — ' + T('rpCertifiedFor') + ' ' + skillNames +
            ' (' + matchingSkills.reduce(function(sum, s) { return sum + (neededSkills[s] || 0); }, 0) + ' ' + T('rpUnassignedJobs') + ')'
        });
      }
    });

    // 2. Load balancing suggestions
    var hourlyLoads = dateData.hourly_breakdown || [];
    var overloaded = hourlyLoads.filter(function(h) { return h.total > h.capacity && h.capacity > 0; });
    var underloaded = hourlyLoads.filter(function(h) { return h.total < h.capacity && h.capacity > 0; });
    if (overloaded.length > 0 && underloaded.length > 0) {
      overloaded.forEach(function(oh) {
        var best = underloaded.reduce(function(prev, curr) {
          return (curr.capacity - curr.total) > (prev.capacity - prev.total) ? curr : prev;
        });
        if (best) {
          recs.push({
            type: 'balance',
            text: T('rpConsiderMoving') + ' ' + (oh.total - oh.capacity) + ' ' + T('rpApptsFrom') + ' ' +
              fmtHour(oh.hour) + ' ' + T('rpTo') + ' ' + fmtHour(best.hour) + ' ' + T('rpToBalance')
          });
        }
      });
    }

    if (!recs.length) {
      container.appendChild(ce('p', 'text-green-600 dark:text-green-400 text-sm text-center py-4', T('rpNoRecommendations')));
      return;
    }

    recs.forEach(function(rec) {
      var row = ce('div', 'flex items-start gap-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-2');
      var icon = rec.type === 'call-in' ? '📞' : '⚖️';
      row.appendChild(ce('span', 'text-lg shrink-0', icon));
      row.appendChild(ce('span', 'text-sm text-blue-800 dark:text-blue-300', rec.text));
      container.appendChild(row);
    });
  }

  // ═══════════════════════════════════════════════════════════════
  // EMPLOYEE SKILLS MATRIX
  // ═══════════════════════════════════════════════════════════════

  function renderSkillsMatrix() {
    var container = clearEl('rp-skills-matrix');
    if (!container) return;

    var allEmps = (rpData.all_employees || []).concat(rpData.inactive_employees || []);
    var svcTypes = rpData.service_types || [];

    if (!allEmps.length) {
      container.appendChild(ce('p', 'text-gray-400 dark:text-gray-500 text-center py-4', T('rpNoEmployees')));
      return;
    }

    // Coverage alerts
    var coverageAlerts = ce('div', 'mb-4');
    var hasAlert = false;
    svcTypes.forEach(function(svc) {
      var certified = allEmps.filter(function(e) { return e.is_active && e.skills && e.skills.indexOf(svc) !== -1; }).length;
      if (certified === 0) {
        hasAlert = true;
        var alert = ce('div', 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded px-3 py-2 mb-1 text-sm text-red-700 dark:text-red-300');
        alert.textContent = T('rpNoCoverage') + ' ' + svcLabel(svc) + ' — ' + T('rpCannotStaff');
        coverageAlerts.appendChild(alert);
      } else if (certified === 1) {
        hasAlert = true;
        var warn = ce('div', 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded px-3 py-2 mb-1 text-sm text-amber-700 dark:text-amber-300');
        warn.textContent = T('rpSingleCoverage') + ' ' + svcLabel(svc) + ' — ' + T('rpConsiderTraining');
        coverageAlerts.appendChild(warn);
      }
    });
    if (hasAlert) container.appendChild(coverageAlerts);

    // Skills matrix table
    var tableWrap = ce('div', 'overflow-x-auto');
    var table = document.createElement('table');
    table.className = 'w-full text-xs border-collapse';

    // Header
    var thead = document.createElement('thead');
    var hRow = document.createElement('tr');
    hRow.appendChild(ce('th', 'p-2 text-left text-gray-500 dark:text-gray-400 font-medium sticky left-0 bg-white dark:bg-gray-800 z-10', T('rpEmployee')));
    svcTypes.forEach(function(svc) {
      var th = ce('th', 'p-2 text-center font-medium text-gray-600 dark:text-gray-400 min-w-[80px]');
      th.textContent = svcLabel(svc);
      // Color bottom border
      th.style.borderBottom = '3px solid ' + svcColor(svc);
      hRow.appendChild(th);
    });
    hRow.appendChild(ce('th', 'p-2 text-center text-gray-500 dark:text-gray-400 font-medium', T('rpTotal')));
    thead.appendChild(hRow);
    table.appendChild(thead);

    // Body
    var tbody = document.createElement('tbody');
    allEmps.forEach(function(emp) {
      var tr = document.createElement('tr');
      if (!emp.is_active) tr.className = 'opacity-50';

      var tdName = document.createElement('td');
      tdName.className = 'p-2 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap sticky left-0 bg-white dark:bg-gray-800';
      tdName.textContent = emp.name + (emp.is_active ? '' : ' (' + T('rpInactive') + ')');
      tr.appendChild(tdName);

      var total = 0;
      svcTypes.forEach(function(svc) {
        var td = document.createElement('td');
        td.className = 'p-2 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition';
        var hasCert = emp.skills && emp.skills.indexOf(svc) !== -1;
        if (hasCert) {
          td.textContent = '✓';
          td.style.color = '#22C55E';
          td.style.fontWeight = 'bold';
          total++;
        } else {
          td.textContent = '—';
          td.style.color = '#D1D5DB';
        }
        // Click to toggle skill
        td.addEventListener('click', (function(empId, skill, currentHas) {
          return function() { toggleSkill(empId, skill, currentHas); };
        })(emp.id, svc, hasCert));
        tr.appendChild(td);
      });

      var tdTotal = ce('td', 'p-2 text-center font-bold');
      tdTotal.style.color = total < 3 ? '#EF4444' : '#22C55E';
      tdTotal.textContent = String(total);
      tr.appendChild(tdTotal);

      tbody.appendChild(tr);
    });

    // Column summary row
    var summaryRow = document.createElement('tr');
    summaryRow.className = 'border-t-2 border-gray-300 dark:border-gray-600 font-semibold';
    summaryRow.appendChild(ce('td', 'p-2 text-gray-500 dark:text-gray-400 sticky left-0 bg-white dark:bg-gray-800', T('rpCertifiedTotal')));
    svcTypes.forEach(function(svc) {
      var count = allEmps.filter(function(e) { return e.is_active && e.skills && e.skills.indexOf(svc) !== -1; }).length;
      var td = ce('td', 'p-2 text-center');
      td.textContent = String(count);
      td.style.color = count < 2 ? '#EF4444' : '#22C55E';
      td.style.fontWeight = 'bold';
      summaryRow.appendChild(td);
    });
    summaryRow.appendChild(ce('td', 'p-2', ''));
    tbody.appendChild(summaryRow);

    table.appendChild(tbody);
    tableWrap.appendChild(table);
    container.appendChild(tableWrap);
  }

  function toggleSkill(empId, skill, currentlyHas) {
    // Find current skills for this employee
    var allEmps = (rpData.all_employees || []).concat(rpData.inactive_employees || []);
    var emp = allEmps.find(function(e) { return e.id === empId; });
    if (!emp) return;

    var newSkills = (emp.skills || []).slice();
    if (currentlyHas) {
      newSkills = newSkills.filter(function(s) { return s !== skill; });
    } else {
      newSkills.push(skill);
    }

    fetch('/api/admin/employees.php', {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify({ id: empId, skills: newSkills })
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
      if (resp.success) {
        // Update local data
        emp.skills = newSkills;
        // Also update in global employees array if it exists
        if (typeof employees !== 'undefined') {
          var gEmp = employees.find(function(e) { return e.id === empId; });
          if (gEmp) gEmp.skills = newSkills;
        }
        renderSkillsMatrix();
        showToast(T('rpSkillUpdated'));
      } else {
        showToast(resp.error || T('rpSkillUpdateFailed'), true);
      }
    })
    .catch(function() { showToast(T('rpSkillUpdateFailed'), true); });
  }

  // ═══════════════════════════════════════════════════════════════
  // OVERVIEW ENHANCEMENTS (called from renderOverview)
  // ═══════════════════════════════════════════════════════════════

  /**
   * Renders the tomorrow stat card value.
   * Called after appointments are loaded.
   */
  window.renderTomorrowStat = function() {
    var tomorrowStr = getDateStr('tomorrow');
    var tomorrowApts = (typeof appointments !== 'undefined') ? appointments.filter(function(a) {
      return a.preferred_date === tomorrowStr && a.status !== 'cancelled';
    }) : [];

    var statEl = document.getElementById('stat-tomorrow');
    if (statEl) statEl.textContent = tomorrowApts.length;

    var detailEl = document.getElementById('stat-tomorrow-detail');
    if (detailEl) {
      if (tomorrowApts.length > 0) {
        // Find peak hour
        var hourMap = {};
        tomorrowApts.forEach(function(a) {
          if (a.preferred_time) {
            var h = parseInt(a.preferred_time.substring(0, 2));
            hourMap[h] = (hourMap[h] || 0) + 1;
          }
        });
        var peakH = null, peakC = 0;
        for (var h in hourMap) {
          if (hourMap[h] > peakC) { peakC = hourMap[h]; peakH = parseInt(h); }
        }
        if (peakH !== null) {
          detailEl.textContent = T('rpPeak') + ': ' + fmtHour(peakH) + ' — ' + peakC;
        } else {
          detailEl.textContent = '';
        }
      } else {
        detailEl.textContent = T('rpNoAppts') || 'No appointments';
      }
    }
  };

  /**
   * Renders skill gap alerts for today and tomorrow in the overview alerts section.
   * Appended after existing alerts.
   */
  window.renderOverviewSkillGapAlerts = function() {
    var container = document.getElementById('overview-alerts');
    if (!container) return;

    // Remove previously appended skill-gap alerts (marked with data attribute)
    var old = container.querySelectorAll('[data-rp-skill-gap]');
    for (var oi = 0; oi < old.length; oi++) old[oi].parentNode.removeChild(old[oi]);

    // Fetch resource data for today + tomorrow to get skill gaps
    var todayStr = getDateStr('today');
    var tomorrowStr = getDateStr('tomorrow');

    fetch('/api/admin/resource-planner.php?dates=' + todayStr + ',' + tomorrowStr, {
      credentials: 'include'
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
      if (!resp.success) return;
      var data = resp.data;

      [todayStr, tomorrowStr].forEach(function(dateStr) {
        var dd = data.dates[dateStr];
        if (!dd || dd.shop_closed || !dd.skill_gaps || !dd.skill_gaps.length) return;

        var dateLabel = dateStr === todayStr ? T('rpToday') : T('rpTomorrow');
        var gaps = dd.skill_gaps.slice(0, 5);

        var alertDiv = document.createElement('div');
        alertDiv.setAttribute('data-rp-skill-gap', '1');
        var isCritical = gaps.some(function(g) { return g.severity === 'critical'; });
        alertDiv.className = isCritical
          ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4'
          : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4';

        var header = ce('div', 'flex items-center gap-2 mb-2');
        var titleColor = isCritical ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400';
        header.appendChild(ce('span', titleColor + ' font-semibold', T('rpSkillGaps') + ' — ' + dateLabel + ' (' + dd.skill_gaps.length + ')'));
        alertDiv.appendChild(header);

        var items = ce('div', 'space-y-1');
        gaps.forEach(function(gap) {
          var itemColor = gap.severity === 'critical' ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300';
          var tag = gap.severity === 'critical' ? 'CRITICAL' : 'WARNING';
          var text = tag + ': ' + gap.demand + ' ' + svcLabel(gap.service) + ' @ ' + fmtHour(gap.hour) +
            ', ' + gap.supply + ' ' + T('rpCertifiedAvail');
          items.appendChild(ce('div', 'text-sm ' + itemColor, text));
        });
        alertDiv.appendChild(items);

        if (dd.skill_gaps.length > 5) {
          alertDiv.appendChild(ce('p', 'text-xs mt-1 ' + (isCritical ? 'text-red-500' : 'text-amber-500'), '+' + (dd.skill_gaps.length - 5) + ' more'));
        }

        container.appendChild(alertDiv);
      });
    })
    .catch(function() { /* silently fail — non-critical enhancement */ });
  };

  /**
   * Enhanced bay utilization chart using stacked bars.
   * Replaces the single-color horizontalBars call.
   */
  window.renderEnhancedBayUtilization = function() {
    var bayEl = document.getElementById('chart-bay-utilization');
    if (!bayEl) return;
    while (bayEl.firstChild) bayEl.removeChild(bayEl.firstChild);

    var todayStr = new Date().toISOString().split('T')[0];
    var todayApts = (typeof appointments !== 'undefined') ? appointments.filter(function(a) {
      return a.preferred_date === todayStr && a.status !== 'cancelled';
    }) : [];

    if (!todayApts.length) {
      bayEl.appendChild(ce('p', 'text-sm text-gray-400 dark:text-gray-500 text-center py-8',
        (adminT[currentLang] || {}).chartNoData || 'No data yet'));
      return;
    }

    // Build hourly data with service breakdown
    var hourData = {};
    for (var h = 8; h <= 17; h++) { hourData[h] = {}; }

    todayApts.forEach(function(a) {
      if (a.preferred_time) {
        var hour = parseInt(a.preferred_time.substring(0, 2));
        if (hour >= 8 && hour <= 17) {
          var svc = a.service || 'other';
          hourData[hour][svc] = (hourData[hour][svc] || 0) + 1;
        }
      }
    });

    // Calculate capacity per hour from employees
    var hourCapacity = {};
    if (typeof employees !== 'undefined') {
      var dayOfWeek = new Date().getDay();
      employees.forEach(function(emp) {
        if (!emp.is_active) return;
        // Assume 8-5 if we don't have schedule data client-side
        for (var ch = 8; ch <= 17; ch++) {
          hourCapacity[ch] = (hourCapacity[ch] || 0) + 1;
        }
      });
    }

    var chartData = [];
    for (var hh = 8; hh <= 17; hh++) {
      var segments = [];
      var svcs = Object.keys(hourData[hh]);
      svcs.forEach(function(svc) {
        segments.push({
          key: svc,
          value: hourData[hh][svc],
          color: svcColor(svc),
          name: svcLabel(svc)
        });
      });
      chartData.push({
        label: fmtHour(hh),
        segments: segments,
        capacity: hourCapacity[hh] || 0
      });
    }

    if (chartData.some(function(d) { var t = 0; d.segments.forEach(function(s){ t += s.value; }); return t > 0; })) {
      OTCharts.stackedHorizontalBars(bayEl, chartData, { capacityLabel: T('rpCapacity') || 'Staff' });
    } else {
      bayEl.appendChild(ce('p', 'text-sm text-gray-400 dark:text-gray-500 text-center py-8',
        (adminT[currentLang] || {}).chartNoData || 'No data yet'));
    }
  };

})();
