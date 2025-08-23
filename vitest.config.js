import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    environment: 'jsdom',
    setupFiles: './vitest.setup.js',
    globals: true,
    include: [
      '__tests__/**/*.test.{js,jsx,ts,tsx}',
      'assets/ts/**/*.test.{ts,tsx}',
      'src/js/**/*.test.{js,jsx,ts,tsx}',
      'src/components/**/*.test.{js,jsx,ts,tsx}',
    ],
  },
  esbuild: {
    jsx: 'automatic',
  },
});
