import { describe, it, expect } from 'vitest';
import { existsSync, readFileSync, readdirSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');

describe('public_html — 403 prevention', () => {
  it('has a root index.html', () => {
    expect(existsSync(resolve(ROOT, 'index.html'))).toBe(true);
  });

  it('root index.html is non-empty and contains <!DOCTYPE html>', () => {
    const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');
    expect(html.length).toBeGreaterThan(100);
    expect(html).toMatch(/<!DOCTYPE html>/i);
  });

  it('has .htaccess at root', () => {
    expect(existsSync(resolve(ROOT, '.htaccess'))).toBe(true);
  });

  it('.htaccess sets DirectoryIndex to index.html', () => {
    const htaccess = readFileSync(resolve(ROOT, '.htaccess'), 'utf-8');
    expect(htaccess).toMatch(/DirectoryIndex\s+index\.html/);
  });
});

describe('public_html — required directories and files', () => {
  it('has admin/index.html', () => {
    expect(existsSync(resolve(ROOT, 'admin/index.html'))).toBe(true);
  });

  it('has book-appointment/index.html', () => {
    expect(existsSync(resolve(ROOT, 'book-appointment/index.html'))).toBe(true);
  });

  it('has assets/ with logo.png, hero-bg.png, favicon.png', () => {
    const assetsDir = resolve(ROOT, 'assets');
    expect(existsSync(assetsDir)).toBe(true);
    const files = readdirSync(assetsDir);
    expect(files).toContain('logo.png');
    expect(files).toContain('hero-bg.png');
    expect(files).toContain('favicon.png');
  });

  it('has images/ directory with service images', () => {
    const imagesDir = resolve(ROOT, 'images');
    expect(existsSync(imagesDir)).toBe(true);
    const files = readdirSync(imagesDir);
    expect(files.length).toBeGreaterThanOrEqual(6);
    expect(files).toContain('tire-services.jpg');
    expect(files).toContain('auto-maintenance.jpg');
  });
});

describe('public_html — no lovable-uploads references', () => {
  it('index.html has no lovable-uploads in href/src attributes', () => {
    const html = readFileSync(resolve(ROOT, 'index.html'), 'utf-8');
    expect(html).not.toMatch(/(?:href|src)="[^"]*\/lovable-uploads\//);
  });

  it('admin/index.html has no lovable-uploads in src/href attributes', () => {
    const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');
    expect(html).not.toMatch(/(?:href|src)="[^"]*\/lovable-uploads\//);
  });

  it('has no lovable-uploads/ directory', () => {
    expect(existsSync(resolve(ROOT, 'lovable-uploads'))).toBe(false);
  });
});

describe('public_html — .htaccess security', () => {
  it('blocks access to dotfiles', () => {
    const htaccess = readFileSync(resolve(ROOT, '.htaccess'), 'utf-8');
    expect(htaccess).toMatch(/\.ht/);
  });

  it('sets error document for 404', () => {
    const htaccess = readFileSync(resolve(ROOT, '.htaccess'), 'utf-8');
    expect(htaccess).toMatch(/ErrorDocument\s+404/);
  });
});
