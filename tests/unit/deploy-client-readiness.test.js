import { describe, it, expect } from 'vitest';
import { existsSync, readFileSync, readdirSync, statSync } from 'fs';
import { resolve } from 'path';

const PUBLIC = resolve(import.meta.dirname, '../../public_html');

describe('Client Deployment Readiness', () => {

  describe('CLI Scripts', () => {
    const requiredScripts = [
      'create-admins-feb2026.php',
      'create-joslyn-admin.php',
      'fetch-google-reviews.php',
      'fetch-inbound-emails.php',
      'generate-vapid-keys.php',
      'health-monitor.php',
      'indexnow-submit.php',
      'list-admins.php',
      'resend-setup-emails.php',
      'retry-calendar-sync.php',
      'run-migration-063.php',
      'seed-email-templates.php',
      'send-admin-credentials.php',
      'send-admin-invite.php',
      'send-estimate-reminders.php',
      'send-platform-overview.php',
      'send-push-notifications.php',
      'send-reminders.php',
      'send-review-requests.php',
      'send-ro-guide-email.php',
      'send-service-reminders.php',
      'send-surveys.php',
      'send-welcome-emails.php',
      'send-workflow-update-email.php',
      'sync-admins-to-members.php',
      'sync-google-business.php',
      'test-email-account.php',
      'test-smtp-debug.php',
    ];

    it.each(requiredScripts)('cli/%s exists', (script) => {
      const filepath = resolve(PUBLIC, 'cli', script);
      expect(existsSync(filepath), `Missing CLI script: cli/${script}`).toBe(true);
    });
  });

  describe('Include Files', () => {
    const requiredIncludes = [
      'bootstrap.php',
      'db.php',
      'auth.php',
      'mail.php',
      'response.php',
      'validate.php',
      'rate-limit.php',
      'schedule.php',
      'vin-decode.php',
      'tire-fitment.php',
      'google-reviews.php',
      'smart-account.php',
      'seo-config.php',
      'seo-head.php',
      'image-helpers.php',
      'sms.php',
      'member-kit-init.php',
      'member-translations.php',
      'engine-kit-init.php',
      'push.php',
      'email-fetcher.php',
      'auth-pages.php',
      'business-hours.php',
      'google-business.php',
      'google-calendar.php',
      'invoices.php',
      'loyalty.php',
      'parts.php',
      'referrals.php',
      'seo-lang.php',
      'sso-handler.php',
      'survey.php',
      'waitlist.php',
    ];

    it.each(requiredIncludes)('includes/%s exists', (file) => {
      const filepath = resolve(PUBLIC, 'includes', file);
      expect(existsSync(filepath), `Missing include: includes/${file}`).toBe(true);
    });
  });

  describe('API Directories', () => {
    const apiDirs = [
      { dir: 'api', label: 'api/ (root endpoints)' },
      { dir: 'api/admin', label: 'api/admin/' },
      { dir: 'api/member', label: 'api/member/' },
      { dir: 'api/commerce', label: 'api/commerce/' },
      { dir: 'api/form', label: 'api/form/' },
      { dir: 'api/auth', label: 'api/auth/' },
    ];

    it.each(apiDirs)('$label exists and contains PHP files', ({ dir, label }) => {
      const dirPath = resolve(PUBLIC, dir);
      expect(existsSync(dirPath), `Missing directory: ${label}`).toBe(true);

      const files = readdirSync(dirPath).filter((f) => f.endsWith('.php'));
      expect(files.length, `${label} has no PHP files`).toBeGreaterThan(0);
    });
  });

  describe('.env.example', () => {
    const requiredVars = [
      'DB_HOST',
      'DB_NAME',
      'DB_USER',
      'DB_PASSWORD',
      'APP_ENV',
      'APP_URL',
      'APP_SECRET',
      'SMTP_HOST',
      'SMTP_PORT',
      'SMTP_FROM',
      'SMTP_FROM_NAME',
      'CONTACT_EMAIL',
      'GOOGLE_CLIENT_ID',
      'GOOGLE_CLIENT_SECRET',
      'GOOGLE_REDIRECT_URI',
      'MEMBER_KIT_PATH',
      'FORM_KIT_PATH',
      'COMMERCE_KIT_PATH',
      'ENGINE_KIT_PATH',
      'VAPID_SUBJECT',
    ];

    let envContent = '';

    it('.env.example exists', () => {
      // .env.example lives inside public_html/ (deployed to server root)
      const envPath = resolve(PUBLIC, '.env.example');
      expect(existsSync(envPath), 'Missing .env.example in public_html/').toBe(true);
      envContent = readFileSync(envPath, 'utf-8');
    });

    it.each(requiredVars)('contains %s', (varName) => {
      const envPath = resolve(PUBLIC, '.env.example');
      if (!existsSync(envPath)) return; // skip if file missing (caught above)
      const content = readFileSync(envPath, 'utf-8');
      expect(content, `Missing env var: ${varName}`).toContain(varName);
    });
  });

  describe('Security Checks', () => {
    it('.htaccess blocks .env access', () => {
      // In local dev, .env may exist in public_html/ for convenience.
      // On server, .env is outside web root. Either way, .htaccess must block it.
      const htaccess = readFileSync(resolve(PUBLIC, '.htaccess'), 'utf-8');
      expect(
        htaccess,
        '.htaccess must block .env file access'
      ).toMatch(/\.env/);
    });

    it('deploy.sh exists and is executable', () => {
      const deployPath = resolve(PUBLIC, '..', 'deploy.sh');
      expect(existsSync(deployPath), 'Missing deploy.sh').toBe(true);

      const stats = statSync(deployPath);
      const isExecutable = (stats.mode & 0o111) !== 0;
      expect(isExecutable, 'deploy.sh is not executable').toBe(true);
    });
  });

  describe('Service Worker', () => {
    it('sw.js exists in public_html/', () => {
      const swPath = resolve(PUBLIC, 'sw.js');
      expect(existsSync(swPath), 'Missing sw.js').toBe(true);
    });

    it('sw.js contains CACHE_VERSION with a numeric value > 0', () => {
      const swPath = resolve(PUBLIC, 'sw.js');
      if (!existsSync(swPath)) return;

      const content = readFileSync(swPath, 'utf-8');
      const match = content.match(/CACHE_VERSION\s*=\s*'?(\d+)'?/);
      expect(match, 'sw.js does not contain CACHE_VERSION with a numeric value').not.toBeNull();
      expect(Number(match[1]), 'CACHE_VERSION must be > 0').toBeGreaterThan(0);
    });
  });
});
