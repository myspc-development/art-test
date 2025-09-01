const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');

const baseUrl = process.env.BASE_URL || 'http://localhost:8000';
const username = process.env.WP_USER || 'admin';
const password = process.env.WP_PASS || 'password';

// Start WordPress once for the entire suite.
test.beforeAll(() => {
    execSync('docker compose -f docker-compose.yml.example up -d --wait', {
        stdio: 'inherit',
    });
});

// Reset dashboard state before each test without rebuilding containers.
test.beforeEach(() => {
    execSync(
        'docker compose -f docker-compose.yml.example exec -T wordpress wp ap reset-layout 1',
        { stdio: 'inherit' }
    );
});

// Stop and clean up containers when the suite finishes.
test.afterAll(() => {
    execSync('docker compose -f docker-compose.yml.example down -v', {
        stdio: 'inherit',
    });
});

test('user can customize dashboard layout and widgets', async ({ page }) => {
    await page.goto(`${baseUrl}/wp-login.php`);
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#wp-submit')
    ]);

    await page.goto(`${baseUrl}/dashboard`);

    // Reorder widgets by dragging Messages before Favorites
    await page.dragAndDrop('#messages', '#favorites');

    // Switch layout from "2-column" to "3-column"
    await page.click('[data-test-id="layout-switcher"]');
    await page.click('text=3-column');
    await expect(page.locator('.dashboard-grid')).toHaveAttribute('data-columns', '3');

    // Open widget settings, change a value, and save
    await page.click('#messages .widget-settings');
    const modal = page.locator('.widget-settings-modal');
    await expect(modal).toBeVisible();
    await modal.fill('input[name="title"]', 'Updated Messages');
    await modal.click('button:has-text("Save")');
    await expect(modal).toBeHidden();
    await expect(page.locator('#messages .widget-title')).toHaveText('Updated Messages');

    // Verify layout behavior on mobile viewport
    await page.setViewportSize({ width: 480, height: 800 });
    await expect(page.locator('.dashboard-grid')).toHaveAttribute('data-columns', '1');
});
