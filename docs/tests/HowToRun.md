# How to Run Tests

This project includes PHP, JavaScript, and end-to-end test suites. The commands
below assume dependencies have been installed via `composer install` and
`npm install`.

## PHP

| Suite | Command |
| ----- | ------- |
| WordPress integration (Rest & Frontend) | `composer run test:php:wp` |
| Unit | `composer run test:php:unit` |

Test results and coverage reports are written to the `build/` directory.

## JavaScript

Run the Jest suite with coverage and JUnit output:

```bash
npm run test:js
```

For a quiet run in CI use:

```bash
npm run test:js:ci
```

Coverage files are emitted under `coverage/` and JUnit XML under `build/`.

## End-to-End

Playwright tests can be executed with:

```bash
npm run test:e2e
```

Reports are stored in `build/e2e/`.

## Summary

After running the suites, aggregate their results and coverage metrics with:

```bash
npm run test:summary
```

The combined report is written to `build/test-summary.json`.

## Continuous Integration

The GitHub Actions workflow `.github/workflows/tests.yml` runs all of the above
commands on every push and pull request, uploading the contents of `build/` and
any HTML reports as artifacts.

