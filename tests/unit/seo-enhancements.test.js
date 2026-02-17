import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');

let doc;
let allJsonLdScripts;
let automotiveJsonLd;
let faqJsonLd;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;

  // Parse all JSON-LD blocks
  const scripts = doc.querySelectorAll('script[type="application/ld+json"]');
  allJsonLdScripts = [];
  scripts.forEach(s => {
    allJsonLdScripts.push(JSON.parse(s.textContent));
  });

  automotiveJsonLd = allJsonLdScripts.find(j => j['@type'] === 'AutomotiveBusiness');
  faqJsonLd = allJsonLdScripts.find(j => j['@type'] === 'FAQPage');
});

// ─── 1. AggregateRating in AutomotiveBusiness JSON-LD ─────────────────────────

describe('SEO Enhancement — AggregateRating', () => {
  it('AutomotiveBusiness JSON-LD exists', () => {
    expect(automotiveJsonLd).toBeDefined();
  });

  it('has aggregateRating property', () => {
    expect(automotiveJsonLd.aggregateRating).toBeDefined();
  });

  it('aggregateRating @type is "AggregateRating"', () => {
    expect(automotiveJsonLd.aggregateRating['@type']).toBe('AggregateRating');
  });

  it('aggregateRating ratingValue is "4.8"', () => {
    expect(automotiveJsonLd.aggregateRating.ratingValue).toBe('4.8');
  });

  it('aggregateRating reviewCount is "150"', () => {
    expect(automotiveJsonLd.aggregateRating.reviewCount).toBe('150');
  });

  it('aggregateRating bestRating is "5"', () => {
    expect(automotiveJsonLd.aggregateRating.bestRating).toBe('5');
  });
});

// ─── 2. FAQPage Schema ───────────────────────────────────────────────────────

describe('SEO Enhancement — FAQPage Schema', () => {
  it('FAQPage JSON-LD block exists as a separate script tag', () => {
    expect(faqJsonLd).toBeDefined();
  });

  it('FAQPage has @context "https://schema.org"', () => {
    expect(faqJsonLd['@context']).toBe('https://schema.org');
  });

  it('FAQPage @type is "FAQPage"', () => {
    expect(faqJsonLd['@type']).toBe('FAQPage');
  });

  it('FAQPage has mainEntity array with 5 questions', () => {
    expect(Array.isArray(faqJsonLd.mainEntity)).toBe(true);
    expect(faqJsonLd.mainEntity.length).toBe(5);
  });

  it('all FAQ items have @type "Question"', () => {
    faqJsonLd.mainEntity.forEach(item => {
      expect(item['@type']).toBe('Question');
    });
  });

  it('all FAQ items have acceptedAnswer with @type "Answer"', () => {
    faqJsonLd.mainEntity.forEach(item => {
      expect(item.acceptedAnswer).toBeDefined();
      expect(item.acceptedAnswer['@type']).toBe('Answer');
      expect(item.acceptedAnswer.text.length).toBeGreaterThan(0);
    });
  });

  it('FAQ includes question about services offered', () => {
    const q = faqJsonLd.mainEntity.find(i =>
      i.name.toLowerCase().includes('services')
    );
    expect(q).toBeDefined();
    const answer = q.acceptedAnswer.text.toLowerCase();
    expect(answer).toContain('tires');
    expect(answer).toContain('oil change');
    expect(answer).toContain('brake');
    expect(answer).toContain('alignment');
    expect(answer).toContain('mobile service');
    expect(answer).toContain('roadside assistance');
  });

  it('FAQ includes question about Spanish language support', () => {
    const q = faqJsonLd.mainEntity.find(i =>
      i.name.toLowerCase().includes('spanish')
    );
    expect(q).toBeDefined();
    const answer = q.acceptedAnswer.text.toLowerCase();
    expect(answer).toContain('bilingual');
    expect(answer).toContain('english');
    expect(answer).toContain('spanish');
  });

  it('FAQ includes question about hours', () => {
    const q = faqJsonLd.mainEntity.find(i =>
      i.name.toLowerCase().includes('hours')
    );
    expect(q).toBeDefined();
    const answer = q.acceptedAnswer.text.toLowerCase();
    expect(answer).toContain('mon');
    expect(answer).toContain('sat');
    expect(answer).toContain('7am');
    expect(answer).toContain('7pm');
    expect(answer).toContain('sunday');
  });

  it('FAQ includes question about location', () => {
    const q = faqJsonLd.mainEntity.find(i =>
      i.name.toLowerCase().includes('located')
    );
    expect(q).toBeDefined();
    const answer = q.acceptedAnswer.text;
    expect(answer).toContain('8536 SE 82nd Ave');
    expect(answer).toContain('Portland');
    expect(answer).toContain('97266');
  });

  it('FAQ includes question about mobile service', () => {
    const q = faqJsonLd.mainEntity.find(i =>
      i.name.toLowerCase().includes('mobile service')
    );
    expect(q).toBeDefined();
    const answer = q.acceptedAnswer.text.toLowerCase();
    expect(answer).toContain('yes');
  });
});

// ─── 3. Hreflang Tags ───────────────────────────────────────────────────────

describe('SEO Enhancement — Hreflang Tags', () => {
  it('has hreflang="en" link pointing to https://oregon.tires/', () => {
    const link = doc.querySelector('link[rel="alternate"][hreflang="en"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toBe('https://oregon.tires/');
  });

  it('has hreflang="es" link pointing to https://oregon.tires/?lang=es', () => {
    const link = doc.querySelector('link[rel="alternate"][hreflang="es"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toBe('https://oregon.tires/?lang=es');
  });

  it('has hreflang="x-default" link pointing to https://oregon.tires/', () => {
    const link = doc.querySelector('link[rel="alternate"][hreflang="x-default"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('href')).toBe('https://oregon.tires/');
  });

  it('all three hreflang tags are in the <head>', () => {
    const head = doc.querySelector('head');
    const hreflangs = head.querySelectorAll('link[rel="alternate"][hreflang]');
    expect(hreflangs.length).toBe(3);
  });
});

// ─── 4. Service Schema with hasOfferCatalog & price ranges ───────────────────

describe('SEO Enhancement — Service Schema (hasOfferCatalog)', () => {
  it('AutomotiveBusiness has hasOfferCatalog', () => {
    expect(automotiveJsonLd.hasOfferCatalog).toBeDefined();
  });

  it('hasOfferCatalog @type is "OfferCatalog"', () => {
    expect(automotiveJsonLd.hasOfferCatalog['@type']).toBe('OfferCatalog');
  });

  it('hasOfferCatalog has a name', () => {
    expect(automotiveJsonLd.hasOfferCatalog.name).toBeDefined();
    expect(automotiveJsonLd.hasOfferCatalog.name.length).toBeGreaterThan(0);
  });

  it('hasOfferCatalog has itemListElement array', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    expect(Array.isArray(items)).toBe(true);
    expect(items.length).toBeGreaterThanOrEqual(7);
  });

  it('each offer has @type "Offer" with itemOffered containing a Service', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    items.forEach(item => {
      expect(item['@type']).toBe('Offer');
      expect(item.itemOffered).toBeDefined();
      expect(item.itemOffered['@type']).toBe('Service');
      expect(item.itemOffered.name.length).toBeGreaterThan(0);
    });
  });

  it('offers include services with price ranges (priceRange or priceSpecification)', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    // At least some services should have price info
    const withPriceInfo = items.filter(item =>
      item.priceSpecification || item.price
    );
    expect(withPriceInfo.length).toBeGreaterThanOrEqual(3);
  });

  it('price specifications have priceCurrency USD', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    const withPriceSpec = items.filter(item => item.priceSpecification);
    withPriceSpec.forEach(item => {
      expect(item.priceSpecification.priceCurrency).toBe('USD');
    });
  });

  it('service names include key services: Tire Installation, Oil Change, Brake Service', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    const names = items.map(i => i.itemOffered.name);
    expect(names).toContain('Tire Installation');
    expect(names).toContain('Oil Change');
    expect(names).toContain('Brake Service');
  });

  it('service names include Wheel Alignment, Engine Diagnostics, Suspension Repair', () => {
    const items = automotiveJsonLd.hasOfferCatalog.itemListElement;
    const names = items.map(i => i.itemOffered.name);
    expect(names).toContain('Wheel Alignment');
    expect(names).toContain('Engine Diagnostics');
    expect(names).toContain('Suspension Repair');
  });

  it('AutomotiveBusiness has priceRange property', () => {
    expect(automotiveJsonLd.priceRange).toBeDefined();
    expect(automotiveJsonLd.priceRange).toBe('$$');
  });
});

// ─── Overall: Multiple JSON-LD blocks ────────────────────────────────────────

describe('SEO Enhancement — Multiple JSON-LD blocks coexist', () => {
  it('page has at least 2 JSON-LD script blocks', () => {
    const scripts = doc.querySelectorAll('script[type="application/ld+json"]');
    expect(scripts.length).toBeGreaterThanOrEqual(2);
  });

  it('all JSON-LD blocks parse as valid JSON', () => {
    const scripts = doc.querySelectorAll('script[type="application/ld+json"]');
    scripts.forEach(s => {
      expect(() => JSON.parse(s.textContent)).not.toThrow();
    });
  });

  it('AutomotiveBusiness JSON-LD is not broken by enhancements', () => {
    expect(automotiveJsonLd['@context']).toBe('https://schema.org');
    expect(automotiveJsonLd['@type']).toBe('AutomotiveBusiness');
    expect(automotiveJsonLd.name).toBe('Oregon Tires Auto Care');
    expect(automotiveJsonLd.telephone).toBe('(503) 367-9714');
    expect(automotiveJsonLd.address).toBeDefined();
    expect(automotiveJsonLd.geo).toBeDefined();
    expect(automotiveJsonLd.openingHours).toBeDefined();
    expect(automotiveJsonLd.knowsLanguage).toBeDefined();
  });
});
