# End-to-End Tests

## Prerequisites

- A WordPress instance running and accessible at the URL defined in `.env.e2e`.
- Test users exist in that instance matching the credentials in `.env.e2e` (admin, member, artist, organization accounts).

## Quick Start

1. Copy the example environment file:
   ```bash
   cp .env.e2e.example .env.e2e
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Install Playwright browsers:
   ```bash
   npm run e2e:install
   ```

## Running Tests

- `npm run e2e` – run the full E2E test suite in headless mode.
- `npm run e2e:headed` – run tests with the browser UI visible.
- `npm run e2e:ui` – launch the interactive Playwright test runner.
- `npm run e2e:report` – open the report from the last test run.
