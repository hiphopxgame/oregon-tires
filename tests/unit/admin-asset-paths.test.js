import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(adminHtml);
  doc = dom.window.document;
});

describe('admin — normalizeAssetPath function', () => {
  it('normalizeAssetPath function is defined in admin', () => {
    expect(adminHtml).toMatch(/function\s+normalizeAssetPath\s*\(/);
  });

  it('ASSET_PATH_MAP is defined in admin', () => {
    expect(adminHtml).toMatch(/ASSET_PATH_MAP\s*=\s*\{/);
  });

  it('ASSET_PATH_MAP contains hero-bg mapping', () => {
    expect(adminHtml).toContain('afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png');
    expect(adminHtml).toContain('hero-bg.png');
  });
});

describe('admin — renderServiceImages uses normalizeAssetPath', () => {
  it('renderServiceImages uses normalizeAssetPath for image URLs', () => {
    const renderFn = adminHtml.match(/function\s+renderServiceImages\s*\(\)\s*\{[\s\S]*?\n\}/);
    expect(renderFn).not.toBeNull();
    expect(renderFn[0]).toContain('normalizeAssetPath');
  });
});

describe('admin — service image manager translations', () => {
  it('service image titles use adminT translations', () => {
    // Service image titles should reference adminT for bilingual support
    const renderFn = adminHtml.match(/function\s+renderServiceImages\s*\(\)\s*\{[\s\S]*?\n\}/);
    expect(renderFn).not.toBeNull();
    expect(renderFn[0]).toMatch(/adminT\[currentLang\]/);
  });
});
