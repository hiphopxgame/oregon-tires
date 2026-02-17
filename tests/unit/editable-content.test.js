import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');
const mainHtml = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');

let adminDoc, mainDoc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const adminDom = new JSDOM(adminHtml);
  adminDoc = adminDom.window.document;
  const mainDom = new JSDOM(mainHtml);
  mainDoc = mainDom.window.document;
});

// =====================================================================
// ADMIN: Site Content tab exists in nav
// =====================================================================
describe('admin — Site Content tab in navigation', () => {
  it('has a Site Content tab button in the nav', () => {
    const btn = adminDoc.querySelector('[data-tab="sitecontent"]');
    expect(btn).not.toBeNull();
    expect(btn.textContent).toMatch(/site\s*content|contenido/i);
  });

  it('Site Content tab button calls switchTab("sitecontent")', () => {
    const btn = adminDoc.querySelector('[data-tab="sitecontent"]');
    const onclick = btn.getAttribute('onclick');
    expect(onclick).toContain("switchTab('sitecontent')");
  });

  it('has a tab-content div with id "tab-sitecontent"', () => {
    const tabDiv = adminDoc.getElementById('tab-sitecontent');
    expect(tabDiv).not.toBeNull();
    expect(tabDiv.classList.contains('tab-content')).toBe(true);
    expect(tabDiv.classList.contains('hidden')).toBe(true);
  });
});

// =====================================================================
// ADMIN: Site Content form fields
// =====================================================================
describe('admin — Site Content settings form', () => {
  const settingKeys = [
    'phone', 'email', 'address',
    'hours_weekday', 'hours_sunday',
    'rating_value', 'review_count'
  ];

  for (const key of settingKeys) {
    it(`has English input for setting "${key}"`, () => {
      const input = adminDoc.getElementById(`setting-${key}-en`);
      expect(input).not.toBeNull();
    });

    it(`has Spanish input for setting "${key}"`, () => {
      const input = adminDoc.getElementById(`setting-${key}-es`);
      expect(input).not.toBeNull();
    });
  }

  it('has a save button for site settings', () => {
    const btn = adminDoc.getElementById('save-site-settings');
    expect(btn).not.toBeNull();
  });

  it('has a last-updated display element', () => {
    const el = adminDoc.getElementById('settings-last-updated');
    expect(el).not.toBeNull();
  });
});

// =====================================================================
// ADMIN: Bilingual labels using adminT
// =====================================================================
describe('admin — Site Content translation keys', () => {
  it('adminT has tabSiteContent key in English', () => {
    expect(adminHtml).toContain('tabSiteContent');
  });

  it('adminT has siteContentHeading key in English', () => {
    expect(adminHtml).toContain('siteContentHeading');
  });

  it('adminT has saveSiteSettings key in English', () => {
    expect(adminHtml).toContain('saveSiteSettings');
  });

  it('adminT has settingPhone key for field labels', () => {
    expect(adminHtml).toContain('settingPhone');
  });

  it('adminT has settingEmail key for field labels', () => {
    expect(adminHtml).toContain('settingEmail');
  });

  it('adminT has settingAddress key for field labels', () => {
    expect(adminHtml).toContain('settingAddress');
  });

  it('adminT has settingHoursWeekday key for field labels', () => {
    expect(adminHtml).toContain('settingHoursWeekday');
  });

  it('adminT has settingHoursSunday key for field labels', () => {
    expect(adminHtml).toContain('settingHoursSunday');
  });
});

// =====================================================================
// ADMIN: switchTab handles sitecontent
// =====================================================================
describe('admin — switchTab handles sitecontent', () => {
  it('switchTab function body contains "sitecontent" handling or is generic enough', () => {
    // The generic switchTab function uses tab-{name} pattern, so it will work
    // as long as the tab-sitecontent div exists (tested above) and the button
    // has data-tab="sitecontent" (tested above)
    expect(adminHtml).toContain("tab-sitecontent");
  });
});

// =====================================================================
// ADMIN: JS function to load/save site settings
// =====================================================================
describe('admin — site settings JS functions', () => {
  it('has a loadSiteSettings function', () => {
    expect(adminHtml).toMatch(/function\s+loadSiteSettings/);
  });

  it('has a saveSiteSettings function', () => {
    expect(adminHtml).toMatch(/function\s+saveSiteSettings/);
  });

  it('references oretir_site_settings table', () => {
    expect(adminHtml).toContain('oretir_site_settings');
  });
});

// =====================================================================
// MAIN SITE: data-setting attributes on key elements
// =====================================================================
describe('main site — data-setting attributes', () => {
  it('has data-setting="phone" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="phone"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="email" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="email"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="address" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="address"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="hours_weekday" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="hours_weekday"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="hours_sunday" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="hours_sunday"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="rating_value" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="rating_value"]');
    expect(els.length).toBeGreaterThan(0);
  });

  it('has data-setting="review_count" element(s)', () => {
    const els = mainDoc.querySelectorAll('[data-setting="review_count"]');
    expect(els.length).toBeGreaterThan(0);
  });
});

// =====================================================================
// MAIN SITE: loadSiteSettings function
// =====================================================================
describe('main site — loadSiteSettings function', () => {
  it('has a loadSiteSettings function', () => {
    expect(mainHtml).toMatch(/function\s+loadSiteSettings/);
  });

  it('references oretir_site_settings table', () => {
    expect(mainHtml).toContain('oretir_site_settings');
  });

  it('uses data-setting attribute selector to update elements', () => {
    expect(mainHtml).toContain('data-setting');
  });

  it('loadSiteSettings is called on page load', () => {
    expect(mainHtml).toMatch(/loadSiteSettings\s*\(\s*\)/);
  });

  it('has fallback defaults for graceful degradation', () => {
    // Should contain default values so if Supabase fails, site still works
    expect(mainHtml).toContain('(503) 367-9714');
    expect(mainHtml).toContain('oregontirespdx@gmail.com');
  });
});

// =====================================================================
// MAIN SITE: loadSiteSettings respects currentLang
// =====================================================================
describe('main site — loadSiteSettings bilingual support', () => {
  it('uses value_en and value_es from settings', () => {
    expect(mainHtml).toContain('value_en');
    expect(mainHtml).toContain('value_es');
  });

  it('chooses value based on currentLang', () => {
    expect(mainHtml).toMatch(/currentLang.*value_en|value_en.*currentLang/s);
  });
});

// =====================================================================
// SQL migration reference (Supabase table schema)
// =====================================================================
describe('admin — Supabase table setup', () => {
  it('admin references setting_key field', () => {
    expect(adminHtml).toContain('setting_key');
  });

  it('admin references value_en and value_es fields', () => {
    expect(adminHtml).toContain('value_en');
    expect(adminHtml).toContain('value_es');
  });

  it('admin references updated_at field', () => {
    expect(adminHtml).toContain('updated_at');
  });
});
