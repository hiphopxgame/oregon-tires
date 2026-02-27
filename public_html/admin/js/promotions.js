/**
 * Oregon Tires — Admin Promotions Manager
 * Handles CRUD for seasonal promotions displayed on the homepage banner.
 */

(function() {
  'use strict';

  const API = '/api/admin/promotions.php';
  let promotions = [];
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

  // ─── Load promotions ──────────────────────────────────────────
  async function loadPromotions() {
    try {
      const res = await fetch(API, { credentials: 'include' });
      const json = await res.json();
      if (json.success) {
        promotions = json.data || [];
      } else {
        promotions = [];
      }
      renderPromotionsTable();
    } catch (err) {
      console.error('loadPromotions error:', err);
      if (typeof showToast === 'function') showToast('Failed to load promotions', true);
    }
  }

  // ─── Render table ─────────────────────────────────────────────
  function renderPromotionsTable() {
    const container = document.getElementById('promotions-table-body');
    if (!container) return;

    container.textContent = '';

    if (!promotions.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 6;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = 'No promotions yet. Click "New Promotion" to create one.';
      tr.appendChild(td);
      container.appendChild(tr);
      return;
    }

    promotions.forEach(function(promo) {
      const tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Title cell
      const tdTitle = document.createElement('td');
      tdTitle.className = 'px-4 py-3';
      const titleText = document.createElement('div');
      titleText.className = 'font-medium text-sm dark:text-gray-200';
      titleText.textContent = promo.title_en || '(untitled)';
      tdTitle.appendChild(titleText);
      if (promo.badge_text) {
        const badge = document.createElement('span');
        badge.className = 'inline-block mt-1 text-xs px-2 py-0.5 rounded-full font-bold';
        badge.style.backgroundColor = promo.bg_color || '#f59e0b';
        badge.style.color = promo.text_color || '#000';
        badge.textContent = promo.badge_text;
        tdTitle.appendChild(badge);
      }
      tr.appendChild(tdTitle);

      // Status cell
      const tdStatus = document.createElement('td');
      tdStatus.className = 'px-4 py-3';
      const statusBadge = document.createElement('span');
      const isActive = Number(promo.is_active) === 1;
      const now = new Date();
      const startsAt = promo.starts_at ? new Date(promo.starts_at) : null;
      const endsAt = promo.ends_at ? new Date(promo.ends_at) : null;
      const isLive = isActive && (!startsAt || startsAt <= now) && (!endsAt || endsAt >= now);
      const isScheduled = isActive && startsAt && startsAt > now;
      const isExpired = isActive && endsAt && endsAt < now;

      if (!isActive) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
        statusBadge.textContent = 'Inactive';
      } else if (isLive) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300';
        statusBadge.textContent = 'Live';
      } else if (isScheduled) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300';
        statusBadge.textContent = 'Scheduled';
      } else if (isExpired) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300';
        statusBadge.textContent = 'Expired';
      }
      tdStatus.appendChild(statusBadge);
      tr.appendChild(tdStatus);

      // Date range cell
      const tdDates = document.createElement('td');
      tdDates.className = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-400';
      const startStr = promo.starts_at ? new Date(promo.starts_at).toLocaleDateString() : 'Always';
      const endStr = promo.ends_at ? new Date(promo.ends_at).toLocaleDateString() : 'Never';
      tdDates.textContent = startStr + ' — ' + endStr;
      tr.appendChild(tdDates);

      // Sort order cell
      const tdSort = document.createElement('td');
      tdSort.className = 'px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400';
      tdSort.textContent = promo.sort_order || '0';
      tr.appendChild(tdSort);

      // Preview cell (color swatch)
      const tdPreview = document.createElement('td');
      tdPreview.className = 'px-4 py-3';
      const swatch = document.createElement('div');
      swatch.className = 'w-8 h-8 rounded border dark:border-gray-600';
      swatch.style.backgroundColor = promo.bg_color || '#f59e0b';
      swatch.title = 'BG: ' + (promo.bg_color || '#f59e0b');
      tdPreview.appendChild(swatch);
      tr.appendChild(tdPreview);

      // Actions cell
      const tdActions = document.createElement('td');
      tdActions.className = 'px-4 py-3';
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      const editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400';
      editBtn.textContent = 'Edit';
      editBtn.addEventListener('click', function() { openEditForm(promo); });
      actionsWrap.appendChild(editBtn);

      const toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? 'Deactivate' : 'Activate';
      toggleBtn.addEventListener('click', function() { toggleActive(promo); });
      actionsWrap.appendChild(toggleBtn);

      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = 'Delete';
      delBtn.addEventListener('click', function() { deletePromotion(promo.id); });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);

      container.appendChild(tr);
    });
  }

  // ─── Toggle form visibility ───────────────────────────────────
  function togglePromotionForm() {
    const form = document.getElementById('promotion-form-panel');
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

  // ─── Reset form fields ────────────────────────────────────────
  function resetForm() {
    editingId = null;
    document.getElementById('promo-title-en').value = '';
    document.getElementById('promo-title-es').value = '';
    document.getElementById('promo-body-en').value = '';
    document.getElementById('promo-body-es').value = '';
    document.getElementById('promo-cta-text-en').value = 'Book Now';
    document.getElementById('promo-cta-text-es').value = 'Reserve Ahora';
    document.getElementById('promo-cta-url').value = '/book-appointment/';
    document.getElementById('promo-bg-color').value = '#f59e0b';
    document.getElementById('promo-text-color').value = '#000000';
    document.getElementById('promo-badge').value = '';
    document.getElementById('promo-active').checked = true;
    document.getElementById('promo-starts').value = '';
    document.getElementById('promo-ends').value = '';
    document.getElementById('promo-sort').value = '0';
    document.getElementById('promo-form-title').textContent = 'New Promotion';
    document.getElementById('promo-save-btn').textContent = 'Create Promotion';
  }

  // ─── Open form for editing ────────────────────────────────────
  function openEditForm(promo) {
    editingId = promo.id;
    document.getElementById('promo-title-en').value = promo.title_en || '';
    document.getElementById('promo-title-es').value = promo.title_es || '';
    document.getElementById('promo-body-en').value = promo.body_en || '';
    document.getElementById('promo-body-es').value = promo.body_es || '';
    document.getElementById('promo-cta-text-en').value = promo.cta_text_en || 'Book Now';
    document.getElementById('promo-cta-text-es').value = promo.cta_text_es || 'Reserve Ahora';
    document.getElementById('promo-cta-url').value = promo.cta_url || '/book-appointment/';
    document.getElementById('promo-bg-color').value = promo.bg_color || '#f59e0b';
    document.getElementById('promo-text-color').value = promo.text_color || '#000000';
    document.getElementById('promo-badge').value = promo.badge_text || '';
    document.getElementById('promo-active').checked = Number(promo.is_active) === 1;
    // Format datetime-local values (strip seconds if present)
    document.getElementById('promo-starts').value = promo.starts_at ? promo.starts_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo-ends').value = promo.ends_at ? promo.ends_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo-sort').value = promo.sort_order || '0';
    document.getElementById('promo-form-title').textContent = 'Edit Promotion';
    document.getElementById('promo-save-btn').textContent = 'Update Promotion';

    const form = document.getElementById('promotion-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Save (create or update) ──────────────────────────────────
  async function savePromotion() {
    const titleEn = document.getElementById('promo-title-en').value.trim();
    if (!titleEn) {
      if (typeof showToast === 'function') showToast('Title (EN) is required', true);
      return;
    }

    const payload = {
      title_en: titleEn,
      title_es: document.getElementById('promo-title-es').value.trim(),
      body_en: document.getElementById('promo-body-en').value.trim(),
      body_es: document.getElementById('promo-body-es').value.trim(),
      cta_text_en: document.getElementById('promo-cta-text-en').value.trim() || 'Book Now',
      cta_text_es: document.getElementById('promo-cta-text-es').value.trim() || 'Reserve Ahora',
      cta_url: document.getElementById('promo-cta-url').value.trim() || '/book-appointment/',
      bg_color: document.getElementById('promo-bg-color').value,
      text_color: document.getElementById('promo-text-color').value,
      badge_text: document.getElementById('promo-badge').value.trim(),
      is_active: document.getElementById('promo-active').checked ? 1 : 0,
      starts_at: document.getElementById('promo-starts').value ? document.getElementById('promo-starts').value.replace('T', ' ') + ':00' : null,
      ends_at: document.getElementById('promo-ends').value ? document.getElementById('promo-ends').value.replace('T', ' ') + ':00' : null,
      sort_order: parseInt(document.getElementById('promo-sort').value, 10) || 0,
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
        if (typeof showToast === 'function') showToast(editingId ? 'Promotion updated' : 'Promotion created');
        document.getElementById('promotion-form-panel').classList.add('hidden');
        editingId = null;
        loadPromotions();
      } else {
        if (typeof showToast === 'function') showToast(json.error || 'Save failed', true);
      }
    } catch (err) {
      console.error('savePromotion error:', err);
      if (typeof showToast === 'function') showToast('Network error', true);
    }
  }

  // ─── Toggle active status ─────────────────────────────────────
  async function toggleActive(promo) {
    const payload = Object.assign({}, promo, {
      is_active: Number(promo.is_active) === 1 ? 0 : 1,
    });
    try {
      const res = await fetch(API, {
        method: 'PUT',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(payload.is_active ? 'Promotion activated' : 'Promotion deactivated');
        loadPromotions();
      }
    } catch (err) {
      console.error('toggleActive error:', err);
    }
  }

  // ─── Delete promotion ─────────────────────────────────────────
  async function deletePromotion(id) {
    if (!confirm('Delete this promotion? This cannot be undone.')) return;

    try {
      const res = await fetch(API, {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast('Promotion deleted');
        loadPromotions();
      }
    } catch (err) {
      console.error('deletePromotion error:', err);
    }
  }

  // ─── Live preview ─────────────────────────────────────────────
  function updatePreview() {
    const preview = document.getElementById('promo-live-preview');
    if (!preview) return;

    const bgColor = document.getElementById('promo-bg-color').value;
    const textColor = document.getElementById('promo-text-color').value;
    const titleEn = document.getElementById('promo-title-en').value || 'Your Promotion Title';
    const badge = document.getElementById('promo-badge').value;
    const ctaText = document.getElementById('promo-cta-text-en').value || 'Book Now';

    preview.style.backgroundColor = bgColor;
    preview.style.color = textColor;

    preview.textContent = '';

    if (badge) {
      const badgeEl = document.createElement('span');
      badgeEl.className = 'inline-block px-2 py-0.5 rounded-full text-xs font-bold mr-2 border';
      badgeEl.style.borderColor = textColor;
      badgeEl.textContent = badge;
      preview.appendChild(badgeEl);
    }

    const titleSpan = document.createElement('span');
    titleSpan.className = 'font-semibold';
    titleSpan.textContent = titleEn;
    preview.appendChild(titleSpan);

    const ctaSpan = document.createElement('span');
    ctaSpan.className = 'ml-3 underline font-bold';
    ctaSpan.textContent = ctaText + ' →';
    preview.appendChild(ctaSpan);
  }

  // ─── Expose to global scope ───────────────────────────────────
  window.loadPromotions = loadPromotions;
  window.togglePromotionForm = togglePromotionForm;
  window.savePromotion = savePromotion;
  window.updatePromoPreview = updatePreview;
})();
