import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
const baseUrl = process.env.BASE_URL || 'http://localhost:8000';

test('customizes dashboard layout', async ({ page }) => {
  await page.goto(`${baseUrl}/wp-login.php`);
  // … test steps …
  await expect(page.locator('#dashboard')).toBeVisible();
});
