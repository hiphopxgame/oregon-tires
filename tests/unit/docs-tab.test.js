import { describe, it, expect } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

describe('Documentation tab — navigation button', () => {
  it('has a Docs tab button in nav-tabs', () => {
    expect(adminHtml).toMatch(/data-tab="docs"/);
  });

  it('Docs button calls switchTab("docs")', () => {
    expect(adminHtml).toMatch(/onclick="switchTab\('docs'\)"/);
  });

  it('Docs button has the standard tab-btn class', () => {
    const btn = adminHtml.match(/<button[^>]*data-tab="docs"[^>]*>/);
    expect(btn).not.toBeNull();
    expect(btn[0]).toContain('tab-btn');
  });

  it('Docs button appears after Analytics button in nav order', () => {
    const analyticsPos = adminHtml.indexOf('data-tab="analytics"');
    const docsPos = adminHtml.indexOf('data-tab="docs"');
    expect(analyticsPos).toBeGreaterThan(-1);
    expect(docsPos).toBeGreaterThan(analyticsPos);
  });
});

describe('Documentation tab — content container', () => {
  it('has a tab-docs content div', () => {
    expect(adminHtml).toMatch(/id="tab-docs"/);
  });

  it('tab-docs has standard tab-content and hidden classes', () => {
    const div = adminHtml.match(/<div[^>]*id="tab-docs"[^>]*>/);
    expect(div).not.toBeNull();
    expect(div[0]).toContain('tab-content');
    expect(div[0]).toContain('hidden');
  });

  it('tab-docs has fade-in class', () => {
    const div = adminHtml.match(/<div[^>]*id="tab-docs"[^>]*>/);
    expect(div[0]).toContain('fade-in');
  });
});

describe('Documentation tab — sub-tabs', () => {
  it('has 3 sub-tab buttons: Manual, Features, Improvements', () => {
    expect(adminHtml).toMatch(/id="docs-view-manual"/);
    expect(adminHtml).toMatch(/id="docs-view-features"/);
    expect(adminHtml).toMatch(/id="docs-view-improvements"/);
  });

  it('sub-tab buttons call switchDocsView()', () => {
    expect(adminHtml).toMatch(/switchDocsView\('manual'\)/);
    expect(adminHtml).toMatch(/switchDocsView\('features'\)/);
    expect(adminHtml).toMatch(/switchDocsView\('improvements'\)/);
  });

  it('has 3 sub-tab content divs', () => {
    expect(adminHtml).toMatch(/id="docs-subtab-manual"/);
    expect(adminHtml).toMatch(/id="docs-subtab-features"/);
    expect(adminHtml).toMatch(/id="docs-subtab-improvements"/);
  });

  it('Manual sub-tab is visible by default, others hidden', () => {
    const manual = adminHtml.match(/<div[^>]*id="docs-subtab-manual"[^>]*>/);
    const features = adminHtml.match(/<div[^>]*id="docs-subtab-features"[^>]*>/);
    const improvements = adminHtml.match(/<div[^>]*id="docs-subtab-improvements"[^>]*>/);
    expect(manual).not.toBeNull();
    expect(features).not.toBeNull();
    expect(improvements).not.toBeNull();
    // Manual should NOT have hidden class
    expect(manual[0]).not.toContain('hidden');
    // Features and Improvements should be hidden
    expect(features[0]).toContain('hidden');
    expect(improvements[0]).toContain('hidden');
  });
});

describe('Documentation tab — switchDocsView function', () => {
  it('defines switchDocsView function', () => {
    expect(adminHtml).toMatch(/function switchDocsView\s*\(/);
  });

  it('switchDocsView toggles sub-tab visibility', () => {
    // Should reference the 3 subtab IDs
    expect(adminHtml).toMatch(/docs-subtab-manual/);
    expect(adminHtml).toMatch(/docs-subtab-features/);
    expect(adminHtml).toMatch(/docs-subtab-improvements/);
  });
});

describe('Documentation tab — Manual content', () => {
  it('contains Manual heading text', () => {
    // Extract the docs-subtab-manual section
    const manualStart = adminHtml.indexOf('id="docs-subtab-manual"');
    expect(manualStart).toBeGreaterThan(-1);
    const section = adminHtml.slice(manualStart, manualStart + 5000);
    expect(section).toMatch(/Getting Started/);
  });

  it('contains Appointments section', () => {
    const manualStart = adminHtml.indexOf('id="docs-subtab-manual"');
    const manualEnd = adminHtml.indexOf('id="docs-subtab-features"');
    const section = adminHtml.slice(manualStart, manualEnd);
    expect(section).toMatch(/Managing Appointments/);
  });

  it('contains Troubleshooting section', () => {
    const manualStart = adminHtml.indexOf('id="docs-subtab-manual"');
    const manualEnd = adminHtml.indexOf('id="docs-subtab-features"');
    const section = adminHtml.slice(manualStart, manualEnd);
    expect(section).toMatch(/Troubleshooting/);
  });
});

describe('Documentation tab — Features content', () => {
  it('contains Public Website section', () => {
    const featStart = adminHtml.indexOf('id="docs-subtab-features"');
    const featEnd = adminHtml.indexOf('id="docs-subtab-improvements"');
    const section = adminHtml.slice(featStart, featEnd);
    expect(section).toMatch(/Public Website/);
  });

  it('contains Admin Dashboard section', () => {
    const featStart = adminHtml.indexOf('id="docs-subtab-features"');
    const featEnd = adminHtml.indexOf('id="docs-subtab-improvements"');
    const section = adminHtml.slice(featStart, featEnd);
    expect(section).toMatch(/Admin Dashboard/);
  });

  it('contains Database Schema section', () => {
    const featStart = adminHtml.indexOf('id="docs-subtab-features"');
    const featEnd = adminHtml.indexOf('id="docs-subtab-improvements"');
    const section = adminHtml.slice(featStart, featEnd);
    expect(section).toMatch(/Database Schema/);
  });
});

describe('Documentation tab — Improvements content', () => {
  it('contains Critical section', () => {
    const impStart = adminHtml.indexOf('id="docs-subtab-improvements"');
    expect(impStart).toBeGreaterThan(-1);
    const section = adminHtml.slice(impStart, impStart + 10000);
    expect(section).toMatch(/Critical/i);
  });

  it('contains High Priority section', () => {
    const impStart = adminHtml.indexOf('id="docs-subtab-improvements"');
    const section = adminHtml.slice(impStart);
    expect(section).toMatch(/High Priority/i);
  });

  it('contains Priority Action Plan', () => {
    const impStart = adminHtml.indexOf('id="docs-subtab-improvements"');
    const section = adminHtml.slice(impStart);
    expect(section).toMatch(/Priority Action Plan/i);
  });
});
