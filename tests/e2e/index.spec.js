import { test, expect } from '@playwright/test';

test.describe('Public site - /simple/index.html', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/simple/index.html');
  });

  test('page loads with correct title', async ({ page }) => {
    await expect(page).toHaveTitle(/Oregon Tires/);
  });

  test('nav links are present', async ({ page }) => {
    const nav = page.locator('nav.hidden.md\\:flex');
    await expect(nav.locator('a[href="#home"]')).toBeVisible();
    await expect(nav.locator('a[href="#services"]')).toBeVisible();
    await expect(nav.locator('a[href="#about"]')).toBeVisible();
    await expect(nav.locator('a[href="#reviews"]')).toBeVisible();
    await expect(nav.locator('a[href="#contact"]')).toBeVisible();
  });

  test('phone number is visible', async ({ page }) => {
    await expect(page.locator('text=(503) 367-9714').first()).toBeVisible();
  });

  test('email shows oregontirespdx@gmail.com (not obfuscated)', async ({ page }) => {
    const emailLink = page.locator('a[href="mailto:oregontirespdx@gmail.com"]').first();
    await expect(emailLink).toBeVisible();
    await expect(emailLink).toHaveText('oregontirespdx@gmail.com');
  });

  test('no Cloudflare email obfuscation present', async ({ page }) => {
    const cfEmail = page.locator('.__cf_email__');
    await expect(cfEmail).toHaveCount(0);
  });

  test('language toggle button exists', async ({ page }) => {
    const toggle = page.locator('#lang-toggle');
    await expect(toggle).toBeVisible();
    await expect(toggle).toHaveText(/ES/);
  });

  test('language toggle switches to Spanish', async ({ page }) => {
    await page.locator('#lang-toggle').click();
    await expect(page.locator('#lang-toggle')).toHaveText(/EN/);
    // Check a translated element
    await expect(page.locator('[data-t="home"]').first()).toHaveText('Inicio');
  });

  test('language toggle switches back to English', async ({ page }) => {
    await page.locator('#lang-toggle').click();
    await page.locator('#lang-toggle').click();
    await expect(page.locator('#lang-toggle')).toHaveText(/ES/);
    await expect(page.locator('[data-t="home"]').first()).toHaveText('Home');
  });

  test('3 review cards render', async ({ page }) => {
    const reviewCards = page.locator('#reviews-grid > div');
    await expect(reviewCards).toHaveCount(3);
  });

  test('contact form fields are present', async ({ page }) => {
    await expect(page.locator('#contact-form input[name="firstName"]')).toBeVisible();
    await expect(page.locator('#contact-form input[name="lastName"]')).toBeVisible();
    await expect(page.locator('#contact-form input[name="phone"]')).toBeVisible();
    await expect(page.locator('#contact-form input[name="email"]')).toBeVisible();
    await expect(page.locator('#contact-form textarea[name="message"]')).toBeVisible();
    await expect(page.locator('#contact-form button[type="submit"]')).toBeVisible();
  });

  test('Google Maps iframe is present', async ({ page }) => {
    const iframe = page.locator('iframe[src*="google.com/maps"]');
    await expect(iframe).toBeAttached();
  });

  test('Schedule Service link points to /book-appointment', async ({ page }) => {
    const link = page.locator('a[href="/book-appointment"]').first();
    await expect(link).toBeVisible();
  });

  test('logo image loads from lovable-uploads', async ({ page }) => {
    const logo = page.locator('img[src*="lovable-uploads"][alt*="Oregon Tires"]');
    await expect(logo).toBeVisible();
  });
});
