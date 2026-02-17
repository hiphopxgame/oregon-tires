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
// 1. Translation infrastructure exists
// ============================================================
describe('admin translations — infrastructure', () => {
  it('admin HTML contains adminT translation object with en and es keys', () => {
    expect(html).toMatch(/const\s+adminT\s*=\s*\{[\s\S]*?en:\s*\{/);
    expect(html).toMatch(/const\s+adminT\s*=\s*\{[\s\S]*?es:\s*\{/);
  });

  it('en and es objects have the same number of keys', () => {
    // Extract en keys
    const enBlock = html.match(/adminT\s*=\s*\{[\s\S]*?en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(enBlock).not.toBeNull();
    const enKeys = [...enBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]);

    // Extract es keys
    const esBlock = html.match(/adminT\s*=\s*\{[\s\S]*?es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(esBlock).not.toBeNull();
    const esKeys = [...esBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]);

    expect(enKeys.length).toBe(esKeys.length);
    expect(enKeys.length).toBeGreaterThan(40);
  });

  it('no empty string values in en', () => {
    const enBlock = html.match(/adminT\s*=\s*\{[\s\S]*?en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const values = [...enBlock[1].matchAll(/\w+\s*:\s*'([^']*)'/g)].map(m => m[1]);
    for (const v of values) {
      expect(v, 'Found empty en value').not.toBe('');
    }
  });

  it('no empty string values in es', () => {
    const esBlock = html.match(/adminT\s*=\s*\{[\s\S]*?es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const values = [...esBlock[1].matchAll(/\w+\s*:\s*'([^']*)'/g)].map(m => m[1]);
    for (const v of values) {
      expect(v, 'Found empty es value').not.toBe('');
    }
  });

  it('en and es have matching key sets (parity)', () => {
    const enBlock = html.match(/adminT\s*=\s*\{[\s\S]*?en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const esBlock = html.match(/adminT\s*=\s*\{[\s\S]*?es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const enKeys = [...enBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]).sort();
    const esKeys = [...esBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]).sort();
    expect(enKeys).toEqual(esKeys);
  });

  it('toggleAdminLanguage function exists', () => {
    expect(html).toMatch(/function\s+toggleAdminLanguage\s*\(/);
  });

  it('language toggle button exists in admin header', () => {
    const btn = doc.getElementById('admin-lang-toggle');
    expect(btn).not.toBeNull();
  });
});

// ============================================================
// 2. Default language is Spanish
// ============================================================
describe('admin translations — default language', () => {
  it('currentLang variable defaults to es', () => {
    expect(html).toMatch(/let\s+currentLang\s*=\s*['"]es['"]/);
  });
});

// ============================================================
// 3. Static HTML elements have data-t attributes
// ============================================================
describe('admin translations — login screen data-t', () => {
  it('login heading has data-t', () => {
    const el = doc.querySelector('#login-screen h1[data-t]');
    expect(el).not.toBeNull();
  });

  it('login subtitle has data-t', () => {
    const el = doc.querySelector('#login-screen p[data-t]');
    expect(el).not.toBeNull();
  });

  it('login labels have data-t', () => {
    const labels = doc.querySelectorAll('#login-form label[data-t]');
    expect(labels.length).toBeGreaterThanOrEqual(2);
  });

  it('login button has data-t', () => {
    const btn = doc.getElementById('login-btn');
    expect(btn).not.toBeNull();
    expect(btn.hasAttribute('data-t')).toBe(true);
  });

  it('back to website link has data-t', () => {
    const link = doc.querySelector('#login-screen a[data-t]');
    expect(link).not.toBeNull();
  });
});

describe('admin translations — header data-t', () => {
  it('header title has data-t', () => {
    const header = doc.querySelector('#admin-dashboard header');
    expect(header).not.toBeNull();
    const h1 = header.querySelector('h1 span[data-t], h1[data-t]');
    expect(h1).not.toBeNull();
  });

  it('header subtitle has data-t', () => {
    const header = doc.querySelector('#admin-dashboard header');
    const p = header.querySelector('p[data-t]');
    expect(p).not.toBeNull();
  });
});

describe('admin translations — nav tabs data-t', () => {
  const tabs = [
    { tab: 'overview', key: 'tabOverview' },
    { tab: 'appointments', key: 'tabAppointments' },
    { tab: 'messages', key: 'tabMessages' },
    { tab: 'employees', key: 'tabEmployees' },
    { tab: 'gallery', key: 'tabGallery' },
    { tab: 'analytics', key: 'tabAnalytics' },
    { tab: 'docs', key: 'tabDocs' },
  ];

  for (const { tab, key } of tabs) {
    it(`${tab} tab button contains a span with data-t="${key}"`, () => {
      const btn = doc.querySelector(`button[data-tab="${tab}"]`);
      expect(btn).not.toBeNull();
      const span = btn.querySelector(`span[data-t="${key}"]`);
      expect(span, `Tab "${tab}" missing span[data-t="${key}"]`).not.toBeNull();
    });
  }
});

describe('admin translations — overview section data-t', () => {
  it('overview heading has data-t', () => {
    const h2 = doc.querySelector('#tab-overview h2[data-t]');
    expect(h2).not.toBeNull();
  });

  it('stat labels have data-t', () => {
    const stats = doc.querySelectorAll('#tab-overview [data-t]');
    expect(stats.length).toBeGreaterThanOrEqual(5);
  });
});

describe('admin translations — appointments section data-t', () => {
  it('appointments table headers have data-t', () => {
    const ths = doc.querySelectorAll('#tab-appointments th[data-t]');
    expect(ths.length).toBeGreaterThanOrEqual(5);
  });

  it('search placeholder input has data-t', () => {
    const input = doc.getElementById('appt-search');
    expect(input).not.toBeNull();
    expect(input.hasAttribute('data-t')).toBe(true);
  });

  it('calendar nav buttons have data-t', () => {
    const prevBtn = doc.querySelector('#tab-appointments button[data-t="prevDay"]');
    const nextBtn = doc.querySelector('#tab-appointments button[data-t="nextDay"]');
    expect(prevBtn).not.toBeNull();
    expect(nextBtn).not.toBeNull();
  });
});

describe('admin translations — messages section data-t', () => {
  it('messages heading has data-t', () => {
    const h2 = doc.querySelector('#tab-messages h2[data-t]');
    expect(h2).not.toBeNull();
  });

  it('messages table headers have data-t', () => {
    const ths = doc.querySelectorAll('#tab-messages th[data-t]');
    expect(ths.length).toBeGreaterThanOrEqual(3);
  });
});

describe('admin translations — employees section data-t', () => {
  it('employees heading has data-t', () => {
    const h2 = doc.querySelector('#tab-employees h2[data-t], #tab-employees h2 span[data-t]');
    expect(h2).not.toBeNull();
  });

  it('add employee form labels have data-t', () => {
    const labels = doc.querySelectorAll('#add-employee-form label[data-t]');
    expect(labels.length).toBeGreaterThanOrEqual(3);
  });

  it('save and cancel buttons have data-t', () => {
    const form = doc.getElementById('add-employee-form');
    expect(form).not.toBeNull();
    const saveBtns = form.querySelectorAll('button[data-t]');
    expect(saveBtns.length).toBeGreaterThanOrEqual(2);
  });
});

describe('admin translations — gallery section data-t', () => {
  it('gallery heading has data-t', () => {
    const h2 = doc.querySelector('#tab-gallery h2[data-t]');
    expect(h2).not.toBeNull();
  });

  it('gallery sub-tab buttons have data-t', () => {
    const galSub = doc.querySelector('#gal-sub-gallery span[data-t], #gal-sub-gallery[data-t]');
    const svcSub = doc.querySelector('#gal-sub-serviceimgs span[data-t], #gal-sub-serviceimgs[data-t]');
    expect(galSub).not.toBeNull();
    expect(svcSub).not.toBeNull();
  });
});

describe('admin translations — analytics section data-t', () => {
  it('analytics heading has data-t', () => {
    const h2 = doc.querySelector('#tab-analytics h2[data-t]');
    expect(h2).not.toBeNull();
  });
});

describe('admin translations — docs section data-t', () => {
  it('docs heading has data-t', () => {
    const h2 = doc.querySelector('#tab-docs h2[data-t], #tab-docs h2 span[data-t]');
    expect(h2).not.toBeNull();
  });

  it('docs sub-tab buttons have data-t', () => {
    const manual = doc.querySelector('#docs-view-manual span[data-t]');
    const features = doc.querySelector('#docs-view-features span[data-t]');
    const improvements = doc.querySelector('#docs-view-improvements span[data-t]');
    expect(manual).not.toBeNull();
    expect(features).not.toBeNull();
    expect(improvements).not.toBeNull();
  });
});

describe('admin translations — modals data-t', () => {
  it('notes modal title has data-t', () => {
    const h3 = doc.querySelector('#notes-modal h3[data-t]');
    expect(h3).not.toBeNull();
  });

  it('notes modal textarea has data-t for placeholder', () => {
    const textarea = doc.getElementById('notes-text');
    expect(textarea).not.toBeNull();
    expect(textarea.hasAttribute('data-t')).toBe(true);
  });

  it('notes modal buttons have data-t', () => {
    const btns = doc.querySelectorAll('#notes-modal button[data-t]');
    expect(btns.length).toBeGreaterThanOrEqual(2);
  });

  it('account settings modal title has data-t', () => {
    const h3 = doc.querySelector('#account-modal h3[data-t]');
    expect(h3).not.toBeNull();
  });

  it('account settings section headings have data-t', () => {
    const headings = doc.querySelectorAll('#account-modal h4[data-t]');
    expect(headings.length).toBeGreaterThanOrEqual(4);
  });
});

// ============================================================
// 4. Translation key coverage — every data-t has a key in both en and es
// ============================================================
describe('admin translations — key coverage', () => {
  let enKeys, esKeys;

  beforeAll(() => {
    const enBlock = html.match(/adminT\s*=\s*\{[\s\S]*?en:\s*\{([\s\S]*?)\},\s*\n\s*es:/);
    const esBlock = html.match(/adminT\s*=\s*\{[\s\S]*?es:\s*\{([\s\S]*?)\}\s*\n\s*\};/);
    enKeys = new Set([...enBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]));
    esKeys = new Set([...esBlock[1].matchAll(/(\w+)\s*:\s*['"]/g)].map(m => m[1]));
  });

  it('every data-t value has a key in en translations', () => {
    const elements = doc.querySelectorAll('[data-t]');
    const dataTKeys = new Set();
    elements.forEach(el => dataTKeys.add(el.getAttribute('data-t')));
    for (const key of dataTKeys) {
      expect(enKeys.has(key), `data-t="${key}" missing from adminT.en`).toBe(true);
    }
  });

  it('every data-t value has a key in es translations', () => {
    const elements = doc.querySelectorAll('[data-t]');
    const dataTKeys = new Set();
    elements.forEach(el => dataTKeys.add(el.getAttribute('data-t')));
    for (const key of dataTKeys) {
      expect(esKeys.has(key), `data-t="${key}" missing from adminT.es`).toBe(true);
    }
  });

  it('total data-t elements exceeds 50 (comprehensive coverage)', () => {
    const elements = doc.querySelectorAll('[data-t]');
    expect(elements.length).toBeGreaterThan(50);
  });
});

// ============================================================
// 5. Documentation content is NOT translated (no data-t)
// ============================================================
describe('admin translations — docs content NOT translated', () => {
  it('docs-subtab-manual content has no data-t attributes', () => {
    const manual = doc.getElementById('docs-subtab-manual');
    expect(manual).not.toBeNull();
    // The manual section headings inside the content should NOT have data-t
    const h2s = manual.querySelectorAll('h2[data-t], h3[data-t]');
    expect(h2s.length).toBe(0);
  });

  it('docs-subtab-features content has no data-t attributes', () => {
    const features = doc.getElementById('docs-subtab-features');
    expect(features).not.toBeNull();
    const dataTs = features.querySelectorAll('[data-t]');
    expect(dataTs.length).toBe(0);
  });

  it('docs-subtab-improvements content has no data-t attributes', () => {
    const improvements = doc.getElementById('docs-subtab-improvements');
    expect(improvements).not.toBeNull();
    const dataTs = improvements.querySelectorAll('[data-t]');
    expect(dataTs.length).toBe(0);
  });
});
