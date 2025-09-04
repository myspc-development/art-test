import { test, expect } from '@playwright/test';

const BASE_DASHBOARD_PATH = process.env.BASE_DASHBOARD_PATH || '/dashboard';
const widgetLocator = '[data-slug]';

test.use({ storageState: 'e2e/.state-admin.json' });

test('admin dashboard displays widgets', async ({ page }) => {
  await page.goto(BASE_DASHBOARD_PATH);
  const widgets = page.locator(widgetLocator);
  await expect(widgets.first()).toBeVisible();
  const content = await page.content();
  expect(
    content.includes('widget_admin_guide') || content.includes('widget_site_stats')
  ).toBe(true);
});
