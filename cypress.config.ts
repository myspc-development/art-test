import { defineConfig } from 'cypress';

export default defineConfig({
  reporter: 'cypress-multi-reporters',
  reporterOptions: {
    reporterEnabled: 'spec, mocha-junit-reporter',
    mochaJunitReporterReporterOptions: {
      mochaFile: 'build/e2e/cypress.xml'
    }
  },
  videosFolder: 'build/e2e/videos',
  screenshotsFolder: 'build/e2e/screenshots',
  trashAssetsBeforeRuns: false,
  video: true,
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:8080',
    screenshotOnRunFailure: true,
    env: {
      ARTIST_USER: process.env.CYPRESS_ARTIST_USER || 'artist',
      PUBLIC_USER: process.env.CYPRESS_PUBLIC_USER || 'public_user',
      MEMBER_USER: process.env.CYPRESS_MEMBER_USER || 'member',
      MEMBER_PASS: process.env.CYPRESS_MEMBER_PASS || 'password'
    }
  }
});
