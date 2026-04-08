<?php declare(strict_types=1);
/**
 * Form Kit — Admin Inbox Template
 *
 * Full-page admin inbox for managing form submissions.
 * Uses Tailwind CSS via CDN for admin pages. Self-contained vanilla JS.
 *
 * Usage:
 *   $inboxConfig = ['site_key' => 'oregon.tires', 'api_base' => '/api/form'];
 *   require FORM_KIT_TEMPLATES . '/form/admin-inbox.php';
 */

$inboxConfig = array_merge([
    'site_key'   => '',
    'api_base'   => '/api/form',
    'page_title' => 'Contact Inbox',
], $inboxConfig ?? []);

$siteKey   = htmlspecialchars($inboxConfig['site_key'], ENT_QUOTES, 'UTF-8');
$apiBase   = htmlspecialchars($inboxConfig['api_base'], ENT_QUOTES, 'UTF-8');
$pageTitle = htmlspecialchars($inboxConfig['page_title'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    gray: {
                        950: '#030712'
                    }
                }
            }
        }
    };
    </script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .fki-fade-in { animation: fkiFadeIn 0.2s ease-out; }
        @keyframes fkiFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fki-row-unread { background-color: rgba(16, 185, 129, 0.04); }
        .fki-row:hover { background-color: rgba(255, 255, 255, 0.03); }
        .fki-cal-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 9999px; font-size: 10px; font-weight: 500; cursor: pointer; transition: opacity 0.15s; vertical-align: middle; margin-left: 4px; }
        .fki-cal-badge:hover { opacity: 0.8; }
        .fki-cal-ok { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
        .fki-cal-fail { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
        .fki-status-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
        }
        .fki-spinner {
            display: inline-block; width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,0.15);
            border-top-color: #10b981;
            border-radius: 50%;
            animation: fkiSpin 0.6s linear infinite;
        }
        @keyframes fkiSpin { to { transform: rotate(360deg); } }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

<div id="fki-app" class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white"><?php echo $pageTitle; ?></h1>
            <p class="text-sm text-gray-400 mt-1" id="fki-subtitle">Loading submissions...</p>
        </div>
        <button id="fki-mark-all-read"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 rounded-lg hover:bg-emerald-400/20 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Mark All Read
        </button>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total</div>
            <div class="text-2xl font-bold text-white" id="fki-stat-total">--</div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Unread</div>
            <div class="text-2xl font-bold text-emerald-400" id="fki-stat-unread">--</div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">This Week</div>
            <div class="text-2xl font-bold text-white" id="fki-stat-week">--</div>
        </div>
    </div>

    <!-- Filters + Search -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
        <!-- Filter Tabs -->
        <div class="flex gap-1 bg-gray-900 border border-gray-800 rounded-lg p-1 overflow-x-auto" id="fki-filters">
            <button class="fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap" data-filter="all">All</button>
            <button class="fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap" data-filter="new">New</button>
            <button class="fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap" data-filter="read">Read</button>
            <button class="fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap" data-filter="archived">Archived</button>
            <button class="fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap" data-filter="spam">Spam</button>
        </div>

        <!-- Search -->
        <div class="relative flex-1 sm:max-w-xs ml-auto">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" id="fki-search" placeholder="Search name, email, subject..."
                   class="w-full pl-10 pr-4 py-2 text-sm bg-gray-900 border border-gray-800 rounded-lg text-gray-200 placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 transition-colors">
        </div>
    </div>

    <!-- Loading State -->
    <div id="fki-loading" class="flex items-center justify-center py-20">
        <div class="fki-spinner"></div>
        <span class="ml-3 text-gray-400 text-sm">Loading submissions...</span>
    </div>

    <!-- Empty State -->
    <div id="fki-empty" class="hidden text-center py-20">
        <svg class="w-16 h-16 mx-auto text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/></svg>
        <h3 class="text-lg font-semibold text-gray-400 mb-1">No submissions found</h3>
        <p class="text-sm text-gray-500" id="fki-empty-msg">Check back later for new messages.</p>
    </div>

    <!-- Error State -->
    <div id="fki-error-state" class="hidden">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6 text-center">
            <p class="text-red-400 font-medium mb-2" id="fki-error-title">Failed to load submissions</p>
            <p class="text-sm text-red-400/70 mb-4" id="fki-error-detail"></p>
            <button id="fki-retry" class="px-4 py-2 text-sm font-medium text-white bg-red-500/20 rounded-lg hover:bg-red-500/30 transition-colors">Retry</button>
        </div>
    </div>

    <!-- Submissions Table -->
    <div id="fki-table-wrapper" class="hidden">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <!-- Table Header -->
            <div class="hidden sm:grid grid-cols-12 gap-2 px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-800">
                <div class="col-span-1"></div>
                <div class="col-span-2">Name</div>
                <div class="col-span-3">Email</div>
                <div class="col-span-4">Subject</div>
                <div class="col-span-2 text-right">Date</div>
            </div>

            <!-- Table Body -->
            <div id="fki-table-body"></div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4 text-sm">
            <span class="text-gray-500" id="fki-pagination-info">Showing 0-0 of 0</span>
            <div class="flex gap-2">
                <button id="fki-prev"
                        class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-900 border border-gray-800 rounded-lg hover:border-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        disabled>Previous</button>
                <button id="fki-next"
                        class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-900 border border-gray-800 rounded-lg hover:border-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        disabled>Next</button>
            </div>
        </div>
    </div>

    <!-- Detail Panel (expanded below row, rendered dynamically) -->
    <div id="fki-detail-panel" class="hidden mt-4 fki-fade-in">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <!-- Detail Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-white" id="fki-detail-name"></h3>
                    <p class="text-sm text-gray-400" id="fki-detail-email"></p>
                </div>
                <button id="fki-detail-close" class="p-2 text-gray-500 hover:text-gray-300 transition-colors rounded-lg hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Detail Body -->
            <div class="px-6 py-5">
                <!-- Meta Row -->
                <div class="flex flex-wrap gap-4 text-sm text-gray-400 mb-4" id="fki-detail-meta"></div>

                <!-- Subject -->
                <div id="fki-detail-subject-row" class="mb-4 hidden">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Subject</div>
                    <p class="text-gray-200" id="fki-detail-subject"></p>
                </div>

                <!-- Message -->
                <div class="mb-6">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Message</div>
                    <div class="bg-gray-800/50 border border-gray-700/50 rounded-lg p-4 text-gray-200 text-sm leading-relaxed whitespace-pre-wrap" id="fki-detail-message"></div>
                </div>

                <!-- Extra fields -->
                <div id="fki-detail-extra" class="hidden mb-6">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Additional Info</div>
                    <div class="bg-gray-800/50 border border-gray-700/50 rounded-lg p-4" id="fki-detail-extra-body"></div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-800">
                    <button id="fki-action-read"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                    </button>
                    <button id="fki-action-archive"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-yellow-400 bg-yellow-400/10 border border-yellow-400/20 rounded-lg hover:bg-yellow-400/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        Archive
                    </button>
                    <button id="fki-action-spam"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-orange-400 bg-orange-400/10 border border-orange-400/20 rounded-lg hover:bg-orange-400/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Spam
                    </button>
                    <button id="fki-action-delete"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-400 bg-red-400/10 border border-red-400/20 rounded-lg hover:bg-red-400/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Delete
                    </button>
                    <a id="fki-action-reply" href="#"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 rounded-lg hover:bg-emerald-400/20 transition-colors ml-auto">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        Reply
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    'use strict';

    // ── Configuration ────────────────────────────────────────────────────────
    var CONFIG = {
        siteKey: '<?php echo $siteKey; ?>',
        apiBase: '<?php echo $apiBase; ?>'.replace(/\/+$/, ''),
        perPage: 20
    };

    // ── State ────────────────────────────────────────────────────────────────
    var state = {
        submissions: [],
        stats: { total: 0, unread: 0, week: 0 },
        filter: 'all',
        search: '',
        offset: 0,
        totalCount: 0,
        selectedId: null,
        selectedData: null,
        loading: false
    };

    // ── DOM References ───────────────────────────────────────────────────────
    var els = {
        subtitle:     document.getElementById('fki-subtitle'),
        statTotal:    document.getElementById('fki-stat-total'),
        statUnread:   document.getElementById('fki-stat-unread'),
        statWeek:     document.getElementById('fki-stat-week'),
        search:       document.getElementById('fki-search'),
        loading:      document.getElementById('fki-loading'),
        empty:        document.getElementById('fki-empty'),
        emptyMsg:     document.getElementById('fki-empty-msg'),
        errorState:   document.getElementById('fki-error-state'),
        errorDetail:  document.getElementById('fki-error-detail'),
        tableWrapper: document.getElementById('fki-table-wrapper'),
        tableBody:    document.getElementById('fki-table-body'),
        prevBtn:      document.getElementById('fki-prev'),
        nextBtn:      document.getElementById('fki-next'),
        pagInfo:      document.getElementById('fki-pagination-info'),
        detail:       document.getElementById('fki-detail-panel'),
        markAllRead:  document.getElementById('fki-mark-all-read'),
        retry:        document.getElementById('fki-retry')
    };

    // ── API Helpers ──────────────────────────────────────────────────────────
    function apiUrl(path, params) {
        var url = CONFIG.apiBase + '/' + path;
        if (params) {
            var qs = Object.keys(params).map(function(k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
            }).join('&');
            if (qs) url += '?' + qs;
        }
        return url;
    }

    function apiFetch(path, params, options) {
        var url = apiUrl(path, params);
        var opts = Object.assign({
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        }, options || {});
        return fetch(url, opts)
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, status: res.status, data: data };
                });
            });
    }

    function apiPost(path, body) {
        return apiFetch(path, null, {
            method: 'POST',
            body: JSON.stringify(body)
        });
    }

    // ── Rendering Helpers ────────────────────────────────────────────────────
    function showView(view) {
        els.loading.classList.add('hidden');
        els.empty.classList.add('hidden');
        els.errorState.classList.add('hidden');
        els.tableWrapper.classList.add('hidden');

        if (view === 'loading')  els.loading.classList.remove('hidden');
        if (view === 'empty')    els.empty.classList.remove('hidden');
        if (view === 'error')    els.errorState.classList.remove('hidden');
        if (view === 'table')    els.tableWrapper.classList.remove('hidden');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var now = new Date();
        var diffMs = now.getTime() - d.getTime();
        var diffMin = Math.floor(diffMs / 60000);
        var diffHr  = Math.floor(diffMs / 3600000);
        var diffDay = Math.floor(diffMs / 86400000);

        if (diffMin < 1) return 'Just now';
        if (diffMin < 60) return diffMin + 'm ago';
        if (diffHr < 24) return diffHr + 'h ago';
        if (diffDay < 7) return diffDay + 'd ago';

        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[d.getMonth()] + ' ' + d.getDate();
    }

    function truncate(str, len) {
        if (!str) return '';
        return str.length > len ? str.substring(0, len) + '...' : str;
    }

    function statusDotColor(status) {
        switch (status) {
            case 'new':      return 'bg-emerald-400';
            case 'read':     return 'bg-gray-500';
            case 'archived': return 'bg-yellow-400';
            case 'spam':     return 'bg-orange-400';
            default:         return 'bg-gray-600';
        }
    }

    // ── Render Stats ─────────────────────────────────────────────────────────
    function renderStats() {
        els.statTotal.textContent  = String(state.stats.total);
        els.statUnread.textContent = String(state.stats.unread);
        els.statWeek.textContent   = String(state.stats.week);
        els.subtitle.textContent   = state.stats.total + ' submission' + (state.stats.total !== 1 ? 's' : '') + ' total';
    }

    // ── Render Filter Tabs ───────────────────────────────────────────────────
    function renderFilters() {
        var tabs = document.querySelectorAll('.fki-filter-tab');
        for (var i = 0; i < tabs.length; i++) {
            var tab = tabs[i];
            var isActive = tab.getAttribute('data-filter') === state.filter;
            if (isActive) {
                tab.className = 'fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap bg-emerald-500/20 text-emerald-400';
            } else {
                tab.className = 'fki-filter-tab px-3 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap text-gray-400 hover:text-gray-200 hover:bg-gray-800';
            }
        }
    }

    // ── Render Table ─────────────────────────────────────────────────────────
    function renderTable() {
        // Clear existing rows
        while (els.tableBody.firstChild) {
            els.tableBody.removeChild(els.tableBody.firstChild);
        }

        if (state.submissions.length === 0) {
            var msg = state.search
                ? 'No results for "' + state.search + '".'
                : (state.filter !== 'all' ? 'No ' + state.filter + ' submissions.' : 'Check back later for new messages.');
            els.emptyMsg.textContent = msg;
            showView('empty');
            return;
        }

        showView('table');

        state.submissions.forEach(function(sub) {
            var row = document.createElement('div');
            row.className = 'fki-row grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2 px-4 py-3 border-b border-gray-800/60 cursor-pointer transition-colors items-center';
            if (sub.status === 'new') {
                row.classList.add('fki-row-unread');
            }
            row.setAttribute('data-id', sub.id);

            // Status dot
            var dotCell = document.createElement('div');
            dotCell.className = 'hidden sm:flex col-span-1 justify-center';
            var dot = document.createElement('span');
            dot.className = 'fki-status-dot ' + statusDotColor(sub.status);
            dot.title = sub.status;
            dotCell.appendChild(dot);
            row.appendChild(dotCell);

            // Name
            var nameCell = document.createElement('div');
            nameCell.className = 'sm:col-span-2 font-medium text-sm text-gray-200 truncate';
            // Mobile: show dot inline
            var mobileDot = document.createElement('span');
            mobileDot.className = 'fki-status-dot inline-block sm:hidden mr-2 align-middle ' + statusDotColor(sub.status);
            nameCell.appendChild(mobileDot);
            var nameText = document.createElement('span');
            nameText.textContent = sub.name || '(no name)';
            nameCell.appendChild(nameText);
            row.appendChild(nameCell);

            // Email
            var emailCell = document.createElement('div');
            emailCell.className = 'sm:col-span-3 text-sm text-gray-400 truncate';
            emailCell.textContent = sub.email || '';
            row.appendChild(emailCell);

            // Subject
            var subjectCell = document.createElement('div');
            subjectCell.className = 'sm:col-span-4 text-sm text-gray-500 truncate';
            subjectCell.textContent = truncate(sub.subject || sub.message || '', 60);
            row.appendChild(subjectCell);

            // Date + Calendar badge
            var dateCell = document.createElement('div');
            dateCell.className = 'sm:col-span-2 text-xs text-gray-500 sm:text-right';
            dateCell.textContent = formatDate(sub.created_at);

            // Calendar sync badge from action_results
            var calResult = null;
            if (sub.action_results) {
                var ar = typeof sub.action_results === 'string' ? JSON.parse(sub.action_results) : sub.action_results;
                if (ar && ar.google_calendar) calResult = ar.google_calendar;
            }
            if (calResult) {
                var calBadge = document.createElement('span');
                if (calResult.success) {
                    calBadge.className = 'fki-cal-badge fki-cal-ok';
                    calBadge.textContent = '\u2713 Cal';
                    calBadge.title = 'Synced to Google Calendar';
                    if (calResult.calendar_link) {
                        calBadge.style.cursor = 'pointer';
                        calBadge.addEventListener('click', function(e) {
                            e.stopPropagation();
                            window.open(calResult.calendar_link, '_blank');
                        });
                    }
                } else {
                    calBadge.className = 'fki-cal-badge fki-cal-fail';
                    calBadge.textContent = '\u2717 Cal';
                    calBadge.title = 'Calendar sync failed';
                }
                dateCell.appendChild(document.createTextNode(' '));
                dateCell.appendChild(calBadge);
            }

            row.appendChild(dateCell);

            // Click handler
            row.addEventListener('click', function() {
                selectSubmission(sub);
            });

            els.tableBody.appendChild(row);
        });

        renderPagination();
    }

    // ── Render Pagination ────────────────────────────────────────────────────
    function renderPagination() {
        var start = state.offset + 1;
        var end = Math.min(state.offset + state.submissions.length, state.totalCount);
        els.pagInfo.textContent = 'Showing ' + start + '-' + end + ' of ' + state.totalCount;
        els.prevBtn.disabled = state.offset === 0;
        els.nextBtn.disabled = (state.offset + CONFIG.perPage) >= state.totalCount;
    }

    // ── Detail Panel ─────────────────────────────────────────────────────────
    function selectSubmission(sub) {
        state.selectedId = sub.id;
        state.selectedData = sub;

        document.getElementById('fki-detail-name').textContent = sub.name || '(no name)';
        document.getElementById('fki-detail-email').textContent = sub.email || '';

        // Meta row
        var metaEl = document.getElementById('fki-detail-meta');
        while (metaEl.firstChild) metaEl.removeChild(metaEl.firstChild);

        if (sub.phone) {
            var phoneMeta = document.createElement('span');
            phoneMeta.textContent = 'Phone: ' + sub.phone;
            metaEl.appendChild(phoneMeta);
        }
        var dateMeta = document.createElement('span');
        dateMeta.textContent = 'Received: ' + (sub.created_at ? new Date(sub.created_at).toLocaleString() : 'Unknown');
        metaEl.appendChild(dateMeta);

        var statusMeta = document.createElement('span');
        var statusBadge = document.createElement('span');
        statusBadge.className = 'inline-flex items-center gap-1';
        var sDot = document.createElement('span');
        sDot.className = 'fki-status-dot inline-block ' + statusDotColor(sub.status);
        statusBadge.appendChild(sDot);
        var sText = document.createElement('span');
        sText.textContent = (sub.status || 'unknown').charAt(0).toUpperCase() + (sub.status || 'unknown').slice(1);
        statusBadge.appendChild(sText);
        statusMeta.appendChild(statusBadge);
        metaEl.appendChild(statusMeta);

        // Calendar sync info in detail view
        var detailCalResult = null;
        if (sub.action_results) {
            var detailAr = typeof sub.action_results === 'string' ? JSON.parse(sub.action_results) : sub.action_results;
            if (detailAr && detailAr.google_calendar) detailCalResult = detailAr.google_calendar;
        }
        if (detailCalResult) {
            var calMeta = document.createElement('span');
            calMeta.className = 'inline-flex items-center gap-1';
            var calIcon = document.createElement('span');
            if (detailCalResult.success) {
                calIcon.className = 'fki-cal-badge fki-cal-ok';
                calIcon.textContent = '\u2713 Calendar Synced';
                if (detailCalResult.calendar_link) {
                    var calLink = document.createElement('a');
                    calLink.href = detailCalResult.calendar_link;
                    calLink.target = '_blank';
                    calLink.rel = 'noopener';
                    calLink.className = 'fki-cal-badge fki-cal-ok';
                    calLink.textContent = '\u2713 View in Calendar';
                    calLink.style.textDecoration = 'none';
                    calMeta.appendChild(calLink);
                } else {
                    calMeta.appendChild(calIcon);
                }
            } else {
                calIcon.className = 'fki-cal-badge fki-cal-fail';
                calIcon.textContent = '\u2717 Calendar Failed';
                calMeta.appendChild(calIcon);
            }
            metaEl.appendChild(calMeta);
        }

        // Subject
        var subjectRow = document.getElementById('fki-detail-subject-row');
        var subjectEl = document.getElementById('fki-detail-subject');
        if (sub.subject) {
            subjectEl.textContent = sub.subject;
            subjectRow.classList.remove('hidden');
        } else {
            subjectRow.classList.add('hidden');
        }

        // Message
        document.getElementById('fki-detail-message').textContent = sub.message || '(empty)';

        // Extra fields
        var extraContainer = document.getElementById('fki-detail-extra');
        var extraBody = document.getElementById('fki-detail-extra-body');
        while (extraBody.firstChild) extraBody.removeChild(extraBody.firstChild);

        var extraData = sub.extra_data || sub.form_data;
        if (extraData && typeof extraData === 'object' && Object.keys(extraData).length > 0) {
            var skipKeys = ['name', 'email', 'phone', 'subject', 'message', 'site_key', 'form_type', '_hp_email'];
            var hasExtra = false;
            Object.keys(extraData).forEach(function(key) {
                if (skipKeys.indexOf(key) !== -1) return;
                hasExtra = true;
                var row = document.createElement('div');
                row.className = 'flex gap-2 text-sm py-1';
                var label = document.createElement('span');
                label.className = 'text-gray-500 font-medium min-w-[100px]';
                label.textContent = key + ':';
                var val = document.createElement('span');
                val.className = 'text-gray-300';
                val.textContent = String(extraData[key]);
                row.appendChild(label);
                row.appendChild(val);
                extraBody.appendChild(row);
            });
            if (hasExtra) {
                extraContainer.classList.remove('hidden');
            } else {
                extraContainer.classList.add('hidden');
            }
        } else {
            extraContainer.classList.add('hidden');
        }

        // Update Mark Read / Mark Unread button
        updateReadButton(sub.status);

        // Reply mailto
        var replyLink = document.getElementById('fki-action-reply');
        var mailSubject = sub.subject ? 'Re: ' + sub.subject : 'Re: Your message';
        replyLink.href = 'mailto:' + encodeURIComponent(sub.email || '') + '?subject=' + encodeURIComponent(mailSubject);

        // Show panel
        els.detail.classList.remove('hidden');
        els.detail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Auto-mark as read if new
        if (sub.status === 'new') {
            markSubmission(sub.id, 'read', true);
        }
    }

    function updateReadButton(status) {
        var btn = document.getElementById('fki-action-read');
        while (btn.firstChild) btn.removeChild(btn.firstChild);

        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'w-4 h-4');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('stroke-width', '2');
        svg.setAttribute('stroke', 'currentColor');
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');

        if (status === 'new') {
            // Show as "Mark Read"
            path.setAttribute('d', 'M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51');
            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-400 bg-blue-400/10 border border-blue-400/20 rounded-lg hover:bg-blue-400/20 transition-colors';
            svg.appendChild(path);
            btn.appendChild(svg);
            var txt = document.createElement('span');
            txt.textContent = 'Mark Read';
            btn.appendChild(txt);
        } else {
            // Show as "Mark Unread"
            path.setAttribute('d', 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75');
            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-400 bg-blue-400/10 border border-blue-400/20 rounded-lg hover:bg-blue-400/20 transition-colors';
            svg.appendChild(path);
            btn.appendChild(svg);
            var txt2 = document.createElement('span');
            txt2.textContent = 'Mark Unread';
            btn.appendChild(txt2);
        }
    }

    function closeDetail() {
        els.detail.classList.add('hidden');
        state.selectedId = null;
        state.selectedData = null;
    }

    // ── Data Loading ─────────────────────────────────────────────────────────
    function loadSubmissions() {
        state.loading = true;
        showView('loading');
        closeDetail();

        var params = {
            site_key: CONFIG.siteKey,
            offset: state.offset,
            limit: CONFIG.perPage
        };
        if (state.filter !== 'all') {
            params.status = state.filter;
        }
        if (state.search) {
            params.search = state.search;
        }

        apiFetch('submissions.php', params)
            .then(function(result) {
                state.loading = false;
                if (!result.ok || !result.data.success) {
                    els.errorDetail.textContent = (result.data && result.data.error) || 'Unknown error';
                    showView('error');
                    return;
                }

                state.submissions = result.data.submissions || result.data.data || [];
                state.totalCount  = result.data.total || state.submissions.length;

                // Update stats if provided
                if (result.data.stats) {
                    state.stats = result.data.stats;
                }

                renderStats();
                renderTable();
            })
            .catch(function(err) {
                state.loading = false;
                console.error('Form Kit inbox load error:', err);
                els.errorDetail.textContent = err.message || 'Network error';
                showView('error');
            });
    }

    function loadStats() {
        apiFetch('stats.php', { site_key: CONFIG.siteKey })
            .then(function(result) {
                if (result.ok && result.data.success && result.data.stats) {
                    state.stats = result.data.stats;
                    renderStats();
                }
            })
            .catch(function(err) {
                console.error('Form Kit stats error:', err);
            });
    }

    // ── Actions ──────────────────────────────────────────────────────────────
    function markSubmission(id, newStatus, silent) {
        apiPost('mark-read.php', {
            site_key: CONFIG.siteKey,
            id: id,
            status: newStatus
        })
        .then(function(result) {
            if (!silent) {
                if (result.ok && result.data.success) {
                    loadSubmissions();
                    loadStats();
                } else {
                    console.error('Mark failed:', result.data);
                }
            } else {
                // Just refresh stats
                loadStats();
                // Update row styling
                var row = els.tableBody.querySelector('[data-id="' + id + '"]');
                if (row) row.classList.remove('fki-row-unread');
            }
        })
        .catch(function(err) {
            console.error('Mark error:', err);
        });
    }

    function deleteSubmission(id) {
        if (!confirm('Are you sure you want to delete this submission? This cannot be undone.')) return;

        apiPost('submissions.php', {
            site_key: CONFIG.siteKey,
            id: id,
            action: 'delete'
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                closeDetail();
                loadSubmissions();
                loadStats();
            } else {
                console.error('Delete failed:', result.data);
                alert('Failed to delete submission.');
            }
        })
        .catch(function(err) {
            console.error('Delete error:', err);
            alert('Network error. Please try again.');
        });
    }

    function markAllRead() {
        if (!confirm('Mark all submissions as read?')) return;

        apiPost('mark-read.php', {
            site_key: CONFIG.siteKey,
            action: 'mark_all_read'
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                loadSubmissions();
                loadStats();
            } else {
                console.error('Mark all read failed:', result.data);
            }
        })
        .catch(function(err) {
            console.error('Mark all read error:', err);
        });
    }

    // ── Search Debounce ──────────────────────────────────────────────────────
    var searchTimeout = null;
    function onSearchInput() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            state.search = els.search.value.trim();
            state.offset = 0;
            loadSubmissions();
        }, 300);
    }

    // ── Event Listeners ──────────────────────────────────────────────────────

    // Filter tabs
    var filterTabs = document.querySelectorAll('.fki-filter-tab');
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            state.filter = this.getAttribute('data-filter');
            state.offset = 0;
            renderFilters();
            loadSubmissions();
        });
    }

    // Search
    els.search.addEventListener('input', onSearchInput);

    // Pagination
    els.prevBtn.addEventListener('click', function() {
        if (state.offset > 0) {
            state.offset = Math.max(0, state.offset - CONFIG.perPage);
            loadSubmissions();
        }
    });
    els.nextBtn.addEventListener('click', function() {
        if ((state.offset + CONFIG.perPage) < state.totalCount) {
            state.offset += CONFIG.perPage;
            loadSubmissions();
        }
    });

    // Detail close
    document.getElementById('fki-detail-close').addEventListener('click', closeDetail);

    // Detail actions
    document.getElementById('fki-action-read').addEventListener('click', function() {
        if (!state.selectedData) return;
        var newStatus = state.selectedData.status === 'new' ? 'read' : 'new';
        markSubmission(state.selectedId, newStatus, false);
    });
    document.getElementById('fki-action-archive').addEventListener('click', function() {
        if (!state.selectedId) return;
        markSubmission(state.selectedId, 'archived', false);
    });
    document.getElementById('fki-action-spam').addEventListener('click', function() {
        if (!state.selectedId) return;
        markSubmission(state.selectedId, 'spam', false);
    });
    document.getElementById('fki-action-delete').addEventListener('click', function() {
        if (!state.selectedId) return;
        deleteSubmission(state.selectedId);
    });

    // Mark all read
    els.markAllRead.addEventListener('click', markAllRead);

    // Retry
    els.retry.addEventListener('click', function() {
        loadSubmissions();
        loadStats();
    });

    // ── Initialize ───────────────────────────────────────────────────────────
    renderFilters();
    loadSubmissions();
    loadStats();

})();
</script>

</body>
</html>
