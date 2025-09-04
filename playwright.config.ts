import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: 'e2e',
  // Only run *.spec.ts or *.spec.js under e2e/
  testMatch: /.*\.spec\.(ts|js)/,
  // Explicitly ignore all other test dirs/patterns
  // (Jest tests live elsewhere)
  ignore: [
    '**/__tests__/**',
    '**/*.test.*',
    'assets/**',
    'src/**',
    'vendor/**',
    'node_modules/**'
  ],
  retries: 0,
  reporter: [
    ['list'],
    ['junit', { outputFile: 'build/playwright/junit.xml' }],
    ['html', { outputFolder: 'build/playwright/html', open: 'never' }]
  ],
  use: {
    baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8889',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
    screenshot: 'only-on-failure'
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  outputDir: 'build/playwright/out'
});
