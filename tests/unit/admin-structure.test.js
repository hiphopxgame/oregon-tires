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

describe('admin — page basics', () => {
  it('has DOCTYPE html', () => {
    expect(html).toMatch(/^<!DOCTYPE html>/i);
  });

  it('has title containing "Oregon Tires Admin"', () => {
    const title = doc.querySelector('title');
    expect(title).not.toBeNull();
    expect(title.textContent).toContain('Oregon Tires Admin');
  });
});

describe('admin — login form', () => {
  it('login form exists', () => {
    const form = doc.getElementById('login-form');
    expect(form).not.toBeNull();
    expect(form.tagName).toBe('FORM');
  });

  it('has email input', () => {
    const input = doc.getElementById('login-email');
    expect(input).not.toBeNull();
    expect(input.getAttribute('type')).toBe('email');
  });

  it('has password input', () => {
    const input = doc.getElementById('login-password');
    expect(input).not.toBeNull();
    expect(input.getAttribute('type')).toBe('password');
  });

  it('Sign In button exists', () => {
    const btn = doc.getElementById('login-btn');
    expect(btn).not.toBeNull();
    expect(btn.textContent).toContain('Sign In');
  });
});

describe('admin — dashboard visibility', () => {
  it('dashboard div exists and is hidden by default', () => {
    const dashboard = doc.getElementById('admin-dashboard');
    expect(dashboard).not.toBeNull();
    expect(dashboard.classList.contains('hidden')).toBe(true);
  });
});

describe('admin — back to website link', () => {
  it('has "Back to website" link', () => {
    const link = doc.querySelector('a[href="/"]');
    expect(link).not.toBeNull();
    expect(link.textContent.toLowerCase()).toMatch(/back to website|view site/i);
  });
});

describe('admin — logo', () => {
  it('logo exists with relative path ../assets/logo.png', () => {
    const logos = doc.querySelectorAll('img[src="../assets/logo.png"]');
    expect(logos.length).toBeGreaterThan(0);
  });
});

describe('admin — tab navigation', () => {
  const expectedTabs = ['overview', 'appointments', 'messages', 'employees', 'gallery', 'analytics'];

  for (const tab of expectedTabs) {
    it(`has tab button for "${tab}"`, () => {
      const btn = doc.querySelector(`button[data-tab="${tab}"]`);
      expect(btn, `tab button for "${tab}" not found`).not.toBeNull();
    });
  }
});

// Supabase is not used — backend is PHP with MySQL
// Removed obsolete Supabase test

describe('admin — core functions', () => {
  it('has signOut function defined', () => {
    expect(html).toMatch(/function\s+signOut\s*\(/);
  });

  it('has showToast function defined', () => {
    expect(html).toMatch(/function\s+showToast\s*\(/);
  });
});

describe('admin — modals', () => {
  it('has notes-modal element', () => {
    const modal = doc.getElementById('notes-modal');
    expect(modal).not.toBeNull();
  });

  it('has account-modal element', () => {
    const modal = doc.getElementById('account-modal');
    expect(modal).not.toBeNull();
  });
});
