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

describe('SEO — essential meta tags', () => {
  it('has DOCTYPE html declaration', () => {
    expect(html).toMatch(/^<!DOCTYPE html>/i);
  });

  it('has meta charset UTF-8', () => {
    const meta = doc.querySelector('meta[charset]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('charset').toUpperCase()).toBe('UTF-8');
  });

  it('has meta viewport', () => {
    const meta = doc.querySelector('meta[name="viewport"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toContain('width=device-width');
  });

  it('has title tag containing "Oregon Tires"', () => {
    const title = doc.querySelector('title');
    expect(title).not.toBeNull();
    expect(title.textContent).toContain('Oregon Tires');
  });

  it('has meta description containing "Portland"', () => {
    const meta = doc.querySelector('meta[name="description"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toContain('Portland');
  });

  it('has meta keywords', () => {
    const meta = doc.querySelector('meta[name="keywords"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content').length).toBeGreaterThan(0);
  });
});

describe('SEO — Open Graph tags', () => {
  it('has og:title', () => {
    const meta = doc.querySelector('meta[property="og:title"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toContain('Oregon Tires');
  });

  it('has og:description', () => {
    const meta = doc.querySelector('meta[property="og:description"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content').length).toBeGreaterThan(0);
  });

  it('has og:type', () => {
    const meta = doc.querySelector('meta[property="og:type"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('website');
  });
});

describe('SEO — Schema.org JSON-LD structured data', () => {
  let jsonLd;

  beforeAll(() => {
    const script = doc.querySelector('script[type="application/ld+json"]');
    expect(script).not.toBeNull();
    jsonLd = JSON.parse(script.textContent);
  });

  it('JSON-LD script tag exists and parses as valid JSON', () => {
    expect(jsonLd).toBeDefined();
    expect(typeof jsonLd).toBe('object');
  });

  it('JSON-LD @type is "AutomotiveBusiness"', () => {
    expect(jsonLd['@type']).toBe('AutomotiveBusiness');
  });

  it('JSON-LD contains correct phone: (503) 367-9714', () => {
    expect(jsonLd.telephone).toBe('(503) 367-9714');
  });

  it('JSON-LD contains correct address: 8536 SE 82nd Ave', () => {
    expect(jsonLd.address).toBeDefined();
    expect(jsonLd.address.streetAddress).toBe('8536 SE 82nd Ave');
  });

  it('JSON-LD contains openingHours', () => {
    expect(jsonLd.openingHours).toBeDefined();
    expect(jsonLd.openingHours.length).toBeGreaterThan(0);
  });
});

describe('SEO — favicon', () => {
  it('has canonical favicon link', () => {
    const link = doc.querySelector('link[rel="icon"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toContain('favicon');
  });
});
