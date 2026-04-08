<?php
/**
 * Commerce Kit — Admin Order Detail Template
 *
 * Self-contained dark-themed single order view.
 * Include in any site's admin layout. Set $orderRef before including.
 *
 * Required: $orderRef (string) — order reference to display
 * Optional: $apiBase (string) — base URL for API (default: '/api/commerce')
 */
$orderRef = $orderRef ?? ($_GET['ref'] ?? '');
$apiBase = $apiBase ?? '/api/commerce';
?>
<div id="commerce-order-detail" class="min-h-screen bg-[#0A0A0A] text-white p-6">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="?page=orders" class="text-gray-400 hover:text-[#D4AF37] text-sm transition-colors" id="back-link">&larr; Back to Orders</a>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">
                Order <code class="text-[#D4AF37] font-mono" id="detail-ref">--</code>
            </h1>
            <p class="text-sm text-gray-400 mt-1" id="detail-date">--</p>
        </div>
        <div id="detail-status-badge"></div>
    </div>

    <!-- Loading State -->
    <div id="detail-loading" class="text-center py-16">
        <p class="text-gray-400">Loading order details...</p>
    </div>

    <!-- Error State -->
    <div id="detail-error" class="hidden text-center py-16">
        <p class="text-red-400 text-lg">Order not found</p>
        <p class="text-gray-500 text-sm mt-2">The requested order could not be loaded.</p>
    </div>

    <!-- Order Content (hidden until loaded) -->
    <div id="detail-content" class="hidden space-y-6">

        <!-- Status Timeline -->
        <div class="bg-[#111827] rounded-xl border border-gray-800 p-6">
            <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Status Timeline</h2>
            <div class="flex items-center gap-0" id="timeline-container">
                <!-- Filled dynamically -->
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Info -->
            <div class="bg-[#111827] rounded-xl border border-gray-800 p-6">
                <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Customer Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">Name</dt>
                        <dd class="text-sm text-white" id="detail-customer-name">--</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Email</dt>
                        <dd class="text-sm text-white" id="detail-customer-email">--</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Phone</dt>
                        <dd class="text-sm text-white" id="detail-customer-phone">--</dd>
                    </div>
                </dl>
            </div>

            <!-- Payment Info -->
            <div class="bg-[#111827] rounded-xl border border-gray-800 p-6">
                <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Payment Details</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">Provider</dt>
                        <dd class="text-sm text-white" id="detail-provider">--</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Method</dt>
                        <dd class="text-sm text-white" id="detail-method">--</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Currency</dt>
                        <dd class="text-sm text-white" id="detail-currency">--</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Paid At</dt>
                        <dd class="text-sm text-white" id="detail-paid-at">--</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-[#111827] rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-6 pb-0">
                <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Line Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">SKU</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="detail-items-tbody"></tbody>
                    <tfoot>
                        <tr class="border-t border-gray-700">
                            <td colspan="3"></td>
                            <td class="px-6 py-3 text-right text-sm text-gray-400">Subtotal</td>
                            <td class="px-6 py-3 text-right text-sm text-white" id="detail-subtotal">--</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td class="px-6 py-3 text-right text-sm text-gray-400">Tax</td>
                            <td class="px-6 py-3 text-right text-sm text-white" id="detail-tax">--</td>
                        </tr>
                        <tr class="border-t border-gray-600">
                            <td colspan="3"></td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-white">Total</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-[#D4AF37]" id="detail-total">--</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-[#111827] rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-6 pb-0">
                <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Transaction History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Transaction ID</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Amount</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody id="detail-tx-tbody">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No transactions recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div id="detail-notes-section" class="hidden bg-[#111827] rounded-xl border border-gray-800 p-6">
            <h2 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-2">Notes</h2>
            <p class="text-sm text-gray-300" id="detail-notes"></p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3" id="action-buttons">
            <!-- Populated dynamically based on status -->
        </div>
    </div>
</div>

<style>
    .status-badge-lg {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-pending { background: #422006; color: #FCD34D; }
    .status-processing { background: #1E3A5F; color: #60A5FA; }
    .status-completed { background: #064E3B; color: #6EE7B7; }
    .status-cancelled { background: #450A0A; color: #FCA5A5; }
    .status-refunded { background: #2E1065; color: #C4B5FD; }
    .status-failed { background: #450A0A; color: #FCA5A5; }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
    }
    .timeline-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #374151;
        border: 2px solid #4B5563;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    .timeline-dot.active {
        background: #D4AF37;
        border-color: #D4AF37;
    }
    .timeline-dot.completed {
        background: #059669;
        border-color: #059669;
    }
    .timeline-dot.error-state {
        background: #DC2626;
        border-color: #DC2626;
    }
    .timeline-line {
        height: 2px;
        flex: 1;
        background: #374151;
        margin-top: 11px;
    }
    .timeline-line.filled {
        background: #059669;
    }
    .timeline-label {
        font-size: 11px;
        color: #6B7280;
        margin-top: 6px;
        text-align: center;
    }
    .timeline-label.active {
        color: #D4AF37;
        font-weight: 600;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        border: none;
    }
    .action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-complete { background: #059669; color: #fff; }
    .btn-complete:hover:not(:disabled) { background: #047857; }
    .btn-cancel { background: #DC2626; color: #fff; }
    .btn-cancel:hover:not(:disabled) { background: #B91C1C; }
    .btn-refund { background: #7C3AED; color: #fff; }
    .btn-refund:hover:not(:disabled) { background: #6D28D9; }

    .tx-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .tx-payment { background: #1E3A5F; color: #60A5FA; }
    .tx-refund { background: #2E1065; color: #C4B5FD; }
    .tx-adjustment { background: #422006; color: #FCD34D; }

    .tx-status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
    }
    .tx-status-pending { background: #422006; color: #FCD34D; }
    .tx-status-completed { background: #064E3B; color: #6EE7B7; }
    .tx-status-failed { background: #450A0A; color: #FCA5A5; }
</style>

<script>
(function() {
    'use strict';

    var ORDER_REF = <?php echo json_encode($orderRef); ?>;
    var API_BASE = <?php echo json_encode($apiBase); ?>;

    function formatCurrency(amount) {
        return '$' + parseFloat(amount || 0).toFixed(2);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '--';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function createStatusBadge(status) {
        var span = document.createElement('span');
        span.className = 'status-badge-lg status-' + (status || 'pending');
        span.textContent = status || 'unknown';
        return span;
    }

    var TIMELINE_STEPS = ['pending', 'processing', 'completed'];
    var TERMINAL_STATUSES = ['cancelled', 'failed', 'refunded'];

    function renderTimeline(status) {
        var container = document.getElementById('timeline-container');
        if (!container) return;
        while (container.firstChild) container.removeChild(container.firstChild);

        var currentIdx = TIMELINE_STEPS.indexOf(status);
        var isTerminal = TERMINAL_STATUSES.indexOf(status) !== -1;

        TIMELINE_STEPS.forEach(function(step, i) {
            // Add connecting line before each step (except first)
            if (i > 0) {
                var line = document.createElement('div');
                line.className = 'timeline-line';
                if (i <= currentIdx) line.className += ' filled';
                container.appendChild(line);
            }

            var stepDiv = document.createElement('div');
            stepDiv.className = 'timeline-step';

            var dot = document.createElement('div');
            dot.className = 'timeline-dot';

            if (isTerminal && i === currentIdx + 1) {
                // Don't mark future steps
            } else if (i < currentIdx) {
                dot.className += ' completed';
                var check = document.createElement('span');
                check.textContent = '\u2713';
                check.style.cssText = 'color:#fff;font-size:12px;font-weight:bold;';
                dot.appendChild(check);
            } else if (i === currentIdx) {
                dot.className += ' active';
            }

            stepDiv.appendChild(dot);

            var label = document.createElement('div');
            label.className = 'timeline-label';
            if (i === currentIdx) label.className += ' active';
            label.textContent = step.charAt(0).toUpperCase() + step.slice(1);
            stepDiv.appendChild(label);

            container.appendChild(stepDiv);
        });

        // Add terminal status if applicable
        if (isTerminal) {
            var line = document.createElement('div');
            line.className = 'timeline-line';
            container.appendChild(line);

            var termStep = document.createElement('div');
            termStep.className = 'timeline-step';
            var termDot = document.createElement('div');
            termDot.className = 'timeline-dot error-state';
            var x = document.createElement('span');
            x.textContent = '\u2717';
            x.style.cssText = 'color:#fff;font-size:12px;font-weight:bold;';
            termDot.appendChild(x);
            termStep.appendChild(termDot);

            var termLabel = document.createElement('div');
            termLabel.className = 'timeline-label active';
            termLabel.style.color = '#FCA5A5';
            termLabel.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            termStep.appendChild(termLabel);

            container.appendChild(termStep);
        }
    }

    function renderLineItems(items) {
        var tbody = document.getElementById('detail-items-tbody');
        if (!tbody) return;
        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

        if (!items || items.length === 0) {
            var emptyRow = document.createElement('tr');
            var emptyTd = document.createElement('td');
            emptyTd.colSpan = 5;
            emptyTd.className = 'px-6 py-4 text-center text-gray-500';
            emptyTd.textContent = 'No line items.';
            emptyRow.appendChild(emptyTd);
            tbody.appendChild(emptyRow);
            return;
        }

        items.forEach(function(item) {
            var tr = document.createElement('tr');
            tr.className = 'border-b border-gray-800';

            var tdDesc = document.createElement('td');
            tdDesc.className = 'px-6 py-3 text-sm text-white';
            tdDesc.textContent = item.description || '';
            tr.appendChild(tdDesc);

            var tdSku = document.createElement('td');
            tdSku.className = 'px-6 py-3 text-sm text-gray-400 font-mono';
            tdSku.textContent = item.sku || '--';
            tr.appendChild(tdSku);

            var tdQty = document.createElement('td');
            tdQty.className = 'px-6 py-3 text-center text-sm text-gray-300';
            tdQty.textContent = item.quantity || 1;
            tr.appendChild(tdQty);

            var tdPrice = document.createElement('td');
            tdPrice.className = 'px-6 py-3 text-right text-sm text-gray-300';
            tdPrice.textContent = formatCurrency(item.unit_price);
            tr.appendChild(tdPrice);

            var tdAmt = document.createElement('td');
            tdAmt.className = 'px-6 py-3 text-right text-sm font-semibold text-white';
            tdAmt.textContent = formatCurrency(item.amount);
            tr.appendChild(tdAmt);

            tbody.appendChild(tr);
        });
    }

    function renderTransactions(transactions) {
        var tbody = document.getElementById('detail-tx-tbody');
        if (!tbody) return;
        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

        if (!transactions || transactions.length === 0) {
            var emptyRow = document.createElement('tr');
            var emptyTd = document.createElement('td');
            emptyTd.colSpan = 6;
            emptyTd.className = 'px-6 py-4 text-center text-gray-500';
            emptyTd.textContent = 'No transactions recorded.';
            emptyRow.appendChild(emptyTd);
            tbody.appendChild(emptyRow);
            return;
        }

        transactions.forEach(function(tx) {
            var tr = document.createElement('tr');
            tr.className = 'border-b border-gray-800';

            // Type badge
            var tdType = document.createElement('td');
            tdType.className = 'px-6 py-3';
            var typeBadge = document.createElement('span');
            typeBadge.className = 'tx-type-badge tx-' + (tx.type || 'payment');
            typeBadge.textContent = tx.type || 'payment';
            tdType.appendChild(typeBadge);
            tr.appendChild(tdType);

            // Provider
            var tdProvider = document.createElement('td');
            tdProvider.className = 'px-6 py-3 text-sm text-gray-300';
            tdProvider.textContent = tx.provider || '--';
            tr.appendChild(tdProvider);

            // Transaction ID
            var tdTxId = document.createElement('td');
            tdTxId.className = 'px-6 py-3 text-sm text-gray-400 font-mono';
            var txIdText = tx.provider_transaction_id || '--';
            if (txIdText.length > 24) txIdText = txIdText.substring(0, 24) + '...';
            tdTxId.textContent = txIdText;
            tr.appendChild(tdTxId);

            // Amount
            var tdAmt = document.createElement('td');
            tdAmt.className = 'px-6 py-3 text-right text-sm font-semibold';
            tdAmt.style.color = tx.type === 'refund' ? '#C4B5FD' : '#fff';
            tdAmt.textContent = (tx.type === 'refund' ? '-' : '') + formatCurrency(tx.amount);
            tr.appendChild(tdAmt);

            // Status
            var tdStatus = document.createElement('td');
            tdStatus.className = 'px-6 py-3 text-center';
            var statusBadge = document.createElement('span');
            statusBadge.className = 'tx-status-badge tx-status-' + (tx.status || 'pending');
            statusBadge.textContent = tx.status || 'pending';
            tdStatus.appendChild(statusBadge);
            tr.appendChild(tdStatus);

            // Date
            var tdDate = document.createElement('td');
            tdDate.className = 'px-6 py-3 text-sm text-gray-400';
            tdDate.textContent = formatDate(tx.created_at);
            tr.appendChild(tdDate);

            tbody.appendChild(tr);
        });
    }

    function renderActionButtons(order) {
        var container = document.getElementById('action-buttons');
        if (!container) return;
        while (container.firstChild) container.removeChild(container.firstChild);

        var status = order.status;

        if (status === 'pending' || status === 'processing') {
            // Mark Completed button
            var completeBtn = document.createElement('button');
            completeBtn.className = 'action-btn btn-complete';
            completeBtn.textContent = 'Mark Completed';
            completeBtn.addEventListener('click', function() {
                updateStatus(order.order_ref, 'completed', completeBtn);
            });
            container.appendChild(completeBtn);

            // Cancel button
            var cancelBtn = document.createElement('button');
            cancelBtn.className = 'action-btn btn-cancel';
            cancelBtn.textContent = 'Cancel Order';
            cancelBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel this order?')) {
                    updateStatus(order.order_ref, 'cancelled', cancelBtn);
                }
            });
            container.appendChild(cancelBtn);
        }

        if (status === 'completed') {
            // Refund button
            var refundBtn = document.createElement('button');
            refundBtn.className = 'action-btn btn-refund';
            refundBtn.textContent = 'Refund Order';
            refundBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to refund this order?')) {
                    updateStatus(order.order_ref, 'refunded', refundBtn);
                }
            });
            container.appendChild(refundBtn);
        }
    }

    function updateStatus(orderRef, newStatus, btn) {
        if (btn) btn.disabled = true;

        fetch(API_BASE + '/orders.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ref: orderRef, status: newStatus })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                loadOrder();
            } else {
                alert('Error: ' + (data.error || 'Failed to update status'));
                if (btn) btn.disabled = false;
            }
        })
        .catch(function(err) {
            console.error('[Commerce] Status update failed:', err);
            alert('Failed to update order status.');
            if (btn) btn.disabled = false;
        });
    }

    function loadOrder() {
        if (!ORDER_REF) {
            document.getElementById('detail-loading').classList.add('hidden');
            document.getElementById('detail-error').classList.remove('hidden');
            return;
        }

        fetch(API_BASE + '/orders.php?ref=' + encodeURIComponent(ORDER_REF), { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('detail-loading').classList.add('hidden');

                if (!data.success || !data.order) {
                    document.getElementById('detail-error').classList.remove('hidden');
                    return;
                }

                var order = data.order;

                document.getElementById('detail-content').classList.remove('hidden');

                // Header
                var refEl = document.getElementById('detail-ref');
                if (refEl) refEl.textContent = order.order_ref || '';

                var dateEl = document.getElementById('detail-date');
                if (dateEl) dateEl.textContent = 'Created ' + formatDate(order.created_at);

                var badgeContainer = document.getElementById('detail-status-badge');
                if (badgeContainer) {
                    while (badgeContainer.firstChild) badgeContainer.removeChild(badgeContainer.firstChild);
                    badgeContainer.appendChild(createStatusBadge(order.status));
                }

                // Timeline
                renderTimeline(order.status);

                // Customer info
                var nameEl = document.getElementById('detail-customer-name');
                if (nameEl) nameEl.textContent = order.customer_name || 'N/A';

                var emailEl = document.getElementById('detail-customer-email');
                if (emailEl) emailEl.textContent = order.customer_email || 'N/A';

                var phoneEl = document.getElementById('detail-customer-phone');
                if (phoneEl) phoneEl.textContent = order.customer_phone || 'N/A';

                // Payment info
                var providerEl = document.getElementById('detail-provider');
                if (providerEl) providerEl.textContent = order.payment_provider || 'N/A';

                var methodEl = document.getElementById('detail-method');
                if (methodEl) methodEl.textContent = order.payment_method || 'N/A';

                var currencyEl = document.getElementById('detail-currency');
                if (currencyEl) currencyEl.textContent = order.currency || 'USD';

                var paidEl = document.getElementById('detail-paid-at');
                if (paidEl) paidEl.textContent = order.paid_at ? formatDate(order.paid_at) : 'Not paid';

                // Totals
                var subtotalEl = document.getElementById('detail-subtotal');
                if (subtotalEl) subtotalEl.textContent = formatCurrency(order.subtotal);

                var taxEl = document.getElementById('detail-tax');
                if (taxEl) taxEl.textContent = formatCurrency(order.tax);

                var totalEl = document.getElementById('detail-total');
                if (totalEl) totalEl.textContent = formatCurrency(order.total);

                // Line items
                renderLineItems(order.line_items || []);

                // Transactions
                renderTransactions(order.transactions || []);

                // Notes
                if (order.notes) {
                    var notesSection = document.getElementById('detail-notes-section');
                    if (notesSection) notesSection.classList.remove('hidden');
                    var notesEl = document.getElementById('detail-notes');
                    if (notesEl) notesEl.textContent = order.notes;
                }

                // Action buttons
                renderActionButtons(order);
            })
            .catch(function(err) {
                console.error('[Commerce] Order load failed:', err);
                document.getElementById('detail-loading').classList.add('hidden');
                document.getElementById('detail-error').classList.remove('hidden');
            });
    }

    // Initial load
    loadOrder();
})();
</script>
