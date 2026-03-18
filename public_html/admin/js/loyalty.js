/**
 * Oregon Tires — Admin Loyalty Program Manager
 * Points ledger + rewards catalog CRUD.
 */
(function() {
  'use strict';

  var API_POINTS = '/api/admin/loyalty.php';
  var API_REWARDS = '/api/admin/loyalty-rewards.php';
  var ledger = [], rewards = [], stats = { members: 0, outstanding: 0, redeemed_month: 0 };
  var editingRewardId = null;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function getCsrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : (typeof csrfToken !== 'undefined' ? csrfToken : '');
  }
  function hdrs(json) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (json) h['Content-Type'] = 'application/json';
    return h;
  }
  function el(tag, cls, txt) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (txt) n.textContent = txt;
    return n;
  }
  function badge(text, color) {
    var colors = {
      green: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
      gray: 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300',
    };
    var s = el('span', 'text-xs px-2 py-1 rounded-full font-medium ' + (colors[color] || colors.gray), text);
    return s;
  }
  function input(id, type, val, cls) {
    var inp = document.createElement('input');
    inp.type = type || 'text'; inp.id = id;
    inp.className = cls || 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    if (val) inp.value = val;
    return inp;
  }
  function modal(id) {
    var old = document.getElementById(id);
    if (old) { old.remove(); return null; }
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center');
    ov.id = id;
    ov.addEventListener('click', function(e) { if (e.target === ov) ov.remove(); });
    return ov;
  }

  // ─── Load ─────────────────────────────────────────────────
  async function loadLoyalty() {
    try {
      var pA = fetch(API_POINTS, { credentials: 'include' });
      var pB = fetch(API_REWARDS, { credentials: 'include' });
      var rA = await (await pA).json(), rB = await (await pB).json();
      ledger = rA.success ? (rA.data || []) : [];
      stats = rA.stats || { members: 0, outstanding: 0, redeemed_month: 0 };
      rewards = rB.success ? (rB.data || []) : [];
    } catch (err) {
      console.error('loadLoyalty error:', err);
      if (typeof showToast === 'function') showToast(t('loyaltyLoadFail', 'Failed to load loyalty data'), true);
    }
    render();
  }

  function render() {
    var c = document.getElementById('loyalty-container');
    if (!c) return;
    c.textContent = '';
    c.appendChild(renderStats());
    c.appendChild(renderPointsSection());
    c.appendChild(renderRewardsSection());
  }

  // ─── Stats ────────────────────────────────────────────────
  function renderStats() {
    var wrap = el('div', 'grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6');
    [
      [t('loyaltyMembers', 'Total Members'), stats.members],
      [t('loyaltyOutstanding', 'Points Outstanding'), stats.outstanding],
      [t('loyaltyRedeemed', 'Redeemed This Month'), stats.redeemed_month],
    ].forEach(function(item) {
      var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border dark:border-gray-700');
      card.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400', item[0]));
      card.appendChild(el('div', 'text-2xl font-bold dark:text-gray-100 mt-1', String(item[1])));
      wrap.appendChild(card);
    });
    return wrap;
  }

  // ─── Points Ledger ────────────────────────────────────────
  function renderPointsSection() {
    var sec = el('div', 'mb-8');
    var hdr = el('div', 'flex items-center justify-between mb-3');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('loyaltyPointsLedger', 'Points Ledger')));
    var btn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('loyaltyAdjustPoints', 'Add / Deduct Points'));
    btn.addEventListener('click', function() { openPointsModal(); });
    hdr.appendChild(btn);
    sec.appendChild(hdr);

    var tbl = el('table', 'w-full text-left border-collapse');
    var thead = document.createElement('thead');
    var hr = el('tr', 'border-b dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400');
    ['Customer', 'Balance', 'Last Activity', 'Actions'].forEach(function(h) {
      hr.appendChild(el('th', 'px-4 py-3 font-medium', t('loyalty' + h.replace(/\s/g, ''), h)));
    });
    thead.appendChild(hr); tbl.appendChild(thead);
    var tbody = document.createElement('tbody');

    if (!ledger.length) {
      var tr = el('tr'), td = el('td', 'text-center py-8 text-gray-400 dark:text-gray-500', t('loyaltyNoMembers', 'No loyalty members yet.'));
      td.colSpan = 4; tr.appendChild(td); tbody.appendChild(tr);
    } else {
      ledger.forEach(function(row) {
        var tr = el('tr', 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50');
        tr.appendChild(el('td', 'px-4 py-3 font-medium text-sm dark:text-gray-200', row.customer_name || row.email || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-3 text-sm font-bold dark:text-gray-200', String(row.balance || 0)));
        tr.appendChild(el('td', 'px-4 py-3 text-sm text-gray-500 dark:text-gray-400', row.last_activity || '\u2014'));
        var tdA = el('td', 'px-4 py-3'), w = el('div', 'flex gap-2');
        var aB = el('button', 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400', t('loyaltyAdd', 'Add'));
        aB.addEventListener('click', function() { openPointsModal(row.customer_id, row.customer_name, 'add'); });
        w.appendChild(aB);
        var dB = el('button', 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400', t('loyaltyDeduct', 'Deduct'));
        dB.addEventListener('click', function() { openPointsModal(row.customer_id, row.customer_name, 'deduct'); });
        w.appendChild(dB); tdA.appendChild(w); tr.appendChild(tdA);
        tbody.appendChild(tr);
      });
    }
    tbl.appendChild(tbody);
    var wrap = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-x-auto');
    wrap.appendChild(tbl); sec.appendChild(wrap);
    return sec;
  }

  // ─── Points Modal ─────────────────────────────────────────
  function openPointsModal(custId, custName, mode) {
    var ov = modal('loyalty-points-modal');
    if (!ov) return;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md mx-4');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', t('loyaltyAdjustTitle', 'Adjust Points')));
    card.appendChild(el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', t('loyaltyCustomer', 'Customer')));
    var cInp = input('loy-m-cust', 'text', custName || '');
    cInp.placeholder = t('loyaltySearchCustomer', 'Search customer name or email...');
    card.appendChild(cInp);
    var cId = input('loy-m-cid', 'hidden', custId || '');
    card.appendChild(cId);
    var results = el('div', 'hidden mb-3 max-h-32 overflow-y-auto border rounded-lg dark:border-gray-600');
    results.id = 'loy-cust-results'; card.appendChild(results);
    var timer;
    cInp.addEventListener('input', function() {
      clearTimeout(timer); var q = cInp.value.trim();
      if (q.length < 2) { results.classList.add('hidden'); return; }
      timer = setTimeout(function() { searchCustomers(q, results, cInp, cId); }, 300);
    });
    card.appendChild(el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', t('loyaltyAmount', 'Points Amount')));
    var amt = input('loy-m-amt', 'number'); amt.min = '1'; card.appendChild(amt);
    card.appendChild(el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', t('loyaltyReason', 'Reason')));
    card.appendChild(input('loy-m-reason', 'text'));

    var row = el('div', 'flex gap-3 justify-end');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); }); row.appendChild(canc);
    var addB = el('button', 'bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700', t('loyaltyAddPoints', 'Add Points'));
    addB.addEventListener('click', function() { submitPoints('add', ov); }); row.appendChild(addB);
    var dedB = el('button', 'bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700', t('loyaltyDeductPoints', 'Deduct Points'));
    dedB.addEventListener('click', function() { submitPoints('deduct', ov); }); row.appendChild(dedB);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
    if (mode === 'add') addB.focus(); else if (mode === 'deduct') dedB.focus(); else amt.focus();
  }

  async function searchCustomers(q, box, nameInp, idInp) {
    try {
      var res = await fetch('/api/admin/customers.php?search=' + encodeURIComponent(q), { credentials: 'include' });
      var json = await res.json(); box.textContent = '';
      var list = json.success ? (json.data || []) : [];
      if (!list.length) { box.classList.add('hidden'); return; }
      box.classList.remove('hidden');
      list.slice(0, 5).forEach(function(c) {
        var name = c.name || (c.first_name + ' ' + c.last_name);
        var r = el('div', 'px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm dark:text-gray-200', name + ' \u2014 ' + (c.email || ''));
        r.addEventListener('click', function() { nameInp.value = name; idInp.value = c.id; box.classList.add('hidden'); });
        box.appendChild(r);
      });
    } catch (_) { box.classList.add('hidden'); }
  }

  async function submitPoints(mode, ov) {
    var cid = document.getElementById('loy-m-cid').value;
    var amt = parseInt(document.getElementById('loy-m-amt').value, 10);
    var reason = document.getElementById('loy-m-reason').value.trim();
    if (!cid) { if (typeof showToast === 'function') showToast(t('loyaltySelectCustomer', 'Select a customer'), true); return; }
    if (!amt || amt < 1) { if (typeof showToast === 'function') showToast(t('loyaltyEnterAmount', 'Enter a valid amount'), true); return; }
    try {
      var res = await fetch(API_POINTS, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ customer_id: cid, points: mode === 'deduct' ? -amt : amt, reason: reason }),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(mode === 'add' ? t('loyaltyPointsAdded', 'Points added') : t('loyaltyPointsDeducted', 'Points deducted'));
        ov.remove(); loadLoyalty();
      } else { if (typeof showToast === 'function') showToast(json.error || t('loyaltySaveFail', 'Save failed'), true); }
    } catch (err) {
      console.error('submitPoints error:', err);
      if (typeof showToast === 'function') showToast(t('loyaltyNetworkError', 'Network error'), true);
    }
  }

  // ─── Rewards Catalog ──────────────────────────────────────
  function renderRewardsSection() {
    var sec = el('div', 'mb-8');
    var hdr = el('div', 'flex items-center justify-between mb-3');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('loyaltyRewardsCatalog', 'Rewards Catalog')));
    var nb = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('loyaltyNewReward', 'New Reward'));
    nb.addEventListener('click', function() { openRewardForm(); }); hdr.appendChild(nb);
    sec.appendChild(hdr);
    var grid = el('div', 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4');
    if (!rewards.length) {
      grid.appendChild(el('p', 'text-gray-400 dark:text-gray-500 col-span-full text-center py-8', t('loyaltyNoRewards', 'No rewards yet. Click "New Reward" to create one.')));
    } else {
      rewards.forEach(function(rw) {
        var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 flex flex-col');
        var top = el('div', 'flex items-start justify-between mb-2');
        top.appendChild(el('h4', 'font-semibold dark:text-gray-100 text-sm', (currentLang === 'es' ? rw.name_es : rw.name_en) || rw.name_en || '(unnamed)'));
        top.appendChild(badge(Number(rw.active) ? t('loyaltyActive', 'Active') : t('loyaltyInactive', 'Inactive'), Number(rw.active) ? 'green' : 'gray'));
        card.appendChild(top);
        card.appendChild(el('div', 'text-xl font-bold text-brand mb-1', String(rw.points_cost || 0) + ' pts'));
        card.appendChild(el('p', 'text-sm text-gray-500 dark:text-gray-400 flex-1', rw.description || ''));
        var acts = el('div', 'flex gap-2 mt-3 pt-3 border-t dark:border-gray-700');
        var eB = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('actionEdit', 'Edit'));
        eB.addEventListener('click', function() { openRewardForm(rw); }); acts.appendChild(eB);
        var tB = el('button', Number(rw.active) ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400' : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400',
          Number(rw.active) ? t('actionDeactivate', 'Deactivate') : t('actionActivate', 'Activate'));
        tB.addEventListener('click', function() { toggleRewardActive(rw); }); acts.appendChild(tB);
        var xB = el('button', 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400', t('actionDelete', 'Delete'));
        xB.addEventListener('click', function() { deleteReward(rw.id); }); acts.appendChild(xB);
        card.appendChild(acts); grid.appendChild(card);
      });
    }
    sec.appendChild(grid); return sec;
  }

  // ─── Reward Form Modal ────────────────────────────────────
  function openRewardForm(rw) {
    var ov = modal('loyalty-reward-modal');
    if (!ov) { ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center'); ov.id = 'loyalty-reward-modal'; ov.addEventListener('click', function(e) { if (e.target === ov) ov.remove(); }); }
    editingRewardId = rw ? rw.id : null;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md mx-4');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', rw ? t('loyaltyEditReward', 'Edit Reward') : t('loyaltyNewReward', 'New Reward')));
    [
      ['rw-name-en', t('loyaltyNameEn', 'Name (EN)'), rw ? rw.name_en : '', 'text'],
      ['rw-name-es', t('loyaltyNameEs', 'Name (ES)'), rw ? rw.name_es : '', 'text'],
      ['rw-cost', t('loyaltyCost', 'Points Cost'), rw ? rw.points_cost : '', 'number'],
      ['rw-desc', t('loyaltyDescription', 'Description'), rw ? rw.description : '', 'text'],
    ].forEach(function(f) {
      card.appendChild(el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', f[1]));
      card.appendChild(input(f[0], f[3], f[2] || ''));
    });
    var chkW = el('label', 'flex items-center gap-2 mb-4 text-sm dark:text-gray-300');
    var chk = document.createElement('input'); chk.type = 'checkbox'; chk.id = 'rw-active';
    chk.checked = rw ? Number(rw.active) === 1 : true;
    chkW.appendChild(chk); chkW.appendChild(document.createTextNode(t('loyaltyActive', 'Active')));
    card.appendChild(chkW);
    var row = el('div', 'flex gap-3 justify-end');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); }); row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('actionSave', 'Save'));
    save.addEventListener('click', function() { saveReward(ov); }); row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  async function saveReward(ov) {
    var ne = document.getElementById('rw-name-en').value.trim();
    if (!ne) { if (typeof showToast === 'function') showToast(t('loyaltyNameRequired', 'Name (EN) is required'), true); return; }
    var payload = {
      name_en: ne, name_es: document.getElementById('rw-name-es').value.trim(),
      points_cost: parseInt(document.getElementById('rw-cost').value, 10) || 0,
      description: document.getElementById('rw-desc').value.trim(),
      active: document.getElementById('rw-active').checked ? 1 : 0,
    };
    if (editingRewardId) payload.id = editingRewardId;
    try {
      var res = await fetch(API_REWARDS, {
        method: editingRewardId ? 'PUT' : 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify(payload),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingRewardId ? t('loyaltyRewardUpdated', 'Reward updated') : t('loyaltyRewardCreated', 'Reward created'));
        ov.remove(); editingRewardId = null; loadLoyalty();
      } else { if (typeof showToast === 'function') showToast(json.error || t('loyaltySaveFail', 'Save failed'), true); }
    } catch (err) {
      console.error('saveReward error:', err);
      if (typeof showToast === 'function') showToast(t('loyaltyNetworkError', 'Network error'), true);
    }
  }

  async function toggleRewardActive(rw) {
    try {
      var res = await fetch(API_REWARDS, {
        method: 'PUT', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ id: rw.id, name_en: rw.name_en, name_es: rw.name_es, points_cost: rw.points_cost, description: rw.description, active: Number(rw.active) ? 0 : 1 }),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(Number(rw.active) ? t('loyaltyRewardDeactivated', 'Reward deactivated') : t('loyaltyRewardActivated', 'Reward activated'));
        loadLoyalty();
      }
    } catch (err) { console.error('toggleRewardActive error:', err); }
  }

  async function deleteReward(id) {
    if (!confirm(t('loyaltyDeleteConfirm', 'Delete this reward? This cannot be undone.'))) return;
    try {
      var res = await fetch(API_REWARDS, {
        method: 'DELETE', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      var json = await res.json();
      if (json.success) { if (typeof showToast === 'function') showToast(t('loyaltyRewardDeleted', 'Reward deleted')); loadLoyalty(); }
    } catch (err) { console.error('deleteReward error:', err); }
  }

  // ─── Expose ───────────────────────────────────────────────
  window.loadLoyalty = loadLoyalty;
})();
