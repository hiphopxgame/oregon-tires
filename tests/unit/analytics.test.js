import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const publicHtml = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let publicDoc, adminDoc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const publicDom = new JSDOM(publicHtml);
  publicDoc = publicDom.window.document;
  const adminDom = new JSDOM(adminHtml);
  adminDoc = adminDom.window.document;
});

// ─── Public Site — GA4 Setup ────────────────────────────────────────────────

describe('public site — GA4 analytics setup', () => {
  it('creates gtag.js script dynamically via document.createElement', () => {
    expect(publicHtml).toContain("document.createElement('script')");
    expect(publicHtml).toContain("'https://www.googletagmanager.com/gtag/js?id='");
  });

  it('has dataLayer initialization in IIFE', () => {
    expect(publicHtml).toContain('window.dataLayer = window.dataLayer || []');
  });

  it('defines the gtag function that pushes to dataLayer', () => {
    expect(publicHtml).toContain('function gtag(){dataLayer.push(arguments);}');
  });

  it('assigns gtag to window.gtag', () => {
    expect(publicHtml).toContain('window.gtag = gtag');
  });

  it('calls gtag("config", id) with measurement ID', () => {
    expect(publicHtml).toContain("gtag('config', id)");
  });

  it('has GA4 measurement ID configured', () => {
    expect(publicHtml).toContain('G-CHYMTNB6LH');
  });

  it('has IIFE wrapper for GA initialization', () => {
    expect(publicHtml).toContain('(function()');
  });
});

// ─── Public Site — Custom Event Tracking ────────────────────────────────────

describe('public site — custom event tracking', () => {
  it('tracks form_submit event on contact form submission', () => {
    expect(publicHtml).toContain("gtag('event', 'form_submit'");
  });

  it('tracks language_switch event in toggleLanguage()', () => {
    expect(publicHtml).toContain("gtag('event', 'language_switch'");
  });

  it('tracks cta_click event for Schedule Service links', () => {
    expect(publicHtml).toContain("gtag('event', 'cta_click'");
  });

  it('guards analytics calls with typeof gtag check', () => {
    const gtagEventCalls = publicHtml.match(/gtag\('event'/g) || [];
    const guardedCalls = publicHtml.match(/typeof gtag\s*===?\s*'function'\)\s*gtag\('event'/g) || [];
    expect(guardedCalls.length).toBe(gtagEventCalls.length);
  });
});

// ─── Admin Site — GA4 Setup ─────────────────────────────────────────────────

describe('admin site — GA4 analytics setup', () => {
  it('creates gtag.js script dynamically via document.createElement', () => {
    expect(adminHtml).toContain("document.createElement('script')");
    expect(adminHtml).toContain("'https://www.googletagmanager.com/gtag/js?id='");
  });

  it('defines the gtag function that pushes to dataLayer', () => {
    expect(adminHtml).toContain('function gtag(){dataLayer.push(arguments);}');
  });

  it('has dataLayer initialization in IIFE', () => {
    expect(adminHtml).toContain('window.dataLayer = window.dataLayer || []');
  });

  it('calls gtag("config", id) with measurement ID', () => {
    expect(adminHtml).toContain("gtag('config', id)");
  });

  it('has GA4 measurement ID configured', () => {
    expect(adminHtml).toContain('G-CHYMTNB6LH');
  });
});

// ─── Public Site — Error Tracking ───────────────────────────────────────────

describe('public site — error tracking', () => {
  it('has a global error handler (window.addEventListener error)', () => {
    expect(publicHtml).toContain("window.addEventListener('error'");
  });

  it('has an unhandled promise rejection handler', () => {
    expect(publicHtml).toContain("window.addEventListener('unhandledrejection'");
  });

  it('sends exceptions to GA4 via gtag event', () => {
    expect(publicHtml).toContain("gtag('event', 'exception'");
  });
});

// ─── Admin Site — Error Tracking ────────────────────────────────────────────

describe('admin site — error tracking', () => {
  it('has a global error handler (window.addEventListener error)', () => {
    expect(adminHtml).toContain("window.addEventListener('error'");
  });

  it('has an unhandled promise rejection handler', () => {
    expect(adminHtml).toContain("window.addEventListener('unhandledrejection'");
  });

  it('sends exceptions to GA4 via gtag event', () => {
    expect(adminHtml).toContain("gtag('event', 'exception'");
  });
});
