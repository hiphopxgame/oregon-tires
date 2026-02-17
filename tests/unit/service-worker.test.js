import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
let sw;

beforeAll(() => {
  sw = readFileSync(resolve(ROOT, 'sw.js'), 'utf-8');
});

// ─── Cache Name Versioning ──────────────────────────────────────────────────

describe('Cache name versioning', () => {
  it('defines a versioned cache name like oregon-tires-v<number>', () => {
    expect(sw).toMatch(/CACHE_NAME\s*=\s*['"]oregon-tires-v\d+['"]/);
  });

  it('uses CACHE_NAME constant (not hardcoded strings) for cache operations', () => {
    // caches.open should reference CACHE_NAME, not a string literal
    const opensWithVariable = (sw.match(/caches\.open\(/g) || []).length;
    const opensWithCacheName = (sw.match(/caches\.open\(\s*CACHE_NAME\s*\)/g) || []).length;
    expect(opensWithCacheName).toBe(opensWithVariable);
  });
});

// ─── Install Event — Pre-cache Critical Assets ─────────────────────────────

describe('Install event — pre-cache critical assets', () => {
  it('has an install event listener', () => {
    expect(sw).toMatch(/self\.addEventListener\(\s*['"]install['"]/);
  });

  it('pre-caches the root path /', () => {
    // Must appear in the precache list
    expect(sw).toMatch(/['"]\/['"]/);
  });

  it('pre-caches /assets/logo.png', () => {
    expect(sw).toContain('/assets/logo.png');
  });

  it('pre-caches /assets/hero-bg.png', () => {
    expect(sw).toContain('/assets/hero-bg.png');
  });

  it('pre-caches /assets/favicon.png', () => {
    expect(sw).toContain('/assets/favicon.png');
  });

  it('pre-caches /manifest.json', () => {
    expect(sw).toContain('/manifest.json');
  });

  it('pre-caches all 7 service images from /images/', () => {
    const serviceImages = [
      '/images/fast-cars.jpg',
      '/images/tire-services.jpg',
      '/images/quality-parts.jpg',
      '/images/expert-technicians.jpg',
      '/images/specialized-services.jpg',
      '/images/auto-maintenance.jpg',
      '/images/bilingual-service.jpg',
    ];
    for (const img of serviceImages) {
      expect(sw).toContain(img);
    }
  });

  it('calls skipWaiting() during install', () => {
    expect(sw).toMatch(/self\.skipWaiting\(\)/);
  });
});

// ─── Fetch Strategy — HTML (Network-first) ──────────────────────────────────

describe('Fetch strategy — HTML pages (network-first)', () => {
  it('detects navigation requests or HTML content type', () => {
    // Must check for navigate mode or html accept header
    const checksNavigate = /request\.mode\s*===?\s*['"]navigate['"]/.test(sw);
    const checksAcceptHtml = /request\.headers\.get\(\s*['"]accept['"]\s*\)/.test(sw);
    const checksDestDocument = /request\.destination\s*===?\s*['"]document['"]/.test(sw);
    expect(checksNavigate || checksAcceptHtml || checksDestDocument).toBe(true);
  });

  it('uses network-first strategy: fetches from network before falling back to cache', () => {
    // The pattern for network-first is: fetch() first, then .catch() falls back to caches.match()
    // Should see a fetch call followed by cache fallback in the HTML/navigation handling path
    // We look for the pattern of fetch() with a catch that does caches.match
    expect(sw).toMatch(/fetch\(.*\)[\s\S]*?\.catch\([\s\S]*?caches\.match/);
  });
});

// ─── Fetch Strategy — Images (Cache-first) ──────────────────────────────────

describe('Fetch strategy — Images (cache-first)', () => {
  it('identifies image requests', () => {
    // Must check for image destination or image file extensions or image content type
    const checksDestination = /request\.destination\s*===?\s*['"]image['"]/.test(sw);
    const checksExtension = /\.(png|jpg|jpeg|gif|svg|webp)/.test(sw);
    const checksUrl = /\.url\./.test(sw);
    expect(checksDestination || checksExtension).toBe(true);
  });

  it('checks cache first for images (caches.match before fetch)', () => {
    // For cache-first: caches.match() is tried first, then fetch on miss
    // The image handling block should have caches.match -> fetch pattern
    expect(sw).toMatch(/caches\.match\([\s\S]*?fetch\(/);
  });

  it('caches image responses from the network for future use', () => {
    // After fetching, the response should be cloned and put into cache
    expect(sw).toMatch(/\.clone\(\)/);
    expect(sw).toMatch(/cache\.put\(/);
  });
});

// ─── Fetch Strategy — Supabase API (Network-only) ──────────────────────────

describe('Fetch strategy — Supabase API calls (network-only)', () => {
  it('detects Supabase API calls by domain', () => {
    expect(sw).toContain('supabase.co');
  });

  it('does NOT cache Supabase API responses (returns early or uses network-only)', () => {
    // The Supabase check should either return early (no respondWith) or explicitly skip caching
    // We check that there is a return statement or the handler exits before caching
    const supabaseBlock = sw.match(/supabase\.co[\s\S]*?return/);
    expect(supabaseBlock).not.toBeNull();
  });
});

// ─── Fetch Strategy — Static Assets (Stale-while-revalidate) ────────────────

describe('Fetch strategy — other static assets (stale-while-revalidate)', () => {
  it('implements stale-while-revalidate: serves from cache while fetching update', () => {
    // Stale-while-revalidate pattern: respond with cached version AND fetch fresh copy
    // The code should have both caches.match AND fetch happening, with cache.put to update
    // We look for the characteristic pattern of returning cached response while updating
    const hasSWRComment = /stale.while.revalidate/i.test(sw);
    const hasCacheMatchAndFetch = /caches\.match/.test(sw) && /fetch\(/.test(sw) && /cache\.put/.test(sw);
    expect(hasSWRComment || hasCacheMatchAndFetch).toBe(true);
  });

  it('updates the cache in the background after serving stale content', () => {
    // The fetch + cache.put should happen even when a cached response exists
    expect(sw).toMatch(/cache\.put\(\s*event\.request/);
  });
});

// ─── Activate Event — Old Cache Cleanup ─────────────────────────────────────

describe('Activate event — old cache cleanup', () => {
  it('has an activate event listener', () => {
    expect(sw).toMatch(/self\.addEventListener\(\s*['"]activate['"]/);
  });

  it('calls caches.keys() to enumerate existing caches', () => {
    expect(sw).toMatch(/caches\.keys\(\)/);
  });

  it('filters caches to find ones not matching CACHE_NAME', () => {
    // Should compare against CACHE_NAME to find old caches
    expect(sw).toMatch(/filter\([\s\S]*?CACHE_NAME/);
  });

  it('deletes old caches via caches.delete()', () => {
    expect(sw).toMatch(/caches\.delete\(/);
  });

  it('calls clients.claim() to take control immediately', () => {
    expect(sw).toMatch(/self\.clients\.claim\(\)|clients\.claim\(\)/);
  });
});

// ─── Offline Fallback ───────────────────────────────────────────────────────

describe('Offline fallback', () => {
  it('provides an offline response when both network and cache fail for navigation', () => {
    // Should have a fallback that returns an HTML response for failed navigation requests
    // Look for: new Response with HTML content type, or a reference to offline page
    const hasOfflineResponse = /new\s+Response\([\s\S]*?text\/html/i.test(sw);
    const hasOfflinePage = /offline/i.test(sw);
    expect(hasOfflineResponse || hasOfflinePage).toBe(true);
  });

  it('offline fallback includes meaningful content (not empty)', () => {
    // The offline response should contain some HTML content indicating offline state
    const offlineMatch = sw.match(/new\s+Response\(\s*(['"`])([\s\S]*?)\1/);
    if (offlineMatch) {
      expect(offlineMatch[2].length).toBeGreaterThan(20);
    } else {
      // If using template literal, check for backtick version
      const templateMatch = sw.match(/new\s+Response\(\s*`([\s\S]*?)`/);
      expect(templateMatch).not.toBeNull();
      if (templateMatch) {
        expect(templateMatch[1].length).toBeGreaterThan(20);
      }
    }
  });

  it('offline fallback sets Content-Type to text/html', () => {
    expect(sw).toMatch(/['"]Content-Type['"]\s*:\s*['"]text\/html/);
  });
});
