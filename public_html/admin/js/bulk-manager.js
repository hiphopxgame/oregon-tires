/**
 * Oregon Tires — Reusable Bulk Selection & Delete Manager
 *
 * Provides checkbox selection, bulk toolbar, and bulk delete for any admin tab.
 *
 * Usage:
 *   BulkManager.init({ tab: 'customers', endpoint: 'customers.php', onDelete: loadCustomers });
 *   // In table header:  BulkManager.selectAllHtml()
 *   // In each row:      BulkManager.checkboxHtml(item.id)
 *   // After table render: BulkManager.bind()
 */
(function () {
  'use strict';

  function t(key, fallback) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fallback;
  }

  function checkSuperAdmin() {
    return typeof currentUser !== 'undefined' && currentUser
      && currentUser.type === 'admin'
      && (currentUser.role === 'superadmin' || currentUser.role === 'super_admin');
  }

  var cfg = null;
  var selectedIds = new Set();

  window.BulkManager = {

    /**
     * Initialize for a specific tab.
     * @param {Object} config
     * @param {string} config.tab - Tab identifier
     * @param {string} config.endpoint - API endpoint filename (e.g. 'customers.php')
     * @param {Function} config.onDelete - Callback after successful delete
     * @param {boolean} [config.superAdminOnly=true] - Require superadmin for delete
     * @param {string} [config.deleteWarning] - Custom warning message translation key
     * @param {Function} [config.extraToolbarHtml] - Returns extra toolbar buttons HTML
     */
    init: function (config) {
      cfg = config;
      selectedIds = new Set();
    },

    /** HTML for the select-all checkbox in <thead> */
    selectAllHtml: function () {
      if (!cfg) return '';
      return '<input type="checkbox" class="rounded cursor-pointer bulk-select-all-' + cfg.tab + '" onchange="BulkManager.toggleAll(this.checked)" title="' + t('selectAll', 'Select All') + '">';
    },

    /** HTML for a row checkbox */
    checkboxHtml: function (id) {
      if (!cfg) return '';
      return '<input type="checkbox" class="rounded cursor-pointer bulk-cb-' + cfg.tab + '" value="' + id + '" onchange="BulkManager.toggle(' + id + ', this.checked)">';
    },

    /** HTML for the bulk toolbar (inject after table) */
    toolbarHtml: function () {
      if (!cfg) return '';
      var canDelete = cfg.superAdminOnly === false || checkSuperAdmin();
      var deleteBtn = canDelete
        ? '<button onclick="BulkManager.bulkDelete()" class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1.5 rounded font-medium transition-colors">' +
          '<svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
          t('deleteSelected', 'Delete Selected') + '</button>'
        : '';
      var extra = (cfg.extraToolbarHtml && typeof cfg.extraToolbarHtml === 'function') ? cfg.extraToolbarHtml() : '';

      return '<div id="bulk-toolbar-' + cfg.tab + '" class="hidden sticky bottom-0 bg-gray-800 border-t border-gray-600 px-4 py-3 flex items-center gap-4 rounded-b-lg shadow-lg z-10">' +
        '<span class="text-sm text-gray-300"><strong class="text-white bulk-count-' + cfg.tab + '">0</strong> ' + t('itemsSelected', 'selected') + '</span>' +
        extra + deleteBtn +
        '<button onclick="BulkManager.reset()" class="text-sm text-gray-400 hover:text-white ml-auto transition-colors">' + t('cancel', 'Cancel') + '</button>' +
        '</div>';
    },

    /** Call after rendering the table to attach event listeners */
    bind: function () {
      // No-op — events are inline via onchange. Kept for API consistency.
    },

    toggle: function (id, checked) {
      if (checked) {
        selectedIds.add(String(id));
      } else {
        selectedIds.delete(String(id));
      }
      this.updateToolbar();
    },

    toggleAll: function (checked) {
      if (!cfg) return;
      var boxes = document.querySelectorAll('.bulk-cb-' + cfg.tab);
      boxes.forEach(function (cb) {
        cb.checked = checked;
        if (checked) {
          selectedIds.add(cb.value);
        } else {
          selectedIds.delete(cb.value);
        }
      });
      this.updateToolbar();
    },

    updateToolbar: function () {
      if (!cfg) return;
      var toolbar = document.getElementById('bulk-toolbar-' + cfg.tab);
      if (!toolbar) return;
      var count = selectedIds.size;
      if (count > 0) {
        toolbar.classList.remove('hidden');
        var countEl = toolbar.querySelector('.bulk-count-' + cfg.tab);
        if (countEl) countEl.textContent = count;
      } else {
        toolbar.classList.add('hidden');
      }
    },

    getSelectedIds: function () {
      return Array.from(selectedIds);
    },

    bulkDelete: async function () {
      var ids = this.getSelectedIds();
      if (!ids.length) {
        if (typeof showToast === 'function') showToast(t('noItemsSelected', 'No items selected'), 'warning');
        return;
      }

      var warningKey = cfg.deleteWarning || 'bulkDeleteConfirm';
      var msg = t(warningKey, 'Are you sure you want to delete {count} items? This cannot be undone.')
        .replace('{count}', ids.length);
      if (!confirm(msg)) return;

      try {
        var res = await api(cfg.endpoint, { method: 'POST', body: { action: 'bulk_delete', ids: ids.map(Number) } });
        if (res.success) {
          var deleted = res.data && res.data.deleted ? res.data.deleted : ids.length;
          if (typeof showToast === 'function') {
            showToast(deleted + ' ' + t('itemsDeleted', 'items deleted'), 'success');
          }
          this.reset();
          if (cfg.onDelete) cfg.onDelete();
        } else {
          if (typeof showToast === 'function') showToast(res.error || t('bulkDeleteFail', 'Failed to delete'), 'error');
        }
      } catch (e) {
        if (typeof showToast === 'function') showToast(t('bulkDeleteFail', 'Failed to delete'), 'error');
      }
    },

    /** Single item delete with confirmation */
    deleteSingle: async function (id, name) {
      var warningKey = cfg.deleteWarning || 'deleteConfirmSingle';
      var msg = t(warningKey, 'Are you sure you want to delete this item? This cannot be undone.');
      if (name) msg = msg + '\n\n' + name;
      if (!confirm(msg)) return;

      try {
        var res = await api(cfg.endpoint, { method: 'POST', body: { action: 'delete', id: Number(id) } });
        if (res.success) {
          if (typeof showToast === 'function') showToast(t('deleted', 'Deleted'), 'success');
          if (cfg.onDelete) cfg.onDelete();
        } else {
          if (typeof showToast === 'function') showToast(res.error || t('deleteFailed', 'Delete failed'), 'error');
        }
      } catch (e) {
        if (typeof showToast === 'function') showToast(t('deleteFailed', 'Delete failed'), 'error');
      }
    },

    reset: function () {
      if (!cfg) return;
      selectedIds = new Set();
      var boxes = document.querySelectorAll('.bulk-cb-' + cfg.tab + ', .bulk-select-all-' + cfg.tab);
      boxes.forEach(function (cb) { cb.checked = false; });
      this.updateToolbar();
    },

    destroy: function () {
      cfg = null;
      selectedIds = new Set();
    }
  };
})();
