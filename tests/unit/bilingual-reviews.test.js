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

  const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
  expect(tBlockMatch).not.toBeNull();
  const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
  expect(enMatch).not.toBeNull();
  enKeys = [...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);
  const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
  expect(esMatch).not.toBeNull();
  esKeys = [...esMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);
});

describe('bilingual reviews — data structure', () => {
  it('reviews object has "en" and "es" keys (not a flat array)', () => {
    // Reviews should be { en: [...], es: [...] } not a plain array
    expect(html).toMatch(/const\s+reviews\s*=\s*\{\s*\n?\s*en\s*:/);
  });

  it('reviews.en has at least 6 entries', () => {
    const enBlock = html.match(/reviews\s*=\s*\{[\s\S]*?en\s*:\s*\[([\s\S]*?)\]\s*,\s*\n?\s*es/);
    expect(enBlock).not.toBeNull();
    const count = (enBlock[1].match(/\{\s*name\s*:/g) || []).length;
    expect(count).toBeGreaterThanOrEqual(6);
  });

  it('reviews.es has at least 6 entries', () => {
    const esBlock = html.match(/es\s*:\s*\[([\s\S]*?)\]\s*\n?\s*\}/);
    expect(esBlock).not.toBeNull();
    const count = (esBlock[1].match(/\{\s*name\s*:/g) || []).length;
    expect(count).toBeGreaterThanOrEqual(6);
  });
});

describe('bilingual reviews — date translations', () => {
  const dateKeys = ['weeksAgo', 'monthAgo', 'monthsAgo', 'weekAgo'];

  for (const key of dateKeys) {
    it(`translation key "${key}" exists in en`, () => {
      expect(enKeys, `EN missing "${key}"`).toContain(key);
    });
    it(`translation key "${key}" exists in es`, () => {
      expect(esKeys, `ES missing "${key}"`).toContain(key);
    });
  }
});

describe('bilingual reviews — toggleLanguage re-renders reviews', () => {
  it('toggleLanguage calls renderReviews()', () => {
    const toggleFn = html.match(/function\s+toggleLanguage\s*\(\)\s*\{[\s\S]*?\n\}/);
    expect(toggleFn).not.toBeNull();
    expect(toggleFn[0]).toContain('renderReviews');
  });
});

describe('bilingual reviews — form placeholder translations', () => {
  const placeholderKeys = ['phonePlaceholder', 'emailPlaceholder'];

  for (const key of placeholderKeys) {
    it(`translation key "${key}" exists in en`, () => {
      expect(enKeys, `EN missing "${key}"`).toContain(key);
    });
    it(`translation key "${key}" exists in es`, () => {
      expect(esKeys, `ES missing "${key}"`).toContain(key);
    });
  }

  it('toggleLanguage updates form placeholders', () => {
    const toggleFn = html.match(/function\s+toggleLanguage\s*\(\)\s*\{[\s\S]*?\n\}/);
    expect(toggleFn).not.toBeNull();
    expect(toggleFn[0]).toMatch(/placeholder/i);
  });
});

describe('bilingual reviews — en and es key parity maintained', () => {
  it('en and es have the same number of keys', () => {
    expect(enKeys.length).toBe(esKeys.length);
  });

  it('en and es have matching keys', () => {
    const sortedEn = [...enKeys].sort();
    const sortedEs = [...esKeys].sort();
    expect(sortedEn).toEqual(sortedEs);
  });
});
