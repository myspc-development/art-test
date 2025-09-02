import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/playwright',
  reporter: [
    ['junit', { outputFile: 'build/e2e/junit.xml' }],
    ['html', { open: 'never', outputFolder: 'build/e2e/html' }]
  ],
  expect: {
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.001,
    },
  },
});
