import { test, expect } from '@playwright/test';

test.describe('Admin dashboard - /simple/admin.html', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/simple/admin.html');
  });

  test('page loads with correct title', async ({ page }) => {
    await expect(page).toHaveTitle(/Oregon Tires Admin/);
  });

  test('login screen is visible', async ({ page }) => {
    await expect(page.locator('#login-screen')).toBeVisible();
  });

  test('login form has email and password fields', async ({ page }) => {
    await expect(page.locator('#login-email')).toBeVisible();
    await expect(page.locator('#login-password')).toBeVisible();
  });

  test('login form has submit button', async ({ page }) => {
    await expect(page.locator('#login-btn')).toBeVisible();
    await expect(page.locator('#login-btn')).toHaveText('Sign In');
  });

  test('dashboard is hidden before auth', async ({ page }) => {
    await expect(page.locator('#admin-dashboard')).toBeHidden();
  });

  test('back-to-website link points to /simple/', async ({ page }) => {
    const link = page.locator('a[href="/simple/"]').first();
    await expect(link).toBeVisible();
    await expect(link).toHaveText(/Back to website/);
  });

  test('logo loads from lovable-uploads', async ({ page }) => {
    const logo = page.locator('#login-screen img[src*="lovable-uploads"]');
    await expect(logo).toBeVisible();
  });

  test('Admin Dashboard heading is visible', async ({ page }) => {
    await expect(page.locator('#login-screen h1')).toHaveText('Admin Dashboard');
  });
});
