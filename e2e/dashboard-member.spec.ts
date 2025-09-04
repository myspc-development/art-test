import { test, expect } from '@playwright/test';

// Use stored auth state for member user
test.use({ storageState: 'e2e/.state-member.json' });

// Ensure member can interact with dashboard actions without leaving page
// or triggering errors.
test('dashboard action keeps member on dashboard', async ({ page }) => {
  const errors: Error[] = [];
  page.on('pageerror', err => errors.push(err));

  await page.goto('/dashboard');
  const action = page.locator('[data-ap-action="favorite-toggle"], text=/RSVP/i').first();
  await expect(action).toBeVisible();
  await action.click();

  await expect(page).toHaveURL(/dashboard/);
  expect(errors).toHaveLength(0);
});
