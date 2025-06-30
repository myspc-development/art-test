# ArtPulse Testing Strategy

This document outlines automated and manual testing practices used to ensure plugin quality.

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

