<?php
/**
 * Commerce Kit — Admin Order List Template
 *
 * Self-contained dark-themed admin order list.
 * Include in any site's admin layout. Set $siteKey before including.
 *
 * Required: $siteKey (string) — site identifier for API calls
 * Optional: $apiBase (string) — base URL for API (default: '/api/commerce')
 */
$siteKey = $siteKey ?? '';
$apiBase = $apiBase ?? '/api/commerce';
?>
<div id="commerce-orders-app" class="min-h-screen bg-[#0A0A0A] text-white p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Orders</h1>
            <p class="text-sm text-gray-400 mt-1" id="orders-subtitle">Loading...</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="bg-[#111827] rounded-xl px-5 py-3 border border-gray-800">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Total Revenue</p>
                <p class="text-xl font-bold text-[#D4AF37]" id="stat-revenue">--</p>
            </div>
            <div class="bg-[#111827] rounded-xl px-5 py-3 border border-gray-800">
                <p class="text-xs text-gray-400 uppercase tracking-wider">7-Day Revenue</p>
                <p class="text-xl font-bold text-green-400" id="stat-revenue-7d">--</p>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-2 mb-6" id="filter-tabs">
        <button class="filter-tab active px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="">All</button>
        <button class="filter-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="pending">Pending</button>
        <button class="filter-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="processing">Processing</button>
        <button class="filter-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="completed">Completed</button>
        <button class="filter-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="cancelled">Cancelled</button>
        <button class="filter-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors" data-status="refunded">Refunded</button>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <input type="text" id="orders-search" placeholder="Search by name, email, or order ref..."
               class="w-full sm:w-80 px-4 py-2 rounded-lg bg-[#1F2937] border border-gray-700 text-white placeholder-gray-500 text-sm focus:border-[#D4AF37] focus:outline-none transition-colors">
    </div>

    <!-- Orders Table -->
    <div class="bg-[#111827] rounded-xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Provider</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">Loading orders...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div id="orders-empty" class="hidden text-center py-16">
        <p class="text-gray-400 text-lg">No orders found</p>
        <p class="text-gray-500 text-sm mt-2">Orders will appear here when customers place them.</p>
    </div>
</div>

<style>
    .filter-tab {
        background: #1F2937;
        color: #9CA3AF;
        border: 1px solid transparent;
    }
    .filter-tab:hover {
        background: #374151;
        color: #fff;
    }
    .filter-tab.active {
        background: #D4AF37;
        color: #000;
        font-weight: 600;
    }
    .status-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-pending { background: #422006; color: #FCD34D; }
    .status-processing { background: #1E3A5F; color: #60A5FA; }
    .status-completed { background: #064E3B; color: #6EE7B7; }
    .status-cancelled { background: #450A0A; color: #FCA5A5; }
    .status-refunded { background: #2E1065; color: #C4B5FD; }
    .status-failed { background: #450A0A; color: #FCA5A5; }
    .order-row:hover {
        background: #1F2937;
        cursor: pointer;
    }
</style>

<script>
(function() {
    'use strict';

    var SITE_KEY = <?php echo json_encode($siteKey); ?>;
    var API_BASE = <?php echo json_encode($apiBase); ?>;
    var currentFilter = '';
    var currentSearch = '';
    var searchTimer = null;

    function formatCurrency(amount) {
        return '$' + parseFloat(amount || 0).toFixed(2);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '--';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function createStatusBadge(status) {
        var span = document.createElement('span');
        span.className = 'status-badge status-' + (status || 'pending');
        span.textContent = status || 'unknown';
        return span;
    }

    function loadStats() {
        fetch(API_BASE + '/stats.php?site_key=' + encodeURIComponent(SITE_KEY), { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.stats) {
                    var revenueEl = document.getElementById('stat-revenue');
                    if (revenueEl) revenueEl.textContent = formatCurrency(data.stats.total_revenue);

                    var rev7dEl = document.getElementById('stat-revenue-7d');
                    if (rev7dEl) rev7dEl.textContent = formatCurrency(data.stats.revenue_7d);

                    var subtitleEl = document.getElementById('orders-subtitle');
                    if (subtitleEl) {
                        subtitleEl.textContent = data.stats.total_orders + ' total orders | ' +
                            data.stats.completed_orders + ' completed | ' +
                            data.stats.conversion_rate + '% conversion';
                    }
                }
            })
            .catch(function(err) {
                console.error('[Commerce] Stats load failed:', err);
            });
    }

    function loadOrders(status) {
        var url = API_BASE + '/orders.php?site_key=' + encodeURIComponent(SITE_KEY);
        if (status) url += '&status=' + encodeURIComponent(status);
        if (currentSearch) url += '&search=' + encodeURIComponent(currentSearch);

        fetch(url, { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                renderOrders(data.success ? (data.orders || []) : []);
            })
            .catch(function(err) {
                console.error('[Commerce] Orders load failed:', err);
                renderOrders([]);
            });
    }

    function renderOrders(orders) {
        var tbody = document.getElementById('orders-tbody');
        var emptyState = document.getElementById('orders-empty');
        if (!tbody) return;

        // Clear existing rows
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        if (orders.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            tbody.parentElement.parentElement.parentElement.classList.add('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        tbody.parentElement.parentElement.parentElement.classList.remove('hidden');

        orders.forEach(function(order) {
            var tr = document.createElement('tr');
            tr.className = 'order-row border-b border-gray-800 transition-colors';

            // Order Ref
            var tdRef = document.createElement('td');
            tdRef.className = 'px-4 py-3';
            var refCode = document.createElement('code');
            refCode.className = 'text-[#D4AF37] font-mono text-sm';
            refCode.textContent = order.order_ref || '';
            tdRef.appendChild(refCode);
            tr.appendChild(tdRef);

            // Customer
            var tdCustomer = document.createElement('td');
            tdCustomer.className = 'px-4 py-3';
            var nameDiv = document.createElement('div');
            nameDiv.className = 'text-sm text-white';
            nameDiv.textContent = order.customer_name || 'N/A';
            tdCustomer.appendChild(nameDiv);
            if (order.customer_email) {
                var emailDiv = document.createElement('div');
                emailDiv.className = 'text-xs text-gray-500';
                emailDiv.textContent = order.customer_email;
                tdCustomer.appendChild(emailDiv);
            }
            tr.appendChild(tdCustomer);

            // Provider
            var tdProvider = document.createElement('td');
            tdProvider.className = 'px-4 py-3 text-sm text-gray-400';
            tdProvider.textContent = order.payment_provider || '--';
            tr.appendChild(tdProvider);

            // Total
            var tdTotal = document.createElement('td');
            tdTotal.className = 'px-4 py-3 text-right text-sm font-semibold text-white';
            tdTotal.textContent = formatCurrency(order.total);
            tr.appendChild(tdTotal);

            // Status
            var tdStatus = document.createElement('td');
            tdStatus.className = 'px-4 py-3 text-center';
            tdStatus.appendChild(createStatusBadge(order.status));
            tr.appendChild(tdStatus);

            // Date
            var tdDate = document.createElement('td');
            tdDate.className = 'px-4 py-3 text-sm text-gray-400';
            tdDate.textContent = formatDate(order.created_at);
            tr.appendChild(tdDate);

            // Actions
            var tdActions = document.createElement('td');
            tdActions.className = 'px-4 py-3 text-right';
            var viewBtn = document.createElement('a');
            viewBtn.className = 'text-[#D4AF37] hover:text-white text-sm font-medium transition-colors';
            viewBtn.textContent = 'View';
            viewBtn.href = '?page=order-detail&ref=' + encodeURIComponent(order.order_ref || '');
            tdActions.appendChild(viewBtn);
            tr.appendChild(tdActions);

            // Row click navigates to detail
            tr.addEventListener('click', function(e) {
                if (e.target.tagName === 'A') return;
                window.location.href = '?page=order-detail&ref=' + encodeURIComponent(order.order_ref || '');
            });

            tbody.appendChild(tr);
        });
    }

    // Filter tab handlers
    var tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            currentFilter = tab.getAttribute('data-status') || '';
            loadOrders(currentFilter);
        });
    });

    // Search handler
    var searchInput = document.getElementById('orders-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentSearch = searchInput.value.trim();
                loadOrders(currentFilter);
            }, 300);
        });
    }

    // Initial load
    loadStats();
    loadOrders('');
})();
</script>
