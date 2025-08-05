import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    environment: 'jsdom',
    setupFiles: './vitest.setup.js',
    globals: true,
    include: ['__tests__/widgets/**/*.test.{js,jsx}'],
  },
  esbuild: {
    jsx: 'automatic',
  },
});
