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

describe('external dependencies — CDN scripts', () => {
  it('Tailwind CSS is built at compile time (no CDN script tag expected)', () => {
    // Tailwind CSS v4 is built during the build process, not loaded from CDN
    const cdnScripts = doc.querySelectorAll('script[src*="cdn.tailwindcss.com"]');
    expect(cdnScripts.length).toBe(0);
  });

  it('no broken CDN references (all external scripts have https:// URLs)', () => {
    const scripts = doc.querySelectorAll('script[src]');
    scripts.forEach(script => {
      const src = script.getAttribute('src');
      // Only check external URLs (skip local scripts)
      if (src.startsWith('http://') || src.startsWith('https://')) {
        expect(src, `script src "${src}" should use https`).toMatch(/^https:\/\//);
      }
    });
  });
});

describe('external dependencies — Google Maps', () => {
  it('Google Maps iframe exists', () => {
    const iframe = doc.querySelector('iframe[src*="google.com/maps"], iframe[data-src*="google.com/maps"]');
    expect(iframe).not.toBeNull();
  });

  it('Google Maps iframe references the correct address area', () => {
    const iframe = doc.querySelector('iframe[src*="google.com/maps"], iframe[data-src*="google.com/maps"]');
    const src = iframe.getAttribute('src') || iframe.getAttribute('data-src');
    expect(src).toContain('82nd');
  });
});

describe('external dependencies — social media links', () => {
  it('Instagram link exists', () => {
    const link = doc.querySelector('a[href*="instagram.com"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toContain('oregontires');
  });

  it('Facebook link exists', () => {
    const link = doc.querySelector('a[href*="facebook.com"]');
    expect(link).not.toBeNull();
  });
});

describe('external dependencies — Google Reviews', () => {
  it('Google Reviews link exists', () => {
    const link = doc.querySelector('a[href*="google.com/search"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toContain('Oregon+Tires');
  });
});
