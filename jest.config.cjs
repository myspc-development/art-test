module.exports = {
  // Use a browser-like environment so DOM APIs (like localStorage) exist
  testEnvironment: 'jsdom',

  // Give jsdom a non-opaque origin so localStorage is available
  testEnvironmentOptions: {
    url: 'http://localhost/'
  },

  // Transform .js/.jsx/.ts/.tsx files with Babel
  transform: {
    '^.+\\.[jt]sx?$': 'babel-jest'
  },

  // Only run tests in your widgets folder (adjust pattern as needed)
  testMatch: ['**/__tests__/widgets/**/*.test.[jt]s?(x)'],

  // Recognize these extensions
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx'],

  // Load this module to add jest-dom matchers
  setupFilesAfterEnv: ['@testing-library/jest-dom']
};
