module.exports = {
    testEnvironment: 'jsdom',
    testEnvironmentOptions: { url: 'http://localhost/' },
    roots: ['<rootDir>/assets/js', '<rootDir>/js'],
    setupFiles: ['<rootDir>/jest.pre.js'],
    setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
};
