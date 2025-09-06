---
title: How to Run Tests
category: qa
role: qa
last_updated: 2025-08-03
status: draft
---

# How to Run Tests

## Setup
```bash
composer install
npm ci
```

## Linting and Type Checks
```bash
vendor/bin/phpcs
npm run lint:js
npm run typecheck
```

## Codex Checks
The Codex pipeline includes a widget guard checker that prevents widgets from rendering during dashboard builder previews.
Run it in check mode via:
```bash
npm run codex:checks
```
Use the `--fix` flag to insert missing guards automatically:
```bash
python3 scripts/widget-preview-guard-check.py --fix
```

## Test Commands
```bash
npm run test:js -- --coverage
npm run test:php
npm run test:e2e
```

## Troubleshooting
- Reset the WordPress test database if migrations fail.
- Use headless mode when Cypress cannot open a browser.
- Configure `XDEBUG_MODE=coverage` for coverage reports.
