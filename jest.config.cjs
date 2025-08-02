module.exports = {
  testEnvironment: 'jsdom',
  testEnvironmentOptions: { url: 'http://localhost/' },
  roots: ['<rootDir>/__tests__', '<rootDir>/assets/js', '<rootDir>/js'],
  transform: { '^.+\\.[jt]sx?$': 'babel-jest' },
  setupFiles: ['<rootDir>/jest.pre.js'],
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js']
};
