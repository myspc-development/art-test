# ArtPulse End-to-End Testing Guide

This guide explains how to run the automated browser tests powered by
[Cypress](https://www.cypress.io/). These tests simulate common user flows so you
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

Set environment variables for your WordPress user and then run Cypress:

```bash
CYPRESS_WP_USER=admin CYPRESS_WP_PASS=password npx cypress run
```

Or open the interactive runner:

```bash
npx cypress open
```

If you need seed data such as sample messages run:

```bash
wp db import data/message-seed.sql
```

## Notes

- The tests assume your site is reachable at `http://localhost:8000`. Set
  `CYPRESS_BASE_URL` to use a different address.
- Ensure your site has a page available at `/artists/sample-artist/` or update
  the tests accordingly.
- When developing new features, extend the Cypress suite with additional
  checks for monetization workflows such as requesting commissions or managing
  subscriptions.
