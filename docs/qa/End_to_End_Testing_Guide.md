---
title: End-to-End Testing Guide
category: qa
role: qa
last_updated: 2025-07-20
status: complete
---

# ArtPulse End-to-End Testing Guide

This guide explains how to run the automated browser tests powered by
[Cypress](https://www.cypress.io/). These tests simulate common user flows so you
can verify that the plugin works correctly across updates.

## Prerequisites

- Node.js 18 or newer
- A local WordPress site running at `http://localhost:8000`
- The ArtPulse plugin activated on that site
- Test credentials for an account able to log in

Ensure Node dependencies are installed (`npm install`). See [../development-setup.md](../development-setup.md) for full environment instructions.

## Running the Tests

The suite expects credentials for several sample accounts. You can override
them with environment variables or rely on the defaults defined in
`cypress.config.ts`:

- `CYPRESS_ARTIST_USER` – artist account username (default `artist`)
- `CYPRESS_PUBLIC_USER` – public user without dashboard access (default `public_user`)
- `CYPRESS_MEMBER_USER` – member account username (default `member`)
- `CYPRESS_MEMBER_PASS` – member account password (default `password`)

Run all specs headlessly:

```bash
npm run cypress:run
```

Or open the interactive runner:

```bash
npx cypress open
```

If you need seed data such as sample messages run:

```bash
wp db import data/message-seed.sql
```

### Puppeteer Monetization Workflow

The repository includes a simple Puppeteer script that validates the
monetization interface. Install Node dependencies first so Puppeteer is
available:

```bash
npm install
```

Run the test by providing your WordPress credentials and optional base URL:

```bash
BASE_URL=http://localhost:8000 \
WP_USER=admin \
WP_PASS=password \
npm run test:e2e
```

The script launches the browser in headless mode with a 10 second default
timeout. Adjust these options in `tests/e2e/monetization-workflow.js` if your
environment requires different settings.

## Notes

- The tests assume your site is reachable at `http://localhost:8000`. Set
  `CYPRESS_BASE_URL` to use a different address.
- Ensure your site has a page available at `/artists/sample-artist/` or update
  the tests accordingly.
- When developing new features, extend the Cypress suite with additional
  checks for monetization workflows such as requesting commissions or managing
  subscriptions.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
