/**
 * Oregon Tires — Admin Referrals Management
 * Handles referral listing, status filtering, mark complete, and award points.
 */
(function() {
  'use strict';

  var API = '/api/admin/referrals.php';
  var currentFilter = 'all';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  function getCsrf() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }

  function fetchOpts(method, body) {
    var o = { method: method || 'GET', credentials: 'include', headers: { 'X-CSRF-Token': getCsrf() } };
    if (body) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    return o;
  }

  function formatDate(str) {
    if (!str) return '-';
    var locale = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
    return new Date(str).toLocaleDateString(locale, { month: 'short', day: 'numeric', year: 'numeric' });
  }

  var badgeStyles = { pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    rewarded: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    expired: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' };

  function makeBadge(status) {
    var s = document.createElement('span');
    s.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + (badgeStyles[status] || badgeStyles.pending);
    var statusMap = { pending: t('refPending','Pending'), completed: t('refCompleted','Completed'), rewarded: t('refRewarded','Rewarded'), expired: t('refExpired','Expired') };
    s.textContent = statusMap[status] || status; return s;
  }

  // ─── Load Referrals ──────────────────────────────────────────
  window.loadReferrals = async function() {
    var box = document.getElementById('referrals-container');
    if (!box) return;
    var params = new URLSearchParams({ mode: 'admin' });
    if (currentFilter !== 'all') params.set('status', currentFilter);
    try {
      var res = await fetch(API + '?' + params.toString(), fetchOpts());
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Load failed');
      render(box, json.data || [], json.stats || {});
    } catch (err) {
      console.error('loadReferrals error:', err);
      box.textContent = '';
      var p = document.createElement('p');
      p.className = 'text-red-600 dark:text-red-400 p-4';
      p.textContent = t('refLoadError', 'Failed to load referrals.');
      box.appendChild(p);
    }
  };

  // ─── Render ──────────────────────────────────────────────────
  function render(box, referrals, stats) {
    box.textContent = '';

    var sd = document.createElement('div');
    sd.className = 'flex flex-wrap gap-4 mb-4';
    [[t('refTotal','Total Referrals'), stats.total || 0, 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300'],
     [t('refCompleted','Completed'), stats.completed || 0, 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400'],
     [t('refAwarded','Points Awarded'), stats.points_awarded || 0, 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400']
    ].forEach(function(item) {
      var d = document.createElement('div'); d.className = 'border rounded-lg px-4 py-2 ' + item[2];
      var sp = document.createElement('span'); sp.className = 'text-sm font-semibold';
      sp.textContent = item[1] + ' ' + item[0]; d.appendChild(sp); sd.appendChild(d);
    });
    box.appendChild(sd);

    // Filter buttons
    var fd = document.createElement('div');
    fd.className = 'flex flex-wrap gap-2 mb-4';
    var filterLabels = { all: t('refAll','All'), pending: t('refPending','Pending'), booked: t('refBooked','Booked'), completed: t('refCompleted','Completed'), rewarded: t('refRewarded','Rewarded'), expired: t('refExpired','Expired') };
    ['all', 'pending', 'booked', 'completed', 'rewarded', 'expired'].forEach(function(f) {
      var btn = document.createElement('button');
      var active = currentFilter === f;
      btn.className = 'px-3 py-1.5 text-xs font-medium rounded-lg transition ' +
        (active ? 'bg-brand text-white' : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500');
      btn.textContent = filterLabels[f] || f;
      btn.addEventListener('click', function() { currentFilter = f; loadReferrals(); });
      fd.appendChild(btn);
    });
    box.appendChild(fd);

    if (!referrals.length) {
      var ep = document.createElement('p');
      ep.className = 'text-gray-500 dark:text-gray-400 text-center py-8';
      ep.textContent = t('refNoResults', 'No referrals found.');
      box.appendChild(ep);
      return;
    }

    // Table
    var wrap = document.createElement('div');
    wrap.className = 'overflow-x-auto';
    var table = document.createElement('table');
    table.className = 'w-full text-sm';
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var hr = document.createElement('tr');
    var colLabels = { code: t('refCode','Referral Code'), referrer: t('refReferrer','Referrer'), referred: t('refReferred','Referred Customer'), status: t('refStatus','Status'), points: t('refPoints','Bonus Points'), created: t('refCreated','Created'), actions: t('refActions','Actions') };
    ['code','referrer','referred','status','points','created','actions'].forEach(function(k) {
      var th = document.createElement('th'); th.className = 'text-left p-3 font-medium text-gray-600 dark:text-gray-300';
      th.textContent = colLabels[k]; hr.appendChild(th);
    });
    thead.appendChild(hr); table.appendChild(thead);

    var tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-200 dark:divide-gray-700';
    referrals.forEach(function(ref) {
      var tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition';

      var cells = [
        { text: ref.referral_code || '-', cls: 'p-3 font-mono text-xs text-gray-800 dark:text-gray-200' },
        { text: ref.referrer_name || '-', cls: 'p-3 font-medium text-gray-800 dark:text-gray-200' },
        { text: ref.referred_name || '-', cls: 'p-3 text-gray-600 dark:text-gray-300' },
        null, // status badge
        { text: String(ref.bonus_points || 0), cls: 'p-3 text-gray-600 dark:text-gray-300' },
        { text: formatDate(ref.created_at), cls: 'p-3 text-gray-600 dark:text-gray-300' },
        null  // actions
      ];

      cells.forEach(function(c, i) {
        var td = document.createElement('td');
        if (i === 3) { td.className = 'p-3'; td.appendChild(makeBadge(ref.status || 'pending')); }
        else if (i === 6) { td.className = 'p-3'; td.appendChild(buildActions(ref)); }
        else { td.className = c.cls; td.textContent = c.text; }
        tr.appendChild(td);
      });

      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    wrap.appendChild(table);
    box.appendChild(wrap);
  }

  // ─── Action Buttons ──────────────────────────────────────────
  function buildActions(ref) {
    var div = document.createElement('div');
    div.className = 'flex gap-2';
    if (ref.status === 'pending' || ref.status === 'booked') {
      var b = document.createElement('button');
      b.className = 'text-blue-600 dark:text-blue-400 hover:text-blue-800 text-xs font-medium';
      b.textContent = t('refMarkComplete', 'Mark Complete');
      b.addEventListener('click', function() { markComplete(ref.id, b); });
      div.appendChild(b);
    }
    if (ref.status === 'completed') {
      var a = document.createElement('button');
      a.className = 'text-green-600 dark:text-green-400 hover:text-green-800 text-xs font-medium';
      a.textContent = t('refAwardPoints', 'Award Points');
      a.addEventListener('click', function() { awardPoints(ref.id, a); });
      div.appendChild(a);
    }
    return div;
  }

  // ─── Mark Complete ───────────────────────────────────────────
  async function markComplete(id, btn) {
    if (btn) btn.disabled = true;
    try {
      var res = await fetch(API, fetchOpts('PUT', { id: id, status: 'completed' }));
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed');
      if (typeof showToast === 'function') showToast(t('refCompleteOk', 'Referral marked complete.'));
      loadReferrals();
    } catch (err) {
      console.error('markComplete error:', err);
      if (typeof showToast === 'function') showToast(t('refFail', 'Action failed. Please try again.'), true);
      if (btn) btn.disabled = false;
    }
  }

  // ─── Award Points ───────────────────────────────────────────
  async function awardPoints(id, btn) {
    if (btn) btn.disabled = true;
    try {
      var res = await fetch(API, fetchOpts('POST', { action: 'award_points', id: id }));
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed');
      if (typeof showToast === 'function') showToast(t('refAwardOk', 'Points awarded successfully.'));
      loadReferrals();
    } catch (err) {
      console.error('awardPoints error:', err);
      if (typeof showToast === 'function') showToast(t('refFail', 'Action failed. Please try again.'), true);
      if (btn) btn.disabled = false;
    }
  }

})();
