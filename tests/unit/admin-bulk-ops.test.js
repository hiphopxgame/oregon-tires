import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ============================================================
// 1. Bulk selection UI — checkbox in table header
// ============================================================
describe('bulk ops — select-all checkbox in table header', () => {
  it('appointments table header has a select-all checkbox', () => {
    const cb = doc.getElementById('select-all-appts');
    expect(cb).not.toBeNull();
    expect(cb.tagName).toBe('INPUT');
    expect(cb.getAttribute('type')).toBe('checkbox');
  });

  it('select-all checkbox is inside a <th> element', () => {
    const cb = doc.getElementById('select-all-appts');
    expect(cb.closest('th')).not.toBeNull();
  });

  it('select-all checkbox calls toggleSelectAll on change', () => {
    const cb = doc.getElementById('select-all-appts');
    const onchange = cb.getAttribute('onchange');
    expect(onchange).toMatch(/toggleSelectAll/);
  });
});

// ============================================================
// 2. Bulk action toolbar
// ============================================================
describe('bulk ops — toolbar element', () => {
  it('bulk toolbar exists with id="bulk-toolbar"', () => {
    const toolbar = doc.getElementById('bulk-toolbar');
    expect(toolbar).not.toBeNull();
  });

  it('bulk toolbar is hidden by default', () => {
    const toolbar = doc.getElementById('bulk-toolbar');
    expect(toolbar.classList.contains('hidden')).toBe(true);
  });

  it('bulk toolbar has a selected count element', () => {
    const countEl = doc.getElementById('bulk-count');
    expect(countEl).not.toBeNull();
  });
});

// ============================================================
// 3. Bulk action buttons / controls
// ============================================================
describe('bulk ops — toolbar controls', () => {
  it('has a bulk status dropdown (id="bulk-status")', () => {
    const sel = doc.getElementById('bulk-status');
    expect(sel).not.toBeNull();
    expect(sel.tagName).toBe('SELECT');
  });

  it('bulk status dropdown has status options', () => {
    const sel = doc.getElementById('bulk-status');
    const options = sel.querySelectorAll('option');
    expect(options.length).toBeGreaterThanOrEqual(4);
  });

  it('has an apply-status button calling applyBulkStatus()', () => {
    const btn = doc.querySelector('#bulk-toolbar button[onclick*="applyBulkStatus"]');
    expect(btn).not.toBeNull();
  });

  it('has a bulk employee dropdown (id="bulk-employee")', () => {
    const sel = doc.getElementById('bulk-employee');
    expect(sel).not.toBeNull();
    expect(sel.tagName).toBe('SELECT');
  });

  it('has an apply-assign button calling applyBulkAssign()', () => {
    const btn = doc.querySelector('#bulk-toolbar button[onclick*="applyBulkAssign"]');
    expect(btn).not.toBeNull();
  });

  it('has an Export CSV button calling exportAppointmentsCsv()', () => {
    const btn = doc.querySelector('#bulk-toolbar button[onclick*="exportAppointmentsCsv"]');
    expect(btn).not.toBeNull();
  });
});

// ============================================================
// 4. Bulk operation JS functions exist
// ============================================================
describe('bulk ops — JS functions defined', () => {
  it('toggleSelectAll function exists', () => {
    expect(html).toMatch(/function\s+toggleSelectAll\s*\(/);
  });

  it('updateBulkToolbar function exists', () => {
    expect(html).toMatch(/function\s+updateBulkToolbar\s*\(/);
  });

  it('applyBulkStatus function exists', () => {
    expect(html).toMatch(/function\s+applyBulkStatus\s*\(/);
  });

  it('applyBulkAssign function exists', () => {
    expect(html).toMatch(/function\s+applyBulkAssign\s*\(/);
  });

  it('exportAppointmentsCsv function exists', () => {
    expect(html).toMatch(/function\s+exportAppointmentsCsv\s*\(/);
  });
});

// ============================================================
// 5. renderAppointments generates row checkboxes
// ============================================================
describe('bulk ops — row checkboxes in renderAppointments', () => {
  it('renderAppointments template includes appt-checkbox class', () => {
    expect(html).toMatch(/appt-checkbox/);
  });

  it('renderAppointments template includes updateBulkToolbar onchange', () => {
    // The row checkbox should call updateBulkToolbar when toggled
    expect(html).toMatch(/appt-checkbox[\s\S]*?onchange.*updateBulkToolbar|updateBulkToolbar[\s\S]*?appt-checkbox/);
  });

  it('empty state colspan accounts for checkbox column (8 columns)', () => {
    expect(html).toMatch(/colspan="8"/);
  });
});

// ============================================================
// 6. Translation keys for bulk operations
// ============================================================
describe('bulk ops — translation keys', () => {
  let enBlock, esBlock;

  beforeAll(() => {
    const enMatch = html.match(/adminT\s*=\s*\{[\s\S]*?en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const esMatch = html.match(/adminT\s*=\s*\{[\s\S]*?es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    enBlock = enMatch ? enMatch[1] : '';
    esBlock = esMatch ? esMatch[1] : '';
  });

  const requiredKeys = [
    'selectAll',
    'selected',
    'changeStatus',
    'applyStatus',
    'assignEmployee',
    'applyAssign',
    'exportCsv',
    'selectStatus',
    'selectEmployee',
    'appointmentsUpdated',
    'appointmentsAssigned',
  ];

  for (const key of requiredKeys) {
    it(`en has "${key}" translation key`, () => {
      expect(enBlock).toMatch(new RegExp(`${key}\\s*:`));
    });

    it(`es has "${key}" translation key`, () => {
      expect(esBlock).toMatch(new RegExp(`${key}\\s*:`));
    });
  }
});

// ============================================================
// 7. Toolbar is inside the list view section (not calendar)
// ============================================================
describe('bulk ops — placement', () => {
  it('bulk toolbar is inside the list view container', () => {
    const listView = doc.getElementById('appt-subtab-list');
    expect(listView).not.toBeNull();
    const toolbar = listView.querySelector('#bulk-toolbar');
    expect(toolbar).not.toBeNull();
  });

  it('bulk toolbar is NOT inside the calendar view', () => {
    const calView = doc.getElementById('appt-subtab-calendar');
    expect(calView).not.toBeNull();
    const toolbar = calView.querySelector('#bulk-toolbar');
    expect(toolbar).toBeNull();
  });
});
