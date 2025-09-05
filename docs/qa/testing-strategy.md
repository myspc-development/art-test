---
title: ArtPulse Testing Strategy
category: qa
role: qa
last_updated: 2025-07-20
status: complete
---

# ArtPulse Testing Strategy

This document outlines automated and manual testing practices used to ensure plugin quality.

## Prerequisites

Ensure the project is configured as described in [../development-setup.md](../development-setup.md) so PHP and Node dependencies are installed and the WordPress test library is available. If Chrome cannot be downloaded for Puppeteer-based tests, set `PUPPETEER_SKIP_DOWNLOAD=1` before running `npm install`.

## Automated Tests

- **PHPUnit** tests cover core classes and REST endpoints.
- Run all tests with:
  ```bash
  npm test
  ```
- Individual suites can be executed with `npm run test:php`, `npm run test:js`,
  or `npm run test:e2e` if you only need a subset.
- Coding standards are checked with PHP_CodeSniffer:
  ```bash
  composer sniff
  ```
- JavaScript functionality can be tested with Jest or Cypress.

## Manual Tests

- Verify features in multiple browsers on desktop and mobile.
- Confirm keyboard navigation and screen reader labels across the dashboard.
- Perform user acceptance testing for major workflows such as creating events and managing RSVPs.
- Follow [frontend-dashboard-testing.md](frontend-dashboard-testing.md) for role-specific dashboard verification.

## Continuous Integration

Automated tests are executed in the CI pipeline on every pull request so regressions are caught early.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
