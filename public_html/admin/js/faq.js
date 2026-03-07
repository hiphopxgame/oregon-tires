/**
 * Oregon Tires — Admin FAQ Manager
 * Handles CRUD for FAQ items displayed on the homepage.
 */

(function() {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  const API = '/api/admin/faq.php';
  let faqs = [];
  let editingId = null;

  function getCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function getHeaders(isJson) {
    const h = { 'X-CSRF-Token': getCsrf() };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }

  // ─── Load FAQs ──────────────────────────────────────────────
  async function loadFaqs() {
    try {
      const res = await fetch(API, { credentials: 'include' });
      const json = await res.json();
      faqs = json.success ? (json.data || []) : [];
      renderFaqTable();
    } catch (err) {
      console.error('loadFaqs error:', err);
      if (typeof showToast === 'function') showToast('Failed to load FAQs', true);
    }
  }

  // ─── Render table ───────────────────────────────────────────
  function renderFaqTable() {
    const container = document.getElementById('faq-table-body');
    if (!container) return;

    container.textContent = '';

    if (!faqs.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 5;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = t('faqNoFaqs', 'No FAQs yet. Click "New FAQ" to create one.');
      tr.appendChild(td);
      container.appendChild(tr);
      return;
    }

    faqs.forEach(function(faq) {
      const tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Question cell
      const tdQ = document.createElement('td');
      tdQ.className = 'px-4 py-3';
      const qText = document.createElement('div');
      qText.className = 'font-medium text-sm dark:text-gray-200';
      const question = faq.question_en || '(untitled)';
      qText.textContent = question.length > 80 ? question.substring(0, 80) + '...' : question;
      tdQ.appendChild(qText);
      tr.appendChild(tdQ);

      // Status cell
      const tdStatus = document.createElement('td');
      tdStatus.className = 'px-4 py-3';
      const statusBadge = document.createElement('span');
      const isActive = Number(faq.is_active) === 1;
      statusBadge.className = isActive
        ? 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
        : 'text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
      statusBadge.textContent = isActive ? t('faqActive', 'Active') : t('faqInactive', 'Inactive');
      tdStatus.appendChild(statusBadge);
      tr.appendChild(tdStatus);

      // Sort order cell
      const tdSort = document.createElement('td');
      tdSort.className = 'px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400';
      tdSort.textContent = faq.sort_order || '0';
      tr.appendChild(tdSort);

      // Actions cell
      const tdActions = document.createElement('td');
      tdActions.className = 'px-4 py-3';
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      const editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400';
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function() { openEditForm(faq); });
      actionsWrap.appendChild(editBtn);

      const toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? t('actionDeactivate', 'Deactivate') : t('actionActivate', 'Activate');
      toggleBtn.addEventListener('click', function() { toggleFaqActive(faq); });
      actionsWrap.appendChild(toggleBtn);

      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function() { deleteFaq(faq.id); });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);

      container.appendChild(tr);
    });
  }

  // ─── Toggle form visibility ─────────────────────────────────
  function toggleFaqForm() {
    const form = document.getElementById('faq-form-panel');
    if (!form) return;
    const isHidden = form.classList.contains('hidden');
    if (isHidden) {
      resetForm();
      form.classList.remove('hidden');
    } else {
      form.classList.add('hidden');
      editingId = null;
    }
  }

  // ─── Reset form fields ──────────────────────────────────────
  function resetForm() {
    editingId = null;
    document.getElementById('faq-question-en').value = '';
    document.getElementById('faq-question-es').value = '';
    document.getElementById('faq-answer-en').value = '';
    document.getElementById('faq-answer-es').value = '';
    document.getElementById('faq-active').checked = true;
    document.getElementById('faq-sort').value = '0';
    document.getElementById('faq-form-title').textContent = t('faqNewFaq', 'New FAQ');
    document.getElementById('faq-save-btn').textContent = t('faqCreateFaq', 'Create FAQ');
  }

  // ─── Open form for editing ──────────────────────────────────
  function openEditForm(faq) {
    editingId = faq.id;
    document.getElementById('faq-question-en').value = faq.question_en || '';
    document.getElementById('faq-question-es').value = faq.question_es || '';
    document.getElementById('faq-answer-en').value = faq.answer_en || '';
    document.getElementById('faq-answer-es').value = faq.answer_es || '';
    document.getElementById('faq-active').checked = Number(faq.is_active) === 1;
    document.getElementById('faq-sort').value = faq.sort_order || '0';
    document.getElementById('faq-form-title').textContent = t('faqEditFaq', 'Edit FAQ');
    document.getElementById('faq-save-btn').textContent = t('faqUpdateFaq', 'Update FAQ');

    const form = document.getElementById('faq-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Save (create or update) ────────────────────────────────
  async function saveFaq() {
    const questionEn = document.getElementById('faq-question-en').value.trim();
    if (!questionEn) {
      if (typeof showToast === 'function') showToast('Question (EN) is required', true);
      return;
    }

    const payload = {
      question_en: questionEn,
      question_es: document.getElementById('faq-question-es').value.trim(),
      answer_en: document.getElementById('faq-answer-en').value.trim(),
      answer_es: document.getElementById('faq-answer-es').value.trim(),
      is_active: document.getElementById('faq-active').checked ? 1 : 0,
      sort_order: parseInt(document.getElementById('faq-sort').value, 10) || 0,
    };

    const method = editingId ? 'PUT' : 'POST';
    if (editingId) payload.id = editingId;

    try {
      const res = await fetch(API, {
        method: method,
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingId ? t('faqUpdated', 'FAQ updated') : t('faqCreated', 'FAQ created'));
        document.getElementById('faq-form-panel').classList.add('hidden');
        editingId = null;
        loadFaqs();
      } else {
        if (typeof showToast === 'function') showToast(json.error || 'Save failed', true);
      }
    } catch (err) {
      console.error('saveFaq error:', err);
      if (typeof showToast === 'function') showToast('Network error', true);
    }
  }

  // ─── Toggle active status ───────────────────────────────────
  async function toggleFaqActive(faq) {
    const payload = {
      id: faq.id,
      question_en: faq.question_en,
      question_es: faq.question_es,
      answer_en: faq.answer_en,
      answer_es: faq.answer_es,
      is_active: Number(faq.is_active) === 1 ? 0 : 1,
      sort_order: faq.sort_order,
    };
    try {
      const res = await fetch(API, {
        method: 'PUT',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(payload.is_active ? t('faqActivated', 'FAQ activated') : t('faqDeactivated', 'FAQ deactivated'));
        loadFaqs();
      }
    } catch (err) {
      console.error('toggleFaqActive error:', err);
    }
  }

  // ─── Delete FAQ ─────────────────────────────────────────────
  async function deleteFaq(id) {
    if (!confirm('Delete this FAQ? This cannot be undone.')) return;

    try {
      const res = await fetch(API, {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('faqDeleted', 'FAQ deleted'));
        loadFaqs();
      }
    } catch (err) {
      console.error('deleteFaq error:', err);
    }
  }

  // ─── Expose to global scope ─────────────────────────────────
  window.loadFaqs = loadFaqs;
  window.toggleFaqForm = toggleFaqForm;
  window.saveFaq = saveFaq;
})();
