import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/e2e',
  testMatch: ['**/*.spec.ts', '**/*.spec.tsx', '**/*.spec.js'],
  testIgnore: ['cypress/**', 'assets/**', 'src/**', 'node_modules/**'],
  fullyParallel: true,
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    headless: true,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  reporter: [
    ['list'],
    ['html', { outputFolder: 'build/e2e/html', open: 'never' }],
    ['junit', { outputFile: 'build/e2e/junit.xml' }]
  ],
  outputDir: 'build/e2e/artifacts'
});
