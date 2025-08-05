// jest.config.cjs
module.exports = {
  // Use jsdom so DOM APIs exist
  testEnvironment: 'jsdom',

  // Legacy option for older Jest versions
  testURL: 'http://localhost/',

  // Modern option for jsdom URL
  testEnvironmentOptions: {
    url: 'http://localhost/'
  },

  // Before each test file, run this to patch/mimic localStorage if needed
  setupFiles: ['<rootDir>/jest.setup.js'],

  // After env is ready, load jest-dom matchers
  setupFilesAfterEnv: ['@testing-library/jest-dom'],

  // Transform files via babel-jest
  transform: {
    '^.+\\.[jt]sx?$': 'babel-jest'
  },

  // Only run your widget tests
  testMatch: ['**/__tests__/widgets/**/*.test.[jt]s?(x)'],

  // Recognized extensions
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx']
};
