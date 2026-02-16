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

describe('navigation — nav links', () => {
  const expectedSections = [
    { name: 'home', href: '#home' },
    { name: 'services', href: '#services' },
    { name: 'about', href: '#about' },
    { name: 'reviews', href: '#reviews' },
    { name: 'gallery', href: '#gallery' },
    { name: 'contact', href: '#contact' },
  ];

  for (const { name, href } of expectedSections) {
    it(`has nav link for "${name}" pointing to ${href}`, () => {
      const link = doc.querySelector(`nav a[href="${href}"], #mobile-menu a[href="${href}"]`);
      expect(link, `nav link for ${name} not found`).not.toBeNull();
    });
  }

  it('all nav links use anchor hrefs (#section)', () => {
    const navLinks = doc.querySelectorAll('nav a[href^="#"]');
    expect(navLinks.length).toBeGreaterThanOrEqual(6);
    navLinks.forEach(link => {
      expect(link.getAttribute('href')).toMatch(/^#/);
    });
  });
});

describe('navigation — target sections exist', () => {
  const sectionIds = ['home', 'services', 'about', 'reviews', 'gallery', 'contact'];

  for (const id of sectionIds) {
    it(`section #${id} exists in the document`, () => {
      const section = doc.getElementById(id);
      expect(section, `section #${id} not found`).not.toBeNull();
    });
  }
});

describe('navigation — mobile menu', () => {
  it('mobile menu element #mobile-menu exists', () => {
    const menu = doc.getElementById('mobile-menu');
    expect(menu).not.toBeNull();
  });

  it('mobile menu has hidden class by default', () => {
    const menu = doc.getElementById('mobile-menu');
    expect(menu.classList.contains('hidden')).toBe(true);
  });
});

describe('navigation — header branding', () => {
  it('logo image exists in header', () => {
    const header = doc.querySelector('header');
    expect(header).not.toBeNull();
    const logo = header.querySelector('img[src*="logo"]');
    expect(logo, 'logo image not found in header').not.toBeNull();
  });

  it('"Schedule Service" link exists pointing to book-appointment', () => {
    const link = doc.querySelector('a[href="/book-appointment"]');
    expect(link).not.toBeNull();
    expect(link.getAttribute('data-t')).toBe('scheduleService');
  });
});

describe('navigation — sticky header', () => {
  it('header has sticky positioning classes (sticky, top-0, z-50)', () => {
    const header = doc.querySelector('header');
    expect(header).not.toBeNull();
    expect(header.classList.contains('sticky')).toBe(true);
    expect(header.classList.contains('top-0')).toBe(true);
    expect(header.classList.contains('z-50')).toBe(true);
  });
});

describe('navigation — smooth scroll', () => {
  it('smooth scroll CSS rule exists (scroll-behavior: smooth)', () => {
    expect(html).toMatch(/scroll-behavior:\s*smooth/);
  });
});
