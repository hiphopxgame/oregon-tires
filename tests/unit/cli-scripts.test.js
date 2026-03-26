import { describe, it, expect } from 'vitest';
import { readFileSync, readdirSync } from 'fs';
import { resolve } from 'path';

const CLI_DIR = resolve(import.meta.dirname, '../../public_html/cli');

const phpFiles = readdirSync(CLI_DIR).filter(
  (f) => f.endsWith('.php') && !f.endsWith('.sql')
);

// Scripts that load env via vendor/autoload.php + Dotenv directly (legacy pattern)
// instead of bootstrap.php. Both patterns work — bootstrap.php just wraps the same logic.
// Scripts that don't need bootstrap.php or autoload at all
const SELF_CONTAINED_SCRIPTS = new Set([
  'indexnow-submit.php',       // parses .env manually, only needs INDEXNOW_KEY — no DB, no autoload
]);

const INLINE_BOOTSTRAP_SCRIPTS = new Set([
  'list-admins.php',           // uses vendor/autoload.php + db.php
  'send-admin-invite.php',     // uses vendor/autoload.php + PHPMailer
  'send-platform-overview.php',// uses vendor/autoload.php + PHPMailer
  'send-welcome-emails.php',   // uses vendor/autoload.php + PHPMailer
  'sync-admins-to-members.php',// uses vendor/autoload.php + Dotenv
  'test-email-account.php',    // uses vendor/autoload.php + Dotenv
  'test-smtp-debug.php',       // uses vendor/autoload.php + PHPMailer
]);

describe('CLI Script Standards', () => {
  describe.each(phpFiles)('%s', (filename) => {
    const filepath = resolve(CLI_DIR, filename);
    const content = readFileSync(filepath, 'utf-8');

    it('declares strict_types=1', () => {
      expect(
        content,
        `${filename} must contain declare(strict_types=1)`
      ).toContain('declare(strict_types=1)');
    });

    it('loads PHP dependencies', () => {
      if (SELF_CONTAINED_SCRIPTS.has(filename)) return; // self-contained, no deps needed
      // Must use either bootstrap.php OR vendor/autoload.php
      const usesBootstrap = content.includes('bootstrap.php');
      const usesAutoload = content.includes('vendor/autoload.php');
      expect(
        usesBootstrap || usesAutoload,
        `${filename} must require bootstrap.php or vendor/autoload.php`
      ).toBe(true);
    });

    if (!INLINE_BOOTSTRAP_SCRIPTS.has(filename) && !SELF_CONTAINED_SCRIPTS.has(filename)) {
      it('uses correct relative path to bootstrap.php', () => {
        const correctPath = "__DIR__ . '/../includes/bootstrap.php'";
        const wrongPath = '/../public_html/includes/';

        expect(
          content,
          `${filename} must use ${correctPath}`
        ).toContain(correctPath);

        expect(
          content,
          `${filename} must NOT use ${wrongPath} (cli/ is already inside public_html/)`
        ).not.toContain(wrongPath);
      });
    }
  });
});
