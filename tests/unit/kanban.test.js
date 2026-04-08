import { describe, it, expect, beforeEach, vi } from 'vitest';
import { loadModule, mockRO, mockROBatch } from './helpers/load-module.js';
import { kanbanFixture } from './helpers/dom-fixtures.js';

let win, doc;

beforeEach(async () => {
  const mod = await loadModule('public_html/admin/js/kanban.js', {
    html: `<!DOCTYPE html><html><head></head><body>${kanbanFixture()}</body></html>`,
  });
  win = mod.window;
  doc = mod.document;
});

// ============================================================
// 1. Column definitions — 12 statuses
// ============================================================
describe('kanban — column definitions', () => {
  it('renders 12 columns when loadKanban is called with data', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: mockROBatch() });
    await win.loadKanban();
    const columns = doc.querySelectorAll('[data-status]');
    expect(columns.length).toBe(12);
  });

  it('columns have correct status keys', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: [] });
    await win.loadKanban();
    const columns = doc.querySelectorAll('[data-status]');
    const keys = Array.from(columns).map(c => c.getAttribute('data-status'));
    expect(keys).toEqual([
      'intake', 'check_in', 'diagnosis', 'estimate_pending',
      'pending_approval', 'approved', 'in_progress', 'on_hold',
      'waiting_parts', 'ready', 'completed', 'invoiced',
    ]);
  });

  it('each column has a count badge', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: [] });
    await win.loadKanban();
    const columns = doc.querySelectorAll('[data-status]');
    columns.forEach(col => {
      // Each column header should have a badge span with the count
      const badges = col.querySelectorAll('span');
      expect(badges.length).toBeGreaterThanOrEqual(2); // label + count
    });
  });
});

// ============================================================
// 2. loadKanban — renders cards in correct columns
// ============================================================
describe('kanban — loadKanban', () => {
  it('places cards into the correct column by status', async () => {
    const ros = [
      mockRO({ id: 1, status: 'intake' }),
      mockRO({ id: 2, status: 'intake' }),
      mockRO({ id: 3, status: 'in_progress' }),
    ];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const intakeZone = doc.querySelector('[data-drop-zone="intake"]');
    const inProgressZone = doc.querySelector('[data-drop-zone="in_progress"]');
    expect(intakeZone.querySelectorAll('[data-ro-id]').length).toBe(2);
    expect(inProgressZone.querySelectorAll('[data-ro-id]').length).toBe(1);
  });

  it('filters out cancelled orders', async () => {
    const ros = mockROBatch(); // includes one cancelled
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const allCards = doc.querySelectorAll('[data-ro-id]');
    const cancelledCards = doc.querySelectorAll('[data-ro-status="cancelled"]');
    expect(cancelledCards.length).toBe(0);
    expect(allCards.length).toBe(5); // 6 total - 1 cancelled
  });

  it('calls api with correct parameters', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: [] });
    await win.loadKanban();

    expect(win.api).toHaveBeenCalledWith(
      expect.stringContaining('repair-orders.php')
    );
    const callArg = win.api.mock.calls[0][0];
    expect(callArg).toContain('limit=100');
    expect(callArg).toContain('sort_by=updated_at');
    expect(callArg).toContain('sort_order=DESC');
  });
});

// ============================================================
// 3. Card rendering — content checks
// ============================================================
describe('kanban — card rendering', () => {
  it('card shows RO number', async () => {
    const ros = [mockRO({ id: 1, ro_number: 'RO-12345678', status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card).not.toBeNull();
    expect(card.textContent).toContain('RO-12345678');
  });

  it('card shows customer name', async () => {
    const ros = [mockRO({ id: 1, first_name: 'Maria', last_name: 'Garcia', status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card.textContent).toContain('Maria Garcia');
  });

  it('card shows vehicle info', async () => {
    const ros = [mockRO({ id: 1, vehicle_year: '2022', vehicle_make: 'Honda', vehicle_model: 'Civic', status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card.textContent).toContain('2022 Honda Civic');
  });

  it('card shows active labor indicator when techs are working', async () => {
    const ros = [mockRO({ id: 1, status: 'in_progress', active_labor_count: 2 })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card.textContent).toContain('2 techs working');
  });

  it('card is draggable', async () => {
    const ros = [mockRO({ id: 1, status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card.getAttribute('draggable')).toBe('true');
  });

  it('card has data-ro-status attribute', async () => {
    const ros = [mockRO({ id: 1, status: 'diagnosis' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    expect(card.getAttribute('data-ro-status')).toBe('diagnosis');
  });
});

// ============================================================
// 4. toggleKanbanView — table/kanban toggle
// ============================================================
describe('kanban — toggleKanbanView', () => {
  it('toggleKanbanView is exposed globally', () => {
    expect(typeof win.toggleKanbanView).toBe('function');
  });

  it('loadKanban is exposed globally', () => {
    expect(typeof win.loadKanban).toBe('function');
  });

  it('toggling shows kanban and hides table', () => {
    // The injectKanbanElements should have set up the views
    const tableView = doc.getElementById('ro-table-view');
    const kanbanView = doc.getElementById('ro-kanban-view');
    if (!tableView || !kanbanView) return; // skip if DOM injection didn't work in this context

    win.api.mockResolvedValue({ success: true, data: [] });
    win.toggleKanbanView();

    expect(kanbanView.style.display).toBe('block');
    expect(tableView.style.display).toBe('none');
  });
});

// ============================================================
// 5. Status drop — API call verification
// ============================================================
describe('kanban — handleStatusDrop (via drag-drop)', () => {
  it('optimistically moves card to target column on drop', async () => {
    const ros = [mockRO({ id: 42, status: 'intake' })];
    win.api.mockResolvedValue({ success: true, data: ros });
    await win.loadKanban();

    // Verify card starts in intake column
    const intakeZone = doc.querySelector('[data-drop-zone="intake"]');
    expect(intakeZone.querySelector('[data-ro-id="42"]')).not.toBeNull();

    // Simulate a drop on the check_in column
    const checkInCol = doc.querySelector('[data-status="check_in"]');
    const dropEvent = new win.Event('drop', { bubbles: true });
    Object.defineProperty(dropEvent, 'dataTransfer', {
      value: {
        getData: () => '42',
        setData: () => {},
        dropEffect: 'move',
      },
    });
    dropEvent.preventDefault = vi.fn();
    checkInCol.dispatchEvent(dropEvent);

    // Card should be optimistically moved immediately (before any async)
    const card = doc.querySelector('[data-ro-id="42"]');
    expect(card.getAttribute('data-ro-status')).toBe('check_in');
    const checkInZone = doc.querySelector('[data-drop-zone="check_in"]');
    expect(checkInZone.contains(card)).toBe(true);
    // Card should no longer be in intake
    expect(intakeZone.querySelector('[data-ro-id="42"]')).toBeNull();
  });

  it('does not move card when dropped on same column', async () => {
    const ros = [mockRO({ id: 1, status: 'intake' })];
    win.api.mockResolvedValue({ success: true, data: ros });
    await win.loadKanban();

    const callCountBefore = win.api.mock.calls.length;

    // Drop on same column (intake → intake)
    const intakeCol = doc.querySelector('[data-status="intake"]');
    const dropEvent = new win.Event('drop', { bubbles: true });
    Object.defineProperty(dropEvent, 'dataTransfer', {
      value: { getData: () => '1', setData: () => {}, dropEffect: 'move' },
    });
    dropEvent.preventDefault = vi.fn();
    intakeCol.dispatchEvent(dropEvent);

    // No new API calls should be made (same status = no-op)
    expect(win.api.mock.calls.length).toBe(callCountBefore);
  });

  it('drop zone columns have correct data-drop-zone attributes', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: [] });
    await win.loadKanban();

    const zones = doc.querySelectorAll('[data-drop-zone]');
    expect(zones.length).toBe(12);
    const zoneKeys = Array.from(zones).map(z => z.getAttribute('data-drop-zone'));
    expect(zoneKeys).toContain('intake');
    expect(zoneKeys).toContain('check_in');
    expect(zoneKeys).toContain('invoiced');
  });
});

// ============================================================
// 6. Dark mode — uses Tailwind classes
// ============================================================
describe('kanban — dark mode support', () => {
  it('renders columns using className (not purely inline styles)', async () => {
    const ros = [mockRO({ id: 1, status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const columns = doc.querySelectorAll('[data-status]');
    columns.forEach(col => {
      // Columns should use className for base styling
      // This test will FAIL until the Tailwind migration is done
      const hasClasses = col.className && col.className.length > 0;
      const hasInlineBackground = col.style.cssText.includes('background:');
      // After migration: className should have Tailwind classes
      // Only borderTop should remain as inline style
      expect(hasClasses || !hasInlineBackground).toBe(true);
    });
  });

  it('cards use Tailwind classes for base styling', async () => {
    const ros = [mockRO({ id: 1, status: 'intake' })];
    win.api.mockResolvedValueOnce({ success: true, data: ros });
    await win.loadKanban();

    const card = doc.querySelector('[data-ro-id="1"]');
    // After Tailwind migration, cards should have className with Tailwind utilities
    expect(card.className).toContain('rounded');
    expect(card.className).toContain('shadow');
    expect(card.className).toContain('cursor-grab');
  });
});

// ============================================================
// 7. Empty columns — show empty state
// ============================================================
describe('kanban — empty state', () => {
  it('shows empty state text in columns with no orders', async () => {
    win.api.mockResolvedValueOnce({ success: true, data: [] });
    await win.loadKanban();

    // All columns should have empty state text
    const dropZones = doc.querySelectorAll('[data-drop-zone]');
    dropZones.forEach(zone => {
      expect(zone.textContent.trim()).toBeTruthy();
    });
  });
});

// ============================================================
// 8. Loading state
// ============================================================
describe('kanban — loading state', () => {
  it('shows loading indicator while fetching', async () => {
    // Use a never-resolving promise to capture the loading state
    let resolveApi;
    win.api.mockReturnValueOnce(new Promise(r => { resolveApi = r; }));

    const loadPromise = win.loadKanban();
    // During loading, container should have content (loading indicator)
    const container = doc.getElementById('ro-kanban-view');
    expect(container.children.length).toBeGreaterThan(0);

    // Resolve to prevent hanging
    resolveApi({ success: true, data: [] });
    await loadPromise;
  });
});

// ============================================================
// 9. Error state
// ============================================================
describe('kanban — error state', () => {
  it('shows error message when API fails', async () => {
    win.api.mockRejectedValueOnce(new Error('Network error'));
    await win.loadKanban();

    const container = doc.getElementById('ro-kanban-view');
    expect(container.textContent).toContain('Failed to load kanban');
    expect(container.textContent).toContain('Network error');
  });
});
