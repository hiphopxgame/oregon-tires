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
// 1. showSkeleton utility
// ============================================================
describe('admin utilities — showSkeleton', () => {
  it('showSkeleton function is defined in the page script', () => {
    expect(html).toContain('function showSkeleton');
  });

  it('showSkeleton handles table type', () => {
    expect(html).toContain("type === 'table'");
  });

  it('showSkeleton handles cards type', () => {
    expect(html).toContain("type === 'cards'");
  });

  it('showSkeleton handles kanban type', () => {
    expect(html).toContain("type === 'kanban'");
  });

  it('uses the .skeleton CSS class', () => {
    // The showSkeleton function should use the skeleton class for shimmer
    expect(html).toMatch(/showSkeleton[\s\S]*?skeleton/);
  });
});

// ============================================================
// 2. validateForm utility
// ============================================================
describe('admin utilities — form validation', () => {
  it('validateForm function is defined', () => {
    expect(html).toContain('function validateForm');
  });

  it('showFieldError function is defined', () => {
    expect(html).toContain('function showFieldError');
  });

  it('clearFieldError function is defined', () => {
    expect(html).toContain('function clearFieldError');
  });

  it('showFieldError sets aria-invalid attribute', () => {
    expect(html).toContain('aria-invalid');
  });

  it('showFieldError sets aria-describedby for error linkage', () => {
    expect(html).toContain('aria-describedby');
  });

  it('validation error uses red styling class', () => {
    // Should apply red border or text color for errors
    expect(html).toMatch(/showFieldError[\s\S]*?(border-red|text-red)/);
  });
});

// ============================================================
// 3. Skeleton CSS exists
// ============================================================
describe('admin — skeleton CSS', () => {
  it('has .skeleton CSS class with animation', () => {
    expect(html).toContain('.skeleton');
    expect(html).toContain('skeletonPulse');
  });

  it('has dark mode skeleton variant', () => {
    expect(html).toContain('.dark .skeleton');
  });
});

// ============================================================
// 4. Lazy loading covers all tabs
// ============================================================
describe('admin — lazy loading', () => {
  it('lazy loading set is initialized', () => {
    expect(html).toContain('loadedTabs');
  });

  it('switchTab or lazy loading handles all major tabs', () => {
    const tabLoaders = [
      'appointments', 'customers', 'messages', 'employees',
      'blog', 'faq', 'promotions', 'reviews', 'subscribers',
      'invoices', 'labor', 'loyalty', 'referrals', 'reminders',
      'services', 'parts', 'surveys', 'gallery',
    ];
    tabLoaders.forEach(tab => {
      // Tab should have a loader in either switchTab (if-chain) or lazy loading (switch/case)
      const ifPattern = new RegExp(`tab==='${tab}'.*?\\bload|tab==='${tab}'.*?\\brender`, 's');
      const casePattern = new RegExp(`case '${tab}'.*?\\bload|case '${tab}'.*?\\brender`, 's');
      const found = ifPattern.test(html) || casePattern.test(html);
      expect(found, `Tab "${tab}" should have a loader in switchTab or lazy loading`).toBe(true);
    });
  });
});
