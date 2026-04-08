/**
 * Oregon Tires — Admin Subscribers Management
 * Handles subscriber listing, search, CSV export, and unsubscribe actions.
 */
(function() {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  var currentPage = 1;
  var currentSearch = '';
  var csrfToken = document.querySelector('meta[name="csrf-token"]');

  function getCsrf() {
    return csrfToken ? csrfToken.getAttribute('content') : '';
  }

  // ─── BulkManager Init ──────────────────────────────────────
  if (typeof BulkManager !== 'undefined') {
    BulkManager.init({ tab: 'subscribers', endpoint: 'subscribers.php', onDelete: function() { loadSubscribers(); }, superAdminOnly: false, deleteWarning: 'subscriberBulkDeleteWarn' });
  }

  // ─── Load Subscribers ─────────────────────────────────────────
  window.loadSubscribers = async function(page, search) {
    if (typeof page === 'number') currentPage = page;
    if (typeof search === 'string') currentSearch = search;

    var params = new URLSearchParams({
      page: currentPage,
      limit: 20
    });
    if (currentSearch) params.set('search', currentSearch);

    try {
      var res = await fetch('/api/admin/subscribers.php?' + params.toString(), {
        credentials: 'include'
      });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed to load subscribers');

      var data = json.data;
      renderSubscribers(data.subscribers, data.total, data.active_count, data.page, data.pages);
      updateSubscriberBadge(data.active_count);
    } catch (err) {
      console.error('loadSubscribers error:', err);
      var grid = document.getElementById('subscribers-grid');
      if (grid) {
        grid.textContent = '';
        var errP = document.createElement('p');
        errP.className = 'text-red-600 dark:text-red-400 p-4';
        errP.textContent = t('subscriberFailedLoad', 'Failed to load subscribers.');
        grid.appendChild(errP);
      }
    }
  };

  // ─── Render Subscribers Table ─────────────────────────────────
  function renderSubscribers(subscribers, total, activeCount, page, pages) {
    var grid = document.getElementById('subscribers-grid');
    if (!grid) return;

    if (typeof BulkManager !== 'undefined') BulkManager.reset();

    // Clear existing content
    grid.textContent = '';

    // Stats bar
    var statsDiv = document.createElement('div');
    statsDiv.className = 'flex flex-wrap gap-4 mb-4';

    var activeStat = document.createElement('div');
    activeStat.className = 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-4 py-2';
    var activeSpan = document.createElement('span');
    activeSpan.className = 'text-sm text-green-700 dark:text-green-400 font-semibold';
    activeSpan.textContent = activeCount + ' ' + t('subscriberActive', 'Active');
    activeStat.appendChild(activeSpan);
    statsDiv.appendChild(activeStat);

    var totalStat = document.createElement('div');
    totalStat.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-4 py-2';
    var totalSpan = document.createElement('span');
    totalSpan.className = 'text-sm text-gray-600 dark:text-gray-300';
    totalSpan.textContent = total + ' ' + t('analyticsTotal', 'Total');
    totalStat.appendChild(totalSpan);
    statsDiv.appendChild(totalStat);

    grid.appendChild(statsDiv);

    if (subscribers.length === 0) {
      var emptyP = document.createElement('p');
      emptyP.className = 'text-gray-500 dark:text-gray-400 text-center py-8';
      emptyP.textContent = t('subscriberNoResults', 'No subscribers found.');
      grid.appendChild(emptyP);
      return;
    }

    // Table wrapper
    var tableWrap = document.createElement('div');
    tableWrap.className = 'overflow-x-auto';
    var table = document.createElement('table');
    table.className = 'w-full text-sm';

    // Header
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-700';
    var headerRow = document.createElement('tr');
    var headers = [];
    if (typeof BulkManager !== 'undefined') {
      headers.push({ html: BulkManager.selectAllHtml(), align: 'left', isHtml: true });
    }
    headers = headers.concat([
      { text: t('subscriberThEmail', 'Email'), align: 'left' },
      { text: t('subscriberThLanguage', 'Language'), align: 'left' },
      { text: t('subscriberThSource', 'Source'), align: 'left' },
      { text: t('subscriberThSubscribed', 'Subscribed'), align: 'left' },
      { text: t('subscriberThStatus', 'Status'), align: 'left' },
      { text: t('subscriberThActions', 'Actions'), align: 'right' }
    ]);
    headers.forEach(function(h) {
      var th = document.createElement('th');
      th.className = (h.isHtml ? 'w-10 ' : '') + 'text-' + h.align + ' p-3 font-medium text-gray-600 dark:text-gray-300';
      if (h.isHtml) th.innerHTML = h.html;
      else th.textContent = h.text;
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Body
    var tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-200 dark:divide-gray-700';

    subscribers.forEach(function(sub) {
      var tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition';

      var isActive = !sub.unsubscribed_at;

      // Checkbox cell
      if (typeof BulkManager !== 'undefined') {
        var tdCb = document.createElement('td');
        tdCb.className = 'p-3';
        tdCb.innerHTML = BulkManager.checkboxHtml(sub.id);
        tr.appendChild(tdCb);
      }

      // Email cell
      var tdEmail = document.createElement('td');
      tdEmail.className = 'p-3 font-medium text-gray-800 dark:text-gray-200';
      tdEmail.textContent = sub.email;
      tr.appendChild(tdEmail);

      // Language cell
      var tdLang = document.createElement('td');
      tdLang.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdLang.textContent = sub.language === 'es' ? 'ES' : 'EN';
      tr.appendChild(tdLang);

      // Source cell
      var tdSource = document.createElement('td');
      tdSource.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdSource.textContent = sub.source || 'website';
      tr.appendChild(tdSource);

      // Subscribed date cell
      var tdDate = document.createElement('td');
      tdDate.className = 'p-3 text-gray-600 dark:text-gray-300';
      tdDate.textContent = formatDate(sub.subscribed_at);
      tr.appendChild(tdDate);

      // Status cell
      var tdStatus = document.createElement('td');
      tdStatus.className = 'p-3';
      var badge = document.createElement('span');
      if (isActive) {
        badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        badge.textContent = t('subscriberActive', 'Active');
      } else {
        badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400';
        badge.textContent = t('subscriberUnsubscribed', 'Unsubscribed');
      }
      tdStatus.appendChild(badge);
      tr.appendChild(tdStatus);

      // Actions cell
      var tdAction = document.createElement('td');
      tdAction.className = 'p-3 text-right';
      if (isActive) {
        var unsubBtn = document.createElement('button');
        unsubBtn.className = 'text-red-600 dark:text-red-400 hover:text-red-800 text-xs font-medium';
        unsubBtn.textContent = t('subscriberUnsubscribe', 'Unsubscribe');
        unsubBtn.addEventListener('click', function() {
          unsubscribeSubscriber(sub.id, unsubBtn);
        });
        tdAction.appendChild(unsubBtn);
      } else {
        var dateSpan = document.createElement('span');
        dateSpan.className = 'text-xs text-gray-400';
        dateSpan.textContent = formatDate(sub.unsubscribed_at);
        tdAction.appendChild(dateSpan);
      }
      // Delete button via BulkManager
      if (typeof BulkManager !== 'undefined') {
        var delBtn = document.createElement('button');
        delBtn.className = 'text-red-600 dark:text-red-400 hover:text-red-800 text-xs font-medium ml-2';
        delBtn.textContent = t('actionDelete', 'Delete');
        delBtn.addEventListener('click', (function(id, email) { return function() { BulkManager.deleteSingle(id, email); }; })(sub.id, sub.email));
        tdAction.appendChild(delBtn);
      }
      tr.appendChild(tdAction);

      tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    tableWrap.appendChild(table);

    // Bulk toolbar
    if (typeof BulkManager !== 'undefined') {
      var toolbarDiv = document.createElement('div');
      toolbarDiv.innerHTML = BulkManager.toolbarHtml();
      tableWrap.appendChild(toolbarDiv);
    }

    grid.appendChild(tableWrap);

    if (typeof BulkManager !== 'undefined') BulkManager.bind();

    // Pagination
    if (pages > 1) {
      var pagDiv = document.createElement('div');
      pagDiv.className = 'flex items-center justify-between mt-4 pt-4 border-t dark:border-gray-700';

      var pagInfo = document.createElement('span');
      pagInfo.className = 'text-sm text-gray-500 dark:text-gray-400';
      pagInfo.textContent = t('subscriberPage', 'Page') + ' ' + page + ' ' + t('subscriberOf', 'of') + ' ' + pages;
      pagDiv.appendChild(pagInfo);

      var btnGroup = document.createElement('div');
      btnGroup.className = 'flex gap-2';

      if (page > 1) {
        var prevBtn = document.createElement('button');
        prevBtn.className = 'px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 transition';
        prevBtn.textContent = t('subscriberPrevious', 'Previous');
        prevBtn.addEventListener('click', function() { loadSubscribers(page - 1); });
        btnGroup.appendChild(prevBtn);
      }
      if (page < pages) {
        var nextBtn = document.createElement('button');
        nextBtn.className = 'px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 transition';
        nextBtn.textContent = t('subscriberNext', 'Next');
        nextBtn.addEventListener('click', function() { loadSubscribers(page + 1); });
        btnGroup.appendChild(nextBtn);
      }

      pagDiv.appendChild(btnGroup);
      grid.appendChild(pagDiv);
    }
  }

  // ─── Unsubscribe ──────────────────────────────────────────────
  window.unsubscribeSubscriber = async function(id, btn) {
    if (!confirm(t('subscriberConfirmUnsub', 'Are you sure you want to unsubscribe this subscriber?'))) return;

    if (btn) btn.disabled = true;
    try {
      var res = await fetch('/api/admin/subscribers.php', {
        method: 'DELETE',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getCsrf()
        },
        body: JSON.stringify({ id: id })
      });
      var json = await res.json();
      if (!json.success) throw new Error(json.message || 'Failed to unsubscribe');

      if (typeof showToast === 'function') showToast(t('subscriberRemoved', 'Subscriber removed'), false);
      loadSubscribers();
    } catch (err) {
      console.error('unsubscribeSubscriber error:', err);
      if (typeof showToast === 'function') showToast(t('subscriberFailedUnsub', 'Failed to unsubscribe'), true);
      if (btn) btn.disabled = false;
    }
  };

  // ─── Search ───────────────────────────────────────────────────
  window.searchSubscribers = function() {
    var input = document.getElementById('subscriber-search');
    currentSearch = input ? input.value.trim() : '';
    currentPage = 1;
    loadSubscribers(1, currentSearch);
  };

  // ─── Export CSV ───────────────────────────────────────────────
  window.exportSubscribersCsv = function() {
    var params = new URLSearchParams({ export: 'csv' });
    if (currentSearch) params.set('search', currentSearch);
    window.open('/api/admin/subscribers.php?' + params.toString(), '_blank');
  };

  // ─── Update Badge ─────────────────────────────────────────────
  function updateSubscriberBadge(count) {
    var badge = document.getElementById('subscribers-badge');
    if (badge) {
      badge.textContent = count;
      if (count > 0) badge.classList.remove('hidden');
      else badge.classList.add('hidden');
    }
  }

  // ─── Helpers ──────────────────────────────────────────────────
  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return d.toLocaleDateString((typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

})();
