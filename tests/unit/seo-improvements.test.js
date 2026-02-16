import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync, existsSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ─── Meta Tags ───────────────────────────────────────────────────────────────

describe('SEO — additional meta tags', () => {
  it('has <link rel="canonical" href="https://oregon.tires/">', () => {
    const link = doc.querySelector('link[rel="canonical"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toBe('https://oregon.tires/');
  });

  it('has <meta property="og:url">', () => {
    const meta = doc.querySelector('meta[property="og:url"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('https://oregon.tires/');
  });

  it('has <meta property="og:image">', () => {
    const meta = doc.querySelector('meta[property="og:image"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toContain('https://oregon.tires/');
  });

  it('has <meta property="og:site_name">', () => {
    const meta = doc.querySelector('meta[property="og:site_name"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('Oregon Tires Auto Care');
  });

  it('has <meta name="twitter:card" content="summary_large_image">', () => {
    const meta = doc.querySelector('meta[name="twitter:card"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('summary_large_image');
  });

  it('has <meta name="twitter:title">', () => {
    const meta = doc.querySelector('meta[name="twitter:title"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content').length).toBeGreaterThan(0);
  });

  it('has <meta name="twitter:image">', () => {
    const meta = doc.querySelector('meta[name="twitter:image"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toContain('https://oregon.tires/');
  });

  it('has <meta name="robots" content="index, follow">', () => {
    const meta = doc.querySelector('meta[name="robots"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('index, follow');
  });

  it('has og:image:width set to 1200', () => {
    const meta = doc.querySelector('meta[property="og:image:width"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('1200');
  });

  it('has og:image:height set to 630', () => {
    const meta = doc.querySelector('meta[property="og:image:height"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toBe('630');
  });

  it('has og:image:type', () => {
    const meta = doc.querySelector('meta[property="og:image:type"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toMatch(/image\/(png|jpeg)/);
  });

  it('has og:image:alt with descriptive text', () => {
    const meta = doc.querySelector('meta[property="og:image:alt"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content').length).toBeGreaterThan(5);
  });

  it('has <meta name="theme-color"> for mobile browsers', () => {
    const meta = doc.querySelector('meta[name="theme-color"]');
    expect(meta).not.toBeNull();
    expect(meta.getAttribute('content')).toMatch(/^#[0-9a-fA-F]{6}$/);
  });

  it('has <link rel="apple-touch-icon">', () => {
    const link = doc.querySelector('link[rel="apple-touch-icon"]');
    expect(link).not.toBeNull();
  });
});

// ─── robots.txt ──────────────────────────────────────────────────────────────

describe('SEO — robots.txt', () => {
  let robotsTxt;

  beforeAll(() => {
    const robotsPath = resolve(ROOT, 'robots.txt');
    expect(existsSync(robotsPath)).toBe(true);
    robotsTxt = readFileSync(robotsPath, 'utf-8');
  });

  it('file exists at public_html/robots.txt', () => {
    expect(robotsTxt).toBeDefined();
    expect(robotsTxt.length).toBeGreaterThan(0);
  });

  it('contains User-agent: *', () => {
    expect(robotsTxt).toContain('User-agent: *');
  });

  it('contains Allow: /', () => {
    expect(robotsTxt).toContain('Allow: /');
  });

  it('contains Disallow: /admin/', () => {
    expect(robotsTxt).toContain('Disallow: /admin/');
  });

  it('contains Sitemap: directive with full URL', () => {
    expect(robotsTxt).toContain('Sitemap: https://oregon.tires/sitemap.xml');
  });
});

// ─── sitemap.xml ─────────────────────────────────────────────────────────────

describe('SEO — sitemap.xml', () => {
  let sitemapXml;

  beforeAll(() => {
    const sitemapPath = resolve(ROOT, 'sitemap.xml');
    expect(existsSync(sitemapPath)).toBe(true);
    sitemapXml = readFileSync(sitemapPath, 'utf-8');
  });

  it('file exists at public_html/sitemap.xml', () => {
    expect(sitemapXml).toBeDefined();
    expect(sitemapXml.length).toBeGreaterThan(0);
  });

  it('is valid XML (starts with <?xml)', () => {
    expect(sitemapXml.trimStart()).toMatch(/^<\?xml/);
  });

  it('contains <urlset with proper namespace', () => {
    expect(sitemapXml).toContain('<urlset');
    expect(sitemapXml).toContain('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"');
  });

  it('contains URL for homepage', () => {
    expect(sitemapXml).toContain('<loc>https://oregon.tires/</loc>');
  });

  it('contains <lastmod> dates', () => {
    expect(sitemapXml).toMatch(/<lastmod>\d{4}-\d{2}-\d{2}<\/lastmod>/);
  });

  it('contains <changefreq> values', () => {
    expect(sitemapXml).toMatch(/<changefreq>\w+<\/changefreq>/);
  });

  it('contains section URLs (services, about, contact)', () => {
    expect(sitemapXml).toContain('oregon.tires/#services');
    expect(sitemapXml).toContain('oregon.tires/#about');
    expect(sitemapXml).toContain('oregon.tires/#contact');
  });
});

// ─── Enhanced Structured Data ────────────────────────────────────────────────

describe('SEO — enhanced Schema.org JSON-LD', () => {
  let jsonLd;

  beforeAll(() => {
    const script = doc.querySelector('script[type="application/ld+json"]');
    expect(script).not.toBeNull();
    jsonLd = JSON.parse(script.textContent);
  });

  it('JSON-LD contains "@type": "AutomotiveBusiness"', () => {
    expect(jsonLd['@type']).toBe('AutomotiveBusiness');
  });

  it('has "url" property', () => {
    expect(jsonLd.url).toBeDefined();
    expect(jsonLd.url).toContain('oregon.tires');
  });

  it('has "image" property', () => {
    expect(jsonLd.image).toBeDefined();
    expect(jsonLd.image.length).toBeGreaterThan(0);
  });

  it('has "geo" with latitude and longitude', () => {
    expect(jsonLd.geo).toBeDefined();
    expect(jsonLd.geo['@type']).toBe('GeoCoordinates');
    expect(jsonLd.geo.latitude).toBeDefined();
    expect(jsonLd.geo.longitude).toBeDefined();
    expect(typeof jsonLd.geo.latitude).toBe('number');
    expect(typeof jsonLd.geo.longitude).toBe('number');
  });

  it('has "aggregateRating" with ratingValue and reviewCount', () => {
    expect(jsonLd.aggregateRating).toBeDefined();
    expect(jsonLd.aggregateRating['@type']).toBe('AggregateRating');
    expect(jsonLd.aggregateRating.ratingValue).toBeDefined();
    expect(jsonLd.aggregateRating.reviewCount).toBeDefined();
  });

  it('has "hasOfferCatalog" with services listed', () => {
    expect(jsonLd.hasOfferCatalog).toBeDefined();
    expect(jsonLd.hasOfferCatalog['@type']).toBe('OfferCatalog');
    const items = jsonLd.hasOfferCatalog.itemListElement;
    expect(Array.isArray(items)).toBe(true);
    expect(items.length).toBeGreaterThanOrEqual(7);

    const serviceNames = items.map(i => i.itemOffered.name);
    expect(serviceNames).toContain('Tire Installation');
    expect(serviceNames).toContain('Tire Repair');
    expect(serviceNames).toContain('Oil Change');
    expect(serviceNames).toContain('Brake Service');
    expect(serviceNames).toContain('Wheel Alignment');
    expect(serviceNames).toContain('Engine Diagnostics');
    expect(serviceNames).toContain('Suspension Repair');
  });

  it('has "address" with full postal address', () => {
    expect(jsonLd.address).toBeDefined();
    expect(jsonLd.address['@type']).toBe('PostalAddress');
    expect(jsonLd.address.streetAddress).toBe('8536 SE 82nd Ave');
    expect(jsonLd.address.addressLocality).toBe('Portland');
    expect(jsonLd.address.addressRegion).toBe('OR');
    expect(jsonLd.address.postalCode).toBe('97266');
    expect(jsonLd.address.addressCountry).toBe('US');
  });

  it('has "telephone"', () => {
    expect(jsonLd.telephone).toBeDefined();
    expect(jsonLd.telephone).toContain('503');
  });

  it('has "openingHoursSpecification"', () => {
    expect(jsonLd.openingHoursSpecification).toBeDefined();
    expect(Array.isArray(jsonLd.openingHoursSpecification)).toBe(true);
    expect(jsonLd.openingHoursSpecification.length).toBeGreaterThan(0);

    const spec = jsonLd.openingHoursSpecification[0];
    expect(spec['@type']).toBe('OpeningHoursSpecification');
    expect(spec.opens).toBeDefined();
    expect(spec.closes).toBeDefined();
    expect(spec.dayOfWeek).toBeDefined();
  });
});
