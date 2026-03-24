/**
 * Oregon Tires — Admin Google Business Profile Manager
 * Sub-tabs: Posts, Hours Sync, Insights, Q&A
 * Uses createElement/appendChild only (no innerHTML per security rules).
 */
(function() {
  'use strict';

  var API_POSTS = '/api/admin/gbp-posts.php';
  var API_SYNC  = '/api/admin/google-business-sync.php';

  var posts = [], hours = [], insights = {}, questions = [];
  var activeSubTab = 'posts';
  var editingPostId = null;

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
    if (txt !== undefined && txt !== null) n.textContent = String(txt);
    return n;
  }
  function badge(text, color) {
    var colors = {
      green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
      red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
      blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
      gray: 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300',
      purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    };
    return el('span', 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + (colors[color] || colors.gray), text);
  }
  function input(id, type, val, placeholder) {
    var inp = document.createElement('input');
    inp.type = type || 'text'; inp.id = id;
    inp.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    if (val !== undefined && val !== null) inp.value = val;
    if (placeholder) inp.placeholder = placeholder;
    return inp;
  }
  function label(text) {
    return el('label', 'block text-sm font-medium mb-1 dark:text-gray-300', text);
  }
  function select(id, options, selected) {
    var sel = document.createElement('select');
    sel.id = id;
    sel.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    options.forEach(function(o) {
      var opt = document.createElement('option');
      opt.value = o.value; opt.textContent = o.label;
      if (String(o.value) === String(selected)) opt.selected = true;
      sel.appendChild(opt);
    });
    return sel;
  }
  function modal(id) {
    var old = document.getElementById(id);
    if (old) { old.remove(); return null; }
    var ov = el('div', 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center');
    ov.id = id;
    ov.addEventListener('click', function(e) { if (e.target === ov) ov.remove(); });
    return ov;
  }
  function spinner() {
    var s = el('div', 'flex justify-center py-12');
    s.appendChild(el('div', 'animate-spin rounded-full h-8 w-8 border-b-2 border-brand'));
    return s;
  }
  function formatDate(str) {
    if (!str) return '\u2014';
    var locale = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US';
    return new Date(str).toLocaleDateString(locale, { month: 'short', day: 'numeric', year: 'numeric' });
  }

  // ── Main Load ─────────────────────────────────────────────
  async function loadGBP() {
    var c = document.getElementById('tab-gbp');
    if (!c) return;
    c.textContent = '';
    c.appendChild(renderSubTabs());
    var content = el('div', '');
    content.id = 'gbp-content';
    c.appendChild(content);
    await switchSubTab(activeSubTab);
  }

  // ── Sub-tab Navigation ────────────────────────────────────
  function renderSubTabs() {
    var nav = el('div', 'flex gap-1 mb-6 border-b dark:border-gray-700');
    var tabs = [
      { key: 'posts',    label: t('gbpPosts', 'Posts') },
      { key: 'hours',    label: t('gbpHours', 'Hours Sync') },
      { key: 'insights', label: t('gbpInsights', 'Insights') },
      { key: 'qa',       label: t('gbpQA', 'Q&A') },
    ];
    tabs.forEach(function(tab) {
      var btn = el('button', 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition ' +
        (activeSubTab === tab.key
          ? 'border-brand text-brand dark:text-green-400 dark:border-green-400'
          : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'),
        tab.label);
      btn.setAttribute('data-subtab', tab.key);
      btn.addEventListener('click', function() { switchSubTab(tab.key); });
      nav.appendChild(btn);
    });
    return nav;
  }

  async function switchSubTab(key) {
    activeSubTab = key;
    var c = document.getElementById('tab-gbp');
    if (c) {
      c.querySelectorAll('[data-subtab]').forEach(function(b) {
        var isActive = b.getAttribute('data-subtab') === key;
        b.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition ' +
          (isActive
            ? 'border-brand text-brand dark:text-green-400 dark:border-green-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300');
      });
    }
    var content = document.getElementById('gbp-content');
    if (!content) return;
    content.textContent = '';
    content.appendChild(spinner());

    if (key === 'posts') { await fetchPosts(); renderPosts(content); }
    else if (key === 'hours') { await fetchHours(); renderHours(content); }
    else if (key === 'insights') { await fetchInsights(); renderInsights(content); }
    else if (key === 'qa') { await fetchQuestions(); renderQA(content); }
  }

  // ── Posts ─────────────────────────────────────────────────
  async function fetchPosts() {
    try {
      var res = await fetch(API_POSTS, { credentials: 'include' });
      var json = await res.json();
      posts = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchPosts error:', err);
      if (typeof showToast === 'function') showToast(t('gbpPostsLoadFail', 'Failed to load posts'), true);
    }
  }

  function renderPosts(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('gbpPostsList', 'GBP Posts')));
    var addBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('gbpNewPost', 'New Post'));
    addBtn.addEventListener('click', function() { openPostModal(); });
    hdr.appendChild(addBtn);
    content.appendChild(hdr);

    if (!posts.length) {
      var empty = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-8 text-center');
      empty.appendChild(el('p', 'text-gray-400 dark:text-gray-500', t('gbpNoPosts', 'No GBP posts yet. Create one to share updates on Google Business.')));
      content.appendChild(empty);
      return;
    }

    var grid = el('div', 'grid grid-cols-1 md:grid-cols-2 gap-4');
    posts.forEach(function(p) {
      var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 flex flex-col');
      var top = el('div', 'flex items-start justify-between mb-2');
      var title = currentLang === 'es' ? (p.title_es || p.title_en) : p.title_en;
      top.appendChild(el('h4', 'font-semibold dark:text-gray-100 text-sm', title || t('gbpUntitled', '(Untitled)')));
      var statusColor = p.status === 'published' ? 'green' : p.status === 'scheduled' ? 'blue' : 'gray';
      top.appendChild(badge((p.status || 'draft').charAt(0).toUpperCase() + (p.status || 'draft').slice(1), statusColor));
      card.appendChild(top);

      // Type badge
      if (p.post_type) {
        card.appendChild(badge(p.post_type.charAt(0).toUpperCase() + p.post_type.slice(1), 'purple'));
      }

      var body = currentLang === 'es' ? (p.body_es || p.body_en) : p.body_en;
      if (body) {
        var bodyEl = el('p', 'text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-3', body);
        card.appendChild(bodyEl);
      }

      var meta = el('div', 'text-xs text-gray-400 dark:text-gray-500 mt-2');
      meta.textContent = formatDate(p.created_at);
      if (p.cta_type) meta.textContent += '  \u00b7  CTA: ' + p.cta_type;
      card.appendChild(meta);

      var acts = el('div', 'flex gap-2 mt-3 pt-3 border-t dark:border-gray-700');
      var eB = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('actionEdit', 'Edit'));
      eB.addEventListener('click', function() { openPostModal(p); });
      acts.appendChild(eB);
      if (p.status !== 'published') {
        var pubB = el('button', 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400', t('gbpPublish', 'Publish'));
        pubB.addEventListener('click', function() { publishPost(p.id); });
        acts.appendChild(pubB);
      }
      var xB = el('button', 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400', t('actionDelete', 'Delete'));
      xB.addEventListener('click', function() { deletePost(p.id); });
      acts.appendChild(xB);
      card.appendChild(acts);
      grid.appendChild(card);
    });
    content.appendChild(grid);
  }

  function openPostModal(p) {
    var ov = modal('gbp-post-modal');
    if (!ov) return;
    editingPostId = p ? p.id : null;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', p ? t('gbpEditPost', 'Edit Post') : t('gbpNewPost', 'New Post')));

    card.appendChild(label(t('gbpPostType', 'Post Type')));
    card.appendChild(select('gp-type', [
      { value: 'update', label: t('gbpTypeUpdate', 'Update') },
      { value: 'offer', label: t('gbpTypeOffer', 'Offer') },
      { value: 'event', label: t('gbpTypeEvent', 'Event') },
      { value: 'product', label: t('gbpTypeProduct', 'Product') },
    ], p ? p.post_type : 'update'));

    card.appendChild(label(t('gbpTitleEn', 'Title (EN)')));
    card.appendChild(input('gp-title-en', 'text', p ? p.title_en : ''));
    card.appendChild(label(t('gbpTitleEs', 'Title (ES)')));
    card.appendChild(input('gp-title-es', 'text', p ? p.title_es : ''));
    card.appendChild(label(t('gbpBodyEn', 'Body (EN)')));
    var bodyEn = document.createElement('textarea');
    bodyEn.id = 'gp-body-en'; bodyEn.rows = 3;
    bodyEn.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    bodyEn.value = p ? (p.body_en || '') : '';
    card.appendChild(bodyEn);
    card.appendChild(label(t('gbpBodyEs', 'Body (ES)')));
    var bodyEs = document.createElement('textarea');
    bodyEs.id = 'gp-body-es'; bodyEs.rows = 3;
    bodyEs.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    bodyEs.value = p ? (p.body_es || '') : '';
    card.appendChild(bodyEs);

    card.appendChild(label(t('gbpImageUrl', 'Image URL')));
    card.appendChild(input('gp-image', 'url', p ? p.image_url : '', 'https://...'));

    // CTA
    card.appendChild(label(t('gbpCtaType', 'CTA Type')));
    card.appendChild(select('gp-cta-type', [
      { value: '', label: t('gbpNoCta', 'None') },
      { value: 'book', label: t('gbpCtaBook', 'Book') },
      { value: 'call', label: t('gbpCtaCall', 'Call Now') },
      { value: 'learn_more', label: t('gbpCtaLearn', 'Learn More') },
      { value: 'order', label: t('gbpCtaOrder', 'Order Online') },
      { value: 'sign_up', label: t('gbpCtaSignUp', 'Sign Up') },
    ], p ? p.cta_type : ''));
    card.appendChild(label(t('gbpCtaUrl', 'CTA URL')));
    card.appendChild(input('gp-cta-url', 'url', p ? p.cta_url : '', 'https://oregon.tires/...'));

    // Offer/Event dates
    var datesRow = el('div', 'grid grid-cols-2 gap-3');
    var d1 = el('div', '');
    d1.appendChild(label(t('gbpStartDate', 'Start Date')));
    d1.appendChild(input('gp-start', 'date', p ? (p.start_date || '') : ''));
    datesRow.appendChild(d1);
    var d2 = el('div', '');
    d2.appendChild(label(t('gbpEndDate', 'End Date')));
    d2.appendChild(input('gp-end', 'date', p ? (p.end_date || '') : ''));
    datesRow.appendChild(d2);
    card.appendChild(datesRow);

    // Buttons
    var row = el('div', 'flex gap-3 justify-end mt-2');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('actionSave', 'Save'));
    save.addEventListener('click', function() { savePost(ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  async function savePost(ov) {
    var data = {
      _csrf: getCsrf(),
      post_type: document.getElementById('gp-type').value,
      title_en: document.getElementById('gp-title-en').value.trim(),
      title_es: document.getElementById('gp-title-es').value.trim(),
      body_en: document.getElementById('gp-body-en').value.trim(),
      body_es: document.getElementById('gp-body-es').value.trim(),
      image_url: document.getElementById('gp-image').value.trim(),
      cta_type: document.getElementById('gp-cta-type').value || null,
      cta_url: document.getElementById('gp-cta-url').value.trim() || null,
      start_date: document.getElementById('gp-start').value || null,
      end_date: document.getElementById('gp-end').value || null,
    };
    if (!data.title_en && !data.body_en) { showToast(t('gbpContentRequired', 'Title or body is required'), true); return; }
    try {
      var method = editingPostId ? 'PUT' : 'POST';
      if (editingPostId) data.id = editingPostId;
      var res = await fetch(API_POSTS, { method: method, headers: hdrs(true), credentials: 'include', body: JSON.stringify(data) });
      var json = await res.json();
      if (json.success) {
        showToast(editingPostId ? t('gbpPostUpdated', 'Post updated') : t('gbpPostCreated', 'Post created'));
        ov.remove(); switchSubTab('posts');
      } else { showToast(json.error || t('gbpPostSaveFail', 'Save failed'), true); }
    } catch (err) { showToast(t('gbpNetworkError', 'Network error'), true); }
  }

  async function publishPost(id) {
    try {
      var res = await fetch(API_POSTS, {
        method: 'PUT', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), id: id, status: 'published' }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('gbpPostPublished', 'Post published to Google')); switchSubTab('posts'); }
      else { showToast(json.error || t('gbpPublishFail', 'Publish failed'), true); }
    } catch (err) { showToast(t('gbpNetworkError', 'Network error'), true); }
  }

  async function deletePost(id) {
    try {
      var res = await fetch(API_POSTS, { method: 'DELETE', headers: hdrs(true), credentials: 'include', body: JSON.stringify({ _csrf: getCsrf(), id: id }) });
      var json = await res.json();
      if (json.success) { showToast(t('gbpPostDeleted', 'Post deleted')); switchSubTab('posts'); }
      else { showToast(json.error || t('gbpDeleteFail', 'Delete failed'), true); }
    } catch (err) { showToast(t('gbpNetworkError', 'Network error'), true); }
  }

  // ── Hours Sync ────────────────────────────────────────────
  async function fetchHours() {
    try {
      var res = await fetch(API_SYNC + '?action=hours', { credentials: 'include' });
      var json = await res.json();
      hours = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchHours error:', err);
    }
  }

  function renderHours(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('gbpBusinessHours', 'Business Hours')));
    var syncBtn = el('button', 'bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2', t('gbpSyncToGoogle', 'Sync to Google'));
    syncBtn.addEventListener('click', function() { syncHours(syncBtn); });
    hdr.appendChild(syncBtn);
    content.appendChild(hdr);

    var wrap = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-hidden');
    var DAY_LABELS = {
      en: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      es: ['Lunes', 'Martes', 'Mi\u00e9rcoles', 'Jueves', 'Viernes', 'S\u00e1bado', 'Domingo'],
    };
    var lang = (typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es' : 'en';
    var dayLabels = DAY_LABELS[lang];

    if (!hours.length) {
      wrap.appendChild(el('p', 'text-center py-8 text-gray-400 dark:text-gray-500', t('gbpNoHours', 'No business hours configured. Set them in Site Settings first.')));
    } else {
      var tbl = el('table', 'w-full text-sm');
      var tbody = document.createElement('tbody');
      hours.forEach(function(h, idx) {
        var tr = el('tr', 'border-b dark:border-gray-700 last:border-0');
        tr.appendChild(el('td', 'px-4 py-3 font-medium dark:text-gray-200', dayLabels[idx] || h.day));
        if (h.closed) {
          var tdC = el('td', 'px-4 py-3');
          tdC.appendChild(badge(t('gbpClosed', 'Closed'), 'red'));
          tr.appendChild(tdC);
        } else {
          tr.appendChild(el('td', 'px-4 py-3 dark:text-gray-300', (h.open || '9:00 AM') + ' \u2013 ' + (h.close || '6:00 PM')));
        }
        tbody.appendChild(tr);
      });
      tbl.appendChild(tbody); wrap.appendChild(tbl);
    }
    content.appendChild(wrap);

    // Last sync info
    var info = el('div', 'mt-3 text-xs text-gray-400 dark:text-gray-500 text-right');
    info.textContent = t('gbpLastSync', 'Last sync: ') + (hours._last_sync ? formatDate(hours._last_sync) : t('gbpNever', 'Never'));
    content.appendChild(info);
  }

  async function syncHours(btn) {
    btn.disabled = true;
    btn.textContent = t('gbpSyncing', 'Syncing...');
    try {
      var res = await fetch(API_SYNC, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), action: 'sync_hours' }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('gbpHoursSynced', 'Hours synced to Google')); switchSubTab('hours'); }
      else { showToast(json.error || t('gbpSyncFail', 'Sync failed'), true); btn.disabled = false; btn.textContent = t('gbpSyncToGoogle', 'Sync to Google'); }
    } catch (err) {
      showToast(t('gbpNetworkError', 'Network error'), true);
      btn.disabled = false; btn.textContent = t('gbpSyncToGoogle', 'Sync to Google');
    }
  }

  // ── Insights ──────────────────────────────────────────────
  async function fetchInsights() {
    try {
      var res = await fetch(API_SYNC + '?action=insights', { credentials: 'include' });
      var json = await res.json();
      insights = json.success ? (json.data || {}) : {};
    } catch (err) {
      console.error('fetchInsights error:', err);
    }
  }

  function renderInsights(content) {
    content.textContent = '';
    content.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100 mb-4', t('gbpInsightsTitle', 'Google Business Insights')));

    // Stat cards
    var stats = el('div', 'grid grid-cols-2 md:grid-cols-4 gap-4 mb-6');
    var statItems = [
      { label: t('gbpSearchViews', 'Search Views'), value: insights.search_views, icon: '' },
      { label: t('gbpMapsViews', 'Maps Views'), value: insights.maps_views, icon: '' },
      { label: t('gbpWebsiteClicks', 'Website Clicks'), value: insights.website_clicks, icon: '' },
      { label: t('gbpPhoneCalls', 'Phone Calls'), value: insights.phone_calls, icon: '' },
    ];
    statItems.forEach(function(s) {
      var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 text-center');
      card.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', s.label));
      card.appendChild(el('div', 'text-2xl font-bold dark:text-gray-100', String(s.value || 0)));
      stats.appendChild(card);
    });
    content.appendChild(stats);

    // Views chart (search + maps)
    if (insights.views_trend && insights.views_trend.length && typeof OTCharts !== 'undefined') {
      var viewsSection = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      viewsSection.appendChild(el('h4', 'text-sm font-semibold mb-3 dark:text-gray-200', t('gbpViewsTrend', 'Views Over Time')));
      var chartBox = el('div', '');
      viewsSection.appendChild(chartBox);
      content.appendChild(viewsSection);
      try {
        var labels = insights.views_trend.map(function(r) { return r.date; });
        var searchData = insights.views_trend.map(function(r) { return r.search || 0; });
        var mapsData = insights.views_trend.map(function(r) { return r.maps || 0; });
        OTCharts.barChart(chartBox, labels, searchData, { height: 200, color: 'brand', label: t('gbpSearch', 'Search') });
      } catch (e) { console.error('Views chart error:', e); }
    }

    // Clicks chart
    if (insights.clicks_trend && insights.clicks_trend.length && typeof OTCharts !== 'undefined') {
      var clicksSection = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      clicksSection.appendChild(el('h4', 'text-sm font-semibold mb-3 dark:text-gray-200', t('gbpClicksTrend', 'Clicks Over Time')));
      var clicksBox = el('div', '');
      clicksSection.appendChild(clicksBox);
      content.appendChild(clicksSection);
      try {
        var cLabels = insights.clicks_trend.map(function(r) { return r.date; });
        var cData = insights.clicks_trend.map(function(r) { return (r.website || 0) + (r.directions || 0) + (r.phone || 0); });
        OTCharts.barChart(clicksBox, cLabels, cData, { height: 200, color: 'brand' });
      } catch (e) { console.error('Clicks chart error:', e); }
    }

    // Photo views
    if (insights.photo_views !== undefined) {
      var photoCard = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      var photoRow = el('div', 'flex items-center justify-between');
      photoRow.appendChild(el('span', 'text-sm font-semibold dark:text-gray-200', t('gbpPhotoViews', 'Photo Views')));
      photoRow.appendChild(el('span', 'text-2xl font-bold dark:text-gray-100', String(insights.photo_views || 0)));
      photoCard.appendChild(photoRow);
      content.appendChild(photoCard);
    }

    // Empty state
    if (!insights.search_views && !insights.maps_views && !insights.website_clicks) {
      var empty = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-8 text-center');
      empty.appendChild(el('p', 'text-gray-400 dark:text-gray-500', t('gbpNoInsights', 'No insights data available yet. Data syncs weekly from Google Business Profile.')));
      content.appendChild(empty);
    }
  }

  // ── Q&A ───────────────────────────────────────────────────
  async function fetchQuestions() {
    try {
      var res = await fetch(API_SYNC + '?action=questions', { credentials: 'include' });
      var json = await res.json();
      questions = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchQuestions error:', err);
    }
  }

  function renderQA(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('gbpQATitle', 'Questions & Answers')));
    var refreshBtn = el('button', 'text-sm text-brand dark:text-green-400 font-medium hover:opacity-80', t('gbpRefresh', 'Refresh'));
    refreshBtn.addEventListener('click', function() { switchSubTab('qa'); });
    hdr.appendChild(refreshBtn);
    content.appendChild(hdr);

    if (!questions.length) {
      var empty = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-8 text-center');
      empty.appendChild(el('p', 'text-gray-400 dark:text-gray-500', t('gbpNoQuestions', 'No questions found. Questions from your Google Business listing will appear here.')));
      content.appendChild(empty);
      return;
    }

    var wrap = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-hidden');
    var tbl = el('table', 'w-full text-sm');
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var hr = el('tr');
    [t('gbpQuestion', 'Question'), t('gbpAskedBy', 'Asked By'), t('gbpDate', 'Date'), t('gbpStatus', 'Status'), t('gbpActions', 'Actions')].forEach(function(h) {
      hr.appendChild(el('th', 'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase', h));
    });
    thead.appendChild(hr); tbl.appendChild(thead);

    var tbody = document.createElement('tbody');
    questions.forEach(function(q) {
      var tr = el('tr', 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50');
      var tdQ = el('td', 'px-4 py-3');
      tdQ.appendChild(el('div', 'font-medium dark:text-gray-200 text-sm', q.question || '\u2014'));
      if (q.answer) {
        var ansEl = el('div', 'text-xs text-gray-500 dark:text-gray-400 mt-1');
        ansEl.textContent = t('gbpAnswer', 'A: ') + q.answer;
        tdQ.appendChild(ansEl);
      }
      tr.appendChild(tdQ);
      tr.appendChild(el('td', 'px-4 py-3 text-gray-600 dark:text-gray-400', q.author || t('gbpAnonymous', 'Anonymous')));
      tr.appendChild(el('td', 'px-4 py-3 text-gray-500 dark:text-gray-400', formatDate(q.created_at)));

      var tdS = el('td', 'px-4 py-3');
      var status = q.answer ? 'answered' : (q.status === 'ignored' ? 'ignored' : 'unanswered');
      var statusColors = { unanswered: 'yellow', answered: 'green', ignored: 'gray' };
      var statusLabels = { unanswered: t('gbpUnanswered', 'Unanswered'), answered: t('gbpAnswered', 'Answered'), ignored: t('gbpIgnored', 'Ignored') };
      tdS.appendChild(badge(statusLabels[status], statusColors[status]));
      tr.appendChild(tdS);

      var tdA = el('td', 'px-4 py-3');
      var acts = el('div', 'flex gap-2');
      if (!q.answer) {
        var ansBtn = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('gbpReply', 'Reply'));
        ansBtn.addEventListener('click', function() { openAnswerModal(q); });
        acts.appendChild(ansBtn);
        var ignBtn = el('button', 'text-gray-500 hover:text-gray-700 text-sm dark:text-gray-400', t('gbpIgnore', 'Ignore'));
        ignBtn.addEventListener('click', function() { ignoreQuestion(q.id); });
        acts.appendChild(ignBtn);
      } else {
        var editBtn = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('actionEdit', 'Edit'));
        editBtn.addEventListener('click', function() { openAnswerModal(q); });
        acts.appendChild(editBtn);
      }
      tdA.appendChild(acts); tr.appendChild(tdA);
      tbody.appendChild(tr);
    });
    tbl.appendChild(tbody); wrap.appendChild(tbl);
    content.appendChild(wrap);
  }

  function openAnswerModal(q) {
    var ov = modal('gbp-answer-modal');
    if (!ov) return;
    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-lg mx-4');
    card.appendChild(el('h3', 'text-lg font-semibold mb-2 dark:text-gray-100', t('gbpAnswerQuestion', 'Answer Question')));
    card.appendChild(el('p', 'text-sm text-gray-600 dark:text-gray-400 mb-4 italic', '"' + (q.question || '') + '"'));
    card.appendChild(label(t('gbpYourAnswer', 'Your Answer')));
    var ta = document.createElement('textarea');
    ta.id = 'qa-answer'; ta.rows = 4;
    ta.className = 'w-full border rounded-lg px-3 py-2 mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    ta.value = q.answer || '';
    card.appendChild(ta);

    var row = el('div', 'flex gap-3 justify-end');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('gbpSubmitAnswer', 'Submit Answer'));
    save.addEventListener('click', function() { submitAnswer(q.id, ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
    ta.focus();
  }

  async function submitAnswer(qId, ov) {
    var answer = document.getElementById('qa-answer').value.trim();
    if (!answer) { showToast(t('gbpAnswerRequired', 'Please enter an answer'), true); return; }
    try {
      var res = await fetch(API_SYNC, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), action: 'answer_question', question_id: qId, answer: answer }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('gbpAnswerSubmitted', 'Answer submitted')); ov.remove(); switchSubTab('qa'); }
      else { showToast(json.error || t('gbpAnswerFail', 'Submit failed'), true); }
    } catch (err) { showToast(t('gbpNetworkError', 'Network error'), true); }
  }

  async function ignoreQuestion(qId) {
    try {
      var res = await fetch(API_SYNC, {
        method: 'POST', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), action: 'ignore_question', question_id: qId }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('gbpQuestionIgnored', 'Question ignored')); switchSubTab('qa'); }
      else { showToast(json.error || t('gbpIgnoreFail', 'Action failed'), true); }
    } catch (err) { showToast(t('gbpNetworkError', 'Network error'), true); }
  }

  // ── Public API ────────────────────────────────────────────
  window.loadGBP = loadGBP;
})();
