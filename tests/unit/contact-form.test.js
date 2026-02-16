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

describe('contact form — structure', () => {
  it('form element #contact-form exists', () => {
    const form = doc.getElementById('contact-form');
    expect(form).not.toBeNull();
    expect(form.tagName).toBe('FORM');
  });

  it('has firstName input field', () => {
    const input = doc.querySelector('#contact-form input[name="firstName"]');
    expect(input).not.toBeNull();
  });

  it('has lastName input field', () => {
    const input = doc.querySelector('#contact-form input[name="lastName"]');
    expect(input).not.toBeNull();
  });

  it('has phone input field', () => {
    const input = doc.querySelector('#contact-form input[name="phone"]');
    expect(input).not.toBeNull();
  });

  it('has email input field', () => {
    const input = doc.querySelector('#contact-form input[name="email"]');
    expect(input).not.toBeNull();
  });

  it('has message textarea field', () => {
    const textarea = doc.querySelector('#contact-form textarea[name="message"]');
    expect(textarea).not.toBeNull();
  });
});

describe('contact form — validation attributes', () => {
  it('firstName is required', () => {
    const input = doc.querySelector('#contact-form input[name="firstName"]');
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('lastName is required', () => {
    const input = doc.querySelector('#contact-form input[name="lastName"]');
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('phone is required', () => {
    const input = doc.querySelector('#contact-form input[name="phone"]');
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('email is required', () => {
    const input = doc.querySelector('#contact-form input[name="email"]');
    expect(input.hasAttribute('required')).toBe(true);
  });

  it('message is required', () => {
    const textarea = doc.querySelector('#contact-form textarea[name="message"]');
    expect(textarea.hasAttribute('required')).toBe(true);
  });

  it('email field has type="email"', () => {
    const input = doc.querySelector('#contact-form input[name="email"]');
    expect(input.getAttribute('type')).toBe('email');
  });

  it('phone field has type="tel"', () => {
    const input = doc.querySelector('#contact-form input[name="phone"]');
    expect(input.getAttribute('type')).toBe('tel');
  });
});

describe('contact form — submit button and status', () => {
  it('submit button exists with data-t="sendMessage"', () => {
    const btn = doc.querySelector('#contact-form button[type="submit"]');
    expect(btn).not.toBeNull();
    expect(btn.getAttribute('data-t')).toBe('sendMessage');
  });

  it('form status div #form-status exists', () => {
    const status = doc.getElementById('form-status');
    expect(status).not.toBeNull();
  });

  it('form status div is hidden by default (has "hidden" class)', () => {
    const status = doc.getElementById('form-status');
    expect(status.classList.contains('hidden')).toBe(true);
  });

  it('form has no action attribute (handled by JS)', () => {
    const form = doc.getElementById('contact-form');
    expect(form.hasAttribute('action')).toBe(false);
  });
});
