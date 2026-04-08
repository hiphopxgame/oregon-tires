import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');
const kanbanJs = readFileSync(resolve(ROOT, 'admin/js/kanban.js'), 'utf-8');

// ============================================================
// 1. Lazy loading covers all tabs
// ============================================================
describe('performance — lazy loading', () => {
  it('loadedTabs set is initialized with overview', () => {
    expect(html).toContain("var loadedTabs = new Set(['overview'])");
  });

  it('lazy loading switch covers 20+ tabs', () => {
    // Count case statements in the lazy loading switch
    const lazyBlock = html.match(/LAZY LOAD TAB CONTENT[\s\S]*?_origSwitchTab2\(tab\)/);
    expect(lazyBlock).not.toBeNull();
    const caseCount = (lazyBlock[0].match(/case '/g) || []).length;
    expect(caseCount).toBeGreaterThanOrEqual(20);
  });
});

// ============================================================
// 2. Kanban auto-refresh
// ============================================================
describe('performance — kanban auto-refresh', () => {
  it('kanban has auto-refresh interval logic', () => {
    expect(kanbanJs).toContain('autoRefreshInterval');
    expect(kanbanJs).toContain('setInterval');
  });

  it('auto-refresh starts when kanban is toggled on', () => {
    expect(kanbanJs).toContain('startAutoRefresh');
  });

  it('auto-refresh stops when kanban is toggled off', () => {
    expect(kanbanJs).toContain('stopAutoRefresh');
  });

  it('auto-refresh checks document visibility', () => {
    expect(kanbanJs).toContain('visibilityState');
  });

  it('auto-refresh interval is ~60 seconds', () => {
    expect(kanbanJs).toContain('60000');
  });
});

// ============================================================
// 3. Debounce utility
// ============================================================
describe('performance — debounce', () => {
  it('debounce function exists', () => {
    expect(html).toContain('function debounce');
  });

  it('search inputs use debounce', () => {
    expect(html).toContain("debounce(function() { renderAppointments(); }");
    expect(html).toContain("debounce(function() { renderCustomers(); }");
  });
});

// ============================================================
// 4. Tab transitions
// ============================================================
describe('performance — tab transitions', () => {
  it('tab content has enter animation', () => {
    expect(html).toContain('tab-entering');
    expect(html).toContain('tab-visible');
  });

  it('switchTab uses requestAnimationFrame for smooth transitions', () => {
    expect(html).toContain('requestAnimationFrame');
  });
});

// ============================================================
// 5. Kanban skeleton loading (not text)
// ============================================================
describe('performance — kanban loading state', () => {
  it('kanban uses skeleton class for loading (not plain text)', () => {
    expect(kanbanJs).toContain('skeleton');
  });

  it('kanban loading creates multiple skeleton columns', () => {
    // Should create multiple skeleton shimmers
    expect(kanbanJs).toContain("i < 6");
  });
});
