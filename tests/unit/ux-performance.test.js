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

// ===== #5 Image Lazy Loading =====
describe('#5 image lazy loading', () => {
  it('logo img has loading="eager" and fetchpriority="high"', () => {
    const logo = doc.querySelector('header img[src*="logo"]');
    expect(logo).not.toBeNull();
    expect(logo.getAttribute('loading')).toBe('eager');
    expect(logo.getAttribute('fetchpriority')).toBe('high');
  });

  it('gallery image generation JS uses loading="lazy" and decoding="async" on img tags', () => {
    // Check that the loadGalleryForSite function includes lazy loading attributes
    expect(html).toMatch(/loading=.*lazy/);
    expect(html).toMatch(/decoding=.*async/);
    // Specifically in the gallery img template
    const galleryImgPattern = /<img[^>]*src="\$\{img\.image_url\}"[^>]*loading="lazy"[^>]*decoding="async"/;
    const galleryImgPatternAlt = /loading="lazy"[^>]*decoding="async"[^>]*src="\$\{img\.image_url\}"/;
    const hasLazyGallery = galleryImgPattern.test(html) || galleryImgPatternAlt.test(html);
    expect(hasLazyGallery).toBe(true);
  });

  it('service image tryLoadImage function uses loading="lazy" on img tags', () => {
    // The tryLoadImage function creates Image objects; check for lazy attribute
    const tryLoadPattern = /tryLoadImage[\s\S]*?img\.loading\s*=\s*['"]lazy['"]/;
    expect(html).toMatch(tryLoadPattern);
  });
});

// ===== #6 CDN Scripts Loaded =====
describe('#6 CDN scripts loaded correctly', () => {
  it('Tailwind CSS script tag is present in head', () => {
    const scripts = doc.querySelectorAll('head script[src*="tailwindcss"]');
    expect(scripts.length).toBeGreaterThanOrEqual(1);
  });

  it('Supabase SDK script tag is present in head', () => {
    const scripts = doc.querySelectorAll('head script[src*="supabase"]');
    expect(scripts.length).toBeGreaterThanOrEqual(1);
  });

  it('Tailwind CDN does NOT have defer (breaks inline tailwind.config)', () => {
    const script = doc.querySelector('head script[src*="tailwindcss"]');
    expect(script.hasAttribute('defer')).toBe(false);
  });

  it('Supabase SDK does NOT have defer (breaks inline supabase.createClient)', () => {
    const script = doc.querySelector('head script[src*="supabase"]');
    expect(script.hasAttribute('defer')).toBe(false);
  });
});

// ===== #7 Accessibility (public site) =====
describe('#7 accessibility', () => {
  it('mobile menu button has aria-label attribute', () => {
    const btn = doc.querySelector('button.md\\:hidden, header button[aria-label]');
    expect(btn).not.toBeNull();
    expect(btn.hasAttribute('aria-label')).toBe(true);
  });

  it('mobile menu button has aria-expanded attribute', () => {
    const btn = doc.querySelector('button[aria-label*="navigation"], button[aria-label*="menu"]');
    expect(btn).not.toBeNull();
    expect(btn.hasAttribute('aria-expanded')).toBe(true);
  });

  it('language toggle buttons have aria-label attributes', () => {
    const langToggle = doc.getElementById('lang-toggle');
    expect(langToggle).not.toBeNull();
    expect(langToggle.hasAttribute('aria-label')).toBe(true);

    const footerToggle = doc.getElementById('footer-lang-toggle');
    expect(footerToggle).not.toBeNull();
    expect(footerToggle.hasAttribute('aria-label')).toBe(true);
  });

  it('contact form inputs have associated labels', () => {
    const form = doc.getElementById('contact-form');
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
      const name = input.getAttribute('name');
      // Each input should have a label as a preceding sibling or parent label
      const parentDiv = input.closest('div');
      const label = parentDiv ? parentDiv.querySelector('label') : null;
      expect(label, `input "${name}" should have a label`).not.toBeNull();
    });
  });

  it('main-content landmark exists', () => {
    const mainContent = doc.getElementById('main-content');
    expect(mainContent).not.toBeNull();
  });
});

// ===== #8 Color Contrast =====
describe('#8 color contrast', () => {
  it('no text-yellow-400 class on buttons', () => {
    const buttons = doc.querySelectorAll('button, a[class*="bg-yellow"]');
    buttons.forEach(btn => {
      expect(btn.classList.contains('text-yellow-400')).toBe(false);
    });
  });

  it('yellow buttons use text-gray-900 or darker text', () => {
    const yellowBtns = doc.querySelectorAll('[class*="bg-yellow"]');
    yellowBtns.forEach(btn => {
      const classes = btn.className;
      // Should have dark text, not yellow text
      const hasDarkText = classes.includes('text-black') ||
                          classes.includes('text-gray-900') ||
                          classes.includes('text-brand');
      expect(hasDarkText, `button with bg-yellow should have dark text: ${classes}`).toBe(true);
    });
  });

  it('no hover:text-yellow-200 states (fails WCAG)', () => {
    const allElements = doc.querySelectorAll('*');
    allElements.forEach(el => {
      if (el.tagName === 'A' || el.tagName === 'BUTTON') {
        expect(el.className.includes('hover:text-yellow-200')).toBe(false);
      }
    });
  });
});

// ===== #9 Form Validation + Loading State =====
describe('#9 form validation + loading state', () => {
  it('contact form email input has type="email"', () => {
    const input = doc.querySelector('#contact-form input[name="email"]');
    expect(input.getAttribute('type')).toBe('email');
  });

  it('submit button has id="contact-submit"', () => {
    const btn = doc.getElementById('contact-submit');
    expect(btn).not.toBeNull();
    expect(btn.getAttribute('type')).toBe('submit');
  });

  it('submit button has disabled styling classes', () => {
    const btn = doc.getElementById('contact-submit');
    expect(btn.className).toContain('disabled:opacity-50');
    expect(btn.className).toContain('disabled:cursor-not-allowed');
  });

  it('JS code contains logic to disable button during submission', () => {
    expect(html).toMatch(/submitBtn\.disabled\s*=\s*true/);
  });

  it('JS code contains "Sending" or loading text during submission', () => {
    const hasSending = html.includes('Sending...') || html.includes('sending');
    expect(hasSending).toBe(true);
  });

  it('JS re-enables button in finally block', () => {
    expect(html).toMatch(/finally\s*\{[\s\S]*?submitBtn\.disabled\s*=\s*false/);
  });

  it('translation keys include sending text', () => {
    expect(html).toMatch(/sending:\s*'Sending\.\.\.'/);
    expect(html).toMatch(/sending:\s*'Enviando\.\.\.'/);
  });
});

// ===== #15 Lazy-Load Google Maps =====
describe('#15 lazy-load Google Maps', () => {
  it('Google Maps iframe does NOT have a src attribute on initial load', () => {
    const iframe = doc.querySelector('#map-frame');
    expect(iframe).not.toBeNull();
    // Should not have src, should have data-src
    const src = iframe.getAttribute('src');
    expect(!src || src === '').toBe(true);
    expect(iframe.hasAttribute('data-src')).toBe(true);
  });

  it('an IntersectionObserver is used for the maps section', () => {
    expect(html).toMatch(/IntersectionObserver/);
    expect(html).toMatch(/mapObserver|map-frame/);
  });

  it('a placeholder element exists where the map will load', () => {
    const placeholder = doc.getElementById('map-placeholder');
    expect(placeholder).not.toBeNull();
  });

  it('iframe has id="map-frame" and is initially hidden', () => {
    const iframe = doc.getElementById('map-frame');
    expect(iframe).not.toBeNull();
    expect(iframe.classList.contains('hidden')).toBe(true);
  });
});

// ===== #16 Gallery Loading Skeletons =====
describe('#16 gallery loading skeletons', () => {
  it('gallery loading state contains animate-pulse skeleton elements', () => {
    const gallery = doc.getElementById('gallery-section-grid');
    const skeletons = gallery.querySelectorAll('.animate-pulse');
    expect(skeletons.length).toBeGreaterThanOrEqual(3);
  });

  it('no plain "Loading gallery..." text as the only loading indicator', () => {
    const gallery = doc.getElementById('gallery-section-grid');
    // The gallery should have skeleton divs, not just text
    const skeletons = gallery.querySelectorAll('.animate-pulse');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('skeleton elements have bg-gray-200 and rounded classes', () => {
    const gallery = doc.getElementById('gallery-section-grid');
    const skeletons = gallery.querySelectorAll('.animate-pulse');
    skeletons.forEach(s => {
      expect(s.classList.contains('bg-gray-200')).toBe(true);
      expect(s.className).toMatch(/rounded/);
    });
  });
});

// ===== #19 Click-to-Call =====
describe('#19 click-to-call', () => {
  it('phone number in top bar is wrapped in <a href="tel:...">', () => {
    const topBar = doc.querySelector('.bg-brand.text-white.text-sm');
    expect(topBar).not.toBeNull();
    const telLink = topBar.querySelector('a[href*="tel:"]');
    expect(telLink).not.toBeNull();
    expect(telLink.getAttribute('href')).toContain('5033679714');
  });

  it('phone number in contact section is wrapped in <a href="tel:...">', () => {
    const contactSection = doc.getElementById('contact');
    expect(contactSection).not.toBeNull();
    const telLink = contactSection.querySelector('a[href*="tel:5033679714"]');
    expect(telLink).not.toBeNull();
  });

  it('the tel: href contains the correct number (5033679714)', () => {
    const telLinks = doc.querySelectorAll('a[href*="tel:"]');
    expect(telLinks.length).toBeGreaterThanOrEqual(2);
    telLinks.forEach(link => {
      expect(link.getAttribute('href')).toContain('5033679714');
    });
  });
});

// ===== #20 Skip-to-Content Link =====
describe('#20 skip-to-content link', () => {
  it('a skip-to-content <a> tag exists near the top of <body>', () => {
    const skipLink = doc.querySelector('a[href="#main-content"]');
    expect(skipLink).not.toBeNull();
  });

  it('it has href="#main-content"', () => {
    const skipLink = doc.querySelector('a[href="#main-content"]');
    expect(skipLink.getAttribute('href')).toBe('#main-content');
  });

  it('it has sr-only or visually hidden class', () => {
    const skipLink = doc.querySelector('a[href="#main-content"]');
    expect(skipLink.classList.contains('sr-only')).toBe(true);
  });

  it('it has focus:not-sr-only or becomes visible on focus', () => {
    const skipLink = doc.querySelector('a[href="#main-content"]');
    expect(skipLink.className).toContain('focus:not-sr-only');
  });

  it('a corresponding id="main-content" element exists', () => {
    const mainContent = doc.getElementById('main-content');
    expect(mainContent).not.toBeNull();
  });
});
