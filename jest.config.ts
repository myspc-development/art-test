import type { Config } from 'jest';

const config: Config = {
  testEnvironment: 'jsdom',
  roots: [
    '<rootDir>/assets/ts',
    '<rootDir>/assets/js',
    '<rootDir>/src/js',
    '<rootDir>/src/components',
    '<rootDir>/tests',
    '<rootDir>/dashboard',
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
    '^.+\\.(ts|tsx)$': ['ts-jest', { tsconfig: 'tsconfig.jest.json' }],
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

  // Coverage
  collectCoverage: true,
  coverageProvider: 'v8',
  coverageDirectory: 'coverage',
  coverageReporters: ['json', 'lcov', 'text'],
  collectCoverageFrom: [
    'assets/ts/**/*.{ts,tsx}',
    'src/**/*.{ts,tsx,js,jsx}',
    '!src/js/dashboard.js',
    '!src/components/ReactForm.js',
    '!assets/ts/dashboard/components/SortableCard.tsx',
    '!assets/ts/dashboard/widgets/UpcomingEvents.tsx',
    '!assets/ts/dashboard/RoleDashboard.tsx',
    '!**/__tests__/**',
    '!**/__mocks__/**',
    '!**/*.d.ts'
  ],
  coverageThreshold: {
    global: { branches: 80, functions: 80, lines: 80, statements: 80 },
    'assets/ts/dashboard/widgets/**': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80,
    },
    'assets/ts/dashboard/**': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80,
    },
  },
};

export default config;
