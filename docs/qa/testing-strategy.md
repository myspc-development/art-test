---
title: ArtPulse Testing Strategy
category: qa
role: qa
last_updated: 2025-07-20
status: draft
---

# ArtPulse Testing Strategy

This document outlines automated and manual testing practices used to ensure plugin quality.

## Prerequisites

Run `composer install` and `npm install` once your environment has network access so that PHP and Node
dependencies are available. The `scripts/setup-env.sh` helper downloads WordPress into the `wordpress/`
directory and installs the `wp-phpunit` library under `vendor/wp-phpunit/wp-phpunit`. PHPUnit will look for
WordPress using the `WP_CORE_DIR` environment variable which defaults to `./wordpress`. If Chrome cannot be
downloaded for Puppeteer based tests, set `PUPPETEER_SKIP_DOWNLOAD=1` before running `npm install`.

## Automated Tests

- **PHPUnit** tests cover core classes and REST endpoints.
- Run all tests with:
  ```bash
  composer test
  ```
- Coding standards are checked with PHP_CodeSniffer:
  ```bash
  composer sniff
  ```
- JavaScript functionality can be tested with Jest or Cypress.

## Manual Tests

- Verify features in multiple browsers on desktop and mobile.
- Confirm keyboard navigation and screen reader labels across the dashboard.
- Perform user acceptance testing for major workflows such as creating events and managing RSVPs.

## Continuous Integration

Automated tests are executed in the CI pipeline on every pull request so regressions are caught early.
