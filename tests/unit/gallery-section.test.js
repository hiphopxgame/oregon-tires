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

describe('gallery section — structure', () => {
  it('gallery section #gallery exists', () => {
    const section = doc.getElementById('gallery');
    expect(section).not.toBeNull();
    expect(section.tagName).toBe('SECTION');
  });

  it('gallery grid container #gallery-section-grid exists', () => {
    const grid = doc.getElementById('gallery-section-grid');
    expect(grid).not.toBeNull();
  });

  it('gallery has loading placeholder text', () => {
    const grid = doc.getElementById('gallery-section-grid');
    expect(grid.textContent).toContain('Loading gallery');
  });

  it('gallery section has data-t heading', () => {
    const section = doc.getElementById('gallery');
    const heading = section.querySelector('[data-t="gallery"]');
    expect(heading).not.toBeNull();
    expect(heading.tagName).toBe('H2');
  });
});
