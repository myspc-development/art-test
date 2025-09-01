import { defineConfig } from 'cypress';

export default defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:8080',
    env: {
      ARTIST_USER: process.env.CYPRESS_ARTIST_USER || 'artist',
      PUBLIC_USER: process.env.CYPRESS_PUBLIC_USER || 'public_user',
      MEMBER_USER: process.env.CYPRESS_MEMBER_USER || 'member',
      MEMBER_PASS: process.env.CYPRESS_MEMBER_PASS || 'password'
    }
  }
});
