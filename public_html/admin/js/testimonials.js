/**
 * Oregon Tires — Admin Testimonials Manager
 * Handles CRUD for customer reviews displayed on the homepage.
 */

(function() {
  'use strict';

  const API = '/api/admin/testimonials.php';
  let testimonials = [];
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

  // ─── Load testimonials ──────────────────────────────────────
  async function loadTestimonials() {
    try {
      const res = await fetch(API, { credentials: 'include' });
      const json = await res.json();
      testimonials = json.success ? (json.data || []) : [];
      renderTestimonialsTable();
    } catch (err) {
      console.error('loadTestimonials error:', err);
      if (typeof showToast === 'function') showToast('Failed to load reviews', true);
    }
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

    if (!testimonials.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 6;
      td.className = 'text-center py-8 text-gray-400 dark:text-gray-500';
      td.textContent = 'No reviews yet. Click "New Review" to create one.';
      tr.appendChild(td);
      container.appendChild(tr);
      return;
    }

    testimonials.forEach(function(rev) {
      const tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Name cell
      const tdName = document.createElement('td');
      tdName.className = 'px-4 py-3 font-medium text-sm dark:text-gray-200';
      tdName.textContent = rev.customer_name || '(unnamed)';
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

      // Status cell
      const tdStatus = document.createElement('td');
      tdStatus.className = 'px-4 py-3';
      const statusBadge = document.createElement('span');
      const isActive = Number(rev.is_active) === 1;
      statusBadge.className = isActive
        ? 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
        : 'text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
      statusBadge.textContent = isActive ? 'Active' : 'Inactive';
      tdStatus.appendChild(statusBadge);
      tr.appendChild(tdStatus);

      // Actions cell
      const tdActions = document.createElement('td');
      tdActions.className = 'px-4 py-3';
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      const editBtn = document.createElement('button');
      editBtn.className = 'text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-blue-400';
      editBtn.textContent = 'Edit';
      editBtn.addEventListener('click', function() { openEditForm(rev); });
      actionsWrap.appendChild(editBtn);

      const toggleBtn = document.createElement('button');
      toggleBtn.className = isActive
        ? 'text-amber-600 hover:text-amber-800 text-sm font-medium dark:text-amber-400'
        : 'text-green-600 hover:text-green-800 text-sm font-medium dark:text-green-400';
      toggleBtn.textContent = isActive ? 'Deactivate' : 'Activate';
      toggleBtn.addEventListener('click', function() { toggleReviewActive(rev); });
      actionsWrap.appendChild(toggleBtn);

      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-600 hover:text-red-800 text-sm font-medium dark:text-red-400';
      delBtn.textContent = 'Delete';
      delBtn.addEventListener('click', function() { deleteReview(rev.id); });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);

      container.appendChild(tr);
    });
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
    document.getElementById('review-sort').value = '0';
    document.getElementById('review-form-title').textContent = 'New Review';
    document.getElementById('review-save-btn').textContent = 'Create Review';
  }

  // ─── Open form for editing ──────────────────────────────────
  function openEditForm(rev) {
    editingId = rev.id;
    document.getElementById('review-name').value = rev.customer_name || '';
    document.getElementById('review-rating').value = rev.rating || '5';
    document.getElementById('review-text-en').value = rev.review_text_en || '';
    document.getElementById('review-text-es').value = rev.review_text_es || '';
    document.getElementById('review-active').checked = Number(rev.is_active) === 1;
    document.getElementById('review-sort').value = rev.sort_order || '0';
    document.getElementById('review-form-title').textContent = 'Edit Review';
    document.getElementById('review-save-btn').textContent = 'Update Review';

    const form = document.getElementById('review-form-panel');
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Save (create or update) ────────────────────────────────
  async function saveReview() {
    const name = document.getElementById('review-name').value.trim();
    if (!name) {
      if (typeof showToast === 'function') showToast('Customer name is required', true);
      return;
    }

    const payload = {
      customer_name: name,
      rating: parseInt(document.getElementById('review-rating').value, 10) || 5,
      review_text_en: document.getElementById('review-text-en').value.trim(),
      review_text_es: document.getElementById('review-text-es').value.trim(),
      is_active: document.getElementById('review-active').checked ? 1 : 0,
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
        if (typeof showToast === 'function') showToast(editingId ? 'Review updated' : 'Review created');
        document.getElementById('review-form-panel').classList.add('hidden');
        editingId = null;
        loadTestimonials();
      } else {
        if (typeof showToast === 'function') showToast(json.error || 'Save failed', true);
      }
    } catch (err) {
      console.error('saveReview error:', err);
      if (typeof showToast === 'function') showToast('Network error', true);
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
        if (typeof showToast === 'function') showToast(payload.is_active ? 'Review activated' : 'Review deactivated');
        loadTestimonials();
      }
    } catch (err) {
      console.error('toggleReviewActive error:', err);
    }
  }

  // ─── Delete review ──────────────────────────────────────────
  async function deleteReview(id) {
    if (!confirm('Delete this review? This cannot be undone.')) return;

    try {
      const res = await fetch(API, {
        method: 'DELETE',
        headers: getHeaders(true),
        credentials: 'include',
        body: JSON.stringify({ id: id }),
      });
      const json = await res.json();
      if (json.success) {
        if (typeof showToast === 'function') showToast('Review deleted');
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
})();
