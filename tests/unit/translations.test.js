import { describe, it, expect } from 'vitest';
import { translations, getTranslation } from '../../src/js/translations.js';

describe('translations', () => {
  it('has both EN and ES dictionaries', () => {
    expect(translations.en).toBeDefined();
    expect(translations.es).toBeDefined();
  });

  it('EN and ES have the same keys (parity)', () => {
    const enKeys = Object.keys(translations.en).sort();
    const esKeys = Object.keys(translations.es).sort();
    expect(enKeys).toEqual(esKeys);
  });

  it('no empty values in EN', () => {
    for (const [key, value] of Object.entries(translations.en)) {
      expect(value, `EN key "${key}" is empty`).not.toBe('');
    }
  });

  it('no empty values in ES', () => {
    for (const [key, value] of Object.entries(translations.es)) {
      expect(value, `ES key "${key}" is empty`).not.toBe('');
    }
  });

  it('all values are strings', () => {
    for (const lang of ['en', 'es']) {
      for (const [key, value] of Object.entries(translations[lang])) {
        expect(typeof value, `${lang}.${key} should be a string`).toBe('string');
      }
    }
  });

  it('has expected common keys', () => {
    const requiredKeys = ['home', 'services', 'about', 'contact', 'phone', 'email', 'gallery'];
    for (const key of requiredKeys) {
      expect(translations.en[key], `EN missing key: ${key}`).toBeDefined();
      expect(translations.es[key], `ES missing key: ${key}`).toBeDefined();
    }
  });
});

describe('getTranslation', () => {
  it('returns EN translation for known key', () => {
    expect(getTranslation('en', 'home')).toBe('Home');
  });

  it('returns ES translation for known key', () => {
    expect(getTranslation('es', 'home')).toBe('Inicio');
  });

  it('returns empty string for unknown key', () => {
    expect(getTranslation('en', 'nonexistent_key')).toBe('');
  });

  it('returns empty string for unknown language', () => {
    expect(getTranslation('fr', 'home')).toBe('');
  });
});
