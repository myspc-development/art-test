// jest.config.cjs
module.exports = {
  // Use the jsdom environment that brings in localStorage, sessionStorage, etc.
  testEnvironment: 'jest-environment-jsdom-global',

  // Give jsdom a non-opaque origin so URL APIs work
  testEnvironmentOptions: {
    url: 'http://localhost/'
  },

  // Transform JS/TS and JSX/TSX through Babel
  transform: {
    '^.+\\.[jt]sx?$': 'babel-jest'
  },

  // Only run your widget tests
  testMatch: ['**/__tests__/widgets/**/*.test.[jt]s?(x)'],

  // Recognize these extensions
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx'],

  // Add jest-dom matchers (optional, if you still need them)
  setupFilesAfterEnv: ['@testing-library/jest-dom']
};
