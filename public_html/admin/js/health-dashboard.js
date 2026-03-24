/**
 * Oregon Tires — System Health Dashboard
 * Renders health monitoring data in the admin panel.
 */

(function () {
'use strict';

function t(key, fallback) {
  return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
}

function _el(tag, cls, text) {
  var e = document.createElement(tag);
  if (cls) e.className = cls;
  if (text) e.textContent = text;
  return e;
}

function fmtDate(str) {
  if (!str) return '-';
  var d = new Date(str);
  var lang = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
  return d.toLocaleDateString(lang, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function fmtBytes(bytes) {
  if (!bytes) return '-';
  var mb = (bytes / 1048576).toFixed(1);
  return mb + ' MB';
}

function timeAgo(dateStr) {
  if (!dateStr) return '-';
  var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000);
  if (diff < 1) return t('healthJustNow', 'just now');
  if (diff < 60) return diff + 'm ' + t('healthAgo', 'ago');
  var h = Math.floor(diff / 60);
  if (h < 24) return h + 'h ' + t('healthAgo', 'ago');
  var d = Math.floor(h / 24);
  return d + 'd ' + t('healthAgo', 'ago');
}

var _healthRefreshTimer = null;

window.loadHealthDashboard = async function () {
  var container = document.getElementById('health-dashboard-content');
  if (!container) return;
  container.textContent = '';

  if (_healthRefreshTimer) { clearInterval(_healthRefreshTimer); _healthRefreshTimer = null; }

  container.appendChild(_el('p', 'text-gray-400 dark:text-gray-500 text-center py-12', t('healthLoading', 'Loading health data...')));

  try {
    var res = await fetch('/api/admin/health-dashboard.php', { credentials: 'include' });
    var json = await res.json();
    container.textContent = '';

    if (!json.success) {
      container.appendChild(_el('p', 'text-red-500 text-center py-8', json.error || 'Error'));
      return;
    }

    var d = json.data;

    // ── Overall Status Banner ─────────────────────────────────────────
    var banner = _el('div', 'rounded-xl p-5 mb-6 text-center');
    var statusConfig = {
      healthy:  { bg: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800', icon: '\uD83D\uDFE2', text: t('healthAllOperational', 'All Systems Operational'), color: 'text-green-700 dark:text-green-400' },
      degraded: { bg: 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800', icon: '\uD83D\uDFE1', text: t('healthDegraded', 'Performance Degraded'), color: 'text-yellow-700 dark:text-yellow-400' },
      critical: { bg: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800', icon: '\uD83D\uDD34', text: t('healthIssues', 'Issues Detected'), color: 'text-red-700 dark:text-red-400' },
    };
    var sc = statusConfig[d.overall_status] || statusConfig.healthy;
    banner.className += ' ' + sc.bg;
    banner.appendChild(_el('div', 'text-3xl mb-1', sc.icon));
    banner.appendChild(_el('div', 'text-lg font-bold ' + sc.color, sc.text));
    if (d.last_check) {
      banner.appendChild(_el('div', 'text-xs text-gray-500 dark:text-gray-400 mt-1', t('healthLastCheck', 'Last check') + ': ' + timeAgo(d.last_check)));
    }
    container.appendChild(banner);

    // ── Stat Cards ────────────────────────────────────────────────────
    var cards = _el('div', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6');

    // Uptime card
    var uptimeCard = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
    uptimeCard.appendChild(_el('div', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1', t('healthUptime24h', '24h Uptime')));
    var uptimeVal = d.uptime_24h || 0;
    var uptimeCls = uptimeVal >= 99.5 ? 'text-green-600 dark:text-green-400' : uptimeVal >= 98 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400';
    uptimeCard.appendChild(_el('div', 'text-2xl font-bold ' + uptimeCls, uptimeVal.toFixed(1) + '%'));
    uptimeCard.appendChild(_el('div', 'text-xs text-gray-400 mt-1', t('healthUptime7d', '7d') + ': ' + (d.uptime_7d || 0).toFixed(1) + '% | ' + t('healthUptime30d', '30d') + ': ' + (d.uptime_30d || 0).toFixed(1) + '%'));
    cards.appendChild(uptimeCard);

    // SSL card
    var sslCard = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
    sslCard.appendChild(_el('div', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1', t('healthSslCert', 'SSL Certificate')));
    if (d.ssl) {
      var sslDays = d.ssl.days_remaining || 0;
      var sslCls = sslDays > 30 ? 'text-green-600 dark:text-green-400' : sslDays > 14 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400';
      sslCard.appendChild(_el('div', 'text-2xl font-bold ' + sslCls, sslDays + ' ' + t('healthDays', 'days')));
      sslCard.appendChild(_el('div', 'text-xs text-gray-400 mt-1', (d.ssl.issuer || '') + ' \u2022 ' + t('healthExpires', 'Expires') + ' ' + (d.ssl.expires_at || '')));
    } else {
      sslCard.appendChild(_el('div', 'text-sm text-gray-400', t('healthNoData', 'No data yet')));
    }
    cards.appendChild(sslCard);

    // Backup card
    var backupCard = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
    backupCard.appendChild(_el('div', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1', t('healthLastBackup', 'Last Backup')));
    if (d.last_backup) {
      var bkStatus = d.last_backup.status === 'ok' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
      backupCard.appendChild(_el('div', 'text-2xl font-bold ' + bkStatus, d.last_backup.status === 'ok' ? '\u2713' : '\u2717'));
      backupCard.appendChild(_el('div', 'text-xs text-gray-400 mt-1', timeAgo(d.last_backup.checked_at) + ' \u2022 ' + fmtBytes(d.last_backup.size_bytes)));
    } else {
      backupCard.appendChild(_el('div', 'text-sm text-gray-400', t('healthNoBackup', 'No backup yet')));
    }
    cards.appendChild(backupCard);

    // Disk card
    var diskCard = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700');
    diskCard.appendChild(_el('div', 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1', t('healthDiskUsage', 'Disk Usage')));
    if (d.disk) {
      var diskPct = d.disk.percent || 0;
      var diskCls = diskPct < 80 ? 'bg-green-500' : diskPct < 90 ? 'bg-yellow-500' : 'bg-red-500';
      diskCard.appendChild(_el('div', 'text-2xl font-bold text-gray-900 dark:text-white', diskPct + '%'));
      var barOuter = _el('div', 'w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full mt-2 overflow-hidden');
      var barInner = _el('div', diskCls + ' h-2 rounded-full transition-all');
      barInner.style.width = Math.min(diskPct, 100) + '%';
      barOuter.appendChild(barInner);
      diskCard.appendChild(barOuter);
      diskCard.appendChild(_el('div', 'text-xs text-gray-400 mt-1', (d.disk.used_mb || 0) + ' MB / ' + (d.disk.total_mb || 0) + ' MB'));
    } else {
      diskCard.appendChild(_el('div', 'text-sm text-gray-400', t('healthNoData', 'No data yet')));
    }
    cards.appendChild(diskCard);

    container.appendChild(cards);

    // ── Uptime Chart (30 days) ────────────────────────────────────────
    if (d.uptime_chart && d.uptime_chart.length > 0) {
      var chartSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mb-6');
      chartSection.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white mb-4', t('healthUptimeChart', 'Uptime History')));

      var chartWrap = _el('div', 'flex items-end gap-[2px] h-24 overflow-x-auto');
      d.uptime_chart.forEach(function (day) {
        var pct = day.uptime_pct || 0;
        var barColor = pct >= 99.5 ? 'bg-green-500' : pct >= 98 ? 'bg-yellow-500' : 'bg-red-500';
        var bar = _el('div', barColor + ' rounded-t min-w-[8px] flex-1 transition-all relative group');
        bar.style.height = Math.max(pct, 2) + '%';
        bar.title = day.date + ': ' + pct.toFixed(1) + '%';
        chartWrap.appendChild(bar);
      });
      chartSection.appendChild(chartWrap);

      // Date labels
      var labelRow = _el('div', 'flex justify-between text-[10px] text-gray-400 mt-1');
      if (d.uptime_chart.length > 0) {
        labelRow.appendChild(_el('span', '', d.uptime_chart[0].date));
        labelRow.appendChild(_el('span', '', d.uptime_chart[d.uptime_chart.length - 1].date));
      }
      chartSection.appendChild(labelRow);

      container.appendChild(chartSection);
    }

    // ── Response Times ────────────────────────────────────────────────
    if (d.response_times && d.response_times.length > 0) {
      var rtSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mb-6');
      rtSection.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white mb-4', t('healthResponseTimes', 'Response Times (24h avg)')));

      var maxMs = Math.max.apply(null, d.response_times.map(function (r) { return r.avg_ms || 0; }));
      d.response_times.forEach(function (rt) {
        var row = _el('div', 'flex items-center gap-3 mb-2');
        var label = _el('div', 'w-24 text-xs text-gray-600 dark:text-gray-400 truncate', rt.label);
        row.appendChild(label);
        var barWrap = _el('div', 'flex-1 h-5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden');
        var barFill = _el('div', 'h-5 rounded-full transition-all flex items-center justify-end px-2');
        var ms = rt.avg_ms || 0;
        barFill.className += ms < 200 ? ' bg-green-500' : ms < 500 ? ' bg-yellow-500' : ' bg-red-500';
        barFill.style.width = Math.max((ms / (maxMs || 1)) * 100, 8) + '%';
        var msLabel = _el('span', 'text-[10px] font-bold text-white', ms + 'ms');
        barFill.appendChild(msLabel);
        barWrap.appendChild(barFill);
        row.appendChild(barWrap);
        rtSection.appendChild(row);
      });
      container.appendChild(rtSection);
    }

    // ── Two-column layout for tests + crons ──────────────────────────
    var twoCol = _el('div', 'grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6');

    // Feature Tests
    var ftSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700');
    var ftHeader = _el('div', 'flex items-center justify-between mb-3');
    ftHeader.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white', t('healthFeatureTests', 'Feature Tests')));
    if (d.feature_tests && d.feature_tests.last_run) {
      var badge = _el('span', 'text-xs px-2 py-0.5 rounded-full font-medium ' +
        (d.feature_tests.failed === 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'),
        d.feature_tests.passed + '/' + (d.feature_tests.passed + d.feature_tests.failed) + ' ' + t('healthPassed', 'passed'));
      ftHeader.appendChild(badge);
    }
    ftSection.appendChild(ftHeader);

    if (d.feature_tests && d.feature_tests.tests && d.feature_tests.tests.length > 0) {
      d.feature_tests.tests.forEach(function (test) {
        var row = _el('div', 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0');
        var leftSide = _el('div', 'flex items-center gap-2');
        leftSide.appendChild(_el('span', test.status === 'ok' ? 'text-green-500' : 'text-red-500', test.status === 'ok' ? '\u2713' : '\u2717'));
        leftSide.appendChild(_el('span', 'text-sm text-gray-700 dark:text-gray-300', test.label));
        row.appendChild(leftSide);
        if (test.response_time_ms) {
          row.appendChild(_el('span', 'text-xs text-gray-400', test.response_time_ms + 'ms'));
        }
        ftSection.appendChild(row);
      });
    } else {
      ftSection.appendChild(_el('p', 'text-sm text-gray-400 py-4 text-center', t('healthNoTests', 'No test results yet. Run the full health check first.')));
    }
    twoCol.appendChild(ftSection);

    // Cron Status
    var cronSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700');
    cronSection.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white mb-3', t('healthCronStatus', 'Cron Jobs')));

    if (d.cron_status && d.cron_status.length > 0) {
      d.cron_status.forEach(function (cron) {
        var details = cron.details ? JSON.parse(cron.details) : {};
        var row = _el('div', 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0');
        var leftSide = _el('div', 'flex items-center gap-2');
        leftSide.appendChild(_el('span', cron.status === 'ok' ? 'text-green-500' : 'text-yellow-500', cron.status === 'ok' ? '\u2713' : '!'));
        leftSide.appendChild(_el('span', 'text-sm text-gray-700 dark:text-gray-300', cron.label));
        row.appendChild(leftSide);
        row.appendChild(_el('span', 'text-xs text-gray-400', details.hours_ago != null ? details.hours_ago + 'h ' + t('healthAgo', 'ago') : t('healthNever', 'never')));
        cronSection.appendChild(row);
      });
    } else {
      cronSection.appendChild(_el('p', 'text-sm text-gray-400 py-4 text-center', t('healthNoCrons', 'No cron data yet')));
    }
    twoCol.appendChild(cronSection);

    container.appendChild(twoCol);

    // ── Incidents ─────────────────────────────────────────────────────
    var incSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mb-6');
    incSection.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white mb-3', t('healthIncidents', 'Recent Incidents')));

    if (d.incidents && d.incidents.length > 0) {
      d.incidents.forEach(function (inc) {
        var row = _el('div', 'flex items-start gap-3 py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0');
        var dot = _el('span', 'mt-1 flex-shrink-0 w-2 h-2 rounded-full ' + (inc.status === 'fail' ? 'bg-red-500' : 'bg-yellow-500'));
        row.appendChild(dot);
        var content = _el('div', 'flex-1');
        var topLine = _el('div', 'flex items-center gap-2');
        topLine.appendChild(_el('span', 'text-sm font-medium text-gray-800 dark:text-gray-200', inc.label));
        topLine.appendChild(_el('span', 'text-xs px-1.5 py-0.5 rounded font-medium ' +
          (inc.status === 'fail' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'), inc.status));
        content.appendChild(topLine);
        content.appendChild(_el('div', 'text-xs text-gray-400 mt-0.5', fmtDate(inc.checked_at)));
        row.appendChild(content);
        incSection.appendChild(row);
      });
    } else {
      incSection.appendChild(_el('div', 'text-center py-6'));
      var noInc = incSection.lastChild;
      noInc.appendChild(_el('div', 'text-2xl mb-1', '\u2705'));
      noInc.appendChild(_el('div', 'text-sm text-gray-500 dark:text-gray-400', t('healthNoIncidents', 'No incidents in the last 7 days')));
    }
    container.appendChild(incSection);

    // ── Backup History ────────────────────────────────────────────────
    if (d.backup_history && d.backup_history.length > 0) {
      var bkSection = _el('div', 'bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mb-6');
      bkSection.appendChild(_el('h3', 'text-sm font-semibold text-gray-900 dark:text-white mb-3', t('healthBackupLog', 'Backup History')));

      d.backup_history.forEach(function (bk) {
        var details = bk.details ? JSON.parse(bk.details) : {};
        var row = _el('div', 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0');
        var leftSide = _el('div', 'flex items-center gap-2');
        leftSide.appendChild(_el('span', bk.status === 'ok' ? 'text-green-500' : 'text-red-500', bk.status === 'ok' ? '\u2713' : '\u2717'));
        leftSide.appendChild(_el('span', 'text-sm text-gray-700 dark:text-gray-300', fmtDate(bk.checked_at)));
        row.appendChild(leftSide);
        var rightSide = _el('div', 'flex items-center gap-3 text-xs text-gray-400');
        if (details.size_mb) rightSide.appendChild(_el('span', '', details.size_mb + ' MB'));
        if (details.duration_sec) rightSide.appendChild(_el('span', '', details.duration_sec + 's'));
        if (details.file) rightSide.appendChild(_el('span', 'font-mono', details.file));
        row.appendChild(rightSide);
        bkSection.appendChild(row);
      });
      container.appendChild(bkSection);
    }

    // Auto-refresh every 5 minutes
    _healthRefreshTimer = setInterval(function () {
      var healthTab = document.getElementById('tab-health');
      if (healthTab && !healthTab.classList.contains('hidden')) {
        loadHealthDashboard();
      } else {
        clearInterval(_healthRefreshTimer);
        _healthRefreshTimer = null;
      }
    }, 300000);

  } catch (err) {
    container.textContent = '';
    container.appendChild(_el('p', 'text-red-500 text-center py-8', t('healthError', 'Error loading health data')));
    console.error('Health dashboard error:', err);
  }
};

})();
