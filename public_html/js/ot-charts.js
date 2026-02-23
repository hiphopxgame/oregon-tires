/**
 * Oregon Tires — Chart Utilities (Token-Based)
 *
 * Provides token-themed chart rendering for the admin dashboard.
 * Uses design tokens from the Agentic UI Studio pipeline.
 *
 * Usage (in admin analytics):
 *   OTCharts.barChart(container, labels, data, options)
 *   OTCharts.horizontalBars(container, items, options)
 *   OTCharts.stackedBar(container, segments, options)
 *
 * Respects dark mode via .dark class on root.
 */
var OTCharts = (function() {
  'use strict';

  // Design tokens (from /design/tokens.json — Oregon Tires variant)
  var TOKENS = {
    colors: {
      brand: '#0D3618',
      brandLight: '#007030',
      brandMid: '#15803d',
      amber: '#F59E0B',
      series: ['#15803d', '#F59E0B', '#3B82F6', '#8B5CF6', '#EF4444', '#06B6D4', '#EC4899', '#6366F1'],
      // Status colors
      statusNew: '#3B82F6',
      statusPending: '#EAB308',
      statusConfirmed: '#22C55E',
      statusCompleted: '#6B7280',
      statusCancelled: '#EF4444',
    },
    dark: {
      cardBg: '#132319',
      border: '#2D4A33',
      textPrimary: '#DCE8DD',
      textSecondary: '#8FAF92',
      barBg: '#1E3325',
    },
    light: {
      cardBg: '#FFFFFF',
      border: '#E5E7EB',
      textPrimary: '#1F2937',
      textSecondary: '#6B7280',
      barBg: '#E5E7EB',
    },
    radius: '12px',
    font: "'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
  };

  function isDark() {
    return document.documentElement.classList.contains('dark') ||
           document.body.classList.contains('dark');
  }

  function theme() {
    return isDark() ? TOKENS.dark : TOKENS.light;
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  /**
   * Vertical bar chart (replaces the manual flex-based column chart)
   * @param {HTMLElement} container - Target element
   * @param {Array} data - [{label, value}]
   * @param {Object} opts - {height, color, showLabels, labelFormatter}
   */
  function barChart(container, data, opts) {
    opts = opts || {};
    var height = opts.height || 160;
    var color = opts.color || TOKENS.colors.brandMid;
    var labelFmt = opts.labelFormatter || function(d) { return d.label; };
    var t = theme();

    var maxVal = Math.max.apply(null, data.map(function(d) { return parseInt(d.value) || 0; }));
    if (!maxVal) maxVal = 1;

    var wrap = el('div', 'flex items-end gap-1');
    wrap.style.height = height + 'px';
    wrap.setAttribute('role', 'img');
    wrap.setAttribute('aria-label', 'Bar chart with ' + data.length + ' data points');

    data.forEach(function(d) {
      var pct = Math.max(Math.round((parseInt(d.value) / maxVal) * 100), 4);
      var col = el('div', 'flex-1 flex flex-col items-center justify-end h-full group relative');

      // Tooltip
      var tip = el('div', 'absolute -top-6 left-1/2 -translate-x-1/2 text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-10');
      tip.style.backgroundColor = t.cardBg === '#FFFFFF' ? '#1F2937' : t.cardBg;
      tip.style.color = t.cardBg === '#FFFFFF' ? '#FFFFFF' : t.textPrimary;
      tip.textContent = labelFmt(d) + ': ' + d.value;
      col.appendChild(tip);

      // Bar
      var bar = el('div', 'w-full rounded-t transition-all');
      bar.style.height = pct + '%';
      bar.style.backgroundColor = color;
      bar.style.opacity = '0.85';
      bar.addEventListener('mouseenter', function() { bar.style.opacity = '1'; });
      bar.addEventListener('mouseleave', function() { bar.style.opacity = '0.85'; });
      col.appendChild(bar);
      wrap.appendChild(col);
    });

    container.appendChild(wrap);

    // X-axis labels
    if (opts.showLabels !== false && data.length >= 3) {
      var xAxis = el('div', 'flex justify-between text-xs mt-2 px-1');
      xAxis.style.color = t.textSecondary;
      xAxis.appendChild(el('span', '', labelFmt(data[0])));
      xAxis.appendChild(el('span', '', labelFmt(data[Math.floor(data.length / 2)])));
      xAxis.appendChild(el('span', '', labelFmt(data[data.length - 1])));
      container.appendChild(xAxis);
    }
  }

  /**
   * Horizontal bar chart (replaces peak times manual bars)
   * @param {HTMLElement} container - Target element
   * @param {Array} data - [{label, value}]
   * @param {Object} opts - {color, maxValue}
   */
  function horizontalBars(container, data, opts) {
    opts = opts || {};
    var color = opts.color || TOKENS.colors.amber;
    var maxVal = opts.maxValue || Math.max.apply(null, data.map(function(d) { return parseInt(d.value) || 0; }));
    if (!maxVal) maxVal = 1;
    var t = theme();

    var list = el('div', 'space-y-2');
    list.setAttribute('role', 'list');
    list.setAttribute('aria-label', 'Horizontal bar chart');

    data.forEach(function(d) {
      var pct = Math.round((parseInt(d.value) / maxVal) * 100);
      var row = el('div', 'flex items-center gap-3');
      row.setAttribute('role', 'listitem');

      var label = el('span', 'w-20 text-sm font-medium');
      label.style.color = t.textPrimary;
      label.textContent = d.label;
      row.appendChild(label);

      var barBg = el('div', 'flex-1 rounded-full h-4');
      barBg.style.backgroundColor = t.barBg;
      var barFill = el('div', 'rounded-full h-4 transition-all');
      barFill.style.width = pct + '%';
      barFill.style.backgroundColor = color;
      barBg.appendChild(barFill);
      row.appendChild(barBg);

      var val = el('span', 'text-sm w-8 text-right');
      val.style.color = t.textSecondary;
      val.textContent = String(d.value);
      row.appendChild(val);

      list.appendChild(row);
    });

    container.appendChild(list);
  }

  /**
   * Stacked bar + legend (replaces status breakdown)
   * @param {HTMLElement} container - Target element
   * @param {Array} segments - [{label, value, color, textColor}]
   * @param {number} total - Total value for percentages
   */
  function stackedBar(container, segments, total) {
    if (!total) return;
    var t = theme();

    var bar = el('div', 'flex rounded-full h-6 overflow-hidden mb-4');
    bar.setAttribute('role', 'img');
    bar.setAttribute('aria-label', 'Status breakdown: ' + segments.map(function(s) { return s.label + ' ' + s.value; }).join(', '));

    segments.forEach(function(seg) {
      if (seg.value > 0) {
        var pct = Math.max(Math.round((seg.value / total) * 100), 2);
        var section = el('div', 'transition-all');
        section.style.width = pct + '%';
        section.style.backgroundColor = seg.color;
        section.title = seg.label + ': ' + seg.value;
        bar.appendChild(section);
      }
    });
    container.appendChild(bar);

    // Legend
    var legend = el('div', 'grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4');
    segments.forEach(function(seg) {
      var item = el('div', 'flex items-center gap-2');
      var dot = el('div', 'w-3 h-3 rounded-full');
      dot.style.backgroundColor = seg.color;
      item.appendChild(dot);

      var label = el('span', 'text-sm');
      label.style.color = t.textSecondary;
      label.textContent = seg.label;
      item.appendChild(label);

      var val = el('span', 'text-sm font-bold');
      val.style.color = seg.textColor || seg.color;
      val.textContent = String(seg.value);
      item.appendChild(val);

      legend.appendChild(item);
    });
    container.appendChild(legend);
  }

  /**
   * Service popularity bars (replaces inline service bar rendering)
   * @param {HTMLElement} container - Target element
   * @param {Array} data - [{service, count}]
   * @param {string} unitLabel - e.g. "appointments"
   */
  function serviceBars(container, data, unitLabel) {
    var maxVal = data.length ? Math.max.apply(null, data.map(function(s) { return parseInt(s.count); })) : 1;
    var t = theme();

    var list = el('div', 'space-y-3');
    data.forEach(function(s) {
      var pct = Math.round((parseInt(s.count) / maxVal) * 100);
      var row = el('div', '');
      var hdr = el('div', 'flex justify-between text-sm mb-1');

      var name = el('span', 'font-medium');
      name.style.color = t.textPrimary;
      name.textContent = s.service;
      hdr.appendChild(name);

      var count = el('span', '');
      count.style.color = t.textSecondary;
      count.textContent = s.count + ' ' + (unitLabel || '');
      hdr.appendChild(count);

      row.appendChild(hdr);

      var barBg = el('div', 'w-full rounded-full h-3');
      barBg.style.backgroundColor = t.barBg;
      var barFill = el('div', 'rounded-full h-3 transition-all');
      barFill.style.width = pct + '%';
      barFill.style.backgroundColor = TOKENS.colors.brandMid;
      barBg.appendChild(barFill);
      row.appendChild(barBg);

      list.appendChild(row);
    });
    container.appendChild(list);
  }

  /**
   * Pie or donut chart (canvas-based)
   * @param {HTMLElement} container
   * @param {Array} data - [{label, value, color?}]
   * @param {Object} opts - {donut: false, centerLabel: '', size: 200}
   */
  function pieChart(container, data, opts) {
    opts = opts || {};
    var size = opts.size || 200;
    var donut = opts.donut || false;
    var centerLabel = opts.centerLabel || '';
    var t = theme();
    var total = data.reduce(function(s, d) { return s + (parseInt(d.value) || 0); }, 0);
    if (!total) return;

    var wrap = el('div', 'flex items-center gap-6 flex-wrap');
    wrap.setAttribute('role', 'img');
    wrap.setAttribute('aria-label', 'Pie chart: ' + data.map(function(d) { return d.label + ' ' + d.value; }).join(', '));

    var canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    canvas.style.cssText = 'width:' + size + 'px;height:' + size + 'px;flex-shrink:0';
    wrap.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    var cx = size / 2, cy = size / 2, radius = size / 2 - 4;
    var startAngle = -Math.PI / 2;

    data.forEach(function(d, i) {
      var sliceAngle = (parseInt(d.value) / total) * Math.PI * 2;
      var color = d.color || TOKENS.colors.series[i % TOKENS.colors.series.length];
      ctx.beginPath();
      ctx.moveTo(cx, cy);
      ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
      ctx.closePath();
      ctx.fillStyle = color;
      ctx.fill();
      startAngle += sliceAngle;
    });

    if (donut) {
      var innerR = radius * 0.55;
      ctx.beginPath();
      ctx.arc(cx, cy, innerR, 0, Math.PI * 2);
      ctx.fillStyle = t.cardBg;
      ctx.fill();
      if (centerLabel) {
        ctx.font = 'bold 18px ' + TOKENS.font;
        ctx.fillStyle = t.textPrimary;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(centerLabel, cx, cy);
      }
    }

    // Legend
    var legend = el('div', 'flex flex-col gap-2');
    data.forEach(function(d, i) {
      var pct = Math.round((parseInt(d.value) / total) * 100);
      var row = el('div', 'flex items-center gap-2');
      var dot = el('div', 'w-3 h-3 rounded-full flex-shrink-0');
      dot.style.backgroundColor = d.color || TOKENS.colors.series[i % TOKENS.colors.series.length];
      row.appendChild(dot);
      var lbl = el('span', 'text-sm');
      lbl.style.color = t.textPrimary;
      lbl.textContent = d.label;
      row.appendChild(lbl);
      var val = el('span', 'text-sm font-bold');
      val.style.color = t.textSecondary;
      val.textContent = d.value + ' (' + pct + '%)';
      row.appendChild(val);
      legend.appendChild(row);
    });
    wrap.appendChild(legend);
    container.appendChild(wrap);
  }

  /**
   * Line chart (canvas-based with smooth curves)
   * @param {HTMLElement} container
   * @param {Array} data - [{label, value}]
   * @param {Object} opts - {height: 160, color, fillArea: true, showDots: true}
   */
  function lineChart(container, data, opts) {
    opts = opts || {};
    var height = opts.height || 160;
    var color = opts.color || TOKENS.colors.brandMid;
    var fillArea = opts.fillArea !== false;
    var showDots = opts.showDots !== false;
    var t = theme();

    if (!data.length) return;
    var maxVal = Math.max.apply(null, data.map(function(d) { return parseInt(d.value) || 0; }));
    if (!maxVal) maxVal = 1;

    var canvas = document.createElement('canvas');
    var w = 600, h = height;
    canvas.width = w;
    canvas.height = h;
    canvas.style.cssText = 'width:100%;height:' + h + 'px';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', 'Line chart with ' + data.length + ' data points');
    container.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    var padL = 10, padR = 10, padT = 10, padB = 20;
    var cw = w - padL - padR, ch = h - padT - padB;

    function xPos(i) { return padL + (i / (data.length - 1)) * cw; }
    function yPos(v) { return padT + ch - (v / maxVal) * ch; }

    // Grid lines
    ctx.strokeStyle = t.barBg;
    ctx.lineWidth = 0.5;
    for (var g = 0; g <= 4; g++) {
      var gy = padT + (g / 4) * ch;
      ctx.beginPath();
      ctx.moveTo(padL, gy);
      ctx.lineTo(w - padR, gy);
      ctx.stroke();
    }

    // Area fill
    if (fillArea) {
      ctx.beginPath();
      ctx.moveTo(xPos(0), yPos(parseInt(data[0].value)));
      for (var a = 1; a < data.length; a++) {
        var ax0 = xPos(a - 1), ay0 = yPos(parseInt(data[a - 1].value));
        var ax1 = xPos(a), ay1 = yPos(parseInt(data[a].value));
        var acx = (ax0 + ax1) / 2;
        ctx.bezierCurveTo(acx, ay0, acx, ay1, ax1, ay1);
      }
      ctx.lineTo(xPos(data.length - 1), h - padB);
      ctx.lineTo(xPos(0), h - padB);
      ctx.closePath();
      ctx.fillStyle = color + '33';
      ctx.fill();
    }

    // Line
    ctx.beginPath();
    ctx.moveTo(xPos(0), yPos(parseInt(data[0].value)));
    for (var li = 1; li < data.length; li++) {
      var lx0 = xPos(li - 1), ly0 = yPos(parseInt(data[li - 1].value));
      var lx1 = xPos(li), ly1 = yPos(parseInt(data[li].value));
      var lcx = (lx0 + lx1) / 2;
      ctx.bezierCurveTo(lcx, ly0, lcx, ly1, lx1, ly1);
    }
    ctx.strokeStyle = color;
    ctx.lineWidth = 2.5;
    ctx.stroke();

    // Dots
    if (showDots) {
      data.forEach(function(d, i) {
        var dx = xPos(i), dy = yPos(parseInt(d.value));
        ctx.beginPath();
        ctx.arc(dx, dy, 3, 0, Math.PI * 2);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = t.cardBg;
        ctx.lineWidth = 1.5;
        ctx.stroke();
      });
    }

    // X-axis labels
    if (data.length >= 3) {
      var xAxis = el('div', 'flex justify-between text-xs mt-1 px-1');
      xAxis.style.color = t.textSecondary;
      xAxis.appendChild(el('span', '', data[0].label));
      xAxis.appendChild(el('span', '', data[Math.floor(data.length / 2)].label));
      xAxis.appendChild(el('span', '', data[data.length - 1].label));
      container.appendChild(xAxis);
    }
  }

  // Public API
  return {
    TOKENS: TOKENS,
    barChart: barChart,
    horizontalBars: horizontalBars,
    stackedBar: stackedBar,
    serviceBars: serviceBars,
    pieChart: pieChart,
    lineChart: lineChart,
    isDark: isDark,
    theme: theme,
  };
})();
