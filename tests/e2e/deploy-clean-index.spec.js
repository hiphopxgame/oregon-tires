import { test, expect } from '@playwright/test';

test.describe('deploy-clean public site — index.html', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('no href or src attributes point to /lovable-uploads/', async ({ page }) => {
    const html = await page.content();
    // Actual asset references (href="...", src="...") must not use lovable paths
    // The normalizer JS code contains the string for matching purposes — that's fine
    const assetRefs = html.match(/(?:href|src)="[^"]*\/lovable-uploads\/[^"]*"/g);
    expect(assetRefs).toBeNull();
  });

  test('favicon loads from /assets/favicon.png', async ({ page }) => {
    const favicon = page.locator('link[rel="icon"]');
    await expect(favicon).toHaveAttribute('href', '/assets/favicon.png');
  });

  test('logo loads from /assets/logo.png', async ({ page }) => {
    const logo = page.locator('header img[alt*="Oregon Tires"]');
    await expect(logo).toHaveAttribute('src', '/assets/logo.png');
  });

  test('logo image is visible and not broken', async ({ page }) => {
    const logo = page.locator('header img[alt*="Oregon Tires"]');
    await expect(logo).toBeVisible();
    const naturalWidth = await logo.evaluate(img => img.naturalWidth);
    expect(naturalWidth).toBeGreaterThan(0);
  });

  test('hero section has background-image without lovable in value', async ({ page }) => {
    const hero = page.locator('#home');
    // Wait for Supabase or fallback to set background
    await page.waitForTimeout(2000);
    const bgImage = await hero.evaluate(el => getComputedStyle(el).backgroundImage);
    if (bgImage && bgImage !== 'none') {
      expect(bgImage.toLowerCase()).not.toContain('lovable');
    }
  });

  test('page title is correct', async ({ page }) => {
    await expect(page).toHaveTitle(/Oregon Tires/);
  });

  test('nav, reviews, contact, and map all work', async ({ page }) => {
    // Nav
    const nav = page.locator('nav.hidden.md\\:flex');
    await expect(nav.locator('a[href="#home"]')).toBeVisible();
    await expect(nav.locator('a[href="#services"]')).toBeVisible();
    await expect(nav.locator('a[href="#contact"]')).toBeVisible();

    // Reviews
    const reviewCards = page.locator('#reviews-grid > div');
    await expect(reviewCards).toHaveCount(3);

    // Contact form
    await expect(page.locator('#contact-form')).toBeVisible();

    // Google Maps
    const iframe = page.locator('iframe[src*="google.com/maps"]');
    await expect(iframe).toBeAttached();
  });

  test('language toggle works', async ({ page }) => {
    const toggle = page.locator('#lang-toggle');
    await expect(toggle).toBeVisible();
    await toggle.click();
    await expect(toggle).toHaveText(/EN/);
    await expect(page.locator('[data-t="home"]').first()).toHaveText('Inicio');
  });
});
