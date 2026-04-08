import { describe, it, expect, beforeEach, vi } from 'vitest';
import { loadModule } from './helpers/load-module.js';
import { invoicesFixture } from './helpers/dom-fixtures.js';

let win, doc;

/**
 * The invoices module looks for `invoices-container` to render into.
 * The fixture provides the surrounding tab structure; we add the
 * container element that loadInvoices() targets.
 */
function invoicesHtml() {
  return `<!DOCTYPE html><html><head></head><body>
    ${invoicesFixture()}
    <div id="invoices-container"></div>
  </body></html>`;
}

beforeEach(async () => {
  const mod = await loadModule('public_html/admin/js/invoices.js', {
    html: invoicesHtml(),
    globals: {
      BulkManager: {
        init: vi.fn(),
        reset: vi.fn(),
        selectAllHtml: vi.fn(() => '<input type="checkbox" />'),
        checkboxHtml: vi.fn((id) => `<input type="checkbox" value="${id}" />`),
        toolbarHtml: vi.fn(() => '<div class="bulk-toolbar"></div>'),
        bind: vi.fn(),
        deleteSingle: vi.fn(),
      },
    },
  });
  win = mod.window;
  doc = mod.document;
});

// ============================================================
// 1. Global function exposure
// ============================================================
describe('invoices — global functions', () => {
  it('exposes loadInvoices on window', () => {
    expect(typeof win.loadInvoices).toBe('function');
  });
});

// ============================================================
// 2. Module loads without errors
// ============================================================
describe('invoices — module initialization', () => {
  it('loads without throwing errors', () => {
    expect(win.loadInvoices).toBeDefined();
  });

  it('preserves existing mock globals', () => {
    expect(win.csrfToken).toBe('test-csrf-token-abc123');
    expect(typeof win.showToast).toBe('function');
    expect(win.currentLang).toBe('en');
  });

  it('calls BulkManager.init on load', () => {
    expect(win.BulkManager.init).toHaveBeenCalledWith(
      expect.objectContaining({
        tab: 'invoices',
        endpoint: 'invoices.php',
        superAdminOnly: true,
      })
    );
  });
});

// ============================================================
// 3. DOM structure requirements
// ============================================================
describe('invoices — DOM fixtures', () => {
  it('has the invoices tab container', () => {
    expect(doc.getElementById('tab-invoices')).not.toBeNull();
  });

  it('has the invoices-container div for dynamic rendering', () => {
    expect(doc.getElementById('invoices-container')).not.toBeNull();
  });
});

// ============================================================
// 4. loadInvoices renders filter bar and calls fetch
// ============================================================
describe('invoices — loadInvoices DOM rendering', () => {
  it('loadInvoices renders filter bar into invoices-container', async () => {
    // fetch is mocked to return success with empty data
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    // Call loadInvoices — it renders a filter bar synchronously before the fetch
    win.loadInvoices();

    const container = doc.getElementById('invoices-container');
    // Filter bar should have been rendered synchronously (select + input + button)
    const selects = container.querySelectorAll('select');
    expect(selects.length).toBeGreaterThanOrEqual(1);

    const inputs = container.querySelectorAll('input[type="text"]');
    expect(inputs.length).toBeGreaterThanOrEqual(1);

    const buttons = container.querySelectorAll('button');
    expect(buttons.length).toBeGreaterThanOrEqual(1);
  });

  it('filter bar select has status options', async () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();

    const container = doc.getElementById('invoices-container');
    const select = container.querySelector('select');
    const options = select.querySelectorAll('option');
    // Should have: All Statuses, Draft, Sent, Paid, Void = 5
    expect(options.length).toBe(5);
  });

  it('filter bar has a "Create from RO" button', async () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();

    const container = doc.getElementById('invoices-container');
    const buttons = container.querySelectorAll('button');
    const createBtn = Array.from(buttons).find(
      (b) => b.textContent.includes('Create from RO') || b.textContent.includes('+')
    );
    expect(createBtn).toBeDefined();
  });

  it('calls fetch with correct URL pattern', () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();

    expect(win.fetch).toHaveBeenCalled();
    const url = win.fetch.mock.calls[0][0];
    expect(url).toContain('/api/admin/invoices.php');
    expect(url).toContain('page=1');
    expect(url).toContain('per_page=20');
  });

  it('calls BulkManager.reset when loading', () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();

    expect(win.BulkManager.reset).toHaveBeenCalled();
  });

  it('clears container before rendering', () => {
    const container = doc.getElementById('invoices-container');
    // Add some pre-existing content
    const div = doc.createElement('div');
    div.textContent = 'old content';
    container.appendChild(div);

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();

    // Old content should be gone — only filter bar elements remain
    expect(container.textContent).not.toContain('old content');
  });
});

// ============================================================
// 5. Table rendering with invoice data
// ============================================================
describe('invoices — table rendering after fetch resolves', () => {
  it('renders a table with invoice rows after fetch', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'John Doe', ro_number: 'RO-00000001', total: 250.00, status: 'draft', created_at: '2025-01-15T10:00:00Z' },
      { id: 2, invoice_number: 'INV-0002', customer_name: 'Jane Smith', ro_number: 'RO-00000002', total: 500.50, status: 'paid', created_at: '2025-01-16T12:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 2 }),
    });

    win.loadInvoices();

    // Wait for the promise chain to resolve
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const table = container.querySelector('table');
    expect(table).not.toBeNull();

    const rows = table.querySelectorAll('tbody tr');
    expect(rows.length).toBe(2);
  });

  it('renders invoice number in table row', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'sent', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    expect(container.textContent).toContain('INV-0001');
  });

  it('renders status badge with correct text', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'paid', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const badge = container.querySelector('span.rounded-full');
    expect(badge).not.toBeNull();
    expect(badge.textContent).toBe('Paid');
  });

  it('formats currency correctly in table', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 1234.56, status: 'draft', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    expect(container.textContent).toContain('$1234.56');
  });

  it('shows empty state when no invoices returned', async () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [], total: 0 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    expect(container.textContent).toContain('No invoices found');
  });

  it('renders action buttons per invoice status', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'draft', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const actionButtons = container.querySelectorAll('td button');
    // Draft invoices should have: View, Send, Void, Delete = 4 buttons
    expect(actionButtons.length).toBeGreaterThanOrEqual(3);

    const buttonTexts = Array.from(actionButtons).map((b) => b.textContent);
    expect(buttonTexts).toContain('View');
    expect(buttonTexts).toContain('Send');
    expect(buttonTexts).toContain('Void');
  });

  it('paid invoices do not have Send or Void buttons', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'paid', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const actionButtons = container.querySelectorAll('td button');
    const buttonTexts = Array.from(actionButtons).map((b) => b.textContent);
    expect(buttonTexts).toContain('View');
    expect(buttonTexts).not.toContain('Send');
    expect(buttonTexts).not.toContain('Mark Paid');
    expect(buttonTexts).not.toContain('Void');
  });

  it('sent invoices have Mark Paid button', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'sent', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const actionButtons = container.querySelectorAll('td button');
    const buttonTexts = Array.from(actionButtons).map((b) => b.textContent);
    expect(buttonTexts).toContain('Mark Paid');
  });
});

// ============================================================
// 6. Pagination rendering
// ============================================================
describe('invoices — pagination', () => {
  it('renders pagination when total exceeds per page', async () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [{ id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'draft', created_at: '2025-01-15T10:00:00Z' }], total: 40 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    // 40 total / 20 per page = 2 pages → pagination should appear
    expect(container.textContent).toContain('Page 1 of 2');
  });

  it('does not render pagination when total fits one page', async () => {
    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: [{ id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'draft', created_at: '2025-01-15T10:00:00Z' }], total: 5 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    expect(container.textContent).not.toContain('Page');
  });
});

// ============================================================
// 7. Dark mode class support
// ============================================================
describe('invoices — dark mode classes', () => {
  it('table header uses dark mode classes', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'draft', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const headRow = container.querySelector('thead tr');
    expect(headRow).not.toBeNull();
    expect(headRow.className).toContain('dark:bg-gray-700');
  });

  it('status badges use dark mode classes', async () => {
    const invoices = [
      { id: 1, invoice_number: 'INV-0001', customer_name: 'Test', ro_number: 'RO-1', total: 100, status: 'sent', created_at: '2025-01-15T10:00:00Z' },
    ];

    win.fetch.mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ success: true, data: invoices, total: 1 }),
    });

    win.loadInvoices();
    await new Promise((r) => setTimeout(r, 50));

    const container = doc.getElementById('invoices-container');
    const badge = container.querySelector('span.rounded-full');
    expect(badge.className).toContain('dark:');
  });
});
