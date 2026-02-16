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

describe('service images — div IDs', () => {
  const serviceImageIds = [
    'svc-img-expert-technicians',
    'svc-img-fast-cars',
    'svc-img-quality-car-parts',
    'svc-img-bilingual-support',
    'svc-img-tire-shop',
    'svc-img-auto-repair',
    'svc-img-specialized-tools',
  ];

  for (const id of serviceImageIds) {
    it(`service image div #${id} exists`, () => {
      const el = doc.getElementById(id);
      expect(el, `#${id} not found`).not.toBeNull();
    });
  }

  it('all 7 service image divs exist', () => {
    const found = serviceImageIds.filter(id => doc.getElementById(id) !== null);
    expect(found).toHaveLength(7);
  });
});

describe('service images — CSS classes', () => {
  const serviceImageIds = [
    'svc-img-expert-technicians',
    'svc-img-fast-cars',
    'svc-img-quality-car-parts',
    'svc-img-bilingual-support',
    'svc-img-tire-shop',
    'svc-img-auto-repair',
    'svc-img-specialized-tools',
  ];

  for (const id of serviceImageIds) {
    it(`#${id} has bg-cover class`, () => {
      const el = doc.getElementById(id);
      expect(el.classList.contains('bg-cover')).toBe(true);
    });

    it(`#${id} has bg-center class`, () => {
      const el = doc.getElementById(id);
      expect(el.classList.contains('bg-center')).toBe(true);
    });
  }
});

describe('service images — fallbackImages object', () => {
  const expectedKeys = [
    'hero-background',
    'expert-technicians',
    'fast-cars',
    'quality-car-parts',
    'bilingual-support',
    'tire-shop',
    'auto-repair',
    'specialized-tools',
  ];

  it('fallbackImages object exists in the script', () => {
    expect(html).toMatch(/const\s+fallbackImages\s*=\s*\{/);
  });

  for (const key of expectedKeys) {
    it(`fallbackImages defines key "${key}"`, () => {
      const pattern = new RegExp(`['"]${key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}['"]\\s*:`);
      const fallbackBlock = html.match(/const\s+fallbackImages\s*=\s*\{[\s\S]*?\};/);
      expect(fallbackBlock).not.toBeNull();
      expect(fallbackBlock[0]).toMatch(pattern);
    });
  }

  it('fallbackImages has all 8 keys (including hero-background)', () => {
    const fallbackBlock = html.match(/const\s+fallbackImages\s*=\s*\{[\s\S]*?\};/);
    expect(fallbackBlock).not.toBeNull();
    const keys = [...fallbackBlock[0].matchAll(/['"]([^'"]+)['"]\s*:/g)].map(m => m[1]);
    expect(keys).toHaveLength(8);
  });
});

describe('service images — JS functions', () => {
  it('tryLoadImage function is defined', () => {
    expect(html).toMatch(/function\s+tryLoadImage\s*\(/);
  });

  it('applyBg function is defined', () => {
    expect(html).toMatch(/function\s+applyBg\s*\(/);
  });

  it('loadServiceImagesForSite function is defined', () => {
    expect(html).toMatch(/function\s+loadServiceImagesForSite\s*\(/);
  });
});

describe('service images — hero section', () => {
  it('hero section #home element exists', () => {
    const hero = doc.getElementById('home');
    expect(hero).not.toBeNull();
  });
});
