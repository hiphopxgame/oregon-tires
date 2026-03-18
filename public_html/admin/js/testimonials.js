/**
 * Oregon Tires — Admin Testimonials Manager
 * Handles CRUD for customer reviews + Google review fetching + homepage toggle.
 */

(function() {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  const API = '/api/admin/testimonials.php';
  let testimonials = [];
  let editingId = null;
  let activeFilter = 'all'; // all | google | manual | homepage

  function getCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function getHeaders(isJson) {
    const h = { 'X-CSRF-Token': getCsrf() };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }

  // ─── Load testimonials ──────────────────────────────────────
  async function loadTestimonials() {
    try {
      const res = await fetch(API, { credentials: 'include' });
      const json = await res.json();
      testimonials = json.success ? (json.data || []) : [];
      renderTestimonialsTable();
      updateLastSynced();
    } catch (err) {
      console.error('loadTestimonials error:', err);
      if (typeof showToast === 'function') showToast(t('testimLoadFail', 'Failed to load reviews'), true);
    }
  }

  // ─── Update last synced display ───────────────────────────────
  async function updateLastSynced() {
    try {
      const res = await fetch('/api/testimonials.php?scope=stats', { credentials: 'include' });
      const json = await res.json();
      const el = document.getElementById('google-last-synced');
      if (!el || !json.success) return;
      const lastFetched = json.data.last_fetched;
      if (!lastFetched) {
        el.textContent = t('testimNeverSynced', 'Never synced');
        return;
      }
      var d = new Date(lastFetched + ' UTC');
      var diff = Math.floor((Date.now() - d.getTime()) / 60000);
      if (diff < 1) el.textContent = t('testimSyncedJustNow', 'Synced just now');
      else if (diff < 60) el.textContent = t('testimSyncedMinAgo', 'Synced {n} min ago').replace('{n}', diff);
      else if (diff < 1440) el.textContent = t('testimSyncedHrAgo', 'Synced {n}h ago').replace('{n}', Math.floor(diff / 60));
      else el.textContent = t('testimSyncedDayAgo', 'Synced {n}d ago').replace('{n}', Math.floor(diff / 1440));
    } catch (err) {
      // ignore
    }
  }

  // ─── Fetch from Google ────────────────────────────────────────
  async function fetchFromGoogle() {
    var btn = document.getElementById('btn-fetch-google');
    if (btn) { btn.disabled = true; btn.textContent = t('testimFetching', 'Fetching...'); }
    try {
      const res = await fetch(API + '?action=fetch-google', { credentials: 'include' });
      const json = await res.json();
      if (json.success) {
        var msg = t('testimImported', 'Imported {n} new Google review(s)').replace('{n}', json.data.imported || 0);
        if (json.data.total) msg += ' (' + json.data.total + ' total from Google)';
        if (typeof showToast === 'function') showToast(msg);
        loadTestimonials();
      } else {
        if (typeof showToast === 'function') showToast(json.error || t('testimFetchFailed', 'Fetch failed'), true);
      }
    } catch (err) {
      console.error('fetchFromGoogle error:', err);
      if (typeof showToast === 'function') showToast(t('testimNetworkError', 'Network error'), true);
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = '\uD83D\uDD04 ' + t('testimFetchFromGoogle', 'Fetch from Google'); }
    }
  }

  // ─── Filter ───────────────────────────────────────────────────
  function setFilter(filter) {
    activeFilter = filter;
    document.querySelectorAll('.review-filter-btn').forEach(function(b) {
      b.classList.toggle('bg-brand', b.dataset.filter === filter);
      b.classList.toggle('text-white', b.dataset.filter === filter);
      b.classList.toggle('bg-gray-100', b.dataset.filter !== filter);
      b.classList.toggle('dark:bg-gray-600', b.dataset.filter !== filter);
    });
    renderTestimonialsTable();
  }

  function getFilteredTestimonials() {
    if (activeFilter === 'google') return testimonials.filter(function(r) { return r.source === 'google'; });
    if (activeFilter === 'manual') return testimonials.filter(function(r) { return r.source === 'manual'; });
    if (activeFilter === 'homepage') return testimonials.filter(function(r) { return Number(r.show_on_homepage) === 1; });
    return testimonials;
  }

  // ─── Render stars ───────────────────────────────────────────
  function renderStars(rating) {
    return '\u2605'.repeat(rating) + '\u2606'.repeat(5 - rating);
  }

  // ─── Render table ───────────────────────────────────────────
  function renderTestimonialsTable() {
    const container = document.getElementById('testimonials-table-body');
    if (!container) return;

    container.textContent = '';
    var filtered = getFilteredTestimonials();

    if (!filtered.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 7;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = t('reviewNoReviews', 'No reviews match this filter.');
      tr.appendChild(td);
      container.appendChild(tr);
      return;
    }

    filtered.forEach(function(rev) {
      const tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Source badge cell
      var tdSource = document.createElement('td');
      tdSource.className = 'px-4 py-3';
      var sourceBadge = document.createElement('span');
      var isGoogle = rev.source === 'google';
      sourceBadge.className = isGoogle
        ? 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
        : 'text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
      sourceBadge.textContent = isGoogle ? 'Google' : 'Manual';
      tdSource.appendChild(sourceBadge);
      tr.appendChild(tdSource);

      // Name cell (with avatar for Google reviews)
      const tdName = document.createElement('td');
      tdName.className = 'px-4 py-3 font-medium text-sm dark:text-gray-200';
      var nameWrap = document.createElement('div');
      nameWrap.className = 'flex items-center gap-2';
      if (rev.author_photo_url) {
        var avatar = document.createElement('img');
        avatar.src = rev.author_photo_url;
        avatar.alt = '';
        avatar.className = 'w-7 h-7 rounded-full object-cover';
        avatar.loading = 'lazy';
        nameWrap.appendChild(avatar);
      } else {
        var initials = document.createElement('span');
        initials.className = 'w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold text-gray-500 dark:text-gray-300';
        initials.textContent = (rev.customer_name || '?')[0].toUpperCase();
        nameWrap.appendChild(initials);
      }
      var nameText = document.createElement('span');
      nameText.textContent = rev.customer_name || '(unnamed)';
      nameWrap.appendChild(nameText);
      tdName.appendChild(nameWrap);
      tr.appendChild(tdName);

      // Rating cell
      const tdRating = document.createElement('td');
      tdRating.className = 'px-4 py-3';
      const starSpan = document.createElement('span');
      starSpan.className = 'star text-amber-500';
      starSpan.textContent = renderStars(Number(rev.rating) || 5);
      tdRating.appendChild(starSpan);
      tr.appendChild(tdRating);

      // Preview cell
      const tdPreview = document.createElement('td');
      tdPreview.className = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-400';
      const preview = rev.review_text_en || '';
      tdPreview.textContent = preview.length > 60 ? preview.substring(0, 60) + '...' : preview;
      tr.appendChild(tdPreview);

      // Homepage star cell
      var tdHomepage = document.createElement('td');
      tdHomepage.className = 'px-4 py-3 text-center';
      var starBtn = document.createElement('button');
      var isHomepage = Number(rev.show_on_homepage) === 1;
      starBtn.className = 'text-xl transition hover:scale-110 ' + (isHomepage ? 'text-amber-500' : 'text-gray-300 dark:text-gray-600');
      starBtn.textContent = isHomepage ? '\u2605' : '\u2606';
      starBtn.title = isHomepage ? t('testimRemoveHomepage', 'Remove from homepage') : t('testimShowHomepage', 'Show on homepage');
      starBtn.addEventListener('click', function() { toggleHomepage(rev); });
      tdHomepage.appendChild(starBtn);
      tr.appendChild(tdHomepage);

      // Status cell
      const tdStatus = document.createElement('td');
      tdStatus.className = 'px-4 py-3';
      const statusBadge = document.createElement('span');
      const isActive = Number(rev.is_active) === 1;
      statusBadge.className = isActive
        ? 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
        : 'text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
      statusBadge.textContent = isActive ? t('reviewActive', 'Active') : t('reviewInactive', 'Inactive');
      tdStatus.appendChild(statusBadge);
      tr.appendChild(tdStatus);

      // Actions cell
      const tdActions = document.createElement('td');
      tdActions.className = 'px-4 py-3';
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      const editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400';
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function() { openEditForm(rev); });
      actionsWrap.appendChild(editBtn);

      const toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? t('actionDeactivate', 'Deactivate') : t('actionActivate', 'Activate');
      toggleBtn.addEventListener('click', function() { toggleReviewActive(rev); });
      actionsWrap.appendChild(toggleBtn);

      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function() { deleteReview(rev.id); });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);

      container.appendChild(tr);
    });
  }

  // ─── Toggle homepage star ─────────────────────────────────────
  async function toggleHomepage(rev) {
    var newValue = Number(rev.show_on_homepage) === 1 ? 0 : 1;
    try {
      const res = await fetch(API, {
        method: 'PUT',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: rev.id, show_on_homepage: newValue }),
      });
      const json = await res.json();
      if (json.success) {
        rev.show_on_homepage = newValue;
        renderTestimonialsTable();
        if (typeof showToast === 'function') showToast(newValue ? t('testimAddedHomepage', 'Added to homepage') : t('testimRemovedHomepage', 'Removed from homepage'));
      }
    } catch (err) {
      console.error('toggleHomepage error:', err);
    }
  }

  // ─── Toggle form visibility ─────────────────────────────────
  function toggleReviewForm() {
    const form = document.getElementById('review-form-panel');
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
    document.getElementById('review-name').value = '';
    document.getElementById('review-rating').value = '5';
    document.getElementById('review-text-en').value = '';
    document.getElementById('review-text-es').value = '';
    document.getElementById('review-active').checked = true;
    document.getElementById('review-homepage').checked = false;
    document.getElementById('review-sort').value = '0';
    document.getElementById('review-form-title').textContent = t('reviewNewReview', 'New Review');
    document.getElementById('review-save-btn').textContent = t('reviewCreateReview', 'Create Review');
  }

  // ─── Open form for editing ──────────────────────────────────
  function openEditForm(rev) {
    editingId = rev.id;
    document.getElementById('review-name').value = rev.customer_name || '';
    document.getElementById('review-rating').value = rev.rating || '5';
    document.getElementById('review-text-en').value = rev.review_text_en || '';
    document.getElementById('review-text-es').value = rev.review_text_es || '';
    document.getElementById('review-active').checked = Number(rev.is_active) === 1;
    document.getElementById('review-homepage').checked = Number(rev.show_on_homepage) === 1;
    document.getElementById('review-sort').value = rev.sort_order || '0';
    document.getElementById('review-form-title').textContent = t('reviewEditReview', 'Edit Review');
    document.getElementById('review-save-btn').textContent = t('reviewUpdateReview', 'Update Review');

    const form = document.getElementById('review-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Save (create or update) ────────────────────────────────
  async function saveReview() {
    const name = document.getElementById('review-name').value.trim();
    if (!name) {
      if (typeof showToast === 'function') showToast(t('testimNameRequired', 'Customer name is required'), true);
      return;
    }

    const payload = {
      customer_name: name,
      rating: parseInt(document.getElementById('review-rating').value, 10) || 5,
      review_text_en: document.getElementById('review-text-en').value.trim(),
      review_text_es: document.getElementById('review-text-es').value.trim(),
      is_active: document.getElementById('review-active').checked ? 1 : 0,
      show_on_homepage: document.getElementById('review-homepage').checked ? 1 : 0,
      sort_order: parseInt(document.getElementById('review-sort').value, 10) || 0,
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
        if (typeof showToast === 'function') showToast(editingId ? t('reviewUpdated', 'Review updated') : t('reviewCreated', 'Review created'));
        document.getElementById('review-form-panel').classList.add('hidden');
        editingId = null;
        loadTestimonials();
      } else {
        if (typeof showToast === 'function') showToast(json.error || t('testimSaveFailed', 'Save failed'), true);
      }
    } catch (err) {
      console.error('saveReview error:', err);
      if (typeof showToast === 'function') showToast(t('testimNetworkError', 'Network error'), true);
    }
  }

  // ─── Toggle active status ───────────────────────────────────
  async function toggleReviewActive(rev) {
    const payload = {
      id: rev.id,
      customer_name: rev.customer_name,
      rating: rev.rating,
      review_text_en: rev.review_text_en,
      review_text_es: rev.review_text_es,
      is_active: Number(rev.is_active) === 1 ? 0 : 1,
      show_on_homepage: rev.show_on_homepage,
      sort_order: rev.sort_order,
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
        if (typeof showToast === 'function') showToast(payload.is_active ? t('reviewActivated', 'Review activated') : t('reviewDeactivated', 'Review deactivated'));
        loadTestimonials();
      }
    } catch (err) {
      console.error('toggleReviewActive error:', err);
    }
  }

  // ─── Delete review ──────────────────────────────────────────
  async function deleteReview(id) {
    if (!confirm(t('testimDeleteConfirm', 'Delete this review? This cannot be undone.'))) return;

    try {
      const res = await fetch(API, {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast(t('reviewDeleted', 'Review deleted'));
        loadTestimonials();
      }
    } catch (err) {
      console.error('deleteReview error:', err);
    }
  }

  // ─── Expose to global scope ─────────────────────────────────
  window.loadTestimonials = loadTestimonials;
  window.toggleReviewForm = toggleReviewForm;
  window.saveReview = saveReview;
  window.fetchFromGoogle = fetchFromGoogle;
  window.setReviewFilter = setFilter;
})();
