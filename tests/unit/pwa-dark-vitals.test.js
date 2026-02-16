import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync, existsSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');
const adminHtml = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ─── #21 Service Worker ─────────────────────────────────────────────────────

describe('#21 service worker — sw.js file', () => {
  it('public_html/sw.js file exists', () => {
    const swPath = resolve(ROOT, 'sw.js');
    expect(existsSync(swPath)).toBe(true);
  });

  it('sw.js contains install event listener', () => {
    const sw = readFileSync(resolve(ROOT, 'sw.js'), 'utf-8');
    expect(sw).toMatch(/self\.addEventListener\(\s*['"]install['"]/);
  });

  it('sw.js contains fetch event listener', () => {
    const sw = readFileSync(resolve(ROOT, 'sw.js'), 'utf-8');
    expect(sw).toMatch(/self\.addEventListener\(\s*['"]fetch['"]/);
  });

  it('sw.js defines a cache name', () => {
    const sw = readFileSync(resolve(ROOT, 'sw.js'), 'utf-8');
    expect(sw).toMatch(/CACHE_NAME\s*=/);
  });
});

describe('#21 service worker — registration in index.html', () => {
  it('index.html contains service worker registration code', () => {
    expect(html).toContain('navigator.serviceWorker.register');
  });

  it('registration is guarded by feature detection', () => {
    expect(html).toMatch(/['"]serviceWorker['"]\s*in\s*navigator/);
  });
});

// ─── PWA Manifest ───────────────────────────────────────────────────────────

describe('PWA — manifest.json', () => {
  it('manifest.json file exists', () => {
    expect(existsSync(resolve(ROOT, 'manifest.json'))).toBe(true);
  });

  it('manifest.json is valid JSON with required fields', () => {
    const manifest = JSON.parse(readFileSync(resolve(ROOT, 'manifest.json'), 'utf-8'));
    expect(manifest.name).toBeDefined();
    expect(manifest.short_name).toBeDefined();
    expect(manifest.start_url).toBe('/');
    expect(manifest.display).toBe('standalone');
    expect(manifest.theme_color).toBeDefined();
    expect(manifest.icons).toBeDefined();
    expect(manifest.icons.length).toBeGreaterThan(0);
  });

  it('index.html links to manifest.json', () => {
    expect(html).toContain('rel="manifest"');
    expect(html).toContain('manifest.json');
  });
});

// ─── Language toggle updates document.documentElement.lang ──────────────────

describe('language toggle — updates html lang attribute', () => {
  it('public site toggleLanguage sets document.documentElement.lang', () => {
    expect(html).toMatch(/function toggleLanguage[\s\S]*?document\.documentElement\.lang\s*=\s*currentLang/);
  });

  it('admin toggleAdminLanguage sets document.documentElement.lang', () => {
    expect(adminHtml).toMatch(/function toggleAdminLanguage[\s\S]*?document\.documentElement\.lang\s*=\s*currentLang/);
  });
});

// ─── #22 Dark Mode (public site) ────────────────────────────────────────────

describe('#22 dark mode — toggle button', () => {
  it('HTML contains a dark mode toggle button with id="dark-mode-toggle"', () => {
    const btn = doc.getElementById('dark-mode-toggle');
    expect(btn).not.toBeNull();
  });

  it('dark mode toggle button has aria-label', () => {
    const btn = doc.getElementById('dark-mode-toggle');
    expect(btn.hasAttribute('aria-label')).toBe(true);
  });
});

describe('#22 dark mode — toggle function', () => {
  it('a toggleDarkMode function exists in the JS', () => {
    expect(html).toMatch(/function\s+toggleDarkMode\s*\(/);
  });

  it('the function toggles a "dark" class on document.documentElement or <html>', () => {
    // The function may use an intermediate variable (e.g. const html = document.documentElement; html.classList.toggle('dark'))
    const hasDirect = /documentElement\.classList\.toggle\(\s*['"]dark['"]\s*\)/.test(html);
    const hasViaVariable = /document\.documentElement[\s\S]*?\.classList\.toggle\(\s*['"]dark['"]\s*\)/.test(html);
    expect(hasDirect || hasViaVariable).toBe(true);
  });
});

describe('#22 dark mode — persistence', () => {
  it('dark mode preference is saved to localStorage', () => {
    expect(html).toMatch(/localStorage\.setItem\(\s*['"]theme['"]/);
  });

  it('dark mode is read from localStorage on load', () => {
    expect(html).toMatch(/localStorage\.getItem\(\s*['"]theme['"]/);
  });

  it('system preference is detected via prefers-color-scheme', () => {
    expect(html).toContain('prefers-color-scheme');
  });
});

describe('#22 dark mode — Tailwind config', () => {
  it('Tailwind config includes darkMode setting', () => {
    expect(html).toMatch(/darkMode\s*:\s*['"]class['"]/);
  });
});

describe('#22 dark mode — dark: utility classes on key elements', () => {
  it('body has dark:bg-gray-900', () => {
    const body = doc.querySelector('body');
    expect(body.className).toContain('dark:bg-gray-900');
  });

  it('body has dark:text-gray-100', () => {
    const body = doc.querySelector('body');
    expect(body.className).toContain('dark:text-gray-100');
  });
});

// ─── #23 Web Vitals Monitoring ──────────────────────────────────────────────

describe('#23 web vitals — performance observers', () => {
  it('code contains a PerformanceObserver guard', () => {
    expect(html).toMatch(/['"]PerformanceObserver['"]\s*in\s*window/);
  });

  it('code measures LCP (largest-contentful-paint)', () => {
    expect(html).toContain('largest-contentful-paint');
  });

  it('code measures CLS (layout-shift)', () => {
    expect(html).toContain('layout-shift');
  });

  it('results are sent to GA4 via gtag with web_vitals event', () => {
    expect(html).toMatch(/gtag\(\s*['"]event['"]\s*,\s*['"]web_vitals['"]/);
  });

  it('LCP value is reported', () => {
    expect(html).toMatch(/event_label:\s*['"]LCP['"]/);
  });

  it('CLS value is reported', () => {
    expect(html).toMatch(/event_label:\s*['"]CLS['"]/);
  });
});
