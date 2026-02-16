import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

describe('bilingual — language toggle', () => {
  it('language toggle button #lang-toggle exists', () => {
    const btn = doc.getElementById('lang-toggle');
    expect(btn).not.toBeNull();
  });

  it('default language display shows "ES" (offering to switch to Spanish)', () => {
    const btn = doc.getElementById('lang-toggle');
    expect(btn.textContent).toContain('ES');
  });

  it('footer also has a language toggle', () => {
    const footer = doc.querySelector('footer');
    expect(footer).not.toBeNull();
    const toggleBtn = footer.querySelector('button');
    expect(toggleBtn).not.toBeNull();
    expect(toggleBtn.textContent).toMatch(/English|Español/);
  });
});

describe('bilingual — toggleLanguage function', () => {
  it('toggleLanguage function is defined in the script', () => {
    expect(html).toMatch(/function\s+toggleLanguage\s*\(/);
  });
});

describe('bilingual — translation object', () => {
  let enKeys;
  let esKeys;

  beforeAll(() => {
    // Extract the translation object from the inline script
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    expect(tBlockMatch).not.toBeNull();

    // Extract en keys — match property keys that precede a quoted value (key: 'value')
    const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(enMatch).not.toBeNull();
    enKeys = [...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);

    // Extract es keys — match property keys that precede a quoted value (key: 'value')
    const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(esMatch).not.toBeNull();
    esKeys = [...esMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);
  });

  it('translation object "t" has "en" key', () => {
    expect(html).toMatch(/const\s+t\s*=\s*\{[\s\S]*?en:\s*\{/);
  });

  it('translation object "t" has "es" key', () => {
    expect(html).toMatch(/const\s+t\s*=\s*\{[\s\S]*?es:\s*\{/);
  });

  it('en and es translation keys match (parity)', () => {
    const sortedEn = [...enKeys].sort();
    const sortedEs = [...esKeys].sort();
    expect(sortedEn).toEqual(sortedEs);
  });
});

describe('bilingual — data-t coverage', () => {
  it('all elements with data-t attribute have corresponding keys in en translations', () => {
    const elements = doc.querySelectorAll('[data-t]');
    const dataTKeys = new Set();
    elements.forEach(el => dataTKeys.add(el.getAttribute('data-t')));

    // Extract en keys from inline script
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const enKeys = new Set([...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]));

    for (const key of dataTKeys) {
      expect(enKeys.has(key), `data-t="${key}" missing from en translations`).toBe(true);
    }
  });

  it('all elements with data-t attribute have corresponding keys in es translations', () => {
    const elements = doc.querySelectorAll('[data-t]');
    const dataTKeys = new Set();
    elements.forEach(el => dataTKeys.add(el.getAttribute('data-t')));

    // Extract es keys from inline script
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const esKeys = new Set([...esMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]));

    for (const key of dataTKeys) {
      expect(esKeys.has(key), `data-t="${key}" missing from es translations`).toBe(true);
    }
  });

  it('count of data-t elements is > 30 (comprehensive coverage)', () => {
    const elements = doc.querySelectorAll('[data-t]');
    expect(elements.length).toBeGreaterThan(30);
  });
});

describe('bilingual — data-t usage in key sections', () => {
  it('contact form labels use data-t attributes', () => {
    const form = doc.getElementById('contact-form');
    expect(form).not.toBeNull();
    const labels = form.querySelectorAll('label[data-t]');
    expect(labels.length).toBeGreaterThanOrEqual(4);
  });

  it('service section headings use data-t attributes', () => {
    const services = doc.getElementById('services');
    expect(services).not.toBeNull();
    const headings = services.querySelectorAll('[data-t]');
    expect(headings.length).toBeGreaterThan(10);
  });
});
