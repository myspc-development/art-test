import { test, expect } from '@playwright/test';

const admin = {
  user: process.env.ADMIN_USER || 'admin',
  pass: process.env.ADMIN_PASS || 'password',
};

const base = process.env.BASE_URL || 'http://localhost:8000';

const loginPage = `${base}/wp-login.php`;

test('login success', async ({ page }) => {
  await page.goto(loginPage);
  await page.fill('#user_login', admin.user);
  await page.fill('#user_pass', admin.pass);
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/dashboard/);
});

test('login failure shows error', async ({ page }) => {
  await page.goto(loginPage);
  await page.fill('#user_login', 'invalid');
  await page.fill('#user_pass', 'wrong');
  await page.click('#wp-submit');
  await expect(page.locator('#login_error')).toBeVisible();
});
