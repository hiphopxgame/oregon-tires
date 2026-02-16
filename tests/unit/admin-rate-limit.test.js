import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ============================================================
// 1. Rate limiting infrastructure
// ============================================================
describe('admin rate limiting — constants and functions', () => {
  it('MAX_LOGIN_ATTEMPTS constant exists and equals 5', () => {
    expect(html).toMatch(/const\s+MAX_LOGIN_ATTEMPTS\s*=\s*5\b/);
  });

  it('LOCKOUT_DURATION_MS constant exists and equals 900000 (15 minutes)', () => {
    // Accept either the literal 900000 or the expression 15 * 60 * 1000
    expect(html).toMatch(/const\s+LOCKOUT_DURATION_MS\s*=\s*(900000|15\s*\*\s*60\s*\*\s*1000)/);
  });

  it('has a getLoginAttempts function for tracking failed attempts', () => {
    expect(html).toMatch(/function\s+getLoginAttempts\s*\(/);
  });

  it('has a recordFailedAttempt function', () => {
    expect(html).toMatch(/function\s+recordFailedAttempt\s*\(/);
  });

  it('has an isLockedOut function to check lockout status', () => {
    expect(html).toMatch(/function\s+isLockedOut\s*\(/);
  });

  it('has a clearLoginAttempts function', () => {
    expect(html).toMatch(/function\s+clearLoginAttempts\s*\(/);
  });

  it('has a getRemainingLockoutTime function', () => {
    expect(html).toMatch(/function\s+getRemainingLockoutTime\s*\(/);
  });

  it('uses localStorage for attempt tracking', () => {
    expect(html).toMatch(/localStorage\.(getItem|setItem|removeItem)\s*\(\s*['"]loginAttempts['"]\s*\)/);
  });
});

// ============================================================
// 2. Login form security
// ============================================================
describe('admin rate limiting — login form security', () => {
  it('login error messages use generic "Invalid credentials" text (no email enumeration)', () => {
    // The login handler should use a translation key or literal "Invalid credentials"
    // and should NOT expose specific messages like "No account found" or "Wrong password"
    expect(html).toMatch(/invalidCredentials|Invalid credentials/);
  });

  it('has a lockout-message element in the login form area', () => {
    const lockoutEl = doc.getElementById('lockout-message');
    expect(lockoutEl).not.toBeNull();
  });

  it('lockout-message element is hidden by default', () => {
    const lockoutEl = doc.getElementById('lockout-message');
    expect(lockoutEl).not.toBeNull();
    expect(lockoutEl.classList.contains('hidden')).toBe(true);
  });

  it('login handler checks isLockedOut before attempting sign-in', () => {
    expect(html).toMatch(/isLockedOut\s*\(\s*\)/);
  });

  it('login handler calls recordFailedAttempt on failure', () => {
    expect(html).toMatch(/recordFailedAttempt\s*\(\s*\)/);
  });

  it('login handler calls clearLoginAttempts on success', () => {
    expect(html).toMatch(/clearLoginAttempts\s*\(\s*\)/);
  });
});

// ============================================================
// 3. No hardcoded superadmin email in auth logic
// ============================================================
describe('admin rate limiting — no hardcoded admin email in auth', () => {
  it('checkAdminStatus does NOT have a hardcoded email bypass', () => {
    // Extract just the checkAdminStatus function body
    const fnMatch = html.match(/function\s+checkAdminStatus\s*\([^)]*\)\s*\{([\s\S]*?)^\}/m);
    if (fnMatch) {
      const fnBody = fnMatch[1];
      // Should not contain a direct email comparison like email === 'someone@gmail.com'
      expect(fnBody).not.toMatch(/email\s*===?\s*['"][^'"]*@[^'"]*['"]/);
    }
  });

  it('showDashboard does NOT use hardcoded email for role display', () => {
    const fnMatch = html.match(/function\s+showDashboard\s*\([^)]*\)\s*\{([\s\S]*?)^(\}|\n\})/m);
    if (fnMatch) {
      const fnBody = fnMatch[1];
      expect(fnBody).not.toMatch(/===?\s*['"][^'"]*@gmail\.com['"]/);
    }
  });
});

// ============================================================
// 4. Lockout UI
// ============================================================
describe('admin rate limiting — lockout UI', () => {
  it('lockout-message element exists in the login screen', () => {
    const loginScreen = doc.getElementById('login-screen');
    expect(loginScreen).not.toBeNull();
    const lockoutEl = loginScreen.querySelector('#lockout-message');
    expect(lockoutEl).not.toBeNull();
  });

  it('lockout-message has appropriate styling classes', () => {
    const lockoutEl = doc.getElementById('lockout-message');
    expect(lockoutEl).not.toBeNull();
    // Should have some red/warning styling
    const classes = lockoutEl.className;
    expect(classes).toMatch(/red|warning|error/);
  });
});

// ============================================================
// 5. Translation keys for rate limiting messages
// ============================================================
describe('admin rate limiting — translation keys', () => {
  it('adminT.en has invalidCredentials key', () => {
    expect(html).toMatch(/invalidCredentials\s*:\s*'Invalid credentials'/);
  });

  it('adminT.es has invalidCredentials key', () => {
    // Accept both literal á and unicode escape \u00E1
    expect(html).toMatch(/invalidCredentials\s*:\s*'Credenciales inv(á|\\u00E1)lidas'/);
  });

  it('adminT.en has accountLocked key', () => {
    expect(html).toMatch(/accountLocked\s*:\s*'Too many failed attempts/);
  });

  it('adminT.es has accountLocked key', () => {
    expect(html).toMatch(/accountLocked\s*:\s*'Demasiados intentos fallidos/);
  });

  it('adminT.en has attemptsRemaining key', () => {
    expect(html).toMatch(/attemptsRemaining\s*:\s*'/);
  });

  it('adminT.es has attemptsRemaining key', () => {
    // The es block should also have attemptsRemaining
    const esBlock = html.match(/es:\s*\{[\s\S]*?attemptsRemaining\s*:/);
    expect(esBlock).not.toBeNull();
  });
});
