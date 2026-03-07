/**
 * Oregon Tires — Admin Promotions Manager
 * Handles CRUD for promotions (homepage banners + exit-intent popup).
 */

(function() {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  const API = '/api/admin/promotions.php';
  let promotions = [];
  let editingId = null;

  function getCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  // ─── Toggle exit-intent vs banner fields ────────────────────
  function toggleExitIntentFields() {
    var placement = document.getElementById('promo-placement').value;
    var isExit = placement === 'exit_intent';
    var exitFields = document.getElementById('exit-intent-fields');
    var bannerFields = document.querySelectorAll('.banner-only-field');

    if (exitFields) {
      exitFields.classList.toggle('hidden', !isExit);
    }
    bannerFields.forEach(function(el) {
      el.classList.toggle('hidden', isExit);
    });
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
      td.colSpan = 8;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = t('promoNoPromos', 'No promotions yet. Click "New Promotion" to create one.');
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

      // Type cell
      const tdType = document.createElement('td');
      tdType.className = 'px-4 py-3';
      const typeBadge = document.createElement('span');
      if (promo.placement === 'exit_intent') {
        typeBadge.className = 'text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 font-medium';
        typeBadge.textContent = t('promoExitPopup', 'Exit Popup');
      } else {
        typeBadge.className = 'text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300 font-medium';
        typeBadge.textContent = t('promoBanner', 'Banner');
      }
      tdType.appendChild(typeBadge);
      tr.appendChild(tdType);

      // Image cell
      const tdImg = document.createElement('td');
      tdImg.className = 'px-4 py-3';
      if (promo.image_url) {
        const thumb = document.createElement('img');
        thumb.src = promo.image_url;
        thumb.alt = 'Promo image';
        thumb.className = 'h-10 w-auto rounded object-cover';
        tdImg.appendChild(thumb);
      } else {
        const noImg = document.createElement('span');
        noImg.className = 'text-xs text-gray-400';
        noImg.textContent = '—';
        tdImg.appendChild(noImg);
      }
      tr.appendChild(tdImg);

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
        statusBadge.textContent = t('promoInactive', 'Inactive');
      } else if (isLive) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300';
        statusBadge.textContent = t('promoLive', 'Live');
      } else if (isScheduled) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300';
        statusBadge.textContent = t('promoScheduled', 'Scheduled');
      } else if (isExpired) {
        statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300';
        statusBadge.textContent = t('promoExpired', 'Expired');
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
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function() { openEditForm(promo); });
      actionsWrap.appendChild(editBtn);

      const toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? t('actionDeactivate', 'Deactivate') : t('actionActivate', 'Activate');
      toggleBtn.addEventListener('click', function() { toggleActive(promo); });
      actionsWrap.appendChild(toggleBtn);

      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
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
    document.getElementById('promo-placement').value = 'banner';
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
    document.getElementById('promo-form-title').textContent = t('promoNewPromo', 'New Promotion');
    document.getElementById('promo-save-btn').textContent = t('promoCreatePromo', 'Create Promotion');
    // Reset exit-intent fields
    document.getElementById('promo-subtitle-en').value = '';
    document.getElementById('promo-subtitle-es').value = '';
    document.getElementById('promo-placeholder-en').value = '';
    document.getElementById('promo-placeholder-es').value = '';
    document.getElementById('promo-success-en').value = '';
    document.getElementById('promo-success-es').value = '';
    document.getElementById('promo-error-en').value = '';
    document.getElementById('promo-error-es').value = '';
    document.getElementById('promo-nospam-en').value = '';
    document.getElementById('promo-nospam-es').value = '';
    document.getElementById('promo-popup-icon').value = '';
    // Reset image
    var imgInput = document.getElementById('promo-image-file');
    if (imgInput) imgInput.value = '';
    var preview = document.getElementById('promo-image-preview');
    if (preview) preview.classList.add('hidden');
    var existing = document.getElementById('promo-existing-image');
    if (existing) existing.classList.add('hidden');
    // Show/hide correct field sections
    toggleExitIntentFields();
  }

  // ─── Open form for editing ────────────────────────────────────
  function openEditForm(promo) {
    editingId = promo.id;
    document.getElementById('promo-placement').value = promo.placement || 'banner';
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
    document.getElementById('promo-starts').value = promo.starts_at ? promo.starts_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo-ends').value = promo.ends_at ? promo.ends_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo-sort').value = promo.sort_order || '0';
    // Exit-intent fields
    document.getElementById('promo-subtitle-en').value = promo.subtitle_en || '';
    document.getElementById('promo-subtitle-es').value = promo.subtitle_es || '';
    document.getElementById('promo-placeholder-en').value = promo.placeholder_en || '';
    document.getElementById('promo-placeholder-es').value = promo.placeholder_es || '';
    document.getElementById('promo-success-en').value = promo.success_msg_en || '';
    document.getElementById('promo-success-es').value = promo.success_msg_es || '';
    document.getElementById('promo-error-en').value = promo.error_msg_en || '';
    document.getElementById('promo-error-es').value = promo.error_msg_es || '';
    document.getElementById('promo-nospam-en').value = promo.nospam_en || '';
    document.getElementById('promo-nospam-es').value = promo.nospam_es || '';
    document.getElementById('promo-popup-icon').value = promo.popup_icon || '';
    document.getElementById('promo-form-title').textContent = t('promoEditPromo', 'Edit Promotion');
    document.getElementById('promo-save-btn').textContent = t('promoUpdatePromo', 'Update Promotion');
    // Image
    var imgInput = document.getElementById('promo-image-file');
    if (imgInput) imgInput.value = '';
    var preview = document.getElementById('promo-image-preview');
    if (preview) preview.classList.add('hidden');
    var existing = document.getElementById('promo-existing-image');
    if (existing) {
      if (promo.image_url) {
        existing.classList.remove('hidden');
        var img = existing.querySelector('img');
        if (img) img.src = promo.image_url;
      } else {
        existing.classList.add('hidden');
      }
    }
    // Show/hide correct field sections
    toggleExitIntentFields();

    const form = document.getElementById('promotion-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Build FormData from form ─────────────────────────────────
  function buildFormData() {
    const fd = new FormData();
    fd.append('placement', document.getElementById('promo-placement').value);
    fd.append('title_en', document.getElementById('promo-title-en').value.trim());
    fd.append('title_es', document.getElementById('promo-title-es').value.trim());
    fd.append('body_en', document.getElementById('promo-body-en').value.trim());
    fd.append('body_es', document.getElementById('promo-body-es').value.trim());
    fd.append('cta_text_en', document.getElementById('promo-cta-text-en').value.trim() || 'Book Now');
    fd.append('cta_text_es', document.getElementById('promo-cta-text-es').value.trim() || 'Reserve Ahora');
    fd.append('cta_url', document.getElementById('promo-cta-url').value.trim() || '/book-appointment/');
    fd.append('bg_color', document.getElementById('promo-bg-color').value);
    fd.append('text_color', document.getElementById('promo-text-color').value);
    fd.append('badge_text', document.getElementById('promo-badge').value.trim());
    fd.append('is_active', document.getElementById('promo-active').checked ? '1' : '0');
    var starts = document.getElementById('promo-starts').value;
    fd.append('starts_at', starts ? starts.replace('T', ' ') + ':00' : '');
    var ends = document.getElementById('promo-ends').value;
    fd.append('ends_at', ends ? ends.replace('T', ' ') + ':00' : '');
    fd.append('sort_order', document.getElementById('promo-sort').value || '0');
    // Exit-intent fields
    fd.append('subtitle_en', document.getElementById('promo-subtitle-en').value.trim());
    fd.append('subtitle_es', document.getElementById('promo-subtitle-es').value.trim());
    fd.append('placeholder_en', document.getElementById('promo-placeholder-en').value.trim());
    fd.append('placeholder_es', document.getElementById('promo-placeholder-es').value.trim());
    fd.append('success_msg_en', document.getElementById('promo-success-en').value.trim());
    fd.append('success_msg_es', document.getElementById('promo-success-es').value.trim());
    fd.append('error_msg_en', document.getElementById('promo-error-en').value.trim());
    fd.append('error_msg_es', document.getElementById('promo-error-es').value.trim());
    fd.append('nospam_en', document.getElementById('promo-nospam-en').value.trim());
    fd.append('nospam_es', document.getElementById('promo-nospam-es').value.trim());
    fd.append('popup_icon', document.getElementById('promo-popup-icon').value.trim());
    // Image file
    var imgInput = document.getElementById('promo-image-file');
    if (imgInput && imgInput.files.length > 0) {
      fd.append('image', imgInput.files[0]);
    }
    return fd;
  }

  // ─── Save (create or update) ──────────────────────────────────
  async function savePromotion() {
    const titleEn = document.getElementById('promo-title-en').value.trim();
    if (!titleEn) {
      if (typeof showToast === 'function') showToast('Title (EN) is required', true);
      return;
    }

    const fd = buildFormData();

    if (editingId) {
      fd.append('_method', 'PUT');
      fd.append('id', String(editingId));
    }

    try {
      const res = await fetch(API, {
        method: 'POST',
        headers: { 'X-CSRF-Token': getCsrf() },
        credentials: 'include',
        body: fd,
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(editingId ? t('promoUpdated', 'Promotion updated') : t('promoCreated', 'Promotion created'));
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
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('id', String(promo.id));
    fd.append('placement', promo.placement || 'banner');
    fd.append('title_en', promo.title_en || '');
    fd.append('title_es', promo.title_es || '');
    fd.append('body_en', promo.body_en || '');
    fd.append('body_es', promo.body_es || '');
    fd.append('subtitle_en', promo.subtitle_en || '');
    fd.append('subtitle_es', promo.subtitle_es || '');
    fd.append('cta_text_en', promo.cta_text_en || 'Book Now');
    fd.append('cta_text_es', promo.cta_text_es || 'Reserve Ahora');
    fd.append('cta_url', promo.cta_url || '/book-appointment/');
    fd.append('placeholder_en', promo.placeholder_en || '');
    fd.append('placeholder_es', promo.placeholder_es || '');
    fd.append('success_msg_en', promo.success_msg_en || '');
    fd.append('success_msg_es', promo.success_msg_es || '');
    fd.append('error_msg_en', promo.error_msg_en || '');
    fd.append('error_msg_es', promo.error_msg_es || '');
    fd.append('nospam_en', promo.nospam_en || '');
    fd.append('nospam_es', promo.nospam_es || '');
    fd.append('popup_icon', promo.popup_icon || '');
    fd.append('bg_color', promo.bg_color || '#f59e0b');
    fd.append('text_color', promo.text_color || '#000000');
    fd.append('badge_text', promo.badge_text || '');
    fd.append('is_active', Number(promo.is_active) === 1 ? '0' : '1');
    fd.append('starts_at', promo.starts_at || '');
    fd.append('ends_at', promo.ends_at || '');
    fd.append('sort_order', String(promo.sort_order || 0));

    try {
      const res = await fetch(API, {
        method: 'POST',
        headers: { 'X-CSRF-Token': getCsrf() },
        credentials: 'include',
        body: fd,
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(Number(promo.is_active) === 1 ? t('promoDeactivated', 'Promotion deactivated') : t('promoActivated', 'Promotion activated'));
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
      const res = await fetch(API + '?id=' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-Token': getCsrf() },
        credentials: 'include',
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('promoDeleted', 'Promotion deleted'));
        loadPromotions();
      }
    } catch (err) {
      console.error('deletePromotion error:', err);
    }
  }

  // ─── Remove promo image ───────────────────────────────────────
  function removePromoImage() {
    var existing = document.getElementById('promo-existing-image');
    if (existing) existing.classList.add('hidden');
    // Mark for removal on next save
    var removeFlag = document.getElementById('promo-remove-image-flag');
    if (!removeFlag) {
      removeFlag = document.createElement('input');
      removeFlag.type = 'hidden';
      removeFlag.id = 'promo-remove-image-flag';
      document.getElementById('promotion-form-panel').appendChild(removeFlag);
    }
    removeFlag.value = '1';
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

  // Override buildFormData to include remove_image flag
  var _origBuildFormData = buildFormData;
  function buildFormDataWithRemove() {
    var fd = _origBuildFormData();
    var removeFlag = document.getElementById('promo-remove-image-flag');
    if (removeFlag && removeFlag.value === '1') {
      fd.append('remove_image', '1');
    }
    return fd;
  }
  // Patch
  buildFormData = buildFormDataWithRemove;

  // ─── Image file input preview ─────────────────────────────────
  document.addEventListener('DOMContentLoaded', function() {
    var imgInput = document.getElementById('promo-image-file');
    if (imgInput) {
      imgInput.addEventListener('change', function() {
        var preview = document.getElementById('promo-image-preview');
        if (!preview) return;
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {
            var img = preview.querySelector('img');
            if (img) img.src = e.target.result;
            preview.classList.remove('hidden');
          };
          reader.readAsDataURL(this.files[0]);
          // Clear remove flag
          var removeFlag = document.getElementById('promo-remove-image-flag');
          if (removeFlag) removeFlag.value = '';
        } else {
          preview.classList.add('hidden');
        }
      });
    }
  });

  // ─── Expose to global scope ───────────────────────────────────
  window.loadPromotions = loadPromotions;
  window.togglePromotionForm = togglePromotionForm;
  window.savePromotion = savePromotion;
  window.updatePromoPreview = updatePreview;
  window.removePromoImage = removePromoImage;
  window.toggleExitIntentFields = toggleExitIntentFields;
})();
