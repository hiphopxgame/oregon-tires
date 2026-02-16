import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');

let doc;
let enKeys;
let esKeys;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;

  // Extract the translation object from the inline script
  const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
  expect(tBlockMatch).not.toBeNull();

  const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
  expect(enMatch).not.toBeNull();
  enKeys = [...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);

  const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
  expect(esMatch).not.toBeNull();
  esKeys = [...esMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);
});

describe('public-translations — previously hardcoded strings now have data-t', () => {
  it('"out of 5 stars" element has data-t="outOfStars"', () => {
    const el = doc.querySelector('[data-t="outOfStars"]');
    expect(el, 'Element with data-t="outOfStars" not found').not.toBeNull();
  });

  it('"Based on 150+ Google Reviews" element has data-t="basedOnReviews"', () => {
    const el = doc.querySelector('[data-t="basedOnReviews"]');
    expect(el, 'Element with data-t="basedOnReviews" not found').not.toBeNull();
  });

  it('"View All Reviews on Google" element has data-t="viewAllReviews"', () => {
    const el = doc.querySelector('[data-t="viewAllReviews"]');
    expect(el, 'Element with data-t="viewAllReviews" not found').not.toBeNull();
  });

  it('"Loading gallery..." element has data-t="loadingGallery"', () => {
    const el = doc.querySelector('[data-t="loadingGallery"]');
    expect(el, 'Element with data-t="loadingGallery" not found').not.toBeNull();
  });

  it('"Address" heading has data-t="address"', () => {
    const el = doc.querySelector('[data-t="address"]');
    expect(el, 'Element with data-t="address" not found').not.toBeNull();
  });

  it('physical address element has data-t="physicalAddress"', () => {
    const els = doc.querySelectorAll('[data-t="physicalAddress"]');
    expect(els.length, 'Elements with data-t="physicalAddress" not found').toBeGreaterThanOrEqual(1);
  });

  it('"Follow Us" heading has data-t="followUs"', () => {
    const el = doc.querySelector('[data-t="followUs"]');
    expect(el, 'Element with data-t="followUs" not found').not.toBeNull();
  });

  it('copyright text has data-t="copyright"', () => {
    const el = doc.querySelector('[data-t="copyright"]');
    expect(el, 'Element with data-t="copyright" not found').not.toBeNull();
  });

  it('"Powered by" text has data-t="poweredBy"', () => {
    const el = doc.querySelector('[data-t="poweredBy"]');
    expect(el, 'Element with data-t="poweredBy" not found').not.toBeNull();
  });

  it('footer language toggle text has data-t="langToggleFooter"', () => {
    const el = doc.querySelector('[data-t="langToggleFooter"]');
    expect(el, 'Element with data-t="langToggleFooter" not found').not.toBeNull();
  });
});

describe('public-translations — new keys exist in BOTH en and es', () => {
  const newKeys = [
    'outOfStars',
    'basedOnReviews',
    'viewAllReviews',
    'loadingGallery',
    'address',
    'physicalAddress',
    'followUs',
    'copyright',
    'poweredBy',
    'noGalleryImages',
    'galleryLoadError',
    'messageSent',
    'messageError',
    'verified',
    'langToggleFooter',
  ];

  for (const key of newKeys) {
    it(`en translations contain key "${key}"`, () => {
      expect(enKeys, `EN missing key: ${key}`).toContain(key);
    });

    it(`es translations contain key "${key}"`, () => {
      expect(esKeys, `ES missing key: ${key}`).toContain(key);
    });
  }
});

describe('public-translations — en and es key parity and quality', () => {
  it('en and es have the same number of keys', () => {
    expect(enKeys.length).toBe(esKeys.length);
  });

  it('en and es have matching keys (sorted)', () => {
    const sortedEn = [...enKeys].sort();
    const sortedEs = [...esKeys].sort();
    expect(sortedEn).toEqual(sortedEs);
  });

  it('no empty translation values in en', () => {
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const emptyValues = [...enMatch[1].matchAll(/(\w+)\s*:\s*''/g)].map(m => m[1]);
    expect(emptyValues, `EN has empty values for: ${emptyValues.join(', ')}`).toHaveLength(0);
  });

  it('no empty translation values in es', () => {
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const emptyValues = [...esMatch[1].matchAll(/(\w+)\s*:\s*''/g)].map(m => m[1]);
    expect(emptyValues, `ES has empty values for: ${emptyValues.join(', ')}`).toHaveLength(0);
  });
});

describe('public-translations — JS-generated text uses translation keys', () => {
  it('gallery empty state uses t[currentLang].noGalleryImages', () => {
    expect(html).toMatch(/t\[currentLang\]\.noGalleryImages/);
  });

  it('gallery error state uses t[currentLang].galleryLoadError', () => {
    expect(html).toMatch(/t\[currentLang\]\.galleryLoadError/);
  });

  it('contact form success uses t[currentLang].messageSent', () => {
    expect(html).toMatch(/t\[currentLang\]\.messageSent/);
  });

  it('contact form error uses t[currentLang].messageError', () => {
    expect(html).toMatch(/t\[currentLang\]\.messageError/);
  });

  it('review "Verified" badge uses t[currentLang].verified', () => {
    expect(html).toMatch(/t\[currentLang\]\.verified/);
  });
});

describe('public-translations — toggleLanguage updates new elements', () => {
  it('toggleLanguage function updates footer language toggle text', () => {
    // The function should reference langToggleFooter or footer-lang-toggle
    expect(html).toMatch(/footer-lang-toggle|langToggleFooter/);
  });

  it('toggleLanguage function updates copyright text', () => {
    // The copyright element should have data-t so toggleLanguage picks it up automatically
    const el = doc.querySelector('[data-t="copyright"]');
    expect(el).not.toBeNull();
  });
});
