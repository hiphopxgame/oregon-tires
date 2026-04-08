/**
 * Test helper: loads IIFE admin JS modules into a jsdom context with mocked globals.
 *
 * Usage:
 *   const { window, document, dom } = await loadModule('public_html/admin/js/kanban.js');
 *   await window.loadKanban();
 *   expect(document.querySelectorAll('[data-status]').length).toBe(12);
 */
import { readFileSync } from 'fs';
import { resolve } from 'path';
import { vi } from 'vitest';
import { JSDOM } from 'jsdom';

const PROJECT_ROOT = resolve(import.meta.dirname, '../../..');

/**
 * Load an IIFE module into a fresh jsdom window with pre-populated globals.
 *
 * @param {string} modulePath - Path relative to project root (e.g., 'public_html/admin/js/kanban.js')
 * @param {object} [options]
 * @param {string} [options.html] - Custom HTML body content
 * @param {object} [options.globals] - Extra globals to set on window before eval
 * @returns {{ window: Window, document: Document, dom: JSDOM }}
 */
export async function loadModule(modulePath, options = {}) {
  const html = options.html || '<!DOCTYPE html><html><head></head><body></body></html>';

  const dom = new JSDOM(html, {
    url: 'https://oregon.tires/admin/',
    runScripts: 'dangerously',
    pretendToBeVisual: true,
    resources: 'usable',
  });

  const win = dom.window;

  // ── Standard globals every IIFE module expects ──

  // CSRF token
  win.csrfToken = 'test-csrf-token-abc123';

  // Language
  win.currentLang = 'en';

  // Translation object
  win.adminT = {
    en: {},
    es: {},
  };

  // API base path
  win.API = '/api/admin';

  // Core API function (mock)
  win.api = vi.fn().mockResolvedValue({ success: true, data: [] });

  // Toast notification (mock)
  win.showToast = vi.fn();

  // RO detail viewer (mock)
  win.viewRoDetail = vi.fn();

  // Repair order loader (mock)
  win.loadRepairOrders = vi.fn();

  // BulkManager (mock)
  win.BulkManager = {
    init: vi.fn(),
    reset: vi.fn(),
    selectAllHtml: vi.fn(() => ''),
  };

  // Fetch (mock)
  win.fetch = vi.fn().mockResolvedValue({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ success: true, data: [] }),
    text: () => Promise.resolve('{"success":true,"data":[]}'),
  });

  // URLSearchParams (already in jsdom but ensure it's available)
  if (!win.URLSearchParams) {
    win.URLSearchParams = URLSearchParams;
  }

  // Console forwarding for debugging
  win.console = {
    log: vi.fn(),
    error: vi.fn(),
    warn: vi.fn(),
    info: vi.fn(),
  };

  // Apply caller-specified overrides
  if (options.globals) {
    Object.assign(win, options.globals);
  }

  // Read and execute the module
  const absPath = resolve(PROJECT_ROOT, modulePath);
  const code = readFileSync(absPath, 'utf-8');
  win.eval(code);

  return { window: win, document: win.document, dom };
}

/**
 * Create mock repair order data for testing.
 */
export function mockRO(overrides = {}) {
  return {
    id: 1,
    ro_number: 'RO-00000001',
    status: 'intake',
    first_name: 'John',
    last_name: 'Doe',
    vehicle_year: '2020',
    vehicle_make: 'Toyota',
    vehicle_model: 'Camry',
    updated_at: new Date(Date.now() - 3600000).toISOString(), // 1 hour ago
    active_labor_count: 0,
    inspection_count: 0,
    estimate_count: 0,
    ...overrides,
  };
}

/**
 * Create a batch of mock ROs across different statuses.
 */
export function mockROBatch() {
  return [
    mockRO({ id: 1, status: 'intake', ro_number: 'RO-00000001' }),
    mockRO({ id: 2, status: 'check_in', ro_number: 'RO-00000002', first_name: 'Jane' }),
    mockRO({ id: 3, status: 'diagnosis', ro_number: 'RO-00000003', first_name: 'Bob', estimate_count: 1 }),
    mockRO({ id: 4, status: 'in_progress', ro_number: 'RO-00000004', first_name: 'Alice', active_labor_count: 2 }),
    mockRO({ id: 5, status: 'ready', ro_number: 'RO-00000005', first_name: 'Charlie' }),
    mockRO({ id: 6, status: 'cancelled', ro_number: 'RO-00000006', first_name: 'Cancelled' }),
  ];
}
