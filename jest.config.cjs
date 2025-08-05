module.exports = {
  // Use a browser-like environment so DOM APIs (like document.createElement) exist
  testEnvironment: 'jsdom',

  // Transform .js/.jsx/.ts/.tsx files with Babel
  transform: {
    '^.+\\.[jt]sx?$': 'babel-jest'
  },

  // Only run tests in your widgets folder (adjust the pattern as needed)
  testMatch: ['**/__tests__/widgets/**/*.test.[jt]s?(x)'],

  // Recognize these file extensions for modules
  moduleFileExtensions: ['js', 'jsx', 'ts', 'tsx'],

  // After setting up the environment, load these matchers
  setupFilesAfterEnv: ['@testing-library/jest-dom/extend-expect']
};
