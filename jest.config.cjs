// jest.config.cjs
module.exports = {
  // Use the standard jsdom environment
  testEnvironment: 'jsdom',

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
  // Run additional setup after the environment is ready
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js']
};
