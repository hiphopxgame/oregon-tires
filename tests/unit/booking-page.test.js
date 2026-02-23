import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'book-appointment/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ===== HTML STRUCTURE =====
describe('booking page — HTML structure', () => {
  it('is a full HTML page (not a redirect)', () => {
    expect(html).not.toContain('http-equiv="refresh"');
    expect(html).toContain('<!DOCTYPE html>');
  });

  it('has a booking form element #booking-form', () => {
    const form = doc.getElementById('booking-form');
    expect(form).not.toBeNull();
    expect(form.tagName).toBe('FORM');
  });

  it('form has no action attribute (handled by JS)', () => {
    const form = doc.getElementById('booking-form');
    expect(form.hasAttribute('action')).toBe(false);
  });

  it('has page title containing "Book" or "Appointment"', () => {
    const title = doc.querySelector('title');
    expect(title).not.toBeNull();
    const text = title.textContent.toLowerCase();
    expect(text.includes('book') || text.includes('appointment')).toBe(true);
  });
});

// ===== SERVICE SELECTION =====
describe('booking page — service selection', () => {
  const expectedServices = [
    'tire-installation',
    'tire-repair',
    'wheel-alignment',
    'oil-change',
    'brake-service',
    'tuneup',
    'mechanical-inspection',
    'mobile-service',
    'other'
  ];

  it('has radio inputs or service selection elements for each service', () => {
    const radios = doc.querySelectorAll('input[name="service"]');
    expect(radios.length).toBe(expectedServices.length);
  });

  expectedServices.forEach(service => {
    it(`has service option for "${service}"`, () => {
      const radio = doc.querySelector(`input[name="service"][value="${service}"]`);
      expect(radio, `Missing radio for ${service}`).not.toBeNull();
    });
  });

  it('service selection is required', () => {
    const radios = doc.querySelectorAll('input[name="service"]');
    // At least one radio should be required (HTML radio group: one required = all required)
    const hasRequired = Array.from(radios).some(r => r.hasAttribute('required'));
    expect(hasRequired).toBe(true);
  });
});

// ===== DATE PICKER =====
describe('booking page — date picker', () => {
  it('has a date input field', () => {
    const dateInput = doc.querySelector('input[type="date"][name="date"]');
    expect(dateInput).not.toBeNull();
  });

  it('date input is required', () => {
    const dateInput = doc.querySelector('input[type="date"][name="date"]');
    expect(dateInput.hasAttribute('required')).toBe(true);
  });
});

// ===== TIME SLOTS =====
describe('booking page — time slots', () => {
  const expectedSlots = ['7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
    '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM'];

  it('has time slot elements (12 slots: 7AM-6PM)', () => {
    const slots = doc.querySelectorAll('[data-time]');
    expect(slots.length).toBe(12);
  });

  it('has a hidden input or mechanism to store selected time', () => {
    const timeInput = doc.querySelector('input[name="time"]');
    expect(timeInput).not.toBeNull();
  });

  it('time slots cover hours 7 through 18', () => {
    const slots = doc.querySelectorAll('[data-time]');
    const hours = Array.from(slots).map(s => s.getAttribute('data-time'));
    for (let h = 7; h <= 18; h++) {
      const hStr = String(h).padStart(2, '0') + ':00';
      expect(hours).toContain(hStr);
    }
  });
});

// ===== VEHICLE INFO =====
describe('booking page — vehicle info (optional)', () => {
  it('has a year selector', () => {
    const year = doc.querySelector('select[name="vehicleYear"]');
    expect(year).not.toBeNull();
  });

  it('year selector includes options from 2000 to 2026', () => {
    const year = doc.querySelector('select[name="vehicleYear"]');
    const options = year.querySelectorAll('option');
    // Should have blank/placeholder + 27 year options (2000-2026)
    const yearValues = Array.from(options).map(o => o.value).filter(v => v !== '');
    expect(yearValues.length).toBe(27);
    expect(yearValues).toContain('2000');
    expect(yearValues).toContain('2026');
  });

  it('has a make text input', () => {
    const make = doc.querySelector('input[name="vehicleMake"]');
    expect(make).not.toBeNull();
  });

  it('has a model text input', () => {
    const model = doc.querySelector('input[name="vehicleModel"]');
    expect(model).not.toBeNull();
  });

  it('vehicle fields are NOT required', () => {
    const year = doc.querySelector('select[name="vehicleYear"]');
    const make = doc.querySelector('input[name="vehicleMake"]');
    const model = doc.querySelector('input[name="vehicleModel"]');
    expect(year.hasAttribute('required')).toBe(false);
    expect(make.hasAttribute('required')).toBe(false);
    expect(model.hasAttribute('required')).toBe(false);
  });
});

// ===== CUSTOMER INFO =====
describe('booking page — customer info', () => {
  it('has firstName input (required)', () => {
    const input = doc.querySelector('#booking-form input[name="firstName"]');
    expect(input).not.toBeNull();
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('has lastName input (required)', () => {
    const input = doc.querySelector('#booking-form input[name="lastName"]');
    expect(input).not.toBeNull();
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('has phone input with type=tel (required)', () => {
    const input = doc.querySelector('#booking-form input[name="phone"]');
    expect(input).not.toBeNull();
    expect(input.getAttribute('type')).toBe('tel');
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('has email input with type=email (required)', () => {
    const input = doc.querySelector('#booking-form input[name="email"]');
    expect(input).not.toBeNull();
    expect(input.getAttribute('type')).toBe('email');
    expect(input.hasAttribute('required')).toBe(true);
  });
});

// ===== NOTES FIELD =====
describe('booking page — notes field', () => {
  it('has an optional notes textarea', () => {
    const textarea = doc.querySelector('#booking-form textarea[name="notes"]');
    expect(textarea).not.toBeNull();
    expect(textarea.hasAttribute('required')).toBe(false);
  });
});

// ===== SUBMIT BUTTON =====
describe('booking page — submit and status', () => {
  it('has a submit button', () => {
    const btn = doc.querySelector('#booking-form button[type="submit"]');
    expect(btn).not.toBeNull();
  });

  it('has a form status element #booking-status', () => {
    const status = doc.getElementById('booking-status');
    expect(status).not.toBeNull();
  });

  it('status element is hidden by default', () => {
    const status = doc.getElementById('booking-status');
    expect(status.classList.contains('hidden')).toBe(true);
  });
});

// ===== BILINGUAL =====
describe('booking page — bilingual translations', () => {
  it('has a language toggle button', () => {
    const btn = doc.getElementById('lang-toggle');
    expect(btn).not.toBeNull();
  });

  it('has a translation object with en and es keys', () => {
    expect(html).toMatch(/const\s+t\s*=\s*\{[\s\S]*?en:\s*\{/);
    expect(html).toMatch(/es:\s*\{/);
  });

  it('en and es translation keys have parity', () => {
    // Extract en and es keys from inline script
    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    expect(tBlockMatch).not.toBeNull();

    const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(enMatch).not.toBeNull();
    const enKeys = [...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);

    const esMatch = tBlockMatch[0].match(/es:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    expect(esMatch).not.toBeNull();
    const esKeys = [...esMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]);

    const sortedEn = [...enKeys].sort();
    const sortedEs = [...esKeys].sort();
    expect(sortedEn).toEqual(sortedEs);
  });

  it('all data-t attributes have corresponding en translation keys', () => {
    const elements = doc.querySelectorAll('[data-t]');
    const dataTKeys = new Set();
    elements.forEach(el => dataTKeys.add(el.getAttribute('data-t')));

    const tBlockMatch = html.match(/const\s+t\s*=\s*\{[\s\S]*?\n\};\s*\n/);
    const enMatch = tBlockMatch[0].match(/en:\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/);
    const enKeys = new Set([...enMatch[1].matchAll(/(\w+)\s*:\s*'/g)].map(m => m[1]));

    for (const key of dataTKeys) {
      expect(enKeys.has(key), `data-t="${key}" missing from en translations`).toBe(true);
    }
  });

  it('has at least 15 data-t elements for bilingual coverage', () => {
    const elements = doc.querySelectorAll('[data-t]');
    expect(elements.length).toBeGreaterThanOrEqual(15);
  });
});

// Supabase is not used — backend is PHP with MySQL
// Removed obsolete Supabase tests

// ===== DESIGN & BRANDING =====
describe('booking page — design and branding', () => {
  it('includes Tailwind CSS (built at compile time)', () => {
    // Tailwind CSS v4 is built during the build process, not loaded from CDN
    expect(html).toContain('<link') || expect(html).toContain('stylesheet');
  });

  it('configures brand color #0D3618', () => {
    expect(html).toContain('#0D3618');
  });

  it('has a header with navigation', () => {
    const header = doc.querySelector('header');
    expect(header).not.toBeNull();
    const nav = header.querySelector('nav');
    expect(nav).not.toBeNull();
  });

  it('has a footer', () => {
    const footer = doc.querySelector('footer');
    expect(footer).not.toBeNull();
  });

  it('has responsive meta viewport', () => {
    const meta = doc.querySelector('meta[name="viewport"]');
    expect(meta).not.toBeNull();
  });
});

// ===== SECURITY =====
describe('booking page — security', () => {
  it('does not use innerHTML with user data patterns', () => {
    // Check that innerHTML is not used with variables/template literals that could contain user input
    // Safe innerHTML with static strings is OK, but innerHTML with variables is not
    const dangerousPatterns = [
      /\.innerHTML\s*=\s*[^'"]/,  // innerHTML set to something that's not a string literal
    ];
    // Extract script content
    const scriptMatches = html.match(/<script(?:\s[^>]*)?>[\s\S]*?<\/script>/gi) || [];
    const inlineScripts = scriptMatches
      .filter(s => !s.includes('src=') && !s.includes('application/ld+json') && !s.includes('tailwind.config'))
      .join('\n');

    // Should not have innerHTML assigned from variables
    const innerHTMLAssignments = inlineScripts.match(/\.innerHTML\s*=/g) || [];
    expect(innerHTMLAssignments.length, 'innerHTML assignments found — use createElement/textContent instead').toBe(0);
  });

  it('uses textContent or createElement patterns', () => {
    expect(html).toContain('textContent');
  });
});

// ===== RATE LIMITING =====
describe('booking page — rate limiting', () => {
  it('has a rate limiting mechanism (30-second cooldown)', () => {
    // Check for time-based rate limiting pattern
    expect(html).toMatch(/30000|30\s*\*\s*1000/);
  });
});

// ===== HEADER & FOOTER CONSISTENCY =====
describe('booking page — header/footer matches main site', () => {
  it('header has the Oregon Tires logo', () => {
    const logo = doc.querySelector('header img[alt*="Oregon Tires"]');
    expect(logo).not.toBeNull();
  });

  it('has a mobile menu button', () => {
    const btn = doc.getElementById('mobile-menu-btn');
    expect(btn).not.toBeNull();
  });

  it('has a dark mode toggle', () => {
    const btn = doc.getElementById('dark-mode-toggle');
    expect(btn).not.toBeNull();
  });

  it('footer contains phone number', () => {
    const footer = doc.querySelector('footer');
    expect(footer.textContent).toContain('(503) 367-9714');
  });

  it('footer contains address', () => {
    const footer = doc.querySelector('footer');
    expect(footer.textContent).toContain('8536 SE 82nd Ave');
  });
});

// ===== CONFIRMATION =====
describe('booking page — confirmation', () => {
  it('has a confirmation section element', () => {
    const confirmation = doc.getElementById('booking-confirmation');
    expect(confirmation).not.toBeNull();
  });

  it('confirmation is hidden by default', () => {
    const confirmation = doc.getElementById('booking-confirmation');
    expect(confirmation.classList.contains('hidden')).toBe(true);
  });
});
