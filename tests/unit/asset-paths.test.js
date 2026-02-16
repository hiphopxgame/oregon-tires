import { describe, it, expect } from 'vitest';
import { ASSET_PATH_MAP, normalizeAssetPath } from '../../src/js/asset-paths.js';

describe('ASSET_PATH_MAP', () => {
  it('has exactly 3 entries', () => {
    expect(Object.keys(ASSET_PATH_MAP)).toHaveLength(3);
  });

  it('maps logo UUID to logo.png', () => {
    expect(ASSET_PATH_MAP['1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png']).toBe('logo.png');
  });

  it('maps hero-bg UUID to hero-bg.png', () => {
    expect(ASSET_PATH_MAP['afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png']).toBe('hero-bg.png');
  });

  it('maps favicon UUID to favicon.png', () => {
    expect(ASSET_PATH_MAP['b0182aa8-dde3-4175-8f09-21b6122f47f4.png']).toBe('favicon.png');
  });
});

describe('normalizeAssetPath', () => {
  it('rewrites lovable-uploads logo path to /assets/logo.png', () => {
    expect(normalizeAssetPath('/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png'))
      .toBe('/assets/logo.png');
  });

  it('rewrites lovable-uploads hero-bg path to /assets/hero-bg.png', () => {
    expect(normalizeAssetPath('/lovable-uploads/afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png'))
      .toBe('/assets/hero-bg.png');
  });

  it('rewrites lovable-uploads favicon path to /assets/favicon.png', () => {
    expect(normalizeAssetPath('/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png'))
      .toBe('/assets/favicon.png');
  });

  it('passes through /images/ paths unchanged', () => {
    expect(normalizeAssetPath('/images/tire-services.jpg')).toBe('/images/tire-services.jpg');
  });

  it('passes through null unchanged', () => {
    expect(normalizeAssetPath(null)).toBe(null);
  });

  it('passes through undefined unchanged', () => {
    expect(normalizeAssetPath(undefined)).toBe(undefined);
  });

  it('passes through empty string unchanged', () => {
    expect(normalizeAssetPath('')).toBe('');
  });

  it('rewrites unknown UUID under lovable-uploads (directory only)', () => {
    expect(normalizeAssetPath('/lovable-uploads/unknown-file.png'))
      .toBe('/assets/unknown-file.png');
  });

  it('result never contains "lovable" for any known path', () => {
    const paths = [
      '/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png',
      '/lovable-uploads/afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png',
      '/lovable-uploads/b0182aa8-dde3-4175-8f09-21b6122f47f4.png',
    ];
    for (const p of paths) {
      expect(normalizeAssetPath(p)).not.toContain('lovable');
    }
  });
});
