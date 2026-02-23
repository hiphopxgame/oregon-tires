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
// #6 — CSS Loaded
// ============================================================
describe('admin — CSS loaded (#6)', () => {
  it('Tailwind CSS stylesheet is loaded (built at compile time)', () => {
    // Tailwind CSS v4 is built during the build process, not loaded from CDN
    const stylesheets = doc.querySelectorAll('link[rel="stylesheet"]');
    expect(stylesheets.length, 'No stylesheets found').toBeGreaterThan(0);
  });
});

// ============================================================
// #7 — ARIA Accessibility: Login Form
// ============================================================
describe('admin — ARIA: login form (#7)', () => {
  it('login form is a <form> element or has role="form"', () => {
    const form = doc.getElementById('login-form');
    expect(form).not.toBeNull();
    const isForm = form.tagName === 'FORM' || form.getAttribute('role') === 'form';
    expect(isForm).toBe(true);
  });

  it('email input has associated <label> with for attribute or aria-label', () => {
    const input = doc.getElementById('login-email');
    expect(input).not.toBeNull();
    const label = doc.querySelector('label[for="login-email"]');
    const ariaLabel = input.getAttribute('aria-label');
    expect(label || ariaLabel, 'email input has no label or aria-label').toBeTruthy();
  });

  it('password input has associated <label> with for attribute or aria-label', () => {
    const input = doc.getElementById('login-password');
    expect(input).not.toBeNull();
    const label = doc.querySelector('label[for="login-password"]');
    const ariaLabel = input.getAttribute('aria-label');
    expect(label || ariaLabel, 'password input has no label or aria-label').toBeTruthy();
  });
});

// ============================================================
// #7 — ARIA Accessibility: Modals
// ============================================================
describe('admin — ARIA: modals (#7)', () => {
  it('notes-modal has role="dialog"', () => {
    const modal = doc.getElementById('notes-modal');
    expect(modal).not.toBeNull();
    expect(modal.getAttribute('role')).toBe('dialog');
  });

  it('notes-modal has aria-modal="true"', () => {
    const modal = doc.getElementById('notes-modal');
    expect(modal.getAttribute('aria-modal')).toBe('true');
  });

  it('notes-modal has aria-labelledby pointing to its title', () => {
    const modal = doc.getElementById('notes-modal');
    const labelledBy = modal.getAttribute('aria-labelledby');
    expect(labelledBy).toBe('notes-modal-title');
    const title = doc.getElementById('notes-modal-title');
    expect(title, 'notes-modal-title element not found').not.toBeNull();
  });

  it('account-modal has role="dialog"', () => {
    const modal = doc.getElementById('account-modal');
    expect(modal).not.toBeNull();
    expect(modal.getAttribute('role')).toBe('dialog');
  });

  it('account-modal has aria-modal="true"', () => {
    const modal = doc.getElementById('account-modal');
    expect(modal.getAttribute('aria-modal')).toBe('true');
  });

  it('account-modal has aria-labelledby pointing to its title', () => {
    const modal = doc.getElementById('account-modal');
    const labelledBy = modal.getAttribute('aria-labelledby');
    expect(labelledBy).toBe('account-modal-title');
    const title = doc.getElementById('account-modal-title');
    expect(title, 'account-modal-title element not found').not.toBeNull();
  });
});

// ============================================================
// #7 — ARIA Accessibility: Interactive Elements
// ============================================================
describe('admin — ARIA: interactive elements (#7)', () => {
  it('language toggle button has aria-label', () => {
    const btn = doc.getElementById('admin-lang-toggle');
    expect(btn).not.toBeNull();
    expect(btn.getAttribute('aria-label')).toBeTruthy();
  });

  it('close buttons on modals have aria-label="Close"', () => {
    const notesClose = doc.querySelector('#notes-modal button[aria-label="Close"]');
    expect(notesClose, 'notes-modal close button missing aria-label="Close"').not.toBeNull();
    const accountClose = doc.querySelector('#account-modal button[aria-label="Close"]');
    expect(accountClose, 'account-modal close button missing aria-label="Close"').not.toBeNull();
  });

  it('tab buttons have role="tab"', () => {
    const tabs = doc.querySelectorAll('#nav-tabs .tab-btn');
    expect(tabs.length).toBeGreaterThan(0);
    tabs.forEach(tab => {
      expect(tab.getAttribute('role'), `tab "${tab.textContent.trim()}" missing role="tab"`).toBe('tab');
    });
  });

  it('active tab has aria-selected="true", others have "false"', () => {
    const tabs = doc.querySelectorAll('#nav-tabs .tab-btn');
    const activeTabs = [...tabs].filter(t => t.classList.contains('tab-active'));
    const inactiveTabs = [...tabs].filter(t => !t.classList.contains('tab-active'));
    expect(activeTabs.length).toBeGreaterThan(0);
    activeTabs.forEach(tab => {
      expect(tab.getAttribute('aria-selected')).toBe('true');
    });
    inactiveTabs.forEach(tab => {
      expect(tab.getAttribute('aria-selected')).toBe('false');
    });
  });
});

// ============================================================
// #7 — ARIA Accessibility: Keyboard Support
// ============================================================
describe('admin — ARIA: keyboard support (#7)', () => {
  it('has Escape key handler for modals', () => {
    // The script should contain a keydown listener checking for Escape
    expect(html).toMatch(/addEventListener\s*\(\s*['"]keydown['"]/);
    expect(html).toMatch(/['"]Escape['"]/);
  });
});
