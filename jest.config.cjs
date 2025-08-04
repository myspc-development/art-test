module.exports = {
  testEnvironment: 'node',
  transform: { '^.+\\.[jt]sx?$': 'babel-jest' },
  testMatch: ['**/__tests__/widgets/**/*.test.[jt]s?(x)']
};
