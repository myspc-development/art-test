import type { Config } from 'jest';

const config: Config = {
  testEnvironment: 'jsdom',
  roots: [
    '<rootDir>/assets/ts',
    '<rootDir>/assets/js',
    '<rootDir>/src/js',
    '<rootDir>/src/components',
    '<rootDir>/tests',
  ],
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'mjs', 'json'],
  setupFilesAfterEnv: ['<rootDir>/jest.setup.ts'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/$1',
    '\\.(css|less|sass|scss)$': 'identity-obj-proxy',
    '^lucide-react$': '<rootDir>/tests/js/__mocks__/lucide-react.tsx',
    '^framer-motion$': '<rootDir>/tests/js/__mocks__/framer-motion.ts',
    '^recharts$': '<rootDir>/tests/js/__mocks__/recharts.tsx',
    '^@/components/ui/(.*)$': '<rootDir>/tests/js/__mocks__/shadcn-ui.tsx'
  },
  transform: {
    '^.+\\.(ts|tsx)$': ['ts-jest', { tsconfig: 'tsconfig.jest.json', isolatedModules: true }],
    '^.+\\.[cm]?jsx?$': 'babel-jest',
  },
  testRegex: '.*\\.test\\.(ts|tsx|js|jsx)$',
  transformIgnorePatterns: ['/node_modules/'],
    testPathIgnorePatterns: [
      '<rootDir>/assets/js/components/__tests__/CommentThread.test.js',
      '<rootDir>/assets/js/components/__tests__/ReportDialog.test.js',
      '<rootDir>/assets/js/components/__tests__/WidgetSettingsForm.test.js',
      '/assets/ts/.*\\.test\\.js$',
      '<rootDir>/tests/playwright',
    ],
    collectCoverage: true,
    coverageDirectory: 'coverage',
    coverageReporters: ['json', 'lcov', 'text'],
    coverageThreshold: {
      global: { branches: 50, functions: 60, lines: 65, statements: 65 },
      './assets/js/**/widgets/**': {
        branches: 75,
        functions: 80,
        lines: 80,
        statements: 80,
      },
      './assets/ts/**/dashboard/**': {
        branches: 70,
        functions: 75,
        lines: 78,
        statements: 78,
      },
    },
  };

export default config;
