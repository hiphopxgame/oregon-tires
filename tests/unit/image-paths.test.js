import { describe, it, expect } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const indexHtml = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

describe('index.html — no absolute paths for local assets', () => {
  it('favicon href uses relative path (no leading /)', () => {
    expect(indexHtml).toMatch(/href="assets\/favicon\.png"/);
    expect(indexHtml).not.toMatch(/href="\/assets\/favicon\.png"/);
  });

  it('logo src uses relative path (no leading /)', () => {
    expect(indexHtml).toMatch(/src="assets\/logo\.png"/);
    expect(indexHtml).not.toMatch(/src="\/assets\/logo\.png"/);
  });

  it('fallbackImages JS object uses relative paths (no leading /)', () => {
    // Fallback paths like 'images/tire-services.jpg' not '/images/tire-services.jpg'
    const fallbackBlock = indexHtml.match(/const fallbackImages\s*=\s*\{[\s\S]*?\};/);
    expect(fallbackBlock).not.toBeNull();
    const block = fallbackBlock[0];
    expect(block).not.toMatch(/:\s*'\/images\//);
    expect(block).not.toMatch(/:\s*'\/assets\//);
    expect(block).toMatch(/:\s*'images\//);
    expect(block).toMatch(/:\s*'assets\//);
  });

  it('normalizeAssetPath rewrites to relative assets/ (no leading /)', () => {
    // The normalizer should produce 'assets/...' not '/assets/...'
    expect(indexHtml).toMatch(/\.replace\(['"]\/lovable-uploads\/['"],\s*['"]assets\//);
  });
});

describe('admin/index.html — no absolute paths for local assets', () => {
  it('logo src uses relative path with ../ (admin is a subdirectory)', () => {
    const logoMatches = adminHtml.match(/src="[^"]*logo\.png"/g);
    expect(logoMatches).not.toBeNull();
    for (const match of logoMatches) {
      expect(match).not.toContain('"/assets/');
      expect(match).toContain('"../assets/');
    }
  });
});

describe('index.html — image fallback robustness', () => {
  it('uses tryLoadImage or Image preload pattern for service images', () => {
    // The code should validate image URLs load before applying them
    expect(indexHtml).toMatch(/new Image\(\)|tryLoadImage/);
  });

  it('fallback applies per-image on load failure, not just on query error', () => {
    // Should have onerror or .catch per image, not just a top-level try/catch
    expect(indexHtml).toMatch(/\.onerror|\.catch.*fallback|reject/);
  });
});
