import { describe, it, expect } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const htaccess = readFileSync(resolve(ROOT, '.htaccess'), 'utf-8');

describe('.htaccess — HTTPS redirect', () => {
  it('contains RewriteEngine On', () => {
    expect(htaccess).toMatch(/RewriteEngine\s+On/);
  });

  it('contains RewriteCond %{HTTPS} off', () => {
    expect(htaccess).toMatch(/RewriteCond\s+%\{HTTPS\}\s+off/);
  });

  it('contains RewriteRule redirecting to HTTPS with 301', () => {
    expect(htaccess).toMatch(/RewriteRule\s+.*https:\/\/%\{HTTP_HOST\}.*\[.*R=301.*\]/);
  });

  it('HTTPS redirect block is wrapped in <IfModule mod_rewrite.c>', () => {
    const rewriteBlockRegex = /<IfModule\s+mod_rewrite\.c>[\s\S]*?RewriteEngine\s+On[\s\S]*?RewriteCond\s+%\{HTTPS\}\s+off[\s\S]*?RewriteRule[\s\S]*?<\/IfModule>/;
    expect(htaccess).toMatch(rewriteBlockRegex);
  });
});

describe('.htaccess — Block sensitive files', () => {
  it('contains FilesMatch directive', () => {
    expect(htaccess).toMatch(/<FilesMatch/);
  });

  it('blocks .sql files', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*\\\.sql/);
  });

  it('blocks .env files', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*\\\.env/);
  });

  it('blocks .git files', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*\\\.git/);
  });

  it('blocks package.json', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*package\\\.json/);
  });

  it('blocks .bak files', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*\\\.bak/);
  });

  it('blocks .tmp files', () => {
    expect(htaccess).toMatch(/<FilesMatch[^>]*\\\.tmp/);
  });

  it('contains Require all denied inside the FilesMatch block', () => {
    const filesMatchBlock = /<FilesMatch[^>]*>[\s\S]*?Require\s+all\s+denied[\s\S]*?<\/FilesMatch>/;
    expect(htaccess).toMatch(filesMatchBlock);
  });
});
