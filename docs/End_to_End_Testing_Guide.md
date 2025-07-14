# ArtPulse End-to-End Testing Guide

This guide explains how to run the automated browser tests powered by
[Puppeteer](https://pptr.dev/). These tests simulate common user flows so you
can verify that the plugin works correctly across updates.

## Prerequisites

- Node.js 18 or newer
- A local WordPress site running at `http://localhost:8000`
- The ArtPulse plugin activated on that site
- Test credentials for an account able to log in

Install Node dependencies in the plugin directory:

```bash
npm install
```

## Running the Tests

Set environment variables for your WordPress user and then run the test script:

```bash
WP_USER=admin WP_PASS=password npm run test:e2e
```

The script launches a headless browser, logs in and visits a sample artist
profile. It verifies that the tip jar modal is accessible. Logs are printed to
stdout and the browser closes automatically when the test finishes.

## Notes

- The tests assume your site is reachable at `http://localhost:8000`. Set the
  `BASE_URL` environment variable to use a different address.
- Ensure your site has a page available at `/artists/sample-artist/` or update
  the test script accordingly.
- When developing new features, extend the Puppeteer script with additional
  checks for monetization workflows such as requesting commissions or managing
  subscriptions.
