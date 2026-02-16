import { describe, it, expect } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

// Extract only the <script> content (skip HTML/docs that mention console.error in prose)
const scriptBlocks = [...html.matchAll(/<script(?:\s[^>]*)?>([^]*?)<\/script>/gi)]
  .map(m => m[1])
  .filter(s => s.includes('signInWithPassword') || s.includes('updateAccountPassword') || s.includes('updateAccountEmail'));
const scriptContent = scriptBlocks.join('\n');

// ============================================================
// 1. No credential-related words in console.log / console.error string args
// ============================================================
describe('password safety — no credential words in console output', () => {
  it('no console.log call contains the word "password" in its string argument', () => {
    const pattern = /console\.log\s*\([^)]*['"][^'"]*password[^'"]*['"]/gi;
    const matches = scriptContent.match(pattern);
    expect(matches, 'console.log should not contain "password" in string args').toBeNull();
  });

  it('no console.error call contains the word "password" in its string argument', () => {
    const pattern = /console\.error\s*\([^)]*['"][^'"]*password[^'"]*['"]/gi;
    const matches = scriptContent.match(pattern);
    expect(matches, 'console.error should not contain "password" in string args').toBeNull();
  });

  it('no console.log call contains the word "credential" in its string argument', () => {
    const pattern = /console\.log\s*\([^)]*['"][^'"]*credential[^'"]*['"]/gi;
    const matches = scriptContent.match(pattern);
    expect(matches, 'console.log should not contain "credential" in string args').toBeNull();
  });

  it('no console.error call contains the word "credential" in its string argument', () => {
    const pattern = /console\.error\s*\([^)]*['"][^'"]*credential[^'"]*['"]/gi;
    const matches = scriptContent.match(pattern);
    expect(matches, 'console.error should not contain "credential" in string args').toBeNull();
  });
});

// ============================================================
// 2. No full error object logging near auth code
// ============================================================
describe('password safety — no full error object logging near auth code', () => {
  it('login catch block does not log the full error object with console.error(err)', () => {
    const loginBlock = scriptContent.match(/signInWithPassword[\s\S]*?catch\s*\(\s*(\w+)\s*\)[\s\S]*?(?:finally|function\s)/);
    if (loginBlock) {
      const catchVar = loginBlock[1];
      const catchBody = loginBlock[0].match(/catch\s*\(\s*\w+\s*\)\s*\{?([^}]*)/)?.[1] || '';
      const logsFullObj = new RegExp(`console\\.(error|log)\\s*\\(\\s*${catchVar}\\s*\\)`).test(catchBody);
      expect(logsFullObj, `Login catch block should not log full error object with console.error(${catchVar})`).toBe(false);
    }
  });

  it('no console.error(e) or console.error(err) or console.error(error) near auth functions', () => {
    const authPatterns = [
      /signInWithPassword[\s\S]{0,500}/g,
      /updateAccountPassword[\s\S]{0,500}/g,
      /updateAccountEmail[\s\S]{0,500}/g,
    ];

    for (const pattern of authPatterns) {
      const matches = scriptContent.matchAll(pattern);
      for (const m of matches) {
        const region = m[0];
        const bareLog = /console\.(error|log)\s*\(\s*(?:e|err|error)\s*\)/.test(region);
        expect(bareLog, `Found bare console.error/log(error) near auth code: ${region.substring(0, 80)}...`).toBe(false);
      }
    }
  });
});

// ============================================================
// 3. Login failure shows a generic message
// ============================================================
describe('password safety — generic error messages', () => {
  it('login error handler uses a generic/translated message, not raw err.message', () => {
    const loginHandler = scriptContent.match(
      /getElementById\s*\(\s*['"]login-form['"]\s*\)[\s\S]*?catch\s*\(\s*\w+\s*\)\s*\{([^}]*)\}/
    );
    expect(loginHandler, 'login-form handler with catch block should exist').not.toBeNull();

    const catchBody = loginHandler[1];

    // The catch block should NOT directly set textContent = err.message
    const usesRawMessage = /(?:textContent|innerText)\s*=\s*(?:err|error|e)\.message/.test(catchBody);
    expect(usesRawMessage, 'Login catch should not show raw err.message to user').toBe(false);
  });

  it('login error message references a translation key or generic string', () => {
    const loginHandler = scriptContent.match(
      /getElementById\s*\(\s*['"]login-form['"]\s*\)[\s\S]*?catch\s*\(\s*\w+\s*\)\s*\{([^}]*)\}/
    );
    expect(loginHandler).not.toBeNull();

    const catchBody = loginHandler[1];

    const usesTranslation = /adminT\s*\[/.test(catchBody);
    const usesGenericString = /['"](?:Invalid credentials|Login failed|Authentication failed|Sign in failed)['"]/i.test(catchBody);
    expect(
      usesTranslation || usesGenericString,
      'Login error should use translated or generic message'
    ).toBe(true);
  });

  it('password update error handler does NOT expose raw error.message to user', () => {
    // Match the updateAccountPassword function body up to the next function or end
    const pwHandler = scriptContent.match(
      /function\s+updateAccountPassword\s*\(\s*\)[\s\S]*?(?=\nasync function\s|\nfunction\s|\n\/\/\s*Close)/
    );
    expect(pwHandler, 'updateAccountPassword function should exist in script').not.toBeNull();

    const body = pwHandler[0];
    // Should not have showToast('Error: ' + error.message) — leaks Supabase error details
    const exposesRaw = /showToast\s*\(\s*['"]Error:\s*['"]\s*\+\s*error\.message/.test(body);
    expect(exposesRaw, 'Password update should not expose raw error.message in showToast').toBe(false);
  });

  it('email update error handler does NOT expose raw error.message to user', () => {
    const emailHandler = scriptContent.match(
      /function\s+updateAccountEmail\s*\(\s*\)[\s\S]*?(?=\nasync function\s|\nfunction\s|\n\/\/\s*Close)/
    );
    expect(emailHandler, 'updateAccountEmail function should exist in script').not.toBeNull();

    const body = emailHandler[0];
    const exposesRaw = /showToast\s*\(\s*['"]Error:\s*['"]\s*\+\s*error\.message/.test(body);
    expect(exposesRaw, 'Email update should not expose raw error.message in showToast').toBe(false);
  });
});

// ============================================================
// 4. Translation keys exist for auth error messages
// ============================================================
describe('password safety — translation keys for auth errors', () => {
  it('adminT.en has invalidCredentials key', () => {
    expect(html).toMatch(/en:\s*\{[\s\S]*?invalidCredentials\s*:\s*'/);
  });

  it('adminT.es has invalidCredentials key', () => {
    expect(html).toMatch(/es:\s*\{[\s\S]*?invalidCredentials\s*:\s*'/);
  });

  it('adminT.en has passwordUpdateFailed key', () => {
    expect(html).toMatch(/en:\s*\{[\s\S]*?passwordUpdateFailed\s*:\s*'/);
  });

  it('adminT.es has passwordUpdateFailed key', () => {
    expect(html).toMatch(/es:\s*\{[\s\S]*?passwordUpdateFailed\s*:\s*'/);
  });

  it('adminT.en has emailUpdateFailed key', () => {
    expect(html).toMatch(/en:\s*\{[\s\S]*?emailUpdateFailed\s*:\s*'/);
  });

  it('adminT.es has emailUpdateFailed key', () => {
    expect(html).toMatch(/es:\s*\{[\s\S]*?emailUpdateFailed\s*:\s*'/);
  });
});
