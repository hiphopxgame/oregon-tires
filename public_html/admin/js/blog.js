/**
 * Oregon Tires — Admin Blog Management
 * Handles CRUD for blog posts in the admin panel.
 */

(function () {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  var blogPosts = [];
  var blogCategories = [];
  var blogCurrentPage = 1;
  var blogTotalPages = 1;
  var editingPostId = null;

  // ── Helpers ──────────────────────────────────────────────────────
  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function blogFetch(url, options) {
    options = options || {};
    options.credentials = 'include';
    if (!options.headers) options.headers = {};
    options.headers['X-CSRF-Token'] = getCsrfToken();
    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }
    return fetch(url, options).then(function (r) { return r.json(); });
  }

  function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString((typeof currentLang !== 'undefined' && currentLang === 'es') ? 'es-MX' : 'en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
  }

  // ── BulkManager Init ────────────────────────────────────────────
  if (typeof BulkManager !== 'undefined') {
    BulkManager.init({ tab: 'blog', endpoint: 'blog.php', onDelete: function() { loadBlogPosts(blogCurrentPage); }, superAdminOnly: false, deleteWarning: 'blogBulkDeleteWarn' });
  }

  // ── Load Posts ───────────────────────────────────────────────────
  window.loadBlogPosts = function (page) {
    page = page || 1;
    blogCurrentPage = page;

    var statusFilter = '';
    var filterEl = document.getElementById('blog-status-filter');
    if (filterEl) statusFilter = filterEl.value;

    var searchVal = '';
    var searchEl = document.getElementById('blog-search');
    if (searchEl) searchVal = searchEl.value.trim();

    var url = '/api/admin/blog.php?page=' + page + '&limit=15';
    if (statusFilter) url += '&status=' + encodeURIComponent(statusFilter);
    if (searchVal) url += '&search=' + encodeURIComponent(searchVal);

    blogFetch(url)
      .then(function (res) {
        if (!res.success) {
          if (typeof showToast === 'function') showToast(t('blogLoadFail', 'Failed to load blog posts'), true);
          return;
        }

        var data = res.data;
        blogPosts = data.posts || [];
        blogCategories = data.categories || [];
        blogTotalPages = data.pages || 1;

        renderBlogTable();
        renderBlogPagination();
      })
      .catch(function (err) {
        console.error('Blog load error:', err);
        if (typeof showToast === 'function') showToast(t('blogLoadError', 'Error loading blog posts'), true);
      });
  };

  // ── Render Table ─────────────────────────────────────────────────
  function renderBlogTable() {
    var tbody = document.getElementById('blog-table-body');
    if (!tbody) return;

    if (typeof BulkManager !== 'undefined') BulkManager.reset();

    // Populate select-all checkbox in thead
    var selectAllTh = document.getElementById('blog-select-all-th');
    if (selectAllTh && typeof BulkManager !== 'undefined') selectAllTh.innerHTML = BulkManager.selectAllHtml();

    // Populate toolbar
    var toolbarDiv = document.getElementById('blog-bulk-toolbar');
    if (toolbarDiv && typeof BulkManager !== 'undefined') toolbarDiv.innerHTML = BulkManager.toolbarHtml();

    tbody.textContent = '';

    if (blogPosts.length === 0) {
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      td.setAttribute('colspan', '6');
      td.className = 'text-center py-8 text-gray-500 dark:text-gray-400';
      td.textContent = t('blogNoPostsFound', 'No blog posts found.');
      tr.appendChild(td);
      tbody.appendChild(tr);
      return;
    }

    blogPosts.forEach(function (post) {
      var tr = document.createElement('tr');
      tr.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';

      // Checkbox cell
      if (typeof BulkManager !== 'undefined') {
        var tdCb = document.createElement('td');
        tdCb.className = 'py-3 px-4';
        tdCb.innerHTML = BulkManager.checkboxHtml(post.id);
        tr.appendChild(tdCb);
      }

      // Title cell
      var tdTitle = document.createElement('td');
      tdTitle.className = 'py-3 px-4';
      var titleLink = document.createElement('div');
      titleLink.className = 'font-medium text-gray-800 dark:text-gray-200';
      titleLink.textContent = truncate(post.title_en, 60);
      tdTitle.appendChild(titleLink);
      if (post.title_es) {
        var subTitle = document.createElement('div');
        subTitle.className = 'text-xs text-gray-400 dark:text-gray-500 mt-0.5';
        subTitle.textContent = truncate(post.title_es, 50);
        tdTitle.appendChild(subTitle);
      }
      tr.appendChild(tdTitle);

      // Status cell
      var tdStatus = document.createElement('td');
      tdStatus.className = 'py-3 px-4';
      var badge = document.createElement('span');
      badge.className = post.status === 'published'
        ? 'px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
      badge.textContent = post.status === 'published' ? t('blogPublished', 'Published') : t('blogDraft', 'Draft');
      tdStatus.appendChild(badge);
      tr.appendChild(tdStatus);

      // Date cell
      var tdDate = document.createElement('td');
      tdDate.className = 'py-3 px-4 text-sm text-gray-500 dark:text-gray-400';
      tdDate.textContent = post.status === 'published' ? formatDate(post.published_at) : formatDate(post.created_at);
      tr.appendChild(tdDate);

      // Author cell
      var tdAuthor = document.createElement('td');
      tdAuthor.className = 'py-3 px-4 text-sm text-gray-500 dark:text-gray-400';
      tdAuthor.textContent = post.author || '—';
      tr.appendChild(tdAuthor);

      // Actions cell
      var tdActions = document.createElement('td');
      tdActions.className = 'py-3 px-4';
      var actionsWrap = document.createElement('div');
      actionsWrap.className = 'flex gap-2';

      var viewBtn = document.createElement('a');
      viewBtn.href = '/blog/' + encodeURIComponent(post.slug);
      viewBtn.target = '_blank';
      viewBtn.className = 'text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600';
      viewBtn.textContent = t('actionView', 'View');
      actionsWrap.appendChild(viewBtn);

      var editBtn = document.createElement('button');
      editBtn.className = 'text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800';
      editBtn.textContent = t('actionEdit', 'Edit');
      editBtn.addEventListener('click', function () { openBlogEditor(post.id); });
      actionsWrap.appendChild(editBtn);

      var delBtn = document.createElement('button');
      delBtn.className = 'text-xs px-2 py-1 rounded bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800';
      delBtn.textContent = t('actionDelete', 'Delete');
      delBtn.addEventListener('click', function () {
        if (typeof BulkManager !== 'undefined') BulkManager.deleteSingle(post.id, post.title_en || 'this post');
        else deleteBlogPost(post.id, post.title_en);
      });
      actionsWrap.appendChild(delBtn);

      tdActions.appendChild(actionsWrap);
      tr.appendChild(tdActions);

      tbody.appendChild(tr);
    });

    if (typeof BulkManager !== 'undefined') BulkManager.bind();
  }

  // ── Render Pagination ────────────────────────────────────────────
  function renderBlogPagination() {
    var container = document.getElementById('blog-pagination-admin');
    if (!container) return;

    container.textContent = '';

    if (blogTotalPages <= 1) return;

    var prevBtn = document.createElement('button');
    prevBtn.className = 'px-3 py-1 rounded text-sm border hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50';
    prevBtn.textContent = t('blogPrev', '\u2190 Prev');
    prevBtn.disabled = blogCurrentPage <= 1;
    prevBtn.addEventListener('click', function () { loadBlogPosts(blogCurrentPage - 1); });
    container.appendChild(prevBtn);

    var info = document.createElement('span');
    info.className = 'text-sm text-gray-500 dark:text-gray-400 px-3';
    info.textContent = t('blogPage', 'Page') + ' ' + blogCurrentPage + ' ' + t('blogOf', 'of') + ' ' + blogTotalPages;
    container.appendChild(info);

    var nextBtn = document.createElement('button');
    nextBtn.className = 'px-3 py-1 rounded text-sm border hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50';
    nextBtn.textContent = t('blogNext', 'Next \u2192');
    nextBtn.disabled = blogCurrentPage >= blogTotalPages;
    nextBtn.addEventListener('click', function () { loadBlogPosts(blogCurrentPage + 1); });
    container.appendChild(nextBtn);
  }

  // ── Open Editor (New or Edit) ────────────────────────────────────
  window.openBlogEditor = function (postId) {
    editingPostId = postId || null;
    var modal = document.getElementById('blog-editor-modal');
    if (!modal) return;

    // Reset form
    document.getElementById('blog-form-title').textContent = editingPostId ? t('blogEditPost', 'Edit Post') : t('blogNewPost', 'New Blog Post');
    document.getElementById('blog-title-en').value = '';
    document.getElementById('blog-title-es').value = '';
    document.getElementById('blog-slug').value = '';
    document.getElementById('blog-excerpt-en').value = '';
    document.getElementById('blog-excerpt-es').value = '';
    document.getElementById('blog-body-en').value = '';
    document.getElementById('blog-body-es').value = '';
    document.getElementById('blog-image').value = '';
    document.getElementById('blog-author').value = 'Oregon Tires';
    document.getElementById('blog-status').value = 'draft';

    // Reset category checkboxes
    renderCategoryCheckboxes([]);

    if (editingPostId) {
      // Load post data
      blogFetch('/api/admin/blog.php?id=' + editingPostId)
        .then(function (res) {
          if (!res.success) return;
          var p = res.data;
          document.getElementById('blog-title-en').value = p.title_en || '';
          document.getElementById('blog-title-es').value = p.title_es || '';
          document.getElementById('blog-slug').value = p.slug || '';
          document.getElementById('blog-excerpt-en').value = p.excerpt_en || '';
          document.getElementById('blog-excerpt-es').value = p.excerpt_es || '';
          document.getElementById('blog-body-en').value = p.body_en || '';
          document.getElementById('blog-body-es').value = p.body_es || '';
          document.getElementById('blog-image').value = p.featured_image || '';
          document.getElementById('blog-author').value = p.author || 'Oregon Tires';
          document.getElementById('blog-status').value = p.status || 'draft';

          var selectedIds = (p.categories || []).map(function (c) { return c.id; });
          renderCategoryCheckboxes(selectedIds);
        });
    } else {
      renderCategoryCheckboxes([]);
    }

    modal.classList.remove('hidden');
  };

  window.closeBlogEditor = function () {
    var modal = document.getElementById('blog-editor-modal');
    if (modal) modal.classList.add('hidden');
    editingPostId = null;
  };

  // ── Render Category Checkboxes ───────────────────────────────────
  function renderCategoryCheckboxes(selectedIds) {
    var container = document.getElementById('blog-categories-list');
    if (!container) return;
    container.textContent = '';

    blogCategories.forEach(function (cat) {
      var label = document.createElement('label');
      label.className = 'flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300';

      var checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.value = cat.id;
      checkbox.className = 'blog-cat-checkbox rounded';
      if (selectedIds.indexOf(parseInt(cat.id)) !== -1 || selectedIds.indexOf(String(cat.id)) !== -1) {
        checkbox.checked = true;
      }

      var text = document.createTextNode(cat.name_en);
      label.appendChild(checkbox);
      label.appendChild(text);
      container.appendChild(label);
    });
  }

  // ── Auto-Generate Slug ───────────────────────────────────────────
  window.blogAutoSlug = function () {
    var titleEn = document.getElementById('blog-title-en').value;
    var slug = titleEn.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/[\s-]+/g, '-')
      .replace(/^-|-$/g, '');
    document.getElementById('blog-slug').value = slug || '';
  };

  // ── Save Post ────────────────────────────────────────────────────
  window.saveBlogPost = function () {
    var titleEn = document.getElementById('blog-title-en').value.trim();
    var bodyEn = document.getElementById('blog-body-en').value.trim();

    if (!titleEn) {
      if (typeof showToast === 'function') showToast(t('blogTitleRequired', 'Title (EN) is required'), true);
      return;
    }
    if (!bodyEn) {
      if (typeof showToast === 'function') showToast(t('blogBodyRequired', 'Body (EN) is required'), true);
      return;
    }

    // Collect selected categories
    var catCheckboxes = document.querySelectorAll('.blog-cat-checkbox:checked');
    var categoryIds = [];
    catCheckboxes.forEach(function (cb) { categoryIds.push(parseInt(cb.value)); });

    var payload = {
      title_en: titleEn,
      title_es: document.getElementById('blog-title-es').value.trim(),
      slug: document.getElementById('blog-slug').value.trim(),
      excerpt_en: document.getElementById('blog-excerpt-en').value.trim(),
      excerpt_es: document.getElementById('blog-excerpt-es').value.trim(),
      body_en: bodyEn,
      body_es: document.getElementById('blog-body-es').value.trim(),
      featured_image: document.getElementById('blog-image').value.trim(),
      author: document.getElementById('blog-author').value.trim() || 'Oregon Tires',
      status: document.getElementById('blog-status').value,
      category_ids: categoryIds
    };

    var method = editingPostId ? 'PUT' : 'POST';
    if (editingPostId) payload.id = editingPostId;

    blogFetch('/api/admin/blog.php', { method: method, body: payload })
      .then(function (res) {
        if (res.success) {
          if (typeof showToast === 'function') showToast(editingPostId ? t('blogPostUpdated', 'Post updated!') : t('blogPostCreated', 'Post created!'));
          closeBlogEditor();
          loadBlogPosts(blogCurrentPage);
        } else {
          if (typeof showToast === 'function') showToast(res.error || t('blogSaveFail', 'Failed to save'), true);
        }
      })
      .catch(function (err) {
        console.error('Blog save error:', err);
        if (typeof showToast === 'function') showToast(t('blogSaveError', 'Error saving post'), true);
      });
  };

  // ── Delete Post ──────────────────────────────────────────────────
  window.deleteBlogPost = function (id, title) {
    if (!confirm(t('blogDeleteConfirm', 'Delete "{title}"? This cannot be undone.').replace('{title}', title || 'this post'))) return;

    blogFetch('/api/admin/blog.php?id=' + id, { method: 'DELETE' })
      .then(function (res) {
        if (res.success) {
          if (typeof showToast === 'function') showToast(t('blogPostDeleted', 'Post deleted'));
          loadBlogPosts(blogCurrentPage);
        } else {
          if (typeof showToast === 'function') showToast(res.error || t('blogDeleteFail', 'Failed to delete'), true);
        }
      })
      .catch(function (err) {
        console.error('Blog delete error:', err);
        if (typeof showToast === 'function') showToast(t('blogDeleteError', 'Error deleting post'), true);
      });
  };

  // ── Search Debounce ──────────────────────────────────────────────
  var blogSearchTimeout = null;
  window.blogSearchDebounce = function () {
    clearTimeout(blogSearchTimeout);
    blogSearchTimeout = setTimeout(function () { loadBlogPosts(1); }, 400);
  };

  // ── Initialize on Tab Switch ─────────────────────────────────────
  // Hook into the global switchTab to load blog data when tab activates
  var origSwitchTab = window.switchTab;
  if (typeof origSwitchTab === 'function') {
    window.switchTab = function (tabName) {
      origSwitchTab(tabName);
      if (tabName === 'blog') {
        loadBlogPosts(1);
      }
    };
  }
})();
