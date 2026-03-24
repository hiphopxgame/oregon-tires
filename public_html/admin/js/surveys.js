/**
 * Oregon Tires — Admin Surveys & NPS Manager
 * Sub-tabs: Surveys, Results
 * Uses createElement/appendChild only (no innerHTML per security rules).
 */
(function() {
  'use strict';

  var API_SURVEYS = '/api/admin/surveys.php';
  var API_RESULTS = '/api/admin/survey-results.php';

  var surveys = [], results = {}, activeSubTab = 'surveys';
  var resultsPeriod = '30d';
  var editingSurveyId = null;
  var dragSrcIndex = null;

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
  async function loadSurveys() {
    var c = document.getElementById('tab-surveys');
    if (!c) return;
    c.textContent = '';
    c.appendChild(renderSubTabs());
    var content = el('div', '');
    content.id = 'surveys-content';
    c.appendChild(content);
    await switchSubTab(activeSubTab);
  }

  // ── Sub-tab Navigation ────────────────────────────────────
  function renderSubTabs() {
    var nav = el('div', 'flex gap-1 mb-6 border-b dark:border-gray-700');
    var tabs = [
      { key: 'surveys', label: t('surveysSurveys', 'Surveys') },
      { key: 'results', label: t('surveysResults', 'Results') },
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
    var c = document.getElementById('tab-surveys');
    if (c) {
      c.querySelectorAll('[data-subtab]').forEach(function(b) {
        var isActive = b.getAttribute('data-subtab') === key;
        b.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition ' +
          (isActive
            ? 'border-brand text-brand dark:text-green-400 dark:border-green-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300');
      });
    }
    var content = document.getElementById('surveys-content');
    if (!content) return;
    content.textContent = '';
    content.appendChild(spinner());

    if (key === 'surveys') { await fetchSurveys(); renderSurveyList(content); }
    else { await fetchResults(); renderResults(content); }
  }

  // ── Surveys List ──────────────────────────────────────────
  async function fetchSurveys() {
    try {
      var res = await fetch(API_SURVEYS, { credentials: 'include' });
      var json = await res.json();
      surveys = json.success ? (json.data || []) : [];
    } catch (err) {
      console.error('fetchSurveys error:', err);
      if (typeof showToast === 'function') showToast(t('surveysLoadFail', 'Failed to load surveys'), true);
    }
  }

  function renderSurveyList(content) {
    content.textContent = '';
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('surveysList', 'Surveys')));
    var addBtn = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('surveysNew', 'New Survey'));
    addBtn.addEventListener('click', function() { openSurveyModal(); });
    hdr.appendChild(addBtn);
    content.appendChild(hdr);

    if (!surveys.length) {
      var empty = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-8 text-center');
      empty.appendChild(el('p', 'text-gray-400 dark:text-gray-500', t('surveysNoSurveys', 'No surveys yet. Click "New Survey" to create one.')));
      content.appendChild(empty);
      return;
    }

    var grid = el('div', 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4');
    surveys.forEach(function(s) {
      var card = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 flex flex-col');
      var top = el('div', 'flex items-start justify-between mb-2');
      top.appendChild(el('h4', 'font-semibold dark:text-gray-100 text-sm', currentLang === 'es' ? (s.title_es || s.title_en) : s.title_en));
      top.appendChild(badge(Number(s.active) ? t('surveysActive', 'Active') : t('surveysDraft', 'Draft'), Number(s.active) ? 'green' : 'gray'));
      card.appendChild(top);
      var meta = el('div', 'text-xs text-gray-500 dark:text-gray-400 mb-2');
      meta.textContent = (s.question_count || 0) + ' ' + t('surveysQuestions', 'questions') + '  \u00b7  ' + (s.response_count || 0) + ' ' + t('surveysResponses', 'responses');
      card.appendChild(meta);
      if (s.description) {
        card.appendChild(el('p', 'text-sm text-gray-600 dark:text-gray-400 flex-1 line-clamp-2', s.description));
      }
      var acts = el('div', 'flex gap-2 mt-3 pt-3 border-t dark:border-gray-700');
      var eB = el('button', 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400', t('actionEdit', 'Edit'));
      eB.addEventListener('click', function() { openSurveyModal(s); });
      acts.appendChild(eB);
      var tB = el('button', Number(s.active)
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400',
        Number(s.active) ? t('surveysDeactivate', 'Deactivate') : t('surveysActivate', 'Activate'));
      tB.addEventListener('click', function() { toggleSurvey(s); });
      acts.appendChild(tB);
      var xB = el('button', 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400', t('actionDelete', 'Delete'));
      xB.addEventListener('click', function() { deleteSurvey(s.id); });
      acts.appendChild(xB);
      card.appendChild(acts); grid.appendChild(card);
    });
    content.appendChild(grid);
  }

  // ── Survey Modal with Question Builder ────────────────────
  var tempQuestions = [];

  function openSurveyModal(s) {
    var ov = modal('surveys-modal');
    if (!ov) return;
    editingSurveyId = s ? s.id : null;
    tempQuestions = s && s.questions ? JSON.parse(JSON.stringify(s.questions)) : [];

    var card = el('div', 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto');
    card.appendChild(el('h3', 'text-lg font-semibold mb-4 dark:text-gray-100', s ? t('surveysEditSurvey', 'Edit Survey') : t('surveysNew', 'New Survey')));

    card.appendChild(label(t('surveysTitleEn', 'Title (EN)')));
    card.appendChild(input('sm-title-en', 'text', s ? s.title_en : ''));
    card.appendChild(label(t('surveysTitleEs', 'Title (ES)')));
    card.appendChild(input('sm-title-es', 'text', s ? s.title_es : ''));
    card.appendChild(label(t('surveysDescription', 'Description')));
    var desc = document.createElement('textarea');
    desc.id = 'sm-desc'; desc.rows = 2;
    desc.className = 'w-full border rounded-lg px-3 py-2 mb-4 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
    desc.value = s ? (s.description || '') : '';
    card.appendChild(desc);

    // Question builder
    var qSection = el('div', 'border-t dark:border-gray-700 pt-4 mt-2');
    var qHdr = el('div', 'flex items-center justify-between mb-3');
    qHdr.appendChild(el('h4', 'font-semibold dark:text-gray-200', t('surveysQuestionBuilder', 'Questions')));
    var addQ = el('button', 'text-sm text-brand dark:text-green-400 font-medium hover:opacity-80', '+ ' + t('surveysAddQuestion', 'Add Question'));
    addQ.addEventListener('click', function() {
      tempQuestions.push({ question_en: '', question_es: '', type: 'rating', options: '' });
      renderQuestionList(qList);
    });
    qHdr.appendChild(addQ);
    qSection.appendChild(qHdr);
    var qList = el('div', '');
    qList.id = 'sm-question-list';
    renderQuestionList(qList);
    qSection.appendChild(qList);
    card.appendChild(qSection);

    // Buttons
    var row = el('div', 'flex gap-3 justify-end mt-4 border-t dark:border-gray-700 pt-4');
    var canc = el('button', 'px-4 py-2 rounded-lg border dark:border-gray-600 text-sm dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700', t('actionCancel', 'Cancel'));
    canc.addEventListener('click', function() { ov.remove(); });
    row.appendChild(canc);
    var save = el('button', 'bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90', t('actionSave', 'Save'));
    save.addEventListener('click', function() { saveSurvey(ov); });
    row.appendChild(save);
    card.appendChild(row); ov.appendChild(card); document.body.appendChild(ov);
  }

  function renderQuestionList(container) {
    container.textContent = '';
    if (!tempQuestions.length) {
      container.appendChild(el('p', 'text-sm text-gray-400 dark:text-gray-500 text-center py-4', t('surveysNoQuestions', 'No questions yet. Click "+ Add Question" above.')));
      return;
    }
    tempQuestions.forEach(function(q, idx) {
      var qCard = el('div', 'bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-2 border dark:border-gray-600');
      qCard.setAttribute('draggable', 'true');
      qCard.setAttribute('data-qidx', String(idx));

      // Drag events for reordering
      qCard.addEventListener('dragstart', function(e) { dragSrcIndex = idx; e.dataTransfer.effectAllowed = 'move'; });
      qCard.addEventListener('dragover', function(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; qCard.classList.add('ring-2', 'ring-brand'); });
      qCard.addEventListener('dragleave', function() { qCard.classList.remove('ring-2', 'ring-brand'); });
      qCard.addEventListener('drop', function(e) {
        e.preventDefault(); qCard.classList.remove('ring-2', 'ring-brand');
        if (dragSrcIndex !== null && dragSrcIndex !== idx) {
          var moved = tempQuestions.splice(dragSrcIndex, 1)[0];
          tempQuestions.splice(idx, 0, moved);
          renderQuestionList(container);
        }
        dragSrcIndex = null;
      });

      var topRow = el('div', 'flex items-center justify-between mb-2');
      topRow.appendChild(el('span', 'text-xs font-bold text-gray-400 dark:text-gray-500 cursor-grab', '#' + (idx + 1) + ' \u2261'));
      var removeBtn = el('button', 'text-red-500 hover:text-red-700 text-xs font-medium', t('actionRemove', 'Remove'));
      removeBtn.addEventListener('click', function() { tempQuestions.splice(idx, 1); renderQuestionList(container); });
      topRow.appendChild(removeBtn);
      qCard.appendChild(topRow);

      // Question EN
      var qEn = document.createElement('input');
      qEn.type = 'text'; qEn.placeholder = t('surveysQuestionEn', 'Question (EN)');
      qEn.className = 'w-full border rounded px-2 py-1.5 text-sm mb-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
      qEn.value = q.question_en || '';
      qEn.addEventListener('input', function() { tempQuestions[idx].question_en = qEn.value; });
      qCard.appendChild(qEn);

      // Question ES
      var qEs = document.createElement('input');
      qEs.type = 'text'; qEs.placeholder = t('surveysQuestionEs', 'Question (ES)');
      qEs.className = 'w-full border rounded px-2 py-1.5 text-sm mb-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
      qEs.value = q.question_es || '';
      qEs.addEventListener('input', function() { tempQuestions[idx].question_es = qEs.value; });
      qCard.appendChild(qEs);

      // Type selector
      var typeRow = el('div', 'flex gap-2 items-center');
      typeRow.appendChild(el('span', 'text-xs text-gray-500 dark:text-gray-400', t('surveysType', 'Type:')));
      var typeOpts = [
        { value: 'rating', label: t('surveysRating', 'Rating (1-5)') },
        { value: 'nps', label: t('surveysNps', 'NPS (0-10)') },
        { value: 'text', label: t('surveysText', 'Free Text') },
        { value: 'multiple_choice', label: t('surveysMC', 'Multiple Choice') },
      ];
      var typeSel = document.createElement('select');
      typeSel.className = 'border rounded px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
      typeOpts.forEach(function(o) {
        var opt = document.createElement('option');
        opt.value = o.value; opt.textContent = o.label;
        if (o.value === q.type) opt.selected = true;
        typeSel.appendChild(opt);
      });
      typeSel.addEventListener('change', function() {
        tempQuestions[idx].type = typeSel.value;
        renderQuestionList(container);
      });
      typeRow.appendChild(typeSel);
      qCard.appendChild(typeRow);

      // Multiple choice options
      if (q.type === 'multiple_choice') {
        var optInp = document.createElement('input');
        optInp.type = 'text'; optInp.placeholder = t('surveysMcOptions', 'Options (comma-separated)');
        optInp.className = 'w-full border rounded px-2 py-1.5 text-sm mt-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100';
        optInp.value = q.options || '';
        optInp.addEventListener('input', function() { tempQuestions[idx].options = optInp.value; });
        qCard.appendChild(optInp);
      }

      container.appendChild(qCard);
    });
  }

  async function saveSurvey(ov) {
    var data = {
      _csrf: getCsrf(),
      title_en: document.getElementById('sm-title-en').value.trim(),
      title_es: document.getElementById('sm-title-es').value.trim(),
      description: document.getElementById('sm-desc').value.trim(),
      questions: tempQuestions,
    };
    if (!data.title_en) { showToast(t('surveysTitleRequired', 'Survey title is required'), true); return; }
    try {
      var method = editingSurveyId ? 'PUT' : 'POST';
      if (editingSurveyId) data.id = editingSurveyId;
      var res = await fetch(API_SURVEYS, { method: method, headers: hdrs(true), credentials: 'include', body: JSON.stringify(data) });
      var json = await res.json();
      if (json.success) {
        showToast(editingSurveyId ? t('surveysUpdated', 'Survey updated') : t('surveysCreated', 'Survey created'));
        ov.remove(); switchSubTab('surveys');
      } else { showToast(json.error || t('surveysSaveFail', 'Save failed'), true); }
    } catch (err) {
      console.error('saveSurvey error:', err);
      showToast(t('surveysNetworkError', 'Network error'), true);
    }
  }

  async function toggleSurvey(s) {
    try {
      var res = await fetch(API_SURVEYS, {
        method: 'PUT', headers: hdrs(true), credentials: 'include',
        body: JSON.stringify({ _csrf: getCsrf(), id: s.id, active: Number(s.active) ? 0 : 1 }),
      });
      var json = await res.json();
      if (json.success) { showToast(t('surveysStatusChanged', 'Survey status updated')); switchSubTab('surveys'); }
      else { showToast(json.error || t('surveysToggleFail', 'Update failed'), true); }
    } catch (err) { showToast(t('surveysNetworkError', 'Network error'), true); }
  }

  async function deleteSurvey(id) {
    try {
      var res = await fetch(API_SURVEYS, { method: 'DELETE', headers: hdrs(true), credentials: 'include', body: JSON.stringify({ _csrf: getCsrf(), id: id }) });
      var json = await res.json();
      if (json.success) { showToast(t('surveysDeleted', 'Survey deleted')); switchSubTab('surveys'); }
      else { showToast(json.error || t('surveysDeleteFail', 'Delete failed'), true); }
    } catch (err) { showToast(t('surveysNetworkError', 'Network error'), true); }
  }

  // ── Results ───────────────────────────────────────────────
  async function fetchResults() {
    try {
      var res = await fetch(API_RESULTS + '?period=' + resultsPeriod, { credentials: 'include' });
      var json = await res.json();
      results = json.success ? (json.data || {}) : {};
    } catch (err) {
      console.error('fetchResults error:', err);
      if (typeof showToast === 'function') showToast(t('surveysResultsLoadFail', 'Failed to load results'), true);
    }
  }

  function renderResults(content) {
    content.textContent = '';

    // Period selector
    var hdr = el('div', 'flex items-center justify-between mb-4');
    hdr.appendChild(el('h3', 'text-lg font-semibold dark:text-gray-100', t('surveysAnalytics', 'Survey Analytics')));
    var periodWrap = el('div', 'flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1');
    ['7d', '30d', '90d'].forEach(function(p) {
      var btn = el('button', 'px-3 py-1 text-sm rounded-md transition ' +
        (resultsPeriod === p ? 'bg-white dark:bg-gray-600 shadow-sm font-medium dark:text-gray-100' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'), p);
      btn.addEventListener('click', function() { resultsPeriod = p; switchSubTab('results'); });
      periodWrap.appendChild(btn);
    });
    hdr.appendChild(periodWrap);
    content.appendChild(hdr);

    // Stats row
    var statsRow = el('div', 'grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6');
    // NPS
    var npsCard = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 text-center');
    npsCard.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-2', 'Net Promoter Score'));
    var npsVal = results.nps !== undefined ? results.nps : 0;
    npsCard.appendChild(renderNpsGauge(npsVal));
    statsRow.appendChild(npsCard);

    // Avg satisfaction
    var satCard = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 text-center');
    satCard.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', t('surveysAvgSatisfaction', 'Avg. Satisfaction')));
    var satVal = results.avg_satisfaction !== undefined ? Number(results.avg_satisfaction).toFixed(1) : '\u2014';
    satCard.appendChild(el('div', 'text-3xl font-bold dark:text-gray-100 mt-2', satVal));
    satCard.appendChild(el('div', 'text-xs text-gray-400 dark:text-gray-500', t('surveysOutOf5', 'out of 5')));
    statsRow.appendChild(satCard);

    // Total responses
    var respCard = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 text-center');
    respCard.appendChild(el('div', 'text-sm text-gray-500 dark:text-gray-400 mb-1', t('surveysTotalResponses', 'Total Responses')));
    respCard.appendChild(el('div', 'text-3xl font-bold dark:text-gray-100 mt-2', String(results.total_responses || 0)));
    statsRow.appendChild(respCard);
    content.appendChild(statsRow);

    // Response trend chart
    if (results.trend && results.trend.length && typeof OTCharts !== 'undefined') {
      var trendSection = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      trendSection.appendChild(el('h4', 'text-sm font-semibold mb-3 dark:text-gray-200', t('surveysResponseTrend', 'Response Trend')));
      var chartBox = el('div', '');
      chartBox.id = 'survey-trend-chart';
      trendSection.appendChild(chartBox);
      content.appendChild(trendSection);
      try {
        var labels = results.trend.map(function(r) { return r.date; });
        var data = results.trend.map(function(r) { return r.count; });
        OTCharts.barChart(chartBox, labels, data, { height: 200, color: 'brand' });
      } catch (e) { console.error('Chart render error:', e); }
    }

    // Per-question breakdown
    if (results.per_question && results.per_question.length) {
      var qSection = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      qSection.appendChild(el('h4', 'text-sm font-semibold mb-3 dark:text-gray-200', t('surveysPerQuestion', 'Per-Question Breakdown')));
      var tbl = el('table', 'w-full text-sm');
      var thead = document.createElement('thead');
      thead.className = 'bg-gray-50 dark:bg-gray-700';
      var hr = el('tr');
      [t('surveysQuestion', 'Question'), t('surveysType', 'Type'), t('surveysAvg', 'Avg'), t('surveysCount', 'Responses')].forEach(function(h) {
        hr.appendChild(el('th', 'px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase', h));
      });
      thead.appendChild(hr); tbl.appendChild(thead);
      var tbody = document.createElement('tbody');
      results.per_question.forEach(function(pq) {
        var tr = el('tr', 'border-b dark:border-gray-700');
        var qField = currentLang === 'es' ? (pq.question_es || pq.question_en) : pq.question_en;
        tr.appendChild(el('td', 'px-4 py-2 dark:text-gray-200', qField || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-2 text-gray-500 dark:text-gray-400', pq.type || '\u2014'));
        tr.appendChild(el('td', 'px-4 py-2 font-medium dark:text-gray-200', pq.avg !== undefined ? Number(pq.avg).toFixed(1) : '\u2014'));
        tr.appendChild(el('td', 'px-4 py-2 text-gray-500 dark:text-gray-400', String(pq.count || 0)));
        tbody.appendChild(tr);
      });
      tbl.appendChild(tbody); qSection.appendChild(tbl);
      content.appendChild(qSection);
    }

    // Recent text responses
    if (results.recent_text && results.recent_text.length) {
      var textSection = el('div', 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-4 mb-6');
      textSection.appendChild(el('h4', 'text-sm font-semibold mb-3 dark:text-gray-200', t('surveysRecentText', 'Recent Text Responses')));
      results.recent_text.forEach(function(r) {
        var item = el('div', 'border-b dark:border-gray-700 py-3 last:border-0');
        var top = el('div', 'flex items-center justify-between mb-1');
        top.appendChild(el('span', 'text-sm font-medium dark:text-gray-200', r.customer_name || t('surveysAnonymous', 'Anonymous')));
        top.appendChild(el('span', 'text-xs text-gray-400 dark:text-gray-500', formatDate(r.created_at)));
        item.appendChild(top);
        item.appendChild(el('p', 'text-sm text-gray-600 dark:text-gray-400', r.response || ''));
        if (r.question) {
          item.appendChild(el('p', 'text-xs text-gray-400 dark:text-gray-500 mt-1 italic', r.question));
        }
        textSection.appendChild(item);
      });
      content.appendChild(textSection);
    }

    // Low score alerts
    if (results.low_scores && results.low_scores.length) {
      var alertSection = el('div', 'bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-200 dark:border-red-800 p-4 mb-6');
      var alertHdr = el('div', 'flex items-center gap-2 mb-3');
      alertHdr.appendChild(el('span', 'text-red-600 dark:text-red-400 font-semibold text-sm', t('surveysLowScoreAlerts', 'Low Score Alerts')));
      alertHdr.appendChild(badge(String(results.low_scores.length), 'red'));
      alertSection.appendChild(alertHdr);
      results.low_scores.forEach(function(ls) {
        var item = el('div', 'flex items-center justify-between py-2 border-b border-red-100 dark:border-red-900 last:border-0 text-sm');
        var left = el('div', '');
        left.appendChild(el('span', 'font-medium text-red-700 dark:text-red-300', ls.customer_name || t('surveysAnonymous', 'Anonymous')));
        left.appendChild(el('span', 'text-gray-500 dark:text-gray-400 ml-2', ls.survey_title || ''));
        item.appendChild(left);
        item.appendChild(el('span', 'font-bold text-red-600 dark:text-red-400', String(ls.score)));
        alertSection.appendChild(item);
      });
      content.appendChild(alertSection);
    }

    // Empty state
    if (!results.total_responses && (!results.per_question || !results.per_question.length)) {
      var empty = el('div', 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-8 text-center');
      empty.appendChild(el('p', 'text-gray-400 dark:text-gray-500', t('surveysNoResults', 'No survey responses yet. Create and activate a survey to start collecting feedback.')));
      content.appendChild(empty);
    }
  }

  // ── NPS Gauge ─────────────────────────────────────────────
  function renderNpsGauge(value) {
    var wrap = el('div', 'relative mx-auto mt-2');
    wrap.style.width = '160px';
    wrap.style.height = '90px';

    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 160 90');
    svg.setAttribute('width', '160');
    svg.setAttribute('height', '90');

    // Background arc
    var bgArc = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    bgArc.setAttribute('d', describeArc(80, 80, 60, 180, 360));
    bgArc.setAttribute('fill', 'none');
    bgArc.setAttribute('stroke', document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB');
    bgArc.setAttribute('stroke-width', '12');
    bgArc.setAttribute('stroke-linecap', 'round');
    svg.appendChild(bgArc);

    // Value arc: map -100..100 to 180..360
    var clampedVal = Math.max(-100, Math.min(100, value));
    var angle = 180 + ((clampedVal + 100) / 200) * 180;
    if (angle > 180) {
      var valArc = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      valArc.setAttribute('d', describeArc(80, 80, 60, 180, angle));
      valArc.setAttribute('fill', 'none');
      var arcColor = clampedVal >= 50 ? '#22C55E' : clampedVal >= 0 ? '#F59E0B' : '#EF4444';
      valArc.setAttribute('stroke', arcColor);
      valArc.setAttribute('stroke-width', '12');
      valArc.setAttribute('stroke-linecap', 'round');
      svg.appendChild(valArc);
    }

    // Score text
    var txt = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    txt.setAttribute('x', '80'); txt.setAttribute('y', '75');
    txt.setAttribute('text-anchor', 'middle');
    txt.setAttribute('font-size', '24'); txt.setAttribute('font-weight', 'bold');
    txt.setAttribute('fill', document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#1F2937');
    txt.textContent = String(Math.round(value));
    svg.appendChild(txt);

    wrap.appendChild(svg);

    // Labels
    var labels = el('div', 'flex justify-between text-xs text-gray-400 dark:text-gray-500 mt-1');
    labels.appendChild(el('span', '', '-100'));
    labels.appendChild(el('span', '', '+100'));
    wrap.appendChild(labels);

    return wrap;
  }

  function describeArc(x, y, radius, startAngle, endAngle) {
    var start = polarToCartesian(x, y, radius, endAngle);
    var end = polarToCartesian(x, y, radius, startAngle);
    var largeArcFlag = endAngle - startAngle <= 180 ? '0' : '1';
    return 'M ' + start.x + ' ' + start.y + ' A ' + radius + ' ' + radius + ' 0 ' + largeArcFlag + ' 0 ' + end.x + ' ' + end.y;
  }

  function polarToCartesian(cx, cy, radius, angleDeg) {
    var angleRad = (angleDeg - 90) * Math.PI / 180;
    return { x: cx + radius * Math.cos(angleRad), y: cy + radius * Math.sin(angleRad) };
  }

  // ── Public API ────────────────────────────────────────────
  window.loadSurveys = loadSurveys;
})();
