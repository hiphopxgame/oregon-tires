/**
 * Oregon Tires — Admin Services Manager
 * Handles CRUD for services, service FAQs, and related services.
 */

(function() {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  const API = '/api/admin/services.php';
  let services = [];
  let editingId = null;

  function getCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function getHeaders(isJson) {
    var h = { 'X-CSRF-Token': getCsrf() };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }

  var CATEGORY_LABELS = {
    tires: { en: 'Tires', es: 'Llantas' },
    maintenance: { en: 'Maintenance', es: 'Mantenimiento' },
    specialized: { en: 'Specialized', es: 'Especializado' },
  };

  // ─── Load Services ────────────────────────────────────────
  async function loadServices() {
    try {
      var res = await fetch(API, { credentials: 'include' });
      var json = await res.json();
      services = json.success ? (json.data || []) : [];
      renderServiceTable();
    } catch (err) {
      console.error('loadServices error:', err);
      if (typeof showToast === 'function') showToast(t('svcLoadFail', 'Failed to load services'), true);
    }
  }

  // ─── Render table ─────────────────────────────────────────
  function renderServiceTable() {
    var container = document.getElementById('services-table-body');
    if (!container) return;

    container.textContent = '';

    if (!services.length) {
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      td.colSpan = 8;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = t('svcNoServices', 'No services yet. Click "New Service" to create one.');
      tr.appendChild(td);
      container.appendChild(tr);
      return;
    }

    services.forEach(function(svc) {
      var tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Icon cell
      var tdIcon = document.createElement('td');
      tdIcon.className = 'px-3 py-3 text-center text-xl';
      tdIcon.innerHTML = svc.icon || '';
      tr.appendChild(tdIcon);

      // Name cell
      var tdName = document.createElement('td');
      tdName.className = 'px-3 py-3';
      var nameDiv = document.createElement('div');
      nameDiv.className = 'font-medium text-sm dark:text-gray-200';
      nameDiv.textContent = currentLang === 'es' ? (svc.name_es || svc.name_en) : svc.name_en;
      tdName.appendChild(nameDiv);
      var slugDiv = document.createElement('div');
      slugDiv.className = 'text-xs text-gray-400 dark:text-gray-500';
      slugDiv.textContent = '/' + svc.slug;
      tdName.appendChild(slugDiv);
      tr.appendChild(tdName);

      // Category cell
      var tdCat = document.createElement('td');
      tdCat.className = 'px-3 py-3 text-sm text-gray-600 dark:text-gray-400';
      var catLabel = CATEGORY_LABELS[svc.category];
      tdCat.textContent = catLabel ? catLabel[currentLang] || catLabel.en : svc.category;
      tr.appendChild(tdCat);

      // Status cell
      var tdStatus = document.createElement('td');
      tdStatus.className = 'px-3 py-3';
      var statusBadge = document.createElement('span');
      var isActive = Number(svc.is_active) === 1;
      statusBadge.className = isActive
        ? 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
        : 'text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
      statusBadge.textContent = isActive ? t('svcActive', 'Active') : t('svcInactive', 'Inactive');
      tdStatus.appendChild(statusBadge);
      tr.appendChild(tdStatus);

      // Bookable cell
      var tdBook = document.createElement('td');
      tdBook.className = 'px-3 py-3 text-center text-sm';
      tdBook.textContent = Number(svc.is_bookable) === 1 ? '✓' : '—';
      tr.appendChild(tdBook);

      // Page cell
      var tdPage = document.createElement('td');
      tdPage.className = 'px-3 py-3 text-center text-sm';
      tdPage.textContent = Number(svc.has_detail_page) === 1 ? '✓' : '—';
      tr.appendChild(tdPage);

      // FAQs + Sort cell
      var tdMeta = document.createElement('td');
      tdMeta.className = 'px-3 py-3 text-sm text-center text-gray-600 dark:text-gray-400';
      tdMeta.textContent = (svc.faq_count || 0) + ' FAQs · #' + (svc.sort_order || 0);
      tr.appendChild(tdMeta);

      // Actions cell
      var tdActions = document.createElement('td');
      tdActions.className = 'px-3 py-3';
      var actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      var editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400';
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function() { openEditForm(svc); });
      actionsWrap.appendChild(editBtn);

      var faqBtn = document.createElement('button');
      faqBtn.className = 'text-purple-600 hover:text-purple-800 text-sm font-medium dark:text-purple-400';
      faqBtn.textContent = 'FAQs';
      faqBtn.addEventListener('click', function() { openFaqPanel(svc); });
      actionsWrap.appendChild(faqBtn);

      var toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? t('actionDeactivate', 'Deactivate') : t('actionActivate', 'Activate');
      toggleBtn.addEventListener('click', function() { toggleServiceActive(svc); });
      actionsWrap.appendChild(toggleBtn);

      var delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function() { deleteService(svc.id); });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);
      container.appendChild(tr);
    });
  }

  // ─── Toggle form visibility ───────────────────────────────
  function toggleServiceForm() {
    var form = document.getElementById('service-form-panel');
    if (!form) return;
    var isHidden = form.classList.contains('hidden');
    if (isHidden) {
      resetForm();
      form.classList.remove('hidden');
    } else {
      form.classList.add('hidden');
      editingId = null;
    }
  }

  // ─── Reset form fields ────────────────────────────────────
  function resetForm() {
    editingId = null;
    var fields = ['name-en', 'name-es', 'slug', 'icon', 'description-en', 'description-es',
                  'body-en', 'body-es', 'price-en', 'price-es', 'color-hex', 'image-url', 'duration'];
    fields.forEach(function(f) {
      var el = document.getElementById('svc-' + f);
      if (el) el.value = '';
    });
    var el;
    el = document.getElementById('svc-category');
    if (el) el.value = 'maintenance';
    el = document.getElementById('svc-active');
    if (el) el.checked = true;
    el = document.getElementById('svc-bookable');
    if (el) el.checked = true;
    el = document.getElementById('svc-detail-page');
    if (el) el.checked = true;
    el = document.getElementById('svc-sort');
    if (el) el.value = '0';
    el = document.getElementById('svc-color-hex');
    if (el) el.value = '#10B981';

    document.getElementById('svc-form-title').textContent = t('svcNewService', 'New Service');
    document.getElementById('svc-save-btn').textContent = t('svcCreateService', 'Create Service');
  }

  // ─── Auto-generate slug from English name ─────────────────
  function autoSlug() {
    if (editingId) return; // don't auto-slug when editing
    var nameEl = document.getElementById('svc-name-en');
    var slugEl = document.getElementById('svc-slug');
    if (!nameEl || !slugEl) return;
    slugEl.value = nameEl.value
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  // ─── Open form for editing ────────────────────────────────
  function openEditForm(svc) {
    editingId = svc.id;

    var map = {
      'name-en': 'name_en', 'name-es': 'name_es', 'slug': 'slug', 'icon': 'icon',
      'description-en': 'description_en', 'description-es': 'description_es',
      'body-en': 'body_en', 'body-es': 'body_es',
      'price-en': 'price_display_en', 'price-es': 'price_display_es',
      'color-hex': 'color_hex', 'image-url': 'image_url', 'duration': 'duration_estimate',
    };

    Object.keys(map).forEach(function(elId) {
      var el = document.getElementById('svc-' + elId);
      if (el) el.value = svc[map[elId]] || '';
    });

    var el;
    el = document.getElementById('svc-category');
    if (el) el.value = svc.category || 'maintenance';
    el = document.getElementById('svc-active');
    if (el) el.checked = Number(svc.is_active) === 1;
    el = document.getElementById('svc-bookable');
    if (el) el.checked = Number(svc.is_bookable) === 1;
    el = document.getElementById('svc-detail-page');
    if (el) el.checked = Number(svc.has_detail_page) === 1;
    el = document.getElementById('svc-sort');
    if (el) el.value = svc.sort_order || '0';

    document.getElementById('svc-form-title').textContent = t('svcEditService', 'Edit Service');
    document.getElementById('svc-save-btn').textContent = t('svcUpdateService', 'Update Service');

    var form = document.getElementById('service-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Save (create or update) ──────────────────────────────
  async function saveService() {
    var nameEn = document.getElementById('svc-name-en').value.trim();
    var slug = document.getElementById('svc-slug').value.trim();
    if (!nameEn) {
      if (typeof showToast === 'function') showToast(t('svcNameRequired', 'Service name (EN) is required'), true);
      return;
    }
    if (!slug) {
      if (typeof showToast === 'function') showToast(t('svcSlugRequired', 'Slug is required'), true);
      return;
    }

    var payload = {
      name_en: nameEn,
      name_es: document.getElementById('svc-name-es').value.trim(),
      slug: slug,
      icon: document.getElementById('svc-icon').value.trim(),
      description_en: document.getElementById('svc-description-en').value.trim(),
      description_es: document.getElementById('svc-description-es').value.trim(),
      body_en: document.getElementById('svc-body-en').value.trim(),
      body_es: document.getElementById('svc-body-es').value.trim(),
      price_display_en: document.getElementById('svc-price-en').value.trim(),
      price_display_es: document.getElementById('svc-price-es').value.trim(),
      color_hex: document.getElementById('svc-color-hex').value.trim() || '#10B981',
      image_url: document.getElementById('svc-image-url').value.trim(),
      duration_estimate: document.getElementById('svc-duration').value.trim(),
      category: document.getElementById('svc-category').value,
      is_active: document.getElementById('svc-active').checked ? 1 : 0,
      is_bookable: document.getElementById('svc-bookable').checked ? 1 : 0,
      has_detail_page: document.getElementById('svc-detail-page').checked ? 1 : 0,
      sort_order: parseInt(document.getElementById('svc-sort').value, 10) || 0,
    };

    var method = editingId ? 'PUT' : 'POST';
    if (editingId) payload.id = editingId;

    try {
      var res = await fetch(API, {
        method: method,
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingId ? t('svcUpdated', 'Service updated') : t('svcCreated', 'Service created'));
        document.getElementById('service-form-panel').classList.add('hidden');
        editingId = null;
        loadServices();
      } else {
        if (typeof showToast === 'function') showToast(json.error || t('svcSaveFail', 'Save failed'), true);
      }
    } catch (err) {
      console.error('saveService error:', err);
      if (typeof showToast === 'function') showToast(t('svcNetworkError', 'Network error'), true);
    }
  }

  // ─── Toggle active status ─────────────────────────────────
  async function toggleServiceActive(svc) {
    var payload = {
      id: svc.id,
      slug: svc.slug,
      name_en: svc.name_en,
      name_es: svc.name_es,
      description_en: svc.description_en,
      description_es: svc.description_es,
      body_en: svc.body_en || '',
      body_es: svc.body_es || '',
      icon: svc.icon,
      color_hex: svc.color_hex,
      category: svc.category,
      is_active: Number(svc.is_active) === 1 ? 0 : 1,
      is_bookable: svc.is_bookable,
      has_detail_page: svc.has_detail_page,
      sort_order: svc.sort_order,
      duration_estimate: svc.duration_estimate || '',
    };
    try {
      var res = await fetch(API, {
        method: 'PUT',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(payload.is_active ? t('svcActivated', 'Service activated') : t('svcDeactivated', 'Service deactivated'));
        loadServices();
      }
    } catch (err) {
      console.error('toggleServiceActive error:', err);
    }
  }

  // ─── Delete service ───────────────────────────────────────
  async function deleteService(id) {
    if (!confirm(t('svcDeleteConfirm', 'Delete this service? Appointments referencing it will prevent deletion.'))) return;

    try {
      var res = await fetch(API, {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('svcDeleted', 'Service deleted'));
        loadServices();
      } else {
        if (typeof showToast === 'function') showToast(json.error || t('svcDeleteFail', 'Delete failed'), true);
      }
    } catch (err) {
      console.error('deleteService error:', err);
    }
  }

  // ═══════════════════════════════════════════════════════════
  // Service FAQ sub-panel
  // ═══════════════════════════════════════════════════════════
  var currentFaqServiceId = null;
  var currentFaqServiceName = '';
  var serviceFaqs = [];
  var editingFaqId = null;

  function openFaqPanel(svc) {
    currentFaqServiceId = svc.id;
    currentFaqServiceName = currentLang === 'es' ? (svc.name_es || svc.name_en) : svc.name_en;
    var panel = document.getElementById('service-faq-panel');
    if (!panel) return;
    var title = document.getElementById('service-faq-panel-title');
    if (title) title.textContent = currentFaqServiceName + ' — FAQs';
    panel.classList.remove('hidden');
    document.getElementById('service-faq-form').classList.add('hidden');
    loadServiceFaqs();
  }

  function closeServiceFaqPanel() {
    var panel = document.getElementById('service-faq-panel');
    if (panel) panel.classList.add('hidden');
    currentFaqServiceId = null;
  }

  async function loadServiceFaqs() {
    if (!currentFaqServiceId) return;
    try {
      var res = await fetch(API + '?action=faqs&service_id=' + currentFaqServiceId, { credentials: 'include' });
      var json = await res.json();
      serviceFaqs = json.success ? (json.data || []) : [];
      renderServiceFaqs();
    } catch (err) {
      console.error('loadServiceFaqs error:', err);
    }
  }

  function renderServiceFaqs() {
    var container = document.getElementById('service-faq-list');
    if (!container) return;
    container.textContent = '';

    if (!serviceFaqs.length) {
      var p = document.createElement('p');
      p.className = 'text-gray-400 dark:text-gray-500 text-sm py-4 text-center';
      p.textContent = t('svcNoFaqs', 'No FAQs for this service yet.');
      container.appendChild(p);
      return;
    }

    serviceFaqs.forEach(function(faq) {
      var div = document.createElement('div');
      div.className = 'border dark:border-gray-600 rounded-lg p-3 mb-2 flex justify-between items-start';

      var textDiv = document.createElement('div');
      textDiv.className = 'flex-1 mr-3';
      var qDiv = document.createElement('div');
      qDiv.className = 'font-medium text-sm dark:text-gray-200';
      qDiv.textContent = (currentLang === 'es' ? faq.question_es : faq.question_en) || faq.question_en;
      textDiv.appendChild(qDiv);
      var aDiv = document.createElement('div');
      aDiv.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2';
      aDiv.textContent = (currentLang === 'es' ? faq.answer_es : faq.answer_en) || faq.answer_en;
      textDiv.appendChild(aDiv);
      div.appendChild(textDiv);

      var btns = document.createElement('div');
      btns.className = 'flex gap-2 shrink-0';
      var editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 text-sm dark:text-blue-400';
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function() { openFaqEditForm(faq); });
      btns.appendChild(editBtn);
      var delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 text-sm dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function() { deleteServiceFaq(faq.id); });
      btns.appendChild(delBtn);
      div.appendChild(btns);

      container.appendChild(div);
    });
  }

  function toggleServiceFaqForm() {
    var form = document.getElementById('service-faq-form');
    if (!form) return;
    if (form.classList.contains('hidden')) {
      editingFaqId = null;
      ['sfaq-question-en', 'sfaq-question-es', 'sfaq-answer-en', 'sfaq-answer-es'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
      });
      var sort = document.getElementById('sfaq-sort');
      if (sort) sort.value = '0';
      form.classList.remove('hidden');
    } else {
      form.classList.add('hidden');
    }
  }

  function openFaqEditForm(faq) {
    editingFaqId = faq.id;
    document.getElementById('sfaq-question-en').value = faq.question_en || '';
    document.getElementById('sfaq-question-es').value = faq.question_es || '';
    document.getElementById('sfaq-answer-en').value = faq.answer_en || '';
    document.getElementById('sfaq-answer-es').value = faq.answer_es || '';
    document.getElementById('sfaq-sort').value = faq.sort_order || '0';
    var form = document.getElementById('service-faq-form');
    form.classList.remove('hidden');
  }

  async function saveServiceFaq() {
    var qEn = document.getElementById('sfaq-question-en').value.trim();
    if (!qEn) {
      if (typeof showToast === 'function') showToast(t('svcFaqQuestionRequired', 'Question (EN) is required'), true);
      return;
    }

    var payload = {
      service_id: currentFaqServiceId,
      question_en: qEn,
      question_es: document.getElementById('sfaq-question-es').value.trim(),
      answer_en: document.getElementById('sfaq-answer-en').value.trim(),
      answer_es: document.getElementById('sfaq-answer-es').value.trim(),
      sort_order: parseInt(document.getElementById('sfaq-sort').value, 10) || 0,
    };

    var method = editingFaqId ? 'PUT' : 'POST';
    if (editingFaqId) payload.id = editingFaqId;

    try {
      var res = await fetch(API + '?action=faqs', {
        method: method,
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingFaqId ? t('svcFaqUpdated', 'FAQ updated') : t('svcFaqCreated', 'FAQ created'));
        document.getElementById('service-faq-form').classList.add('hidden');
        editingFaqId = null;
        loadServiceFaqs();
        loadServices(); // refresh faq_count
      } else {
        if (typeof showToast === 'function') showToast(json.error || 'Save failed', true);
      }
    } catch (err) {
      console.error('saveServiceFaq error:', err);
    }
  }

  async function deleteServiceFaq(id) {
    if (!confirm(t('svcFaqDeleteConfirm', 'Delete this FAQ?'))) return;
    try {
      var res = await fetch(API + '?action=faqs', {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      var json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('svcFaqDeleted', 'FAQ deleted'));
        loadServiceFaqs();
        loadServices();
      }
    } catch (err) {
      console.error('deleteServiceFaq error:', err);
    }
  }

  // ─── Expose to global scope ───────────────────────────────
  window.loadServices = loadServices;
  window.toggleServiceForm = toggleServiceForm;
  window.saveService = saveService;
  window.autoServiceSlug = autoSlug;
  window.closeServiceFaqPanel = closeServiceFaqPanel;
  window.toggleServiceFaqForm = toggleServiceFaqForm;
  window.saveServiceFaq = saveServiceFaq;
})();
