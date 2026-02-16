import { describe, it, expect } from 'vitest';
import { readFileSync, existsSync, readdirSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../deploy-clean');

function readFile(rel) {
  return readFileSync(resolve(ROOT, rel), 'utf-8');
}

describe('deploy-clean audit — index.html', () => {
  it('contains no /lovable-uploads/ in href or src attributes', () => {
    const html = readFile('index.html');
    // No actual asset references — normalizer string-matching code is allowed
    expect(html).not.toMatch(/(?:href|src)="[^"]*\/lovable-uploads\//);
  });

  it('references /assets/favicon.png', () => {
    const html = readFile('index.html');
    expect(html).toContain('/assets/favicon.png');
  });

  it('references /assets/logo.png', () => {
    const html = readFile('index.html');
    expect(html).toContain('/assets/logo.png');
  });

  it('references /assets/hero-bg.png (in fallback)', () => {
    const html = readFile('index.html');
    expect(html).toContain('/assets/hero-bg.png');
  });

  it('contains normalizeAssetPath function', () => {
    const html = readFile('index.html');
    expect(html).toContain('normalizeAssetPath');
  });
});

describe('deploy-clean audit — admin/index.html', () => {
  it('contains no /lovable-uploads/ paths', () => {
    const html = readFile('admin/index.html');
    expect(html).not.toContain('/lovable-uploads/');
  });

  it('has exactly 2 occurrences of /assets/logo.png', () => {
    const html = readFile('admin/index.html');
    const matches = html.match(/\/assets\/logo\.png/g);
    expect(matches).toHaveLength(2);
  });
});

describe('deploy-clean audit — file structure', () => {
  it('has no lovable-uploads/ directory', () => {
    expect(existsSync(resolve(ROOT, 'lovable-uploads'))).toBe(false);
  });

  it('has assets/ directory with logo.png, hero-bg.png, favicon.png', () => {
    const assetsDir = resolve(ROOT, 'assets');
    expect(existsSync(assetsDir)).toBe(true);
    const files = readdirSync(assetsDir);
    expect(files).toContain('logo.png');
    expect(files).toContain('hero-bg.png');
    expect(files).toContain('favicon.png');
  });

  it('has no lovable references in any HTML (except normalizer code)', () => {
    const indexHtml = readFile('index.html');
    const adminHtml = readFile('admin/index.html');

    // Admin should have zero lovable references
    expect(adminHtml.toLowerCase()).not.toContain('lovable');

    // Index may reference "lovable" only inside normalizeAssetPath/ASSET_PATH_MAP
    const indexLines = indexHtml.split('\n');
    const lovableLines = indexLines.filter(l => l.toLowerCase().includes('lovable'));
    for (const line of lovableLines) {
      const isNormalizerCode = line.includes('normalizeAssetPath') ||
        line.includes('ASSET_PATH_MAP') ||
        line.includes("'/lovable-uploads/'") ||
        line.includes('"/lovable-uploads/"') ||
        line.includes('/lovable-uploads/');
      // Every lovable reference must be part of the normalizer logic
      expect(isNormalizerCode).toBe(true);
    }
  });
});
