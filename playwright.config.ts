import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/playwright',
  reporter: [
    ['html', { open: 'never', outputFolder: 'build/e2e/playwright-report' }],
    ['list'],
    ['junit', { outputFile: 'build/e2e/playwright.xml' }]
  ],
  expect: {
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.001,
    },
  },
});
