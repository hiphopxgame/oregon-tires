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
// 1. Dark mode infrastructure
// ============================================================
describe('admin dark mode — infrastructure', () => {
  it('Tailwind config includes darkMode: class', () => {
    expect(html).toMatch(/tailwind\.config\s*=\s*\{[\s\S]*?darkMode:\s*['"]class['"]/);
  });

  it('dark mode toggle button exists with id admin-dark-toggle', () => {
    const btn = doc.getElementById('admin-dark-toggle');
    expect(btn).not.toBeNull();
  });

  it('toggleAdminDarkMode function exists', () => {
    expect(html).toMatch(/function\s+toggleAdminDarkMode\s*\(/);
  });

  it('localStorage is used to save theme preference', () => {
    expect(html).toMatch(/localStorage\.\s*setItem\s*\(\s*['"]theme['"]/);
  });

  it('localStorage is read for saved theme preference', () => {
    expect(html).toMatch(/localStorage\.\s*getItem\s*\(\s*['"]theme['"]/);
  });

  it('system preference detection exists (prefers-color-scheme)', () => {
    expect(html).toMatch(/prefers-color-scheme:\s*dark/);
  });
});

// ============================================================
// 2. Dark mode class coverage
// ============================================================
describe('admin dark mode — class coverage', () => {
  it('<body> has dark:bg-gray-900 class', () => {
    const body = doc.querySelector('body');
    expect(body.classList.contains('dark:bg-gray-900')).toBe(true);
  });

  it('<body> has dark:text-gray-100 class', () => {
    const body = doc.querySelector('body');
    expect(body.classList.contains('dark:text-gray-100')).toBe(true);
  });

  it('login container has dark background class', () => {
    // The white login card: div.bg-white inside #login-screen
    const loginCard = doc.querySelector('#login-screen .bg-white');
    expect(loginCard).not.toBeNull();
    expect(loginCard.classList.contains('dark:bg-gray-800')).toBe(true);
  });

  it('at least one dark:bg-gray-800 class exists in the HTML', () => {
    expect(html).toMatch(/dark:bg-gray-800/);
  });

  it('at least one dark:text-gray-100 class exists in the HTML', () => {
    expect(html).toMatch(/dark:text-gray-100/);
  });

  it('dashboard stat cards have dark mode classes', () => {
    // Stat cards are in #tab-overview > .grid > .bg-white divs
    const statCards = doc.querySelectorAll('#tab-overview .bg-white.rounded-xl');
    expect(statCards.length).toBeGreaterThan(0);
    let hasDark = false;
    statCards.forEach(card => {
      if (card.classList.contains('dark:bg-gray-800')) hasDark = true;
    });
    expect(hasDark).toBe(true);
  });

  it('table headers have dark mode classes', () => {
    expect(html).toMatch(/bg-yellow-100[^"]*dark:bg-gray-700/);
  });

  it('form inputs have dark mode classes', () => {
    // Check that at least some inputs in the admin have dark mode classes
    const inputs = doc.querySelectorAll('#login-form input');
    let hasDarkInput = false;
    inputs.forEach(input => {
      if (input.classList.contains('dark:bg-gray-700')) hasDarkInput = true;
    });
    expect(hasDarkInput).toBe(true);
  });

  it('notes modal inner div has dark mode class', () => {
    const notesInner = doc.querySelector('#notes-modal .bg-white');
    expect(notesInner).not.toBeNull();
    expect(notesInner.classList.contains('dark:bg-gray-800')).toBe(true);
  });

  it('account modal inner div has dark mode class', () => {
    const accountInner = doc.querySelector('#account-modal .bg-white');
    expect(accountInner).not.toBeNull();
    expect(accountInner.classList.contains('dark:bg-gray-800')).toBe(true);
  });
});

// ============================================================
// 3. Translation keys for dark mode
// ============================================================
describe('admin dark mode — translation keys', () => {
  it('adminT.en has darkMode key', () => {
    expect(html).toMatch(/en:\s*\{[\s\S]*?darkMode:\s*'/);
  });

  it('adminT.en has lightMode key', () => {
    expect(html).toMatch(/en:\s*\{[\s\S]*?lightMode:\s*'/);
  });

  it('adminT.es has darkMode key', () => {
    expect(html).toMatch(/es:\s*\{[\s\S]*?darkMode:\s*'/);
  });

  it('adminT.es has lightMode key', () => {
    expect(html).toMatch(/es:\s*\{[\s\S]*?lightMode:\s*'/);
  });
});
