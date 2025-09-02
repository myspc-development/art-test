import type { Config } from 'jest';

const config: Config = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  roots: [
    '<rootDir>/assets/ts',
    '<rootDir>/assets/js',
    '<rootDir>/src/js',
    '<rootDir>/src/components',
  ],
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx'],
  setupFilesAfterEnv: ['<rootDir>/tests/js/setupTests.ts'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/$1',
    '\\.(css|less|sass|scss)$': 'identity-obj-proxy',
    '^lucide-react$': '<rootDir>/tests/js/__mocks__/lucide-react.tsx',
    '^framer-motion$': '<rootDir>/tests/js/__mocks__/framer-motion.ts',
    '^recharts$': '<rootDir>/tests/js/__mocks__/recharts.tsx',
    '^@/components/ui/(.*)$': '<rootDir>/tests/js/__mocks__/shadcn-ui.tsx'
  },
  transform: {
    '^.+\\.(ts|tsx|js|jsx)$': ['ts-jest', { tsconfig: '<rootDir>/tsconfig.json' }],
  },
  testRegex: '.*\\.test\\.(ts|tsx|js|jsx)$',
  testPathIgnorePatterns: [
    '<rootDir>/assets/js/components/__tests__/CommentThread.test.js',
    '<rootDir>/assets/js/components/__tests__/ReportDialog.test.js',
    '<rootDir>/assets/js/components/__tests__/WidgetSettingsForm.test.js',
  ],
  collectCoverage: true,
  coverageDirectory: 'coverage',
  coverageReporters: ['json', 'lcov', 'text'],
};

export default config;
