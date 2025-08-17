import type { Config } from 'jest';

const config: Config = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/assets/ts'],
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
    '^.+\\.(ts|tsx)$': ['ts-jest', { tsconfig: '<rootDir>/tsconfig.json' }]
  },
  testRegex: '.*\\.test\\.(ts|tsx)$'
};

export default config;
