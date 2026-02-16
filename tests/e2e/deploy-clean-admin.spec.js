import { test, expect } from '@playwright/test';

test.describe('deploy-clean admin — admin/index.html', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/');
  });

  test('page source has no /lovable-uploads/ references', async ({ page }) => {
    const html = await page.content();
    expect(html).not.toContain('/lovable-uploads/');
  });

  test('login logo loads from /assets/logo.png and is visible', async ({ page }) => {
    const logo = page.locator('#login-screen img[alt*="Oregon Tires"]');
    await expect(logo).toBeVisible();
    await expect(logo).toHaveAttribute('src', '/assets/logo.png');
    const naturalWidth = await logo.evaluate(img => img.naturalWidth);
    expect(naturalWidth).toBeGreaterThan(0);
  });

  test('page title is correct', async ({ page }) => {
    await expect(page).toHaveTitle(/Oregon Tires Admin/);
  });

  test('login form is functional', async ({ page }) => {
    await expect(page.locator('#login-email')).toBeVisible();
    await expect(page.locator('#login-password')).toBeVisible();
    await expect(page.locator('#login-btn')).toBeVisible();
    await expect(page.locator('#login-btn')).toHaveText('Sign In');
  });

  test('dashboard is hidden before auth', async ({ page }) => {
    await expect(page.locator('#admin-dashboard')).toBeHidden();
  });
});
