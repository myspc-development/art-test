import { chromium, FullConfig } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Logs into the site for each role defined by E2E_*_USER environment variables
 * and saves the storage state to the e2e directory.
 */
export default async function globalSetup(config: FullConfig) {
  const baseUrl = process.env.BASE_URL || 'http://localhost:8000';
  const browser = await chromium.launch();

  const roleKeys = Object.keys(process.env).filter(
    (key) => key.startsWith('E2E_') && key.endsWith('_USER')
  );

  for (const key of roleKeys) {
    const role = key.slice('E2E_'.length, -'_USER'.length).toLowerCase();
    const username = process.env[key];
    const password =
      process.env[`E2E_${role.toUpperCase()}_PASS`] ||
      process.env[`E2E_${role.toUpperCase()}_PASSWORD`];

    if (!username || !password) {
      console.warn(`Missing credentials for role ${role}, skipping`);
      continue;
    }

    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(`${baseUrl}/wp-login.php`);
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    await Promise.all([
      page.click('#wp-submit'),
      page.waitForNavigation({ waitUntil: 'networkidle' }),
    ]);

    await context.storageState({
      path: path.join(__dirname, `.state-${role}.json`),
    });
    await context.close();
  }

  await browser.close();
}
