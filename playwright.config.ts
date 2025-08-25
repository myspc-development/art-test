import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/playwright',
  reporter: [
    ['html', { open: 'never' }],
    ['list']
  ],
  expect: {
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.001,
    },
  },
});
