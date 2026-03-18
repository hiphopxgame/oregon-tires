/**
 * Oregon Tires — Admin Walk-in Waitlist Manager
 * Handles queue display, walk-in registration, and status transitions.
 */
(function() {
  'use strict';

  var API = '/api/admin/waitlist.php', refreshTimer = null;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }
  function getCsrf() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
  function hdrs(json) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (json) h['Content-Type'] = 'application/json';
    return h;
  }
  function toast(msg, err) { if (typeof showToast === 'function') showToast(msg, err); }

  function statusLabel(s) {
    var map = { waiting: t('wlWaiting','Waiting'), called: t('wlCalled','Called'), in_service: t('wlInService','In Service'), completed: t('wlCompleted','Completed'), no_show: t('wlNoShowStatus','No-Show') };
    return map[s] || s;
  }
  function actionLabel(key) {
    var map = { callNext: t('wlCallNext','Call'), noShow: t('wlNoShow','No-Show'), startService: t('wlStartService','Start'), complete: t('wlComplete','Complete') };
    return map[key] || key;
  }

  var SC = {
    waiting:    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    called:     'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    in_service: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    completed:  'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    no_show:    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  };

  function waitTime(ts) {
    if (!ts) return '-';
    var m = Math.floor((Date.now() - new Date(ts).getTime()) / 60000);
    if (m < 1) return t('wlJustNow', 'just now');
    if (m < 60) return m + t('wlMinAgo', 'm ago');
    return Math.floor(m / 60) + t('wlHrAgo', 'h ago') + ' ' + (m % 60) + t('wlMinAgo', 'm ago');
  }

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text) e.textContent = text;
    return e;
  }

  // ─── Load Waitlist ───────────────────────────────────────────
  window.loadWaitlist = async function() {
    var box = document.getElementById('waitlist-container');
    if (!box) return;
    try {
      var res = await fetch(API, { credentials: 'include' });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Load failed');
      render(box, json.data || []);
    } catch (err) {
      console.error('loadWaitlist error:', err);
      box.textContent = '';
      box.appendChild(el('p', 'text-red-600 dark:text-red-400 p-4', t('wlFailed', 'Action failed')));
    }
    startAutoRefresh();
  };

  // ─── Render ──────────────────────────────────────────────────
  function render(box, entries) {
    box.textContent = '';
    var bar = el('div', 'flex justify-between items-center mb-4');
    var addBtn = el('button', 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition', t('wlAddWalkin', '+ Add Walk-in'));
    addBtn.addEventListener('click', toggleForm);
    bar.appendChild(addBtn);
    box.appendChild(bar);
    box.appendChild(buildForm());

    var active = entries.filter(function(e) { return e.status === 'waiting' || e.status === 'called' || e.status === 'in_service'; });
    var past = entries.filter(function(e) { return e.status === 'completed' || e.status === 'no_show'; });

    if (!active.length && !past.length) {
      box.appendChild(el('p', 'text-gray-500 dark:text-gray-400 text-center py-8', t('wlNoEntries', 'No one on the waitlist.')));
      return;
    }
    if (active.length) box.appendChild(buildTable(active, true));
    if (past.length) {
      box.appendChild(el('hr', 'my-4 border-gray-200 dark:border-gray-700'));
      box.appendChild(buildTable(past, false));
    }
  }

  // ─── Build Table ─────────────────────────────────────────────
  function buildTable(entries, showActions) {
    var wrap = el('div', 'overflow-x-auto');
    var tbl = el('table', 'w-full text-sm');
    var thead = el('thead', 'bg-gray-50 dark:bg-gray-700');
    var hr = document.createElement('tr');
    var cols = [t('wlPosition','#'), t('wlName','Customer'), t('wlPhone','Phone'), t('wlVehicle','Vehicle'), t('wlService','Service Needed'), t('wlWait','Wait Time'), t('wlStatus','Status')];
    if (showActions) cols.push(t('wlActions','Actions'));
    cols.forEach(function(c) { hr.appendChild(el('th', 'text-left p-3 font-medium text-gray-600 dark:text-gray-300 text-xs uppercase', c)); });
    thead.appendChild(hr);
    tbl.appendChild(thead);

    var tbody = el('tbody', 'divide-y divide-gray-200 dark:divide-gray-700');
    entries.forEach(function(entry, i) {
      var tr = el('tr', 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition');
      [String(i+1), entry.customer_name||'-', entry.phone||'-', entry.vehicle_description||'-',
       entry.service_type||'-', waitTime(entry.check_in_at), null].forEach(function(val) {
        var td = el('td', 'p-3 text-gray-700 dark:text-gray-300');
        if (val !== null) td.textContent = val;
        else {
          var badge = el('span', 'inline-flex px-2 py-0.5 rounded-full text-xs font-medium ' + (SC[entry.status]||''), statusLabel(entry.status));
          td.appendChild(badge);
        }
        tr.appendChild(td);
      });
      if (showActions) {
        var tdA = el('td', 'p-3');
        var bw = el('div', 'flex gap-1 flex-wrap');
        getActions(entry.status).forEach(function(act) {
          var b = el('button', act.cls + ' text-xs font-medium px-2 py-1 rounded transition', actionLabel(act.label));
          b.addEventListener('click', function() { updateStatus(entry.id, act.next); });
          bw.appendChild(b);
        });
        tdA.appendChild(bw);
        tr.appendChild(tdA);
      }
      tbody.appendChild(tr);
    });
    tbl.appendChild(tbody);
    wrap.appendChild(tbl);
    return wrap;
  }

  function getActions(s) {
    var call = { label:'callNext', next:'called', cls:'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300' };
    var ns = { label:'noShow', next:'no_show', cls:'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300' };
    var start = { label:'startService', next:'in_service', cls:'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300' };
    var done = { label:'complete', next:'completed', cls:'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200' };
    if (s === 'waiting') return [call, ns];
    if (s === 'called') return [start, ns];
    if (s === 'in_service') return [done];
    return [];
  }

  // ─── Add Walk-in Form ────────────────────────────────────────
  function buildForm() {
    var panel = el('div', 'hidden bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-4 mb-4');
    panel.id = 'waitlist-form-panel';
    var grid = el('div', 'grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3');
    [{id:'wl-name',lbl:t('wlFormName','Name'),type:'text',req:true}, {id:'wl-phone',lbl:t('wlFormPhone','Phone'),type:'tel'},
     {id:'wl-vehicle',lbl:t('wlFormVehicle','Vehicle Description'),type:'text'}, {id:'wl-service',lbl:t('wlFormService','Service Type'),type:'text'}
    ].forEach(function(f) {
      var d = el('div');
      d.appendChild(el('label', 'block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1', f.lbl));
      var inp = el('input', 'w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200');
      inp.id = f.id; inp.type = f.type; if (f.req) inp.required = true;
      d.appendChild(inp);
      grid.appendChild(d);
    });
    panel.appendChild(grid);
    var row = el('div', 'flex gap-2');
    var sBtn = el('button', 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition', t('wlSave', 'Add to Waitlist'));
    sBtn.addEventListener('click', submitWalkin);
    var cBtn = el('button', 'px-4 py-2 border dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition', t('wlCancel', 'Cancel'));
    cBtn.addEventListener('click', toggleForm);
    row.appendChild(sBtn); row.appendChild(cBtn);
    panel.appendChild(row);
    return panel;
  }

  function toggleForm() {
    var p = document.getElementById('waitlist-form-panel');
    if (!p) return;
    p.classList.toggle('hidden');
    if (!p.classList.contains('hidden')) {
      ['wl-name','wl-phone','wl-vehicle','wl-service'].forEach(function(id) {
        var e = document.getElementById(id); if (e) e.value = '';
      });
      var n = document.getElementById('wl-name'); if (n) n.focus();
    }
  }

  // ─── Submit Walk-in ──────────────────────────────────────────
  async function submitWalkin() {
    var name = (document.getElementById('wl-name').value || '').trim();
    if (!name) { toast(t('wlNameRequired', 'Customer name is required'), true); return; }
    var payload = {
      customer_name: name,
      phone: (document.getElementById('wl-phone').value || '').trim(),
      vehicle_description: (document.getElementById('wl-vehicle').value || '').trim(),
      service_type: (document.getElementById('wl-service').value || '').trim()
    };
    try {
      var res = await fetch(API, { method:'POST', credentials:'include', headers:hdrs(true), body:JSON.stringify(payload) });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed');
      toast(t('wlAdded', 'Walk-in added'));
      toggleForm();
      loadWaitlist();
    } catch (err) { console.error('submitWalkin:', err); toast(t('wlFailed', 'Action failed'), true); }
  }

  // ─── Update Status ───────────────────────────────────────────
  async function updateStatus(id, newStatus) {
    try {
      var res = await fetch(API, { method:'PUT', credentials:'include', headers:hdrs(true), body:JSON.stringify({id:id,status:newStatus}) });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed');
      toast(t('wlUpdated', 'Status updated'));
      loadWaitlist();
    } catch (err) { console.error('updateStatus:', err); toast(t('wlFailed', 'Action failed'), true); }
  }

  // ─── Auto-refresh (30s) ──────────────────────────────────────
  function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(function() {
      if (document.getElementById('waitlist-container')) loadWaitlist();
      else clearInterval(refreshTimer);
    }, 30000);
  }

})();
