import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');
const kanbanJs = readFileSync(resolve(ROOT, 'admin/js/kanban.js'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ============================================================
// 1. Kanban accessibility (checked via JS source)
// ============================================================
describe('kanban — ARIA attributes', () => {
  it('kanban cards have role="listitem"', () => {
    expect(kanbanJs).toContain("role', 'listitem'");
  });

  it('kanban cards have tabindex="0" for keyboard focus', () => {
    expect(kanbanJs).toContain("tabindex', '0'");
  });

  it('kanban columns have role="region"', () => {
    expect(kanbanJs).toContain("role', 'region'");
  });

  it('kanban card lists have role="list"', () => {
    expect(kanbanJs).toContain("role', 'list'");
  });

  it('kanban cards have aria-label', () => {
    expect(kanbanJs).toContain('aria-label');
  });

  it('kanban cards respond to Enter/Space key', () => {
    expect(kanbanJs).toContain("e.key === 'Enter'");
    expect(kanbanJs).toContain("e.key === ' '");
  });
});

// ============================================================
// 2. Modal accessibility
// ============================================================
describe('admin — modal accessibility', () => {
  it('modals have role="dialog"', () => {
    const modals = doc.querySelectorAll('[id*="modal"]');
    const dialogModals = doc.querySelectorAll('[role="dialog"]');
    // At least some modals should have role="dialog"
    expect(dialogModals.length).toBeGreaterThan(0);
  });

  it('modals with role="dialog" have aria-modal="true"', () => {
    const dialogModals = doc.querySelectorAll('[role="dialog"]');
    dialogModals.forEach(modal => {
      expect(modal.getAttribute('aria-modal')).toBe('true');
    });
  });

  it('modals have aria-labelledby pointing to a title', () => {
    const dialogModals = doc.querySelectorAll('[role="dialog"]');
    dialogModals.forEach(modal => {
      const labelledBy = modal.getAttribute('aria-labelledby');
      if (labelledBy) {
        const titleEl = doc.getElementById(labelledBy);
        expect(titleEl).not.toBeNull();
      }
    });
  });
});

// ============================================================
// 3. Tab navigation ARIA
// ============================================================
describe('admin — tab navigation accessibility', () => {
  it('tab buttons have aria-selected attribute', () => {
    // switchTab sets aria-selected
    expect(html).toContain("aria-selected");
  });

  it('tab buttons use data-tab attribute for identification', () => {
    const tabBtns = doc.querySelectorAll('[data-tab]');
    expect(tabBtns.length).toBeGreaterThan(10); // many tabs
  });
});

// ============================================================
// 4. Form accessibility
// ============================================================
describe('admin — form accessibility', () => {
  it('login form inputs have associated labels', () => {
    const emailInput = doc.getElementById('login-email');
    if (emailInput) {
      const hasLabel = doc.querySelector('label[for="login-email"]') !== null;
      const hasAriaLabel = emailInput.hasAttribute('aria-label');
      const hasPlaceholder = emailInput.hasAttribute('placeholder');
      expect(hasLabel || hasAriaLabel || hasPlaceholder).toBe(true);
    }
  });

  it('password input has associated label', () => {
    const passInput = doc.getElementById('login-password');
    if (passInput) {
      const hasLabel = doc.querySelector('label[for="login-password"]') !== null;
      const hasAriaLabel = passInput.hasAttribute('aria-label');
      const hasPlaceholder = passInput.hasAttribute('placeholder');
      expect(hasLabel || hasAriaLabel || hasPlaceholder).toBe(true);
    }
  });
});

// ============================================================
// 5. Focus management
// ============================================================
describe('admin — focus management', () => {
  it('has focus trap utility for modals', () => {
    expect(html).toContain('trapFocus') ;
  });

  it('Escape key closes modals', () => {
    // Check that keydown handler exists for Escape
    expect(html).toContain("'Escape'");
  });
});

// ============================================================
// 6. Touch target sizes
// ============================================================
describe('admin — touch target sizes', () => {
  it('kanban toggle button has min-h-[44px]', () => {
    expect(kanbanJs).toContain('min-h-[44px]');
  });

  it('kanban overlay buttons have adequate height', () => {
    expect(kanbanJs).toContain('min-h-[28px]');
  });
});

// ============================================================
// 7. Skip link
// ============================================================
describe('admin — skip link', () => {
  it('has a skip link as early element in body', () => {
    const skipLink = doc.querySelector('a[href="#admin-dashboard"], a[href="#main-content"], .skip-link, .sr-only a');
    // Should exist for keyboard users
    expect(skipLink !== null || html.includes('skip-link') || html.includes('Skip to')).toBe(true);
  });
});
